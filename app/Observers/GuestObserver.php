<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Guest;

class GuestObserver
{
    public function created(Guest $guest): void
    {
        if (! auth()->check()) return;

        ActivityLog::record(
            action: 'guest.created',
            subjectType: 'Guest',
            subjectId: $guest->id,
            subjectLabel: $guest->fullName,
            newValues: [
                'first_name' => $guest->first_name,
                'last_name'  => $guest->last_name,
                'phone'      => $guest->phone,
            ]
        );
    }

    public function updated(Guest $guest): void
    {
        if (! auth()->check()) return;

        ActivityLog::record(
            action: 'guest.updated',
            subjectType: 'Guest',
            subjectId: $guest->id,
            subjectLabel: $guest->fullName,
            oldValues: array_intersect_key($guest->getOriginal(), $guest->getDirty()),
            newValues: $guest->getDirty()
        );
    }

    public function deleted(Guest $guest): void
    {
        if (! auth()->check()) return;

        ActivityLog::record(
            action: 'guest.deleted',
            subjectType: 'Guest',
            subjectId: $guest->id,
            subjectLabel: $guest->fullName
        );
    }
}
