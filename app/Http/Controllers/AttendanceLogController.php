<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Student;
use Illuminate\Http\Request;

class AttendanceLogController extends Controller
{
    // Get all logs
    public function index()
    {
        $logs = AttendanceLog::with(['student', 'bus'])
            ->latest('scan_time')
            ->paginate(15);

        return view('attendance.index', compact('logs'));
    }

    // Get today's logs
    public function todayLogs()
    {
        $logs = AttendanceLog::with(['student', 'bus'])
            ->today()
            ->latest('scan_time')
            ->get();

        return response()->json([
            'success' => true,
            'logs' => $logs
        ]);
    }

    // Handle RFID scan
    public function handleScan(Request $request)
    {
        $validated = $request->validate([
            'rfid_number' => 'required|string',
            'bus_id' => 'required|exists:buses,id',
        ]);

        // Find student by RFID
        $student = Student::where('rfid_number', $validated['rfid_number'])->first();

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid RFID card'
            ], 404);
        }

        // Get last scan to determine scan type
        $lastScan = AttendanceLog::where('student_id', $student->id)
            ->whereDate('scan_time', today())
            ->latest()
            ->first();

        $scanType = $lastScan && $lastScan->scan_type === 'entry' ? 'exit' : 'entry';

        // Create attendance log
        $log = AttendanceLog::create([
            'student_id' => $student->id,
            'bus_id' => $validated['bus_id'],
            'scan_type' => $scanType,
            'scan_time' => now()
        ]);

        // Update student's exit status
        $student->update([
            'has_exited' => ($scanType === 'exit')
        ]);

        return response()->json([
            'success' => true,
            'message' => "Student {$scanType} recorded successfully",
            'log' => $log->load(['student', 'bus'])
        ]);
    }

    // Get student's logs
    public function getStudentLogs($studentId)
    {
        $logs = AttendanceLog::where('student_id', $studentId)
            ->with(['bus'])
            ->latest('scan_time')
            ->get();

        return response()->json([
            'success' => true,
            'logs' => $logs
        ]);
    }

    // Get bus's logs
    public function getBusLogs($busId)
    {
        $logs = AttendanceLog::where('bus_id', $busId)
            ->with(['student'])
            ->latest('scan_time')
            ->get();

        return response()->json([
            'success' => true,
            'logs' => $logs
        ]);
    }

    // Get attendance statistics
    public function getStats()
    {
        $stats = [
            'total_entries' => AttendanceLog::today()->where('scan_type', 'entry')->count(),
            'total_exits' => AttendanceLog::today()->where('scan_type', 'exit')->count(),
            'students_in_bus' => Student::where('has_exited', false)->count()
        ];

        return response()->json($stats);
    }
}