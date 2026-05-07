<?php

namespace App\Enums;

enum UserRole: string
{
    case Owner        = 'owner';
    case Manager      = 'manager';
    case Receptionist = 'receptionist';
    case Housekeeper  = 'housekeeper';
    case Security     = 'security';
    case Accountant   = 'accountant';

    public function label(): string
    {
        return match($this) {
            self::Owner        => 'Владелец',
            self::Manager      => 'Менеджер',
            self::Receptionist => 'Администратор',
            self::Housekeeper  => 'Уборщица',
            self::Security     => 'Охранник',
            self::Accountant   => 'Бухгалтер',
        };
    }

    /**
     * Sections each role can access.
     * 'all' = unrestricted.
     */
    public function permissions(): array
    {
        return match($this) {
            self::Owner => ['all'],

            self::Manager => [
                'dashboard', 'bookings', 'guests', 'housekeeping', 'maintenance',
                'rooms', 'room_types', 'reports', 'expenses', 'finances', 'debt',
                'notifications', 'shift_notes', 'activity',
            ],

            self::Receptionist => [
                'dashboard', 'bookings', 'guests', 'housekeeping', 'maintenance',
                'notifications', 'shift_notes',
            ],

            self::Housekeeper => [
                'dashboard', 'housekeeping', 'maintenance',
                'notifications', 'shift_notes',
            ],

            self::Security => [
                'dashboard', 'bookings',
                'notifications',
            ],

            self::Accountant => [
                'dashboard', 'reports', 'expenses', 'finances', 'debt',
                'notifications',
            ],
        };
    }

    public function can(string $section): bool
    {
        $perms = $this->permissions();
        return in_array('all', $perms) || in_array($section, $perms);
    }

    /** Where to redirect after login. */
    public function homeRoute(): string
    {
        return match($this) {
            self::Housekeeper => 'housekeeping.index',
            self::Security    => 'bookings.index',
            self::Accountant  => 'reports.index',
            default           => 'dashboard',
        };
    }
}
