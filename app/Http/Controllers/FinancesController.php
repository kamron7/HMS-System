<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinancesController extends Controller
{
    private const CATEGORIES = [
        'salary'      => 'Зарплата',
        'utilities'   => 'Коммунальные услуги',
        'supplies'    => 'Расходные материалы',
        'maintenance' => 'Техническое обслуживание',
        'other'       => 'Прочее',
    ];

    private const PAYMENT_METHODS = [
        'cash'     => 'Наличные',
        'card'     => 'Карта',
        'transfer' => 'Перевод',
        'other'    => 'Другое',
    ];

    public function index(Request $request): View
    {
        // Parse period (default: current month)
        $period = $request->query('period', now()->format('Y-m'));
        $parts  = explode('-', $period);
        $start  = Carbon::create($parts[0], $parts[1], 1)->startOfMonth();
        $end    = $start->copy()->endOfMonth();

        // Custom range override
        if ($request->has('start') && $request->has('end')) {
            $start = Carbon::parse($request->input('start'))->startOfDay();
            $end   = Carbon::parse($request->input('end'))->endOfDay();
        }

        // Period label
        $isCustomRange = $request->has('start') && $request->has('end');
        if ($isCustomRange) {
            $periodLabel = $start->format('d.m.Y') . ' — ' . $end->format('d.m.Y');
        } else {
            $monthNames = [
                1  => 'Январь',
                2  => 'Февраль',
                3  => 'Март',
                4  => 'Апрель',
                5  => 'Май',
                6  => 'Июнь',
                7  => 'Июль',
                8  => 'Август',
                9  => 'Сентябрь',
                10 => 'Октябрь',
                11 => 'Ноябрь',
                12 => 'Декабрь',
            ];
            $periodLabel = $monthNames[(int) $start->format('n')] . ' ' . $start->format('Y');
        }

        // Revenue: sum of payments paid_at in period
        $revenue = (float) Payment::whereBetween('paid_at', [$start, $end])->sum('amount');

        // Expenses: sum of expenses expense_date in period
        $expenses = (float) Expense::whereBetween('expense_date', [
            $start->toDateString(),
            $end->toDateString(),
        ])->sum('amount');

        // Profit
        $profit = $revenue - $expenses;

        // Breakdown: expenses by category
        $expenseByCategory = Expense::whereBetween('expense_date', [
            $start->toDateString(),
            $end->toDateString(),
        ])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->mapWithKeys(fn($row) => [$row->category => (float) $row->total]);

        // Revenue by method (payment methods)
        $revenueByMethod = Payment::whereBetween('paid_at', [$start, $end])
            ->selectRaw('method, SUM(amount) as total')
            ->groupBy('method')
            ->orderByDesc('total')
            ->get()
            ->mapWithKeys(fn($row) => [$row->method => (float) $row->total]);

        // Recent payments: last 10 in period
        $recentPayments = Payment::with('booking.guest')
            ->whereBetween('paid_at', [$start, $end])
            ->orderByDesc('paid_at')
            ->limit(10)
            ->get();

        // Recent expenses: last 10 in period
        $recentExpenses = Expense::with('creator')
            ->whereBetween('expense_date', [
                $start->toDateString(),
                $end->toDateString(),
            ])
            ->orderByDesc('expense_date')
            ->limit(10)
            ->get();

        return view('finances.index', compact(
            'revenue',
            'expenses',
            'profit',
            'expenseByCategory',
            'revenueByMethod',
            'recentPayments',
            'recentExpenses',
            'start',
            'end',
            'period',
            'periodLabel',
        ))->with([
            'categories'     => self::CATEGORIES,
            'paymentMethods' => self::PAYMENT_METHODS,
        ]);
    }
}
