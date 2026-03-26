<?php

namespace App\Enums;

enum RoomStatus: string
{
    case Available   = 'available';
    case Occupied    = 'occupied';
    case Cleaning    = 'cleaning';
    case Maintenance = 'maintenance';

    public function label(): string
    {
        return match($this) {
            self::Available   => 'Свободен',
            self::Occupied    => 'Занят',
            self::Cleaning    => 'Уборка',
            self::Maintenance => 'Обслуживание',
        };
    }
}
