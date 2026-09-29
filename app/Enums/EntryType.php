<?php

namespace App\Enums;

enum EntryType: string
{
    case Deposit = 'deposit';
    case Withdraw = 'withdraw';

    public function label(): string
    {
        return match ($this) {
            self::Deposit => 'Setor',
            self::Withdraw => 'Tarik',
        };
    }
}
