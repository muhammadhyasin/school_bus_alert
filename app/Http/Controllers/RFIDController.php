<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Bus;
use App\Models\Student;
use App\Models\BusSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RFIDController extends Controller
{
    public function handleRFIDScan(Request $request)
    {
        $rfidNumber = $request->input('rfid_number');
        $busNumber = $request->input('bus_id'); // This is actually bus_number

        Log::info('RFID Scan Request', [
            'rfid' => $rfidNumber,
            'bus_number' => $busNumber
        ]);

        if (empty($rfidNumber)) {
            return response()->json([
                'success' => false,
                'message' => 'RFID number is required',
            ], 400);
        }

        // If in student registration mode, handle the RFID scan
        if (Cache::has('adding_student_mode')) {
            Cache::put('last_rfid_scan', $rfidNumber, now()->addMinutes(1));
            return response()->json([
                'success' => true,
                'message' => 'RFID scan received for registration',
                'rfid_number' => $rfidNumber,
                'bus_number' => $busNumber
            ]);
        }

        // Get bus ID from bus number
        $bus = Bus::where('bus_number', $busNumber)->first();
        
        if (!$bus) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid bus number',
            ], 400);
        }

        // Check for active session using bus number
        $activeSession = BusSession::getActiveSessionByBusNumber($busNumber);

        Log::info('Active Session Check', [
            'session' => $activeSession,
            'bus_number' => $busNumber,
            'bus_id' => $bus->id
        ]);

        if (!$activeSession) {
            return response()->json([
                'success' => false,
                'message' => 'No active bus session. Please start a session first.',
            ], 400);
        }

        Cache::put('last_rfid_scan', $rfidNumber, now()->addMinutes(1));

        try {
            // Handle attendance with the active session type
            $response = app(TeacherController::class)->handleStudentAttendance(
                $rfidNumber, 
                $bus->id, // Pass the actual bus_id
                $activeSession->session_type
            );

            Log::info('Attendance Response', [
                'response' => $response->getData()
            ]);

            return $response;

        } catch (\Exception $e) {
            Log::error('Attendance Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error processing attendance: ' . $e->getMessage()
            ], 500);
        }
    }
}