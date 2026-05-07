<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Night audit — runs daily at 01:00, posts room_night charges and flags no-shows
Schedule::command('audit:night')->dailyAt('01:00');

// Feedback emails — runs daily at 10:00, sends post-stay review requests
Schedule::command('feedback:send')->dailyAt('10:00');

// Automated emails: pre-arrival reminders (daily at 09:00) + post-checkout thank you (every 2 hours)
Schedule::command('emails:send-automated')->everyTwoHours();

// Telegram alerts: stale bookings, unconfirmed check-ins, overdue payments — every 30 minutes
Schedule::command('telegram:alerts')->everyThirtyMinutes();
