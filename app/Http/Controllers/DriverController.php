<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\Bus;
use App\Models\BusSession;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;


class DriverController extends Controller
{
    protected $notificationController;

    public function __construct(NotificationController $notificationController)
    {
        $this->notificationController = $notificationController;
    }
    public function dashboard()
    {
        $driver = auth()->user();
        $activeBus = Bus::where('driver_id', $driver->id)->first();
        
        $currentSession = BusSession::where('driver_id', $driver->id)
            ->where('status', '!=', 'completed')
            ->latest()
            ->first();

        return view('pages.driver', compact('activeBus', 'currentSession'));
    }

    public function startBusSession(Request $request)
    {
        $request->validate([
            'bus_id' => 'required|exists:buses,bus_number', // Validate bus_number
            'session_type' => 'required|in:morning,evening',
        ]);

        // Get bus by bus_number
        $bus = Bus::where('bus_number', $request->bus_id)->first();

        // Check if there's already an active session
        $existingSession = BusSession::where('bus_id', $bus->id)
            ->where('status', '!=', 'completed')
            ->first();

        if ($existingSession) {
            return response()->json([
                'success' => false,
                'message' => 'There is already an active session for this bus'
            ], 400);
        }

        $session = BusSession::create([
            'bus_id' => $bus->id, // Use the actual bus_id
            'driver_id' => auth()->id(),
            'session_type' => $request->session_type,
            'status' => 'running',
            'started_at' => now(),
        ]);

        // Store current session in cache for RFID scanning
        Cache::put('session_mode', $request->session_type, now()->addHours(12));
        Cache::put('active_bus_session_' . $request->bus_id, $session->id, now()->addHours(12));

        return response()->json([
            'success' => true,
            'message' => 'Bus session started successfully',
            'session' => $session
        ]);
    }

    public function endBusSession(Request $request)
    {
        $request->validate([
            'bus_id' => 'required|exists:buses,bus_number', // Changed to check bus_number
        ]);

        // Get bus by bus_number
        $bus = Bus::where('bus_number', $request->bus_id)->first();

        $session = BusSession::where('bus_id', $bus->id)
            ->where('status', 'running')
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'No active session found for this bus'
            ], 404);
        }

        $session->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);

        // Clear cache
        Cache::forget('session_mode');
        Cache::forget('active_bus_session_' . $request->bus_id);

        return response()->json([
            'success' => true,
            'message' => 'Bus session ended successfully'
        ]);
    }

    public function getCurrentStatus()
    {
        $driver = auth()->user();
        $currentSession = BusSession::where('driver_id', $driver->id)
            ->where('status', 'running')
            ->with('bus')
            ->first();

        return response()->json([
            'success' => true,
            'has_active_session' => !is_null($currentSession),
            'session' => $currentSession
        ]);
    }
    public function getAttendanceLogs()
    {
        $driver = auth()->user();
        $currentSession = BusSession::where('driver_id', $driver->id)
            ->where('status', 'running')
            ->first();

        if (!$currentSession) {
            return '<tr><td colspan="4" class="text-center">No active session</td></tr>';
        }

        $logs = AttendanceLog::where('bus_id', $currentSession->bus_id)
            ->where('session', $currentSession->session_type)
            ->whereDate('created_at', today())
            ->with('student')
            ->orderBy('created_at', 'desc')
            ->get();

        $html = '';
        foreach ($logs as $log) {
            $html .= '<tr>
                <td>' . $log->created_at->format('h:i A') . '</td>
                <td>' . $log->student->name . '</td>
                <td>' . ucfirst($log->scan_type) . '</td>
                <td>
                    <span class="badge bg-' . ($log->scan_type === 'entry' ? 'success' : 'warning') . '">
                        ' . ucfirst($log->scan_type) . '
                    </span>
                </td>
            </tr>';
        }

        return $html ?: '<tr><td colspan="4" class="text-center">No attendance records yet</td></tr>';
    }
    public function reportDelay(Request $request)
    {
        $request->validate([
            'bus_id' => 'required|exists:buses,id',
            'duration' => 'required|numeric|min:1|max:120',
            'reason' => 'required|string|max:255',
            'session_type' => 'required|in:morning,evening'
        ]);

        $bus = Bus::findOrFail($request->bus_id);
        
        // Get all students currently in the bus or waiting for the bus
        $students = Student::whereHas('attendanceLogs', function($query) use ($bus, $request) {
            $query->where('bus_id', $bus->id)
                ->where('session', $request->session_type)
                ->whereDate('created_at', today());
        })->get();

        foreach ($students as $student) {
            $this->notificationController->notifyBusDelay(
                $student,
                $bus,
                $request->duration,
                $request->reason,
                $request->session_type
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Delay notification sent successfully'
        ]);
    }
}