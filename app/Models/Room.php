<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\RoomStatus;
use Database\Factories\RoomFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

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
        'assigned_to',
        'qr_token',
        'images',
    ];

    protected static function booted(): void
    {
        static::creating(function (Room $room) {
            if (empty($room->qr_token)) {
                $room->qr_token = Str::random(32);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'status' => RoomStatus::class,
            'images' => 'array',
        ];
    }

    public function imageUrls(): array
    {
        return collect($this->images ?? [])->map(fn($p) => asset('storage/' . $p))->all();
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(GuestReview::class);
    }

    public function activeBooking(): ?Booking
    {
        return $this->bookings()
            ->where('status', BookingStatus::CheckedIn->value)
            ->with(['guest', 'charges'])
            ->latest()
            ->first();
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
