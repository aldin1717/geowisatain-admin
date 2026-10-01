<?php

namespace App\Enums;

enum IdentityType: string
{
    case KTP = 'ktp';
    case SIM = 'sim';
    case Passport = 'passport';
    case Other = 'other';
    
    public function label(): string
    {
        return match($this) {
            self::KTP => 'KTP',
            self::SIM => 'SIM',
            self::Passport => 'Passport',
            self::Other => 'Other',
        };
    }
}
