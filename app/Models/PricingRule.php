<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingRule extends Model
{
    protected $fillable = [
        'name',
        'room_type_id',
        'date_from',
        'date_to',
        'modifier_type',
        'modifier_value',
        'priority',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date_from'      => 'date',
            'date_to'        => 'date',
            'modifier_type'  => 'string',
            'modifier_value' => 'decimal:2',
            'is_active'      => 'boolean',
            'priority'       => 'integer',
        ];
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
