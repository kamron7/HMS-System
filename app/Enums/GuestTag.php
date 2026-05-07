<?php

namespace App\Enums;

enum GuestTag: string
{
    case Vip       = 'vip';
    case Regular   = 'regular';
    case Blacklist = 'blacklist';

    public function label(): string
    {
        return match($this) {
            self::Vip       => 'ВИП',
            self::Regular   => 'Обычный',
            self::Blacklist => 'Чёрный список',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Vip       => 'gold',
            self::Regular   => 'gray',
            self::Blacklist => 'red',
        };
    }
}
