<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    protected $fillable = [
        'code',
        'discount_percent',
        'valid_from',
        'valid_to',
        'max_uses',
        'uses_count',
        'is_active',
        'room_type_ids',
    ];

    protected function casts(): array
    {
        return [
            'valid_from'       => 'date',
            'valid_to'         => 'date',
            'discount_percent' => 'decimal:2',
            'is_active'        => 'boolean',
            'room_type_ids'    => 'array',
        ];
    }

    public function isValid(?int $roomTypeId = null): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $today = now()->toDateString();

        if ($this->valid_from && $this->valid_from->toDateString() > $today) {
            return false;
        }

        if ($this->valid_to && $this->valid_to->toDateString() < $today) {
            return false;
        }

        if ($this->max_uses !== null && $this->uses_count >= $this->max_uses) {
            return false;
        }

        // Room type restriction: if the promo limits to specific types, check the room
        if ($roomTypeId !== null && ! empty($this->room_type_ids)) {
            if (! in_array($roomTypeId, array_map('intval', $this->room_type_ids))) {
                return false;
            }
        }

        return true;
    }
}
