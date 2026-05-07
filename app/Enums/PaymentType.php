<?php

namespace App\Enums;

enum PaymentType: string
{
    case Prepayment = 'prepayment';
    case Deposit    = 'deposit';

    public function label(): string
    {
        return match($this) {
            self::Prepayment => 'Предоплата',
            self::Deposit    => 'Залог',
        };
    }
}
