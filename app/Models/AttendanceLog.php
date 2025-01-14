<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceLog extends Model
{
    protected $fillable = [
        'student_id',
        'bus_id',
        'scan_type',
        'scan_time',
        'out_scan_time',
        'session',
    ];

    protected $casts = [
        'scan_time' => 'datetime'
    ];

    protected $dates = [
        'scan_time',
        'out_scan_time'
    ];

    // Relationship with Student
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    // Scope for today's logs
    public function scopeToday($query)
    {
        return $query->whereDate('scan_time', today());
    }
    public function scopeMorning($query)
    {
        return $query->where('session', 'morning');
    }

    public function scopeEvening($query)
    {
        return $query->where('session', 'evening');
    }
    public function bus()

    {
        return $this->belongsTo(Bus::class);
    }
}