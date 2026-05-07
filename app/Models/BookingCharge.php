<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingCharge extends Model
{
    public $timestamps = false;
    const CREATED_AT = 'created_at';

    protected $fillable = [
        'booking_id',
        'description',
        'category',
        'amount',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount'     => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function categories(): array
    {
        return [
            'room_night'   => 'Проживание',
            'minibar'      => 'Мини-бар',
            'laundry'      => 'Стирка',
            'room_service' => 'Рум-сервис',
            'parking'      => 'Парковка',
            'spa'          => 'СПА',
            'other'        => 'Прочее',
        ];
    }
}
