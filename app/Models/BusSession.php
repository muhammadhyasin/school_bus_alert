<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class BusSession extends Model
    {
        protected $fillable = [
            'bus_id',
            'driver_id',
            'session_type',
            'status',
            'started_at',
            'completed_at'
        ];

        protected $casts = [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];

        public function bus()
        {
            return $this->belongsTo(Bus::class);
        }

        public function driver()
        {
            return $this->belongsTo(User::class, 'driver_id');
        }
        
        public static function getActiveSessionByBusNumber($busNumber)
        {
            return static::whereHas('bus', function($query) use ($busNumber) {
                    $query->where('bus_number', $busNumber);
                })
                ->where('status', 'running')
                ->whereNull('completed_at')
                ->whereDate('created_at', today())
                ->first();
        }
    }