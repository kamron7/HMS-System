<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    private string $token;

    /**
     * All notification types with their human-readable labels.
     * null telegram_notifications on a user = receives all types.
     */
    public static array $types = [
        'booking_new'                => 'Новое бронирование (от сотрудника)',
        'booking_inquiry'            => 'Новый запрос от клиента',
        'booking_confirmed'          => 'Бронирование подтверждено',
        'booking_checkin'            => 'Заселение',
        'booking_checkout'           => 'Выселение',
        'booking_cancelled'          => 'Отмена / Не явился',
        'maintenance_new'            => 'Заявка на обслуживание',
        'feedback_negative'          => 'Негативный отзыв',
        'alert_checkin_unconfirmed'  => 'Заезд не подтверждён (авто)',
        'alert_stale_pending'        => 'Бронирование ожидает >24ч (авто)',
        'alert_payment_overdue'      => 'Задолженность по оплате (авто)',
        'alert_late_checkout'        => 'Просроченный выезд (авто)',
    ];

    public function __construct()
    {
        $this->token = config('services.telegram.bot_token', '');
    }

    public function sendMessage(User $user, string $text): void
    {
        if (empty($this->token) || empty($user->telegram_chat_id)) {
            return;
        }

        try {
            Http::timeout(5)->post("https://api.telegram.org/bot{$this->token}/sendMessage", [
                'chat_id'    => $user->telegram_chat_id,
                'text'       => $text,
                'parse_mode' => 'HTML',
            ]);
        } catch (\Throwable $e) {
            Log::warning('Telegram send failed for user ' . $user->id . ': ' . $e->getMessage());
        }
    }

    /**
     * Send to all users in $roles, respecting per-user notification preferences.
     * $type must match a key in self::$types. Users with null preferences receive all types.
     */
    public function sendTyped(string $type, array $roles, string $text): void
    {
        User::whereIn('role', $roles)
            ->whereNotNull('telegram_chat_id')
            ->where('is_active', true)
            ->get()
            ->each(function (User $user) use ($type, $text) {
                $prefs = $user->telegram_notifications;
                if ($prefs === null || in_array($type, $prefs)) {
                    $this->sendMessage($user, $text);
                }
            });
    }

    /**
     * Send to all users in $roles regardless of notification preferences.
     * Kept for backward compatibility; prefer sendTyped() for new calls.
     */
    public function sendToRoles(array $roles, string $text): void
    {
        User::whereIn('role', $roles)
            ->whereNotNull('telegram_chat_id')
            ->where('is_active', true)
            ->get()
            ->each(fn(User $user) => $this->sendMessage($user, $text));
    }
}
