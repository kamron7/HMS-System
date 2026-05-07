<?php

namespace App\Enums;

enum CashierShiftStatus: string
{
    case Open   = 'open';
    case Closed = 'closed';

    public function label(): string
    {
        return match($this) {
            self::Open   => 'Открыта',
            self::Closed => 'Закрыта',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Open   => 'green',
            self::Closed => 'gray',
        };
    }
}
