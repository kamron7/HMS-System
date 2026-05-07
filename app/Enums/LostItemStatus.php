<?php

namespace App\Enums;

enum LostItemStatus: string
{
    case Found      = 'found';
    case Stored     = 'stored';
    case Returned   = 'returned';
    case Discarded  = 'discarded';

    public function label(): string
    {
        return match($this) {
            self::Found     => 'Найдено',
            self::Stored    => 'На хранении',
            self::Returned  => 'Возвращено',
            self::Discarded => 'Утилизировано',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Found     => 'blue',
            self::Stored    => 'yellow',
            self::Returned  => 'green',
            self::Discarded => 'gray',
        };
    }
}
