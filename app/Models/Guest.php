<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Enums\IdentityType;

class Guest extends Model
{
    protected $fillable = [
        'guest_code', 'full_name', 'identity_number', 'identity_type', 'gender',
        'phone', 'email', 'address', 'city', 'country', 'notes'
    ];

    protected $casts = [
        'identity_type' => IdentityType::class,
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}
