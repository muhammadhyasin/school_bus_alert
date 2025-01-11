<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bus extends Model
{
    protected $fillable = [
        'bus_number',
        'driver_id',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean'
    ];

}