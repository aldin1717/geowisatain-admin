<?php

namespace App\Enums;

enum BookingType: string
{
    case General = 'general';
    case Corporate = 'corporate';
    case ESDM = 'esdm';
    case TravelAgent = 'travel_agent';
    case Diklat = 'diklat';

    public function label(): string
    {
        return match ($this) {
            self::General => 'Umum',
            self::Corporate => 'Corporate',
            self::ESDM => 'ESDM',
            self::TravelAgent => 'Travel Agent',
            self::Diklat => 'Diklat',
        };
    }
}
