<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
    protected $fillable = [
        'user_id',
        'worker_shift_id',
        'type',
        'logged_at',
        'ip_address',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'logged_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workerShift(): BelongsTo
    {
        return $this->belongsTo(WorkerShift::class);
    }

    public function typeLabel(): string
    {
        return match($this->type) {
            'check_in'      => 'Вход',
            'check_out'     => 'Выход',
            'break_start'   => 'Перерыв начался',
            'break_end'     => 'Перерыв закончился',
            default         => $this->type,
        };
    }

    public function typeColor(): string
    {
        return match($this->type) {
            'check_in'      => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
            'check_out'     => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
            'break_start'   => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
            'break_end'     => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
            default         => 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300',
        };
    }
}
