<?php

namespace App\Enums;

enum MaintenanceStatus: string
{
    case Open       = 'open';
    case InProgress = 'in_progress';
    case Resolved   = 'resolved';

    public function label(): string
    {
        return match($this) {
            self::Open       => 'Открыто',
            self::InProgress => 'В работе',
            self::Resolved   => 'Решено',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Open       => 'red',
            self::InProgress => 'yellow',
            self::Resolved   => 'green',
        };
    }
}
