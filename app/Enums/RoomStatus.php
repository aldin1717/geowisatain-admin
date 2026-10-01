<?php

namespace App\Enums;

enum RoomStatus: string
{
    case Available = 'available';
    case Reserved = 'reserved';
    case Occupied = 'occupied';
    case Dirty = 'dirty';
    case Cleaning = 'cleaning';
    case Maintenance = 'maintenance';
    case OutOfService = 'out_of_service';
    
    public function label(): string
    {
        return match($this) {
            self::Available => 'Available',
            self::Reserved => 'Reserved',
            self::Occupied => 'Occupied',
            self::Dirty => 'Dirty',
            self::Cleaning => 'Cleaning',
            self::Maintenance => 'Maintenance',
            self::OutOfService => 'Out of Service',
        };
    }
}
