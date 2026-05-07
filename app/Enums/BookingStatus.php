<?php

namespace App\Enums;

enum BookingStatus: string
{
    case Inquiry    = 'inquiry';
    case Pending    = 'pending';
    case Confirmed  = 'confirmed';
    case CheckedIn  = 'checked_in';
    case CheckedOut = 'checked_out';
    case Cancelled  = 'cancelled';
    case NoShow     = 'no_show';

    public function allowedTransitions(): array
    {
        return match($this) {
            self::Inquiry    => [self::Pending, self::Cancelled],
            self::Pending    => [self::Confirmed, self::CheckedIn, self::Cancelled, self::NoShow],
            self::Confirmed  => [self::CheckedIn, self::Cancelled, self::NoShow],
            self::CheckedIn  => [self::CheckedOut, self::Cancelled],
            self::CheckedOut => [],
            self::Cancelled  => [],
            self::NoShow     => [self::Cancelled],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions());
    }

    public function label(): string
    {
        return match($this) {
            self::Inquiry    => 'Запрос',
            self::Pending    => 'Ожидает',
            self::Confirmed  => 'Подтверждён',
            self::CheckedIn  => 'Заселён',
            self::CheckedOut => 'Выехал',
            self::Cancelled  => 'Отменён',
            self::NoShow     => 'Не явился',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Inquiry    => 'purple',
            self::Pending    => 'yellow',
            self::Confirmed  => 'blue',
            self::CheckedIn  => 'green',
            self::CheckedOut => 'gray',
            self::Cancelled  => 'red',
            self::NoShow     => 'orange',
        };
    }
}
