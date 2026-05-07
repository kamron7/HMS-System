<?php

namespace App\Http\Controllers;

use App\Enums\CashierShiftStatus;
use App\Enums\PaymentType;
use App\Models\BookingCharge;
use App\Models\CashierShift;
use App\Models\Expense;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CashierShiftController extends Controller
{
    /** Dashboard: active shift OR open form + daily history */
    public function index(): View
    {
        $activeShift = CashierShift::with('user')
            ->where('status', CashierShiftStatus::Open->value)
            ->where('user_id', auth()->id())
            ->latest('opened_at')
            ->first();

        if ($activeShift) {
            $activeShift->load(['user']);
            $cashStats = $this->calculateCashFlow($activeShift);
            $activeShift->cash_in = $cashStats['in'];
            $activeShift->cash_out = $cashStats['out'];
            $activeShift->closing_expected = $activeShift->opening_actual + $cashStats['in'] - $cashStats['out'];
            $activeShift->save();

            $payments = Payment::with(['booking.guest', 'booking.room'])
                ->where('method', 'cash')
                ->where('paid_at', '>=', $activeShift->opened_at)
                ->orderByDesc('paid_at')
                ->limit(15)
                ->get();

            $cashExpenses = Expense::where('payment_method', 'cash')
                ->where('created_at', '>=', $activeShift->opened_at)
                ->orderByDesc('created_at')
                ->limit(15)
                ->get();

            // Daily history — last 20 shifts
            $history = CashierShift::with('user')
                ->where('status', CashierShiftStatus::Closed->value)
                ->orderByDesc('opened_at')
                ->limit(20)
                ->get();

            return view('cashier.index', compact('activeShift', 'payments', 'cashExpenses', 'history'));
        }

        // No active shift — show open form
        $now = now();
        $defaultShift = $now->hour < 14 ? 'morning' : ($now->hour < 22 ? 'evening' : 'night');

        // Today's shifts for summary
        $todayShiftes = CashierShift::with('user')
            ->where('status', CashierShiftStatus::Closed->value)
            ->whereDate('opened_at', $now->toDateString())
            ->get();

        $todayTotal = $todayShiftes->sum('cash_in') - $todayShiftes->sum('cash_out');

        return view('cashier.open', compact('defaultShift', 'todayShiftes', 'todayTotal'));
    }

    /** Open a new shift */
    public function open(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'opening_actual' => ['required', 'numeric', 'min:0'],
            'shift'          => ['required', 'in:morning,evening,night,custom'],
            'shift_custom'   => ['nullable', 'string', 'max:50'],
            'notes_open'     => ['nullable', 'string', 'max:500'],
        ]);

        // Check if user already has an open shift
        $existingOpen = CashierShift::where('status', CashierShiftStatus::Open->value)
            ->where('user_id', auth()->id())
            ->exists();
        if ($existingOpen) {
            return back()->withErrors(['opening_actual' => 'У вас уже есть открытая смена. Закройте её.']);
        }

        $shiftLabel = $validated['shift'] === 'custom'
            ? ($validated['shift_custom'] ?? 'Смена')
            : $validated['shift'];

        CashierShift::create([
            'user_id'        => auth()->id(),
            'opening_actual' => (float) $validated['opening_actual'],
            'shift'          => $shiftLabel,
            'notes_open'     => $validated['notes_open'] ?? null,
            'opened_at'      => now(),
            'status'         => CashierShiftStatus::Open->value,
        ]);

        return redirect()->route('cashier.index')
            ->with('success', "Смена открыта. Касса: " . number_format($validated['opening_actual'], 0, '.', ' ') . ' сум');
    }

    /** Close the active shift */
    public function close(Request $request): RedirectResponse
    {
        $activeShift = CashierShift::where('status', CashierShiftStatus::Open->value)
            ->where('user_id', auth()->id())
            ->latest('opened_at')
            ->first();

        if (! $activeShift) {
            return back()->withErrors(['closing_actual' => 'Нет открытой смены.']);
        }

        $validated = $request->validate([
            'closing_actual' => ['required', 'numeric', 'min:0'],
            'notes_close'    => ['nullable', 'string', 'max:500'],
        ]);

        $cashStats = $this->calculateCashFlow($activeShift);
        $closingExpected = $activeShift->opening_actual + $cashStats['in'] - $cashStats['out'];
        $closingActual = (float) $validated['closing_actual'];
        $closingDifference = $closingActual - $closingExpected;

        $activeShift->update([
            'cash_in'          => $cashStats['in'],
            'cash_out'         => $cashStats['out'],
            'closing_expected' => $closingExpected,
            'closing_actual'   => $closingActual,
            'closing_difference' => $closingDifference,
            'notes_close'      => $validated['notes_close'] ?? null,
            'closed_at'        => now(),
            'status'           => CashierShiftStatus::Closed->value,
        ]);

        return redirect()->route('cashier.index')
            ->with('success', "Смена закрыта. Разница: " . ($closingDifference >= 0 ? '+' : '') . number_format($closingDifference, 0, '.', ' ') . ' сум');
    }

    /** Detailed view of a closed shift */
    public function show(CashierShift $shift): View
    {
        if (auth()->user()->role->value !== 'owner' && $shift->user_id !== auth()->id()) {
            abort(403, 'Доступ запрещён.');
        }

        $shift->load(['user']);
        $closedAt = $shift->closed_at ?? now();

        $payments = Payment::with(['booking.guest', 'booking.room'])
            ->where('method', 'cash')
            ->whereBetween('paid_at', [$shift->opened_at, $closedAt])
            ->orderByDesc('paid_at')
            ->get();

        $charges = BookingCharge::with(['booking.guest', 'booking.room'])
            ->whereBetween('created_at', [$shift->opened_at, $closedAt])
            ->orderByDesc('created_at')
            ->get();

        $cashExpenses = Expense::with('creator')
            ->where('payment_method', 'cash')
            ->whereBetween('created_at', [$shift->opened_at, $closedAt])
            ->orderByDesc('created_at')
            ->get();

        // Merged timeline
        $timeline = collect();

        foreach ($payments as $p) {
            $timeline->push([
                'time' => $p->paid_at,
                'type' => $p->type->value === 'deposit' ? 'deposit' : 'payment',
                'amount' => $p->amount,
                'direction' => 'in',
                'description' => $p->type->value === 'deposit' ? 'Залог' : 'Оплата',
                'detail' => null,
                'guest' => $p->booking?->guest?->fullName,
                'guest_id' => $p->booking?->guest_id,
                'room_number' => $p->booking?->room?->number,
                'room_id' => $p->booking?->room_id,
                'booking_id' => $p->booking?->id,
                'booking_ref' => $p->booking?->booking_ref,
                'user' => null,
            ]);
        }

        foreach ($charges as $c) {
            $timeline->push([
                'time' => $c->created_at,
                'type' => 'charge',
                'amount' => $c->amount,
                'direction' => 'in',
                'description' => $c->description,
                'detail' => $c->category,
                'guest' => $c->booking?->guest?->fullName,
                'guest_id' => $c->booking?->guest_id,
                'room_number' => $c->booking?->room?->number,
                'room_id' => $c->booking?->room_id,
                'booking_id' => $c->booking?->id,
                'booking_ref' => $c->booking?->booking_ref,
                'user' => null,
            ]);
        }

        foreach ($cashExpenses as $e) {
            $timeline->push([
                'time' => $e->created_at,
                'type' => 'expense',
                'amount' => -abs($e->amount),
                'direction' => 'out',
                'description' => $e->description,
                'detail' => $e->category,
                'guest' => null,
                'guest_id' => null,
                'room_number' => null,
                'room_id' => null,
                'booking_id' => null,
                'booking_ref' => null,
                'user' => $e->creator?->name,
            ]);
        }

        $timeline = $timeline->sortByDesc('time')->values();

        return view('cashier.show', compact('shift', 'payments', 'charges', 'cashExpenses', 'timeline'));
    }

    /** Daily summary — all shifts for a given date */
    public function dailySummary(Request $request): View
    {
        $date = $request->date ?? now()->toDateString();

        $shifts = CashierShift::with('user')
            ->whereDate('opened_at', $date)
            ->orderBy('opened_at')
            ->get();

        $totalIn = $shifts->sum('cash_in');
        $totalOut = $shifts->sum('cash_out');
        $totalOpen = $shifts->sum('opening_actual');
        $totalClose = $shifts->sum('closing_actual');
        $totalDiff = $shifts->sum('closing_difference');

        // All operations for the day
        $dayStart = \Carbon\Carbon::parse($date)->startOfDay();
        $dayEnd = \Carbon\Carbon::parse($date)->endOfDay();

        $payments = Payment::with(['booking.guest', 'booking.room'])
            ->where('method', 'cash')
            ->whereBetween('paid_at', [$dayStart, $dayEnd])
            ->orderByDesc('paid_at')
            ->get();

        $cashExpenses = Expense::with('creator')
            ->where('payment_method', 'cash')
            ->whereBetween('created_at', [$dayStart, $dayEnd])
            ->orderByDesc('created_at')
            ->get();

        // Available dates
        $availableDates = CashierShift::selectRaw('DATE(opened_at) as d')
            ->where('status', CashierShiftStatus::Closed->value)
            ->groupBy('d')
            ->orderByDesc('d')
            ->pluck('d');

        return view('cashier.daily', compact('date', 'shifts', 'totalIn', 'totalOut', 'totalOpen', 'totalClose', 'totalDiff', 'payments', 'cashExpenses', 'availableDates'));
    }

    private function calculateCashFlow(CashierShift $shift): array
    {
        $cashIn = (float) Payment::where('method', 'cash')
            ->where('paid_at', '>=', $shift->opened_at)
            ->where('type', PaymentType::Prepayment->value)
            ->sum('amount');

        $depositIn = (float) Payment::where('method', 'cash')
            ->where('paid_at', '>=', $shift->opened_at)
            ->where('type', PaymentType::Deposit->value)
            ->sum('amount');

        $cashOut = (float) Payment::where('method', 'cash')
            ->where('paid_at', '>=', $shift->opened_at)
            ->where('amount', '<', 0)
            ->sum('amount');

        $cashExpenses = (float) Expense::where('payment_method', 'cash')
            ->where('created_at', '>=', $shift->opened_at)
            ->sum('amount');

        return [
            'in'  => $cashIn + $depositIn,
            'out' => abs($cashOut) + $cashExpenses,
        ];
    }
}
