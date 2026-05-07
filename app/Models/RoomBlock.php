<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomBlock extends Model
{
    protected $fillable = [
        'room_id',
        'check_in_date',
        'check_out_date',
        'reason',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'check_in_date'  => 'date',
            'check_out_date' => 'date',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function reasonLabel(string $reason): string
    {
        return match($reason) {
            'cleaning'      => 'Уборка',
            'maintenance'   => 'Ремонт',
            'owner'         => 'Использование владельцем',
            'admin'         => 'Административная бронь',
            default         => 'Другое',
        };
    }
}
