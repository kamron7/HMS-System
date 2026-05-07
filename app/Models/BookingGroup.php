<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class BookingGroup extends Model
{
    protected $fillable = [
        'group_ref',
        'name',
        'created_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (BookingGroup $group) {
            if (empty($group->group_ref)) {
                do {
                    $ref = 'GRP-' . strtoupper(Str::random(6));
                } while (static::where('group_ref', $ref)->exists());

                $group->group_ref = $ref;
            }
        });
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function totalPrice(): float
    {
        return (float) $this->bookings->sum('total_price');
    }
}
