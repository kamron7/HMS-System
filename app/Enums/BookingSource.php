<?php

namespace App\Enums;

enum BookingSource: string
{
    case Staff  = 'staff';
    case Client = 'client';

    public function label(): string
    {
        return match($this) {
            self::Staff  => 'Сотрудник',
            self::Client => 'Клиент',
        };
    }
}
