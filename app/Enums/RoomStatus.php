<?php

namespace App\Enums;

enum RoomStatus: string
{
    case Available   = 'available';
    case Occupied    = 'occupied';
    case Dirty       = 'dirty';
    case Cleaning    = 'cleaning';
    case Inspected   = 'inspected';
    case Maintenance = 'maintenance';

    public function label(): string
    {
        return match($this) {
            self::Available   => 'Свободен',
            self::Occupied    => 'Занят',
            self::Dirty       => 'Грязный',
            self::Cleaning    => 'Уборка',
            self::Inspected   => 'Проверен',
            self::Maintenance => 'Ремонт',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Available   => 'green',
            self::Occupied    => 'red',
            self::Dirty       => 'orange',
            self::Cleaning    => 'yellow',
            self::Inspected   => 'blue',
            self::Maintenance => 'gray',
        };
    }

    public function canCheckIn(): bool
    {
        return in_array($this, [self::Available, self::Inspected, self::Occupied]);
    }

    public function canBook(): bool
    {
        return ! in_array($this, [self::Maintenance, self::Cleaning]);
    }
}
