<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\RoomStatus;
use Database\Factories\RoomFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    /** @use HasFactory<RoomFactory> */
    use HasFactory;

    protected $fillable = [
        'room_type_id',
        'number',
        'floor',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => RoomStatus::class,
        ];
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function isAvailable(string $checkIn, string $checkOut): bool
    {
        return ! $this->bookings()
            ->whereNotIn('status', [
                BookingStatus::Cancelled->value,
                BookingStatus::CheckedOut->value,
            ])
            ->where('check_in_date', '<', $checkOut)
            ->where('check_out_date', '>', $checkIn)
            ->exists();
    }
}
