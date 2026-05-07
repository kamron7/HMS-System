<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InAppNotification extends Model
{
    protected $table = 'notifications';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'url',
        'reference',
        'read_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at'    => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    /**
     * Create notification only if no notification with the same reference exists for the user.
     */
    public static function createIfNotExists(
        int $userId,
        string $type,
        string $title,
        string $body,
        string $reference,
        ?string $url = null
    ): void {
        $exists = static::where('user_id', $userId)
            ->where('reference', $reference)
            ->exists();

        if (! $exists) {
            static::create([
                'user_id'    => $userId,
                'type'       => $type,
                'title'      => $title,
                'body'       => $body,
                'url'        => $url,
                'reference'  => $reference,
                'created_at' => now(),
            ]);
        }
    }
}
