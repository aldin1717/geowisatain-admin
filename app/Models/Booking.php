<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Enums\BookingStatus;
use App\Enums\BookingType;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Booking extends Model
{
    protected $fillable = [
        'booking_number', 'guest_id', 'room_id', 'additional_room_ids', 'check_in_date', 'check_out_date',
        'billing_group_id',
        'num_guests', 'num_nights', 'room_rate', 'discount', 'tax', 'additional_charge',
        'grand_total', 'booking_status', 'payment_status', 'notes',
        'booking_type', 'is_day_use', 'is_early_check_out', 'is_bill_merged',
        'additional_charge_breakdown', 'ballroom_amount', 'actual_check_in', 'actual_check_out',
        'checked_in_by', 'checked_out_by', 'created_by'
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
        'ballroom_amount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'booking_status' => BookingStatus::class,
        'booking_type' => BookingType::class,
        'payment_status' => PaymentStatus::class,
        'is_day_use' => 'boolean',
        'is_early_check_out' => 'boolean',
        'is_bill_merged' => 'boolean',
        'additional_charge_breakdown' => 'array',
        'additional_room_ids' => 'array',
    ];

    public function guest()
    {
        return $this->belongsTo(Guest::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class);
    }

    public function selectedRoomIds(): array
    {
        if ($this->relationLoaded('rooms')) {
            $roomIds = $this->rooms->pluck('id')->all();
        } else {
            $roomIds = $this->rooms()->pluck('rooms.id')->all();
        }

        if ($roomIds === []) {
            $roomIds = array_merge(
                [$this->room_id],
                $this->additional_room_ids ?? []
            );
        }

        return array_values(array_unique(array_merge(
            [$this->room_id],
            array_diff($roomIds, [$this->room_id])
        )));
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function billingGroup()
    {
        return $this->belongsTo(BillingGroup::class);
    }

    public function allocatedPayments()
    {
        return $this->belongsToMany(Payment::class, 'booking_payment_allocations')
            ->withPivot('amount');
    }

    public function totalPaid(): float
    {
        return (float) $this->payments()->where('payment_status', 'paid')->sum('amount')
            + (float) $this->allocatedPayments()->where('payment_status', 'paid')->sum('booking_payment_allocations.amount');
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
