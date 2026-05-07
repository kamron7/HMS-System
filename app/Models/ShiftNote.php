<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftNote extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'body',
        'shift',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shiftLabel(): string
    {
        return match($this->shift) {
            'morning' => 'Утро',
            'evening' => 'Вечер',
            'night'   => 'Ночь',
            default   => $this->shift,
        };
    }

    public function shiftColor(): string
    {
        return match($this->shift) {
            'morning' => 'bg-amber-100 text-amber-800',
            'evening' => 'bg-blue-100 text-blue-800',
            'night'   => 'bg-slate-100 text-slate-700',
            default   => 'bg-slate-100 text-slate-700',
        };
    }
}
