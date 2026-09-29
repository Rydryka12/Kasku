<?php

namespace App\Enums;

enum DebtStatus: string
{
    case Unpaid = 'unpaid';
    case Partial = 'partial';
    case Paid = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'Belum Dibayar',
            self::Partial => 'Sebagian',
            self::Paid => 'Lunas',
        };
    }
}
