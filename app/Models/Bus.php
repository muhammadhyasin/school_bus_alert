<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bus extends Model
{
    protected $fillable = [
        'bus_number',
        'driver_id'
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function sessions()
    {
        return $this->hasMany(BusSession::class);
    }
}