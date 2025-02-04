<?php

use App\Http\Controllers\AttendanceLogController;
use App\Http\Controllers\BusController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RFIDController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Role-specific routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return match(auth()->user()->role) {
            'parent' => redirect()->route('parent.dashboard'),
            'admin' => redirect()->route('students.index'),
            'driver' => redirect()->route('driver.dashboard'),
            default => redirect()->route('login')
        };
    })->name('dashboard');
    Route::get('/pages/driver', function () {
        return view('pages.driver');
    })->middleware('role:driver')->name('driver.dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware(['auth', 'role:admin'])->group(function () {
    // Basic CRUD routes for teacher
    Route::get('/teacher', [StudentController::class, 'index'])->name('students.index');
    Route::get('/students/create', [StudentController::class, 'create'])->name('students.create');
    Route::post('/students', [StudentController::class, 'store'])->name('students.store');
    Route::get('/students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
    Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');
    Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
});

Route::get('/get-latest-logs', [StudentController::class, 'getLatestLogs'])->name('get.latest.logs');
Route::post('/update-session-mode', [TeacherController::class, 'updateSessionMode']);


// routes/web.php
Route::middleware(['auth'])->group(function () {
    Route::post('/hardware-rfid', [TeacherController::class, 'handleHardwareRFID']);
    Route::get('/check-last-rfid', [TeacherController::class, 'checkLastRFID']);
    Route::post('/start-adding-student', [TeacherController::class, 'startAddingStudent']);
    Route::post('/cancel-adding-student', [TeacherController::class, 'cancelAddingStudent']);
});


Route::middleware(['auth', 'role:driver'])->group(function () {
    Route::get('/driver', [DriverController::class, 'dashboard'])->name('driver.dashboard');
    Route::post('/driver/start-session', [DriverController::class, 'startBusSession'])->name('driver.start-session');
    Route::post('/driver/end-session', [DriverController::class, 'endBusSession'])->name('driver.end-session');
    Route::get('/driver/status', [DriverController::class, 'getCurrentStatus'])->name('driver.status');
    Route::get('/driver/attendance-logs', [DriverController::class, 'getAttendanceLogs'])->name('driver.attendance-logs');
});


// Notification Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/notifications/latest', [NotificationController::class, 'getLatestNotifications'])->name('notifications.latest');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markNotificationAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllNotificationsAsRead'])->name('notifications.markAllRead');
});



Route::middleware(['auth'])->group(function () {
    // Parent Dashboard
    Route::get('/parent', [ParentController::class, 'dashboard'])->name('parent.dashboard');
    Route::get('/parent/notifications', [ParentController::class, 'getNotifications'])->name('parent.notifications');
    Route::post('/parent/notifications/{id}/read', [ParentController::class, 'markNotificationAsRead'])->name('parent.notifications.read');
    Route::post('/parent/notifications/read-all', [ParentController::class, 'markAllNotificationsAsRead'])->name('parent.notifications.readAll');
    Route::get('/parent/student/{student}/history', [ParentController::class, 'getStudentHistory'])->name('parent.student.history');
    Route::get('/parent/contacts', [ParentController::class, 'getContactInfo'])->name('parent.contacts');
});

Route::post('/driver/report-delay', [DriverController::class, 'reportDelay'])->name('driver.report-delay');
Route::get('/parent/fees', [ParentController::class, 'getFees'])->name('parent.fees');
Route::post('/parent/fees/pay', [ParentController::class, 'processPayment'])->name('parent.fees.pay');
Route::post('/fees/generate', [TeacherController::class, 'generateFees'])->name('admin.generate-fees');

Route::post('/driver/update-location', 'DriverController@updateLocation')->name('driver.update-location');

Route::resource('buses', BusController::class);
Route::view('/offline', 'offline');

require __DIR__.'/auth.php';
