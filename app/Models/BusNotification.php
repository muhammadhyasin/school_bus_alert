<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusNotification extends Model
{
    protected $fillable = [
        'student_id',
        'type',
        'message',
        'bus_id',
        'recipient_type',
        'recipient_id',
        'is_read',
        'data'
    ];

    protected $casts = [
        'is_read' => 'boolean'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
}