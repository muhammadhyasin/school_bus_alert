<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\AttendanceLog;
use App\Models\Bus;
use App\Models\FeeSchedule;
use App\Models\LocationCard;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class TeacherController extends Controller
{
    protected $notificationController;

    public function __construct(NotificationController $notificationController)
    {
        $this->notificationController = $notificationController;
    }
    
    // Check for last RFID scan during student registration
    public function checkLastRFID()
    {
        $lastScan = Cache::get('last_rfid_scan');
        
        if ($lastScan) {
            // Check if RFID is already registered
            if (Student::where('rfid_number', $lastScan)->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This RFID is already registered'
                ], 400);
            }

            Cache::forget('last_rfid_scan');
            
            return response()->json([
                'success' => true,
                'rfid_number' => $lastScan
            ]);
        }
        
        return response()->json(['message' => 'No new RFID scan'], 404);
    }

    // Start RFID scanning mode for new student
    public function startAddingStudent()
    {
        Cache::put('adding_student_mode', true, now()->addMinutes(5));
        return response()->json([
            'success' => true,
            'message' => 'Ready to scan RFID'
        ]);
    }

    // Cancel RFID scanning mode
    public function cancelAddingStudent()
    {
        Cache::forget('adding_student_mode');
        Cache::forget('last_rfid_scan');
        return response()->json([
            'success' => true,
            'message' => 'Scanning cancelled'
        ]);
    }

    public function handleStudentAttendance($rfidNumber, $busId)
    {
        $locationCard = LocationCard::where('rfid_number', $rfidNumber)->first();
        if ($locationCard) {
            return $this->handleLocationCard($locationCard, $busId);
        }

        $student = Student::where('rfid_number', $rfidNumber)->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Unregistered RFID card'
            ], 404);
        }

        $session = Cache::get('session_mode', 'morning');
        $bus = Bus::find($busId);

        $existingLog = AttendanceLog::where('student_id', $student->id)
            ->where('bus_id', $busId)
            ->where('session', $session)
            ->whereDate('created_at', today())
            ->latest()
            ->first();

        if (!$existingLog) {
            $log = AttendanceLog::create([
                'student_id' => $student->id,
                'bus_id' => $busId,
                'scan_type' => 'entry',
                'session' => $session,
                'scan_time' => now()
            ]);

            $this->notificationController->notifyEntry($student, $bus, $session);

            return response()->json([
                'success' => true,
                'message' => "Student {$session} entry recorded",
                'student' => $student->name,
                'scan_type' => 'entry',
                'session' => $session
            ]);
        } else if ($existingLog->scan_type === 'entry') {
            // Record exit
            $log = AttendanceLog::create([
                'student_id' => $student->id,
                'bus_id' => $busId,
                'scan_type' => 'exit',
                'session' => $session,
                'scan_time' => now()
            ]);

            $this->notificationController->notifyExit($student, $bus, $session);


            return response()->json([
                'success' => true,
                'message' => "Student {$session} exit recorded",
                'student' => $student->name,
                'scan_type' => 'exit',
                'session' => $session
            ]);
        }
    }

    private function handleLocationCard($locationCard, $busId)
    {
        $session = Cache::get('session_mode', 'morning');
        $bus = Bus::find($busId);
        $isSchoolCard = $locationCard->is_school_card; // Add this field to LocationCard model

        if ($session === 'morning' && !$isSchoolCard) {
            // Morning session: Check for students who haven't entered the bus
            $studentsAtLocation = Student::where('exit_location_id', $locationCard->id)
                ->whereDoesntHave('attendanceLogs', function ($query) use ($session) {
                    $query->where('session', $session)
                        ->where('scan_type', 'entry')
                        ->whereDate('created_at', today());
                })
                ->get();

            foreach ($studentsAtLocation as $student) {
                // Notify about missed pickup
                $this->notificationController->notifyMissedPickup($student, $locationCard->location_name);
            }

            return response()->json([
                'message' => "Location card scanned (morning session)",
                'location' => $locationCard->location_name,
                'missed_pickups' => $studentsAtLocation->count()
            ]);
        }

        if ($session === 'evening' && $isSchoolCard) {
            // Evening session: Check for students who attended in morning but haven't entered the bus
            $studentsNotInBus = Student::whereHas('attendanceLogs', function ($query) {
                $query->where('session', 'morning')
                    ->whereDate('created_at', today());
            })
            ->whereDoesntHave('attendanceLogs', function ($query) {
                $query->where('session', 'evening')
                    ->where('scan_type', 'entry')
                    ->whereDate('created_at', today());
            })
            ->get();

            foreach ($studentsNotInBus as $student) {
                // Notify about missed return trip
                $this->notificationController->notifyMissedReturn($student);
            }

            return response()->json([
                'message' => "School card scanned (evening session)",
                'missed_returns' => $studentsNotInBus->count()
            ]);
        }

        // Original logic for handling exit locations
        $studentsToExit = Student::where('exit_location_id', $locationCard->id)
            ->whereHas('attendanceLogs', function ($query) use ($busId, $session) {
                $query->where('bus_id', $busId)
                    ->where('session', $session)
                    ->where('scan_type', 'entry')
                    ->whereDate('created_at', today())
                    ->whereNotExists(function ($subquery) use ($session) {
                        $subquery->from('attendance_logs as exits')
                            ->whereColumn('exits.student_id', 'attendance_logs.student_id')
                            ->where('exits.scan_type', 'exit')
                            ->where('exits.session', $session)
                            ->whereDate('exits.created_at', today());
                    });
            })
            ->get();

        foreach ($studentsToExit as $student) {
            $this->notificationController->notifyMissedStop($student, $bus, $locationCard->location_name);
        }

        return response()->json([
            'message' => "Location card scanned ({$session} session)",
            'location' => $locationCard->location_name,
            'alerts_created' => $studentsToExit->count()
        ]);
    }

    
    public function updateSessionMode(Request $request)
    {
        $mode = $request->input('mode');
        
        if (!in_array($mode, ['morning', 'evening'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid session mode'
            ], 400);
        }

        Cache::put('session_mode', $mode, now()->addHours(12));

        return response()->json([
            'success' => true,
            'message' => 'Session mode updated successfully',
            'mode' => $mode
        ]);
    }
    public function generateFees(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2023|max:2025',
            'amount' => 'required|numeric|min:100'
        ]);

        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];

        foreach ($months as $index => $month) {
            FeeSchedule::updateOrCreate(
                [
                    'month' => $month,
                    'year' => $request->year
                ],
                [
                    'amount' => $request->amount,
                    'due_date' => Carbon::create($request->year, $index + 1, 5),
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Fees generated successfully'
        ]);
    }



    //handle gps 


    const LOCATION_MATCH_THRESHOLD = 50; // meters

    // Add this method to check GPS coordinates
    private function checkNearbyLocations($latitude, $longitude, $busId, $session)
    {
        // Get all location cards
        $locationCards = LocationCard::all();
        
        foreach ($locationCards as $locationCard) {
            $distance = $this->calculateDistance(
                $latitude,
                $longitude,
                $locationCard->latitude,
                $locationCard->longitude
            );
            
            // If bus is within threshold distance of a location
            if ($distance <= ($locationCard->radius ?? self::LOCATION_MATCH_THRESHOLD)) {
                $this->handleLocationReached($locationCard, $busId, $session);
            }
        }
    }

    // Calculate distance between two points
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meters

        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $dLat = $lat2 - $lat1;
        $dLon = $lon2 - $lon1;

        $a = sin($dLat/2) * sin($dLat/2) +
             cos($lat1) * cos($lat2) *
             sin($dLon/2) * sin($dLon/2);
             
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }

    // Handle when bus reaches a location
    private function handleLocationReached($locationCard, $busId, $session)
    {
        // Check if this location has already been processed recently
        $cacheKey = "location_processed_{$busId}_{$locationCard->id}_{$session}_" . date('Y-m-d');
        if (Cache::has($cacheKey)) {
            return;
        }

        // Process based on session and location type
        if ($session === 'morning' && !$locationCard->is_school_card) {
            $this->handleMorningPickup($locationCard, $busId);
        } elseif ($session === 'evening' && $locationCard->is_school_card) {
            $this->handleEveningDeparture($busId);
        } else {
            $this->handleRegularStop($locationCard, $busId, $session);
        }

        // Mark location as processed
        Cache::put($cacheKey, true, now()->addMinutes(5));
    }

    private function handleMorningPickup($locationCard, $busId)
    {
        // Check for students who should be picked up here but haven't boarded
        $missedStudents = Student::where('pickup_location_id', $locationCard->id)
            ->whereDoesntHave('attendanceLogs', function ($query) use ($busId) {
                $query->where('session', 'morning')
                    ->where('bus_id', $busId)
                    ->where('scan_type', 'entry')
                    ->whereDate('created_at', today());
            })
            ->get();

        foreach ($missedStudents as $student) {
            $this->notificationController->notifyMissedPickup(
                $student, 
                $locationCard->location_name
            );
        }
    }

    private function handleEveningDeparture($busId)
    {
        // Check for students who attended school but haven't boarded the return bus
        $missedStudents = Student::whereHas('attendanceLogs', function ($query) {
            $query->where('session', 'morning')
                ->whereDate('created_at', today());
        })
        ->whereDoesntHave('attendanceLogs', function ($query) use ($busId) {
            $query->where('session', 'evening')
                ->where('bus_id', $busId)
                ->where('scan_type', 'entry')
                ->whereDate('created_at', today());
        })
        ->get();

        foreach ($missedStudents as $student) {
            $this->notificationController->notifyMissedReturn($student);
        }
    }

    private function handleRegularStop($locationCard, $busId, $session)
    {
        // Check for students who should exit here
        $studentsToExit = Student::where('exit_location_id', $locationCard->id)
            ->whereHas('attendanceLogs', function ($query) use ($busId, $session) {
                $query->where('bus_id', $busId)
                    ->where('session', $session)
                    ->where('scan_type', 'entry')
                    ->whereDate('created_at', today())
                    ->whereNotExists(function ($subquery) use ($session) {
                        $subquery->from('attendance_logs as exits')
                            ->whereColumn('exits.student_id', 'attendance_logs.student_id')
                            ->where('exits.scan_type', 'exit')
                            ->where('exits.session', $session)
                            ->whereDate('exits.created_at', today());
                    });
            })
            ->get();

        foreach ($studentsToExit as $student) {
            $this->notificationController->notifyMissedStop(
                $student, 
                Bus::find($busId), 
                $locationCard->location_name
            );
        }
    }

    public function updateBusLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'session_id' => 'required'
        ]);

        $bus_id = 1;
        $session = Cache::get('session_mode', 'morning');

        // Check for nearby locations
        $this->checkNearbyLocations(
            $request->latitude,
            $request->longitude,
            $bus_id,
            $session 
        );

        return response()->json([
            'success' => true,
            'message' => 'Location updated successfully',
            'studentsUpdated' => true
        ]);
    }
    // app/Http/Controllers/TeacherController.php

    public function storeLocation(Request $request)
    {
        try {
            $validated = $request->validate([
                'location_name' => 'required|string|max:255',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'radius' => 'nullable|integer|min:10|max:1000',
                'is_school_card' => 'boolean|nullable'
            ]);

            // Set default radius if not provided
            $validated['radius'] = $validated['radius'] ?? 50;
            
            // Create location without RFID
            $location = LocationCard::create([
                'location_name' => $validated['location_name'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'radius' => $validated['radius'],
                'is_school_card' => $validated['is_school_card'] ?? false,
                'sequence_number' => LocationCard::max('sequence_number') + 1,
                'rfid_number' => 'not needed'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Location saved successfully',
                'location' => $location
            ]);

        } catch (\Exception $e) {
            \Log::error('Location creation error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to save location',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}