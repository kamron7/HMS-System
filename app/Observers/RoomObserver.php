<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Room;

class RoomObserver
{
    public function updated(Room $room): void
    {
        if (! auth()->check()) return;

        if ($room->wasChanged('status')) {
            ActivityLog::record(
                action: 'room.status_changed',
                subjectType: 'Room',
                subjectId: $room->id,
                subjectLabel: "Номер {$room->number}",
                oldValues: ['status' => $room->getOriginal('status')],
                newValues: ['status' => $room->status->value]
            );
        }
    }
}
