<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    private const CATEGORIES = [
        'salary'      => 'Зарплата',
        'utilities'   => 'Коммунальные услуги',
        'supplies'    => 'Расходные материалы',
        'maintenance' => 'Техническое обслуживание',
        'other'       => 'Прочее',
    ];

    public function index(Request $request): View
    {
        $query = Expense::query()->with('creator')->orderBy('expense_date', 'desc');

        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }

        if ($month = $request->query('month')) {
            [$year, $monthNum] = explode('-', $month);
            $query->whereYear('expense_date', $year)->whereMonth('expense_date', $monthNum);
        }

        $total = (clone $query)->sum('amount');

        $expenses = $query->paginate(20)->withQueryString();

        return view('expenses.index', [
            'expenses'   => $expenses,
            'categories' => self::CATEGORIES,
            'total'      => $total,
            'filters'    => [
                'category' => $request->query('category', ''),
                'month'    => $request->query('month', ''),
            ],
        ]);
    }

    public function create(): View
    {
        return view('expenses.create', [
            'categories' => self::CATEGORIES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category'     => ['required', 'string', Rule::in(array_keys(self::CATEGORIES))],
            'description'  => ['required', 'string', 'max:500'],
            'amount'       => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date'],
        ]);

        Expense::create(array_merge($validated, ['created_by' => Auth::id()]));

        return redirect()->route('expenses.index')
            ->with('success', 'Расход успешно добавлен.');
    }

    public function edit(Expense $expense): View
    {
        return view('expenses.edit', [
            'expense'    => $expense,
            'categories' => self::CATEGORIES,
        ]);
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $validated = $request->validate([
            'category'     => ['required', 'string', Rule::in(array_keys(self::CATEGORIES))],
            'description'  => ['required', 'string', 'max:500'],
            'amount'       => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date'],
        ]);

        $expense->update($validated);

        return redirect()->route('expenses.index')
            ->with('success', 'Расход успешно обновлён.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', 'Расход удалён.');
    }
}
