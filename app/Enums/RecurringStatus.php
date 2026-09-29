<?php

namespace App\Enums;

enum RecurringStatus: string
{
    case Active = 'active';
    case Paused = 'paused';
    case Ended = 'ended';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::Paused => 'Dijeda',
            self::Ended => 'Selesai',
        };
    }
}
