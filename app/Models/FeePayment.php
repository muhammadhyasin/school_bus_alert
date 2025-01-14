<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeePayment extends Model
{
    protected $fillable = [
        'student_id',
        'fee_schedule_id',
        'amount_paid',
        'payment_method',
        'status',
        'paid_at'
    ];

    protected $dates = ['paid_at'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function feeSchedule()
    {
        return $this->belongsTo(FeeSchedule::class);
    }
}