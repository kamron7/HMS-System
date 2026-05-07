<?php

namespace App\Models;

use App\Enums\LostItemStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LostItem extends Model
{
    protected $fillable = [
        'title',
        'description',
        'status',
        'found_by',
        'room_id',
        'guest_id',
        'booking_id',
        'storage_location',
        'found_at',
        'returned_at',
        'photos',
    ];

    protected function casts(): array
    {
        return [
            'status'      => LostItemStatus::class,
            'found_at'    => 'date',
            'returned_at' => 'date',
        ];
    }

    public function foundBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'found_by');
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
