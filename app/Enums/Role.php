<?php

namespace App\Enums;

enum Role: string
{
    case User = 'user';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::User => 'Pengguna',
            self::Admin => 'Administrator',
        };
    }
}
