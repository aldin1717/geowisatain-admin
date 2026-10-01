<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;

class Booking extends Model
{
    protected $fillable = [
        'booking_number', 'guest_id', 'room_id', 'check_in_date', 'check_out_date',
        'num_guests', 'num_nights', 'room_rate', 'discount', 'tax', 'additional_charge',
        'grand_total', 'booking_status', 'payment_status', 'notes',
        'actual_check_in', 'actual_check_out', 'checked_in_by', 'checked_out_by', 'created_by'
    ];

    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'actual_check_in' => 'datetime',
        'actual_check_out' => 'datetime',
        'room_rate' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'additional_charge' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'booking_status' => BookingStatus::class,
        'payment_status' => PaymentStatus::class,
    ];

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function checkedInBy()
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function checkedOutBy()
    {
        return $this->belongsTo(User::class, 'checked_out_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
