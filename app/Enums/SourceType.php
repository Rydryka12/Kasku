<?php

namespace App\Enums;

enum SourceType: string
{
    case Cash = 'cash';
    case Ewallet = 'ewallet';
    case Bank = 'bank';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::Ewallet => 'E-Wallet',
            self::Bank => 'Bank',
            self::Other => 'Lainnya',
        };
    }
}
