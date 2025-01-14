<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'rfid_number',
        'parent_id',
        'class',
        'section',
        'roll_number',
        'address',
        'phone',
        'status',
        'exit_location_id'
    ];

    protected $casts = [
        'has_exited' => 'boolean',
        'status' => 'boolean'
    ];

    // Relationship with Parent (User)
    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    // Relationship with AttendanceLogs
    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class);
    }

    // Get today's attendance
    public function getTodayAttendanceAttribute()
    {
        return $this->attendanceLogs()
            ->whereDate('created_at', today())
            ->latest()
            ->first();
    }

    // Check if student is present today
    public function getIsPresentTodayAttribute()
    {
        return $this->today_attendance !== null;
    }

    // Get last scan type (entry/exit)
    public function getLastScanTypeAttribute()
    {
        return $this->attendanceLogs()
            ->latest()
            ->value('scan_type');
    }

    // Scope for active students
    public function scopeActive($query)
    {
        return $query->where('status', true);
    }


    // Get student's full details
    public function getFullDetailsAttribute()
    {
        return "{$this->name} - Class {$this->class} {$this->section} (Roll: {$this->roll_number})";
    }



    public function latestAttendance()
    {
        return $this->hasOne(AttendanceLog::class)->latest('scan_time');
    }

    public function exitLocation()
    {
        return $this->belongsTo(LocationCard::class, 'exit_location_id');
    }
    public function feePayments()
    {
        return $this->hasMany(FeePayment::class);
    }


}