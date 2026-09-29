<?php

namespace App\Enums;

enum DebtType: string
{
    case Debt = 'debt';
    case Receivable = 'receivable';

    public function label(): string
    {
        return match ($this) {
            self::Debt => 'Hutang',
            self::Receivable => 'Piutang',
        };
    }
}
