<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeSchedule extends Model
{
    protected $fillable = ['month', 'year', 'amount', 'due_date'];
    
    // Add this to properly cast the due_date
    protected $casts = [
        'due_date' => 'datetime'
    ];

    public function payments()
    {
        return $this->hasMany(FeePayment::class);
    }

    public function isPaid()
    {
        return $this->payments()->where('status', 'completed')->exists();
    }

    public function isOverdue()
    {
        return !$this->isPaid() && $this->due_date < now();
    }
}