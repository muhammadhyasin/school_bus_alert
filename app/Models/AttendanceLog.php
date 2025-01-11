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
        'out_scan_time'
    ];

    protected $casts = [
        'scan_time' => 'datetime'
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
}