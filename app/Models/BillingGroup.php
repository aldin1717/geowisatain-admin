<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingGroup extends Model
{
    protected $fillable = [
        'invoice_number', 'payer_guest_id', 'payment_status', 'created_by',
    ];

    public function payerGuest(): BelongsTo
    {
        return $this->belongsTo(Guest::class, 'payer_guest_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function totalAmount(): float
    {
        return (float) $this->bookings()->sum('grand_total');
    }

    public function paidAmount(): float
    {
        return $this->bookings->sum(fn (Booking $booking) => $booking->totalPaid());
    }

    public function outstandingAmount(): float
    {
        return max($this->totalAmount() - $this->paidAmount(), 0);
    }
}
