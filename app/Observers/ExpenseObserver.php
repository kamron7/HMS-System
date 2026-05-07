<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Expense;

class ExpenseObserver
{
    public function created(Expense $expense): void
    {
        if (! auth()->check()) return;

        ActivityLog::record(
            action: 'expense.created',
            subjectType: 'Expense',
            subjectId: $expense->id,
            subjectLabel: $expense->description ?? "Расход #{$expense->id}",
            newValues: [
                'amount'      => $expense->amount,
                'category'    => $expense->category,
                'description' => $expense->description,
            ]
        );
    }

    public function updated(Expense $expense): void
    {
        if (! auth()->check()) return;

        ActivityLog::record(
            action: 'expense.updated',
            subjectType: 'Expense',
            subjectId: $expense->id,
            subjectLabel: $expense->description ?? "Расход #{$expense->id}",
            oldValues: array_intersect_key($expense->getOriginal(), $expense->getDirty()),
            newValues: $expense->getDirty()
        );
    }

    public function deleted(Expense $expense): void
    {
        if (! auth()->check()) return;

        ActivityLog::record(
            action: 'expense.deleted',
            subjectType: 'Expense',
            subjectId: $expense->id,
            subjectLabel: $expense->description ?? "Расход #{$expense->id}"
        );
    }
}
