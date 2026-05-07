<?php

namespace App\Models;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Enums\PaymentType;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (Booking $booking) {
            if (empty($booking->booking_ref)) {
                do {
                    $ref = 'H-' . strtoupper(Str::random(6));
                } while (static::withTrashed()->where('booking_ref', $ref)->exists());

                $booking->booking_ref = $ref;
            }
        });
    }

    protected $fillable = [
        'room_id',
        'guest_id',
        'check_in_date',
        'check_in_time',
        'check_out_date',
        'check_out_time',
        'actual_check_in_at',
        'actual_check_out_at',
        'adults',
        'children',
        'status',
        'source',
        'total_price',
        'notes',
        'applied_promo_code',
        'discount_amount',
        'booking_ref',
        'invoice_number',
        'created_by',
        'booking_group_id',
    ];

    protected function casts(): array
    {
        return [
            'status'               => BookingStatus::class,
            'source'               => BookingSource::class,
            'check_in_date'        => 'date',
            'check_out_date'       => 'date',
            'actual_check_in_at'   => 'datetime',
            'actual_check_out_at'  => 'datetime',
            'total_price'          => 'decimal:2',
            'discount_amount'      => 'decimal:2',
            'deleted_at'           => 'datetime',
        ];
    }

    // Days difference: positive = late/overstay, negative = early
    public function checkInDiscrepancyDays(): ?int
    {
        if (! $this->actual_check_in_at) return null;
        $planned = $this->check_in_date->startOfDay();
        $actual  = $this->actual_check_in_at->copy()->startOfDay();
        return (int) $planned->diffInDays($actual, false);
    }

    public function checkOutDiscrepancyDays(): ?int
    {
        if (! $this->actual_check_out_at) return null;
        $planned = $this->check_out_date->startOfDay();
        $actual  = $this->actual_check_out_at->copy()->startOfDay();
        return (int) $planned->diffInDays($actual, false);
    }

    public function hasDiscrepancy(): bool
    {
        return ($this->checkInDiscrepancyDays() !== null && $this->checkInDiscrepancyDays() !== 0)
            || ($this->checkOutDiscrepancyDays() !== null && $this->checkOutDiscrepancyDays() !== 0);
    }

    public function isOverdue(): bool
    {
        return $this->status === BookingStatus::CheckedIn
            && $this->check_out_date->isPast()
            && ! $this->check_out_date->isToday();
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function guests(): BelongsToMany
    {
        return $this->belongsToMany(Guest::class, 'booking_guests')
                    ->withPivot('is_primary')
                    ->withTimestamps();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function charges(): HasMany
    {
        return $this->hasMany(BookingCharge::class);
    }

    public function inquiry(): HasOne
    {
        return $this->hasOne(BookingInquiry::class);
    }

    public function bookingGroup(): BelongsTo
    {
        return $this->belongsTo(BookingGroup::class);
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function serviceRequests(): HasMany
    {
        return $this->hasMany(GuestServiceRequest::class);
    }

    public function paymentStatus(): string
    {
        $paid = $this->relationLoaded('payments')
            ? (float) $this->payments->where('type', PaymentType::Prepayment->value)->sum('amount')
            : (float) $this->payments()->where('type', PaymentType::Prepayment->value)->sum('amount');
        $total = (float) $this->total_price;
        if ($paid <= 0) return 'unpaid';
        if ($paid < $total) return 'partial';
        return 'paid';
    }
}
