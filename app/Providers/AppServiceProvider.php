<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\Expense;
use App\Models\Guest;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Room;
use App\Models\User;
use App\Observers\BookingObserver;
use App\Observers\ExpenseObserver;
use App\Observers\GuestObserver;
use App\Observers\MaintenanceRequestObserver;
use App\Observers\PaymentObserver;
use App\Observers\RoomObserver;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Booking::observe(BookingObserver::class);
        Payment::observe(PaymentObserver::class);
        Guest::observe(GuestObserver::class);
        Expense::observe(ExpenseObserver::class);
        Room::observe(RoomObserver::class);
        User::observe(UserObserver::class);
        MaintenanceRequest::observe(MaintenanceRequestObserver::class);
    }
}
