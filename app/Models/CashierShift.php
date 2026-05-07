<?php

namespace App\Models;

use App\Enums\CashierShiftStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashierShift extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'opening_actual',
        'shift',
        'opened_at',
        'closed_at',
        'cash_in',
        'cash_out',
        'closing_expected',
        'closing_actual',
        'closing_difference',
        'notes_open',
        'notes_close',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status'           => CashierShiftStatus::class,
            'opened_at'        => 'datetime',
            'closed_at'        => 'datetime',
            'opening_actual'   => 'decimal:2',
            'cash_in'          => 'decimal:2',
            'cash_out'         => 'decimal:2',
            'closing_expected' => 'decimal:2',
            'closing_actual'   => 'decimal:2',
            'closing_difference' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** All payments made during this shift (cash only) */
    public function cashPayments(): HasMany
    {
        return $this->hasMany(Payment::class)->where('method', 'cash');
    }

    /** All cash expenses during this shift */
    public function cashExpenses(): HasMany
    {
        return $this->hasMany(Expense::class)->where('payment_method', 'cash');
    }
}
