<?php

namespace App\Http\Controllers;
use App\Models\BusNotification;
use App\Models\Student;
use App\Models\Bus;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    const ADMIN_ID = 2;
    public function notifyEntry(Student $student, Bus $bus, $session)
    {
        $message = "{$student->name} has entered bus #{$bus->bus_number} for {$session} session";
        
        // Notify parent
        $this->createNotification([
            'student_id' => $student->id,
            'type' => 'entry',
            'message' => $message,
            'bus_id' => $bus->id,
            'recipient_type' => 'App\Models\User',
            'recipient_id' => $student->parent_id,
            'is_read' => false
        ]);

        // Notify teachers
        $this->createNotification([
            'student_id' => $student->id,
            'type' => 'entry',
            'message' => $message,
            'bus_id' => $bus->id,
            'recipient_type' => 'App\Models\User',
            'recipient_id' => self::ADMIN_ID,
            'is_read' => false
        ]);
    }

    /**
     * Create exit notification for both parent and teachers
     */
    public function notifyExit(Student $student, Bus $bus, $session)
    {
        $message = "{$student->name} has exited bus #{$bus->bus_number} for {$session} session";
        
        // Notify parent
        $this->createNotification([
            'student_id' => $student->id,
            'type' => 'exit',
            'message' => $message,
            'bus_id' => $bus->id,
            'recipient_type' => 'App\Models\User',
            'recipient_id' => $student->parent_id,
            'is_read' => false
        ]);

        // Notify teachers
        $this->createNotification([
            'student_id' => $student->id,
            'type' => 'exit',
            'message' => $message,
            'bus_id' => $bus->id,
            'recipient_type' => 'App\Models\User',
            'recipient_id' => self::ADMIN_ID,
            'is_read' => false
        ]);
    }

    /**
     * Create missed stop notification for both parent and teachers
     */
    public function notifyMissedStop(Student $student, Bus $bus, $locationName)
    {
        $message = "Alert: {$student->name} missed their stop at {$locationName} on bus #{$bus->bus_number}";
        
        // Notify parent
        $this->createNotification([
            'student_id' => $student->id,
            'type' => 'missed_stop',
            'message' => $message,
            'bus_id' => $bus->id,
            'recipient_type' => 'App\Models\User',
            'recipient_id' => $student->parent_id,
            'is_read' => false
        ]);

        // Notify teachers
        $this->createNotification([
            'student_id' => $student->id,
            'type' => 'missed_stop',
            'message' => $message,
            'bus_id' => $bus->id,
            'recipient_type' => 'App\Models\User',
            'recipient_id' => self::ADMIN_ID,
            'is_read' => false
        ]);
    }
    

    /**
     * Create session start notification for teachers and driver
     */
    public function notifySessionStart(Bus $bus, $session)
    {
        $message = "Bus #{$bus->bus_number} has started its {$session} session";
        
        // Notify driver
        $this->createNotification([
            'type' => 'session_start',
            'message' => $message,
            'bus_id' => $bus->id,
            'recipient_type' => 'App\Models\User',
            'recipient_id' => $bus->driver_id,
            'is_read' => false
        ]);

        // Notify teachers
        $this->createNotification([
            'student_id' => $student->id,
            'type' => 'wrong_stop',
            'message' => $message,
            'bus_id' => $bus->id,
            'recipient_type' => 'App\Models\User',
            'recipient_id' => self::ADMIN_ID,
            'is_read' => false
        ]);
    }

    /**
     * Create session end notification for teachers and driver
     */
    public function notifySessionEnd(Bus $bus, $session)
    {
        $message = "Bus #{$bus->bus_number} has ended its {$session} session";
        
        // Notify driver
        $this->createNotification([
            'type' => 'session_end',
            'message' => $message,
            'bus_id' => $bus->id,
            'recipient_type' => 'App\Models\User',
            'recipient_id' => $bus->driver_id,
            'is_read' => false
        ]);

        // Notify teachers
        $this->createNotification([
            'type' => 'session_start',
            'message' => $message,
            'bus_id' => $bus->id,
            'recipient_type' => 'App\Models\User',
            'recipient_id' => self::ADMIN_ID,
            'is_read' => false
        ]);
    }

    /**
     * Create notifications for all teachers
     */
    

    /**
     * Create the notification record
     */
    public function createNotification(array $data)
    {
        return BusNotification::create($data);
    }

    /**
     * Get unread notifications for a user
     */
    public function getUnreadNotifications(User $user)
    {
        return BusNotification::where('recipient_type', 'App\Models\User')
            ->where('recipient_id', $user->id)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(BusNotification $notification)
    {
        return $notification->update(['is_read' => true]);
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead(User $user)
    {
        return BusNotification::where('recipient_type', 'App\Models\User')
            ->where('recipient_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }


    public function getLatestNotifications()
    {
        $user = auth()->user();
        
        $notifications = BusNotification::where('recipient_type', 'App\Models\User')
            ->where('recipient_id', $user->id)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->take(5)  // Get latest 5 unread notifications
            ->get();

        $formattedNotifications = $notifications->map(function($notification) {
            return [
                'id' => $notification->id,
                'message' => $notification->message,
                'type' => $notification->type,
                'time' => $notification->created_at->diffForHumans(),
                'is_read' => $notification->is_read
            ];
        });

        return response()->json([
            'success' => true,
            'notifications' => $formattedNotifications,
            'count' => $notifications->count()
        ]);
    }

    public function markNotificationAsRead($id)
    {
        $notification = BusNotification::findOrFail($id);
        
        if ($notification->recipient_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

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
    public function notifyMissedPickup(Student $student, $location)
    {
        $message = "Alert: {$student->name} was not picked up from {$location}";
        
        // Notify parent
        $this->createNotification([
            'student_id' => $student->id,
            'type' => 'missed_pickup',
            'message' => $message,
            'bus_id' => 1,
            'recipient_type' => 'App\Models\User',
            'recipient_id' => $student->parent_id,
            'is_read' => false
        ]);

        // Notify teachers/admin
        $this->createNotification([
            'student_id' => $student->id,
            'type' => 'missed_pickup',
            'message' => $message,
            'bus_id' => 1,
            'recipient_type' => 'App\Models\User',
            'recipient_id' => self::ADMIN_ID,
            'is_read' => false
        ]);
    }

    public function notifyMissedReturn(Student $student)
    {
        $message = "Alert: {$student->name} has not boarded the bus for return trip";
        
        // Notify parent
        $this->createNotification([
            'student_id' => $student->id,
            'type' => 'missed_return',
            'message' => $message,
            'bus_id' => 1,
            'recipient_type' => 'App\Models\User',
            'recipient_id' => $student->parent_id,
            'is_read' => false
        ]);

        // Notify teachers/admin
        $this->createNotification([
            'student_id' => $student->id,
            'type' => 'missed_return',
            'message' => $message,
            'recipient_type' => 'App\Models\User',
            'bus_id' => 1,
            'recipient_id' => self::ADMIN_ID,
            'is_read' => false
        ]);
    }
    public function notifyBusDelay(Student $student, Bus $bus, $duration, $reason, $session)
    {
        $message = "Bus #{$bus->bus_number} is running approximately {$duration} minutes late for {$session} session. Reason: {$reason}";
        
        // Notify parent
        $this->createNotification([
            'student_id' => $student->id,
            'type' => 'bus_delay',
            'message' => $message,
            'bus_id' => $bus->id,
            'recipient_type' => 'App\Models\User',
            'recipient_id' => $student->parent_id,
            'is_read' => false
        ]);

        // Notify teachers/admin
        $this->createNotification([
            'student_id' => $student->id,
            'type' => 'bus_delay',
            'message' => $message,
            'bus_id' => $bus->id,
            'recipient_type' => 'App\Models\User',
            'recipient_id' => self::ADMIN_ID,
            'is_read' => false
        ]);
    }
}
