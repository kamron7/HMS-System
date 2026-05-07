<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestServiceRequest extends Model
{
    protected $fillable = [
        'booking_id',
        'room_id',
        'upsell_key',
        'label',
        'price_per_unit',
        'quantity',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'price_per_unit' => 'float',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function getTotalPriceAttribute(): float
    {
        return $this->price_per_unit * $this->quantity;
    }
}
