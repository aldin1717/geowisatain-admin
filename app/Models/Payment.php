<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;

class Payment extends Model
{
    protected $fillable = [
        'payment_number', 'booking_id', 'payment_date', 'amount',
        'payment_method', 'payment_status', 'notes', 'created_by'
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'amount' => 'decimal:2',
        'payment_method' => PaymentMethod::class,
        'payment_status' => PaymentStatus::class,
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
