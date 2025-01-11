<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\AttendanceLog;
use App\Models\LocationCard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class TeacherController extends Controller
{
    
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
        // Check if this is a location card
        $locationCard = LocationCard::where('rfid_number', $rfidNumber)->first();
        if ($locationCard) {
            return $this->handleLocationCard($locationCard, $busId);
        }

        // Find student by RFID
        $student = Student::where('rfid_number', $rfidNumber)->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Unregistered RFID card'
            ], 404);
        }

        // Check for existing entry today
        $existingLog = AttendanceLog::where('student_id', $student->id)
            ->where('bus_id', $busId)
            ->whereDate('created_at', today())
            ->first();

        if (!$existingLog) {
            // Create entry log
            $log = AttendanceLog::create([
                'student_id' => $student->id,
                'bus_id' => $busId,
                'scan_type' => 'entry',
                'scan_time' => now()
            ]);

            $this->notifyParent($student, 'entry', $busId);

            return response()->json([
                'success' => true,
                'message' => 'Student entry recorded',
                'student' => $student->name,
                'scan_type' => 'entry'
            ]);
        } else if ($existingLog->scan_type === 'entry') {
            // Record exit
            $existingLog->update([
                'scan_type' => 'exit',
                'out_scan_time' => now()
            ]);

            $this->notifyParent($student, 'exit', $busId);

            return response()->json([
                'success' => true,
                'message' => 'Student exit recorded',
                'student' => $student->name,
                'scan_type' => 'exit'
            ]);
        }
    }

    private function handleLocationCard($locationCard, $busId)
    {
        $studentsToExit = Student::where('exit_location_id', $locationCard->location_id)
            ->whereHas('attendanceLogs', function ($query) use ($busId) {
                $query->where('bus_id', $busId)
                    ->where('scan_type', 'entry')
                    ->whereNull('out_scan_time')
                    ->whereDate('created_at', today());
            })
            ->get();

        foreach ($studentsToExit as $student) {
            $this->createAlert($student, $busId, $locationCard->location_id);
        }

        return response()->json([
            'success' => true,
            'message' => 'Location card scanned',
            'location' => $locationCard->location_name,
            'alerts_created' => $studentsToExit->count()
        ]);
    }

    private function notifyParent($student, $type, $busId)
    {
        // Implement notification logic
    }

    private function createAlert($student, $busId, $locationId)
    {
        // Implement alert creation logic
    }
}