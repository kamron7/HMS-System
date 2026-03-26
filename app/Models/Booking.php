<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    protected $fillable = [
        'room_id',
        'guest_id',
        'check_in_date',
        'check_out_date',
        'adults',
        'children',
        'status',
        'total_price',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status'         => BookingStatus::class,
            'check_in_date'  => 'date',
            'check_out_date' => 'date',
            'total_price'    => 'decimal:2',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function paymentStatus(): string
    {
        $paid = (float) $this->payments()->sum('amount');
        $total = (float) $this->total_price;
        if ($paid <= 0) return 'unpaid';
        if ($paid < $total) return 'partial';
        return 'paid';
    }
}
