<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'payment_number', 'booking_id', 'billing_group_id', 'payment_date', 'amount',
        'payment_method', 'payment_status', 'notes', 'created_by', 'shift_id',
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

    public function billingGroup()
    {
        return $this->belongsTo(BillingGroup::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
