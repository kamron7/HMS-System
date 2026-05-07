<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\InAppNotification;
use App\Models\MaintenanceRequest;
use App\Models\User;

class MaintenanceRequestObserver
{
    public function created(MaintenanceRequest $maintenance): void
    {
        if (! auth()->check()) return;

        ActivityLog::record(
            action: 'maintenance.created',
            subjectType: 'MaintenanceRequest',
            subjectId: $maintenance->id,
            subjectLabel: $maintenance->title,
            newValues: [
                'room_id'  => $maintenance->room_id,
                'priority' => $maintenance->priority->value,
                'status'   => $maintenance->status->value,
            ]
        );

        // Notify managers/owners about new maintenance
        $isUrgent = $maintenance->priority->value === 'urgent';
        $type  = $isUrgent ? 'maintenance_urgent' : 'maintenance_new';
        $title = $isUrgent
            ? "Срочная заявка на ремонт"
            : "Новая заявка на ремонт";
        $body = $maintenance->title . " (Номер: {$maintenance->room_id})";
        $url  = route('maintenance.show', $maintenance);
        $ref  = "maintenance_{$maintenance->id}";

        $recipients = User::whereIn('role', ['owner', 'manager'])->get();
        foreach ($recipients as $user) {
            InAppNotification::createIfNotExists(
                userId: $user->id,
                type: $type,
                title: $title,
                body: $body,
                reference: $ref,
                url: $url
            );
        }
    }

    public function updated(MaintenanceRequest $maintenance): void
    {
        if (! auth()->check()) return;

        if ($maintenance->wasChanged('status')) {
            ActivityLog::record(
                action: 'maintenance.status_changed',
                subjectType: 'MaintenanceRequest',
                subjectId: $maintenance->id,
                subjectLabel: $maintenance->title,
                oldValues: ['status' => $maintenance->getOriginal('status')],
                newValues: ['status' => $maintenance->status->value]
            );
        } else {
            ActivityLog::record(
                action: 'maintenance.updated',
                subjectType: 'MaintenanceRequest',
                subjectId: $maintenance->id,
                subjectLabel: $maintenance->title,
                newValues: $maintenance->getDirty()
            );
        }
    }

    public function deleted(MaintenanceRequest $maintenance): void
    {
        if (! auth()->check()) return;

        ActivityLog::record(
            action: 'maintenance.deleted',
            subjectType: 'MaintenanceRequest',
            subjectId: $maintenance->id,
            subjectLabel: $maintenance->title
        );
    }
}
