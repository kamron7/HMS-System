<?php

namespace App\Enums;

enum MaintenancePriority: string
{
    case Low    = 'low';
    case Medium = 'medium';
    case High   = 'high';
    case Urgent = 'urgent';

    public function label(): string
    {
        return match($this) {
            self::Low    => 'Низкий',
            self::Medium => 'Средний',
            self::High   => 'Высокий',
            self::Urgent => 'Срочный',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Low    => 'gray',
            self::Medium => 'yellow',
            self::High   => 'orange',
            self::Urgent => 'red',
        };
    }
}
