<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case Cash = 'cash';
    case Transfer = 'transfer';
    case DebitCard = 'debit_card';
    case CreditCard = 'credit_card';
    case Other = 'other';
    
    public function label(): string
    {
        return match($this) {
            self::Cash => 'Cash',
            self::Transfer => 'Transfer',
            self::DebitCard => 'Debit Card',
            self::CreditCard => 'Credit Card',
            self::Other => 'Other',
        };
    }
}
