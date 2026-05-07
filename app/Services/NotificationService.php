<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\InAppNotification;
use App\Models\MaintenanceRequest;
use App\Models\User;

class NotificationService
{
    /**
     * Notify owners/managers/receptionists about a new client booking inquiry.
     */
    public function notifyNewInquiry(Booking $booking): void
    {
        $targets = User::whereIn('role', ['owner', 'manager', 'receptionist'])
            ->where('is_active', true)
            ->pluck('id');

        foreach ($targets as $userId) {
            InAppNotification::createIfNotExists(
                userId: $userId,
                type: 'inquiry_new',
                title: 'Новый запрос от клиента',
                body: "Онлайн-запрос #{$booking->id} — " . ($booking->guest?->full_name ?? 'Новый гость') . ", заезд {$booking->check_in_date->format('d.m.Y')}",
                reference: "inquiry_{$booking->id}",
                url: route('bookings.show', $booking)
            );
        }
    }

    /**
     * Notify owners/managers about new maintenance request.
     */
    public function notifyNewMaintenance(MaintenanceRequest $req): void
    {
        $roles = match($req->priority->value ?? $req->priority) {
            'urgent' => ['owner', 'manager', 'receptionist'],
            'high'   => ['owner', 'manager'],
            default  => ['manager'],
        };

        $targets = User::whereIn('role', $roles)
            ->where('is_active', true)
            ->pluck('id');

        $type  = in_array($req->priority->value ?? $req->priority, ['urgent', 'high'])
            ? 'maintenance_urgent' : 'maintenance_new';
        $room  = $req->room?->number ?? '—';
        $guest = $req->guest?->full_name;
        $body  = $guest
            ? "Номер {$room} — {$req->title} (от гостя: {$guest})"
            : "Номер {$room} — {$req->title}";

        foreach ($targets as $userId) {
            InAppNotification::createIfNotExists(
                userId: $userId,
                type: $type,
                title: 'Заявка на обслуживание: ' . $req->title,
                body: $body,
                reference: "maintenance_{$req->id}",
                url: route('maintenance.show', $req)
            );
        }
    }

    /**
     * Notify receptionists/managers when a booking is confirmed (online or by staff).
     */
    public function notifyBookingConfirmed(Booking $booking): void
    {
        $targets = User::whereIn('role', ['owner', 'manager'])
            ->where('is_active', true)
            ->pluck('id');

        foreach ($targets as $userId) {
            InAppNotification::createIfNotExists(
                userId: $userId,
                type: 'booking_confirmed',
                title: 'Бронирование подтверждено',
                body: "#{$booking->id} — " . ($booking->guest?->full_name ?? '—') . ", номер {$booking->room?->number}, заезд {$booking->check_in_date->format('d.m.Y')}",
                reference: "confirmed_{$booking->id}",
                url: route('bookings.show', $booking)
            );
        }
    }

    /**
     * Notify the assigned staff member when a maintenance request is assigned to them.
     */
    public function notifyMaintenanceAssigned(MaintenanceRequest $req): void
    {
        if (! $req->assigned_to) return;

        InAppNotification::createIfNotExists(
            userId: $req->assigned_to,
            type: 'maintenance_assigned',
            title: 'Вам назначена заявка: ' . $req->title,
            body: 'Номер ' . ($req->room?->number ?? '—') . ' — ' . $req->title,
            reference: "maintenance_assigned_{$req->id}",
            url: route('maintenance.show', $req)
        );
    }

    /**
     * Notify the user who created the booking when a guest checks out (for debt awareness).
     */
    public function notifyCheckedOut(Booking $booking): void
    {
        if (! $booking->created_by) return;

        InAppNotification::createIfNotExists(
            userId: $booking->created_by,
            type: 'checkout_done',
            title: 'Гость выехал',
            body: "Бронирование #{$booking->id} — " . ($booking->guest?->full_name ?? '—') . " выехал из номера {$booking->room?->number}",
            reference: "checkout_{$booking->id}",
            url: route('bookings.show', $booking)
        );
    }
}
