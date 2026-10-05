<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Enums\RoomStatus;

class Room extends Model
{
    protected $fillable = [
        'room_number', 'room_type_id', 'floor', 'capacity', 'price_per_night',
        'status', 'description', 'image', 'is_active'
    ];

    protected $casts = [
        'price_per_night' => 'decimal:2',
        'is_active' => 'boolean',
        'status' => RoomStatus::class,
    ];

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function bookingsThroughSelection()
    {
        return $this->belongsToMany(Booking::class);
    }
}
