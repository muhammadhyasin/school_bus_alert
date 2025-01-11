<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RFIDController extends Controller
{
    public function handleRFIDScan(Request $request)
    {
        // Add logging to debug the incoming request
        Log::info('RFID Scan Request:', $request->all());

        // Get RFID number and validate it's not empty
        $rfidNumber = $request->input('rfid_number');
        $busId = $request->input('bus_id');

        if (empty($rfidNumber)) {
            return response()->json([
                'success' => false,
                'message' => 'RFID number is required',
            ], 400);
        }

        // Store RFID in cache
        Cache::put('last_rfid_scan', $rfidNumber, now()->addMinutes(1));

        // Check if we're in adding student mode
        if (!Cache::has('adding_student_mode')) {
            return app(TeacherController::class)->handleStudentAttendance($rfidNumber, $busId);
        }

        return response()->json([
            'success' => true,
            'message' => 'RFID scan received for registration',
            'rfid_number' => $rfidNumber, // Changed from 'rfid' to 'rfid_number' for consistency
            'bus_id' => $busId
        ]);
    }
}