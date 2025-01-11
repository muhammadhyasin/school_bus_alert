<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationCard extends Model
{
    protected $fillable = [
        'rfid_number',
        'location_name'
    ];

    // Relationship with students who exit at this location
    public function students()
    {
        return $this->hasMany(Student::class, 'exit_location_id');
    }
}