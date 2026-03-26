<?php

namespace App\Models;

use Database\Factories\RoomTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomType extends Model
{
    /** @use HasFactory<RoomTypeFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'base_price',
        'capacity',
        'description',
        'amenities',
    ];

    protected function casts(): array
    {
        return [
            'amenities' => 'array',
        ];
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}
