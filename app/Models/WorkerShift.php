<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkerShift extends Model
{
    protected $fillable = [
        'user_id',
        'shift_type',
        'started_at',
        'ended_at',
        'start_note',
        'end_note',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at'   => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(AttendanceLog::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function getDurationAttribute(): ?float
    {
        if (! $this->ended_at) {
            return now()->diffInMinutes($this->started_at) / 60;
        }
        return $this->started_at->diffInMinutes($this->ended_at) / 60;
    }

    public function getDurationFormattedAttribute(): string
    {
        $hours = floor($this->duration);
        $minutes = ($this->duration - $hours) * 60;
        return sprintf('%dч %dм', $hours, round($minutes));
    }
}
