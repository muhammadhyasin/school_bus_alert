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
}