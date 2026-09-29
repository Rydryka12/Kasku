<?php

namespace App\Enums;

enum GoalStatus: string
{
    case Active = 'active';
    case Achieved = 'achieved';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::Achieved => 'Tercapai',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::Active => 'masuk',
            self::Achieved => 'batik',
            self::Cancelled => 'keluar',
        };
    }
}
