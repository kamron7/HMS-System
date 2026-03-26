<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Pending    = 'pending';
    case Confirmed  = 'confirmed';
    case CheckedIn  = 'checked_in';
    case CheckedOut = 'checked_out';
    case Cancelled  = 'cancelled';

    public function allowedTransitions(): array
    {
        return match($this) {
            self::Pending    => [self::Confirmed, self::CheckedIn, self::Cancelled],
            self::Confirmed  => [self::CheckedIn, self::Cancelled],
            self::CheckedIn  => [self::CheckedOut, self::Cancelled],
            self::CheckedOut => [],
            self::Cancelled  => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions());
    }

    public function label(): string
    {
        return match($this) {
            self::Pending    => 'Ожидает',
            self::Confirmed  => 'Подтверждён',
            self::CheckedIn  => 'Заселён',
            self::CheckedOut => 'Выехал',
            self::Cancelled  => 'Отменён',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Pending    => 'yellow',
            self::Confirmed  => 'blue',
            self::CheckedIn  => 'green',
            self::CheckedOut => 'gray',
            self::Cancelled  => 'red',
        };
    }
}
