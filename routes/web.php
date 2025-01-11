<?php

use App\Http\Controllers\AttendanceLogController;
use App\Http\Controllers\BusController;
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
    Route::get('/pages/parent', function () {
        return view('pages.parent');
    })->middleware('role:parent')->name('parent.dashboard');

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
    // Basic CRUD routes for students
    Route::get('/teacher', [StudentController::class, 'index'])->name('students.index');
    Route::get('/students/create', [StudentController::class, 'create'])->name('students.create');
    Route::post('/students', [StudentController::class, 'store'])->name('students.store');
    Route::get('/students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
    Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');
    Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
});


// routes/web.php
Route::middleware(['auth'])->group(function () {
    Route::post('/hardware-rfid', [TeacherController::class, 'handleHardwareRFID']);
    Route::get('/check-last-rfid', [TeacherController::class, 'checkLastRFID']);
    Route::post('/start-adding-student', [TeacherController::class, 'startAddingStudent']);
    Route::post('/cancel-adding-student', [TeacherController::class, 'cancelAddingStudent']);
});



require __DIR__.'/auth.php';
