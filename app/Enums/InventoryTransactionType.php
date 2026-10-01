<?php

namespace App\Enums;

enum InventoryTransactionType: string
{
    case StockIn = 'stock_in';
    case StockOut = 'stock_out';
    case Adjustment = 'adjustment';
    
    public function label(): string
    {
        return match($this) {
            self::StockIn => 'Stock In',
            self::StockOut => 'Stock Out',
            self::Adjustment => 'Adjustment',
        };
    }
}
