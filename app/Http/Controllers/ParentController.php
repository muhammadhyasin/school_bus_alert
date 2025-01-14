<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\AttendanceLog;
use App\Models\BusNotification;
use App\Models\BusSession;
use App\Models\FeePayment;
use App\Models\FeeSchedule;
use Illuminate\Container\Attributes\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ParentController extends Controller
{
    public function dashboard()
    {
        $parent = auth()->user();
        $currentSession = Cache::get('session_mode', 'morning');

        // Debug information
        \Log::info('Parent Dashboard Access', [
            'user_id' => $parent->id,
            'session' => $currentSession
        ]);

        // Get the parent's students
        $students = Student::where('parent_id', $parent->id)
            ->with(['attendanceLogs' => function($query) use ($currentSession) {
                $query->whereDate('created_at', today())
                    ->where('session', $currentSession)
                    ->latest();
            }, 'exitLocation', 'attendanceLogs.bus.driver'])
            ->get();

        \Log::info('Students Found', [
            'count' => $students->count(),
            'students' => $students->pluck('name')
        ]);

        $studentStatuses = [];

        foreach($students as $student) {
            $latestLog = $student->attendanceLogs->first();
            $bus = $latestLog ? $latestLog->bus : null;
            $driver = $bus ? $bus->driver : null;

            $studentStatuses[] = [
                'student_name' => $student->name,
                'status' => $this->getStudentStatus($latestLog),
                'bus_details' => $bus ? [
                    'bus_number' => $bus->bus_number,
                    'driver_name' => $driver->name ?? 'Driver 1',
                    'driver_phone' => $driver->phone ?? '1234567899'
                ] : null,
                'timings' => [
                    'pickup_time' => $latestLog && $latestLog->scan_type === 'entry' ? 
                        $latestLog->created_at->format('h:i A') : null,
                    'drop_time' => $latestLog && $latestLog->scan_type === 'exit' ? 
                        $latestLog->created_at->format('h:i A') : null,
                    'expected_arrival' => $this->calculateExpectedArrival($latestLog, $student->exitLocation)
                ],
                'location' => [
                    'exit_location' => $student->exitLocation ? $student->exitLocation->location_name : 'Not Set'
                ]
            ];
        }

        \Log::info('Student Statuses', [
            'count' => count($studentStatuses),
            'statuses' => $studentStatuses
        ]);

        // Add a default status if no students found
        if (empty($studentStatuses)) {
            $studentStatuses[] = [
                'student_name' => 'No Students Found',
                'status' => [
                    'text' => 'No Data',
                    'icon' => 'ri-information-line',
                    'color' => 'secondary',
                    'description' => 'No student information available'
                ],
                'bus_details' => null,
                'timings' => [
                    'pickup_time' => null,
                    'drop_time' => null,
                    'expected_arrival' => null
                ],
                'location' => [
                    'exit_location' => 'Not Set'
                ]
            ];
        }

        return view('pages.parent', [
            'studentStatuses' => $studentStatuses,
            'currentSession' => $currentSession
        ]);
    }

    private function getStudentStatus($latestLog)
    {
        if (!$latestLog) {
            return [
                'text' => 'Not Picked Up',
                'icon' => 'ri-timer-line',
                'color' => 'warning',
                'description' => 'Waiting for pickup'
            ];
        }

        if ($latestLog->scan_type === 'entry') {
            return [
                'text' => 'On Bus',
                'icon' => 'ri-bus-line',
                'color' => 'success',
                'description' => 'Student is on the bus'
            ];
        }

        return [
            'text' => 'Dropped Off',
            'icon' => 'ri-home-line',
            'color' => 'info',
            'description' => 'Student has been dropped off'
        ];
    }

    private function calculateExpectedArrival($latestLog, $exitLocation)
    {
        if (!$latestLog || $latestLog->scan_type === 'exit' || !$exitLocation) {
            return null;
        }

        return $latestLog->created_at->addMinutes(30)->format('h:i A');
    }


    public function getNotifications()
    {
        $notifications = BusNotification::where('recipient_type', 'App\Models\User')
            ->where('recipient_id', auth()->id())
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'message' => $notification->message,
                    'time' => $notification->created_at->diffForHumans(),
                    'data' => $notification->data
                ];
            });

        \Log::info('Notifications Fetched', [
            'count' => $notifications->count(),
            'notifications' => $notifications->pluck('id')
        ]);

        return response()->json([
            'success' => true,
            'notifications' => $notifications
        ]);
    }

    public function markNotificationAsRead($id)
    {
        $notification = BusNotification::where('recipient_id', auth()->id())
            ->findOrFail($id);

        $notification->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    public function markAllNotificationsAsRead()
    {
        BusNotification::where('recipient_id', auth()->id())
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read'
        ]);
    }

    public function getStudentHistory($studentId)
    {
        $student = Student::where('parent_id', auth()->id())
            ->findOrFail($studentId);

        $history = AttendanceLog::where('student_id', $student->id)
            ->with(['bus', 'bus.driver'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('parent.student-history', compact('student', 'history'));
    }

    public function getContactInfo()
    {
        return response()->json([
            'success' => true,
            'contacts' => [
                'school_office' => [
                    'name' => 'School Office',
                    'phone' => config('school.office_phone'),
                ],
                'emergency' => [
                    'name' => 'Emergency Contact',
                    'phone' => config('school.emergency_phone'),
                ],
                'transport' => [
                    'name' => 'Transport Department',
                    'phone' => config('school.transport_phone'),
                ]
            ]
        ]);
    }
    public function getFees()
    {
        try {
            $student = auth()->user()->students()->firstOrFail();
            
            $feeSchedules = FeeSchedule::orderBy('due_date', 'desc')
                ->get()
                ->map(function($schedule) use ($student) {
                    try {
                        return [
                            'id' => $schedule->id,
                            'month' => $schedule->month,
                            'year' => $schedule->year,
                            'amount' => $schedule->amount,
                            'due_date' => $schedule->due_date ? $schedule->due_date->format('M d, Y') : 'Not Set',
                            'status' => $schedule->isPaid() ? 'paid' : ($schedule->isOverdue() ? 'overdue' : 'pending'),
                            'payment' => $schedule->payments()
                                ->where('student_id', $student->id)
                                ->first()
                        ];
                    } catch (\Exception $e) {
                        \Log::error('Error processing fee schedule: ' . $e->getMessage(), [
                            'schedule_id' => $schedule->id,
                            'due_date' => $schedule->due_date
                        ]);
                        return null;
                    }
                })
                ->filter(); // Remove any null values

            $payments = FeePayment::where('student_id', $student->id)
                ->with('feeSchedule')
                ->orderBy('paid_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'feeSchedules' => $feeSchedules,
                'payments' => $payments
            ]);
        } catch (\Exception $e) {
            \Log::error('Fee fetch error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching fee details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function processPayment(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|exists:fee_schedules,id',
            'payment_method' => 'required|in:cash,card,upi'
        ]);

        $schedule = FeeSchedule::findOrFail($request->schedule_id);
        $student = auth()->user()->students->first();

        // Create fake payment
        $payment = FeePayment::create([
            'student_id' => $student->id,
            'fee_schedule_id' => $schedule->id,
            'amount_paid' => $schedule->amount,
            'payment_method' => $request->payment_method,
            'status' => 'completed',
            'paid_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment processed successfully',
            'payment' => $payment
        ]);
    }
}