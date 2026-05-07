<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\User;

class UserObserver
{
    public function created(User $user): void
    {
        if (! auth()->check()) return;

        ActivityLog::record(
            action: 'user.created',
            subjectType: 'User',
            subjectId: $user->id,
            subjectLabel: $user->name,
            newValues: [
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role->value,
            ]
        );
    }

    public function updated(User $user): void
    {
        if (! auth()->check()) return;

        $skip = ['updated_at', 'created_at', 'remember_token', 'email_verified_at', 'avatar'];

        $dirty = collect($user->getDirty())
            ->except($skip)
            ->when(fn($c) => $c->has('password'), fn($c) => $c->put('password', '***'))
            ->all();

        if (empty($dirty)) return;

        ActivityLog::record(
            action: 'user.updated',
            subjectType: 'User',
            subjectId: $user->id,
            subjectLabel: $user->name,
            newValues: $dirty
        );
    }

    public function deleted(User $user): void
    {
        if (! auth()->check()) return;

        ActivityLog::record(
            action: 'user.deleted',
            subjectType: 'User',
            subjectId: $user->id,
            subjectLabel: $user->name
        );
    }
}
