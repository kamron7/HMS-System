<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function store(Request $request, Booking $booking): RedirectResponse
    {
        if ($booking->status === BookingStatus::Cancelled) {
            return back()->with('error', 'Нельзя добавить платёж к отменённому бронированию.');
        }

        $validated = $request->validate([
            'amount'  => ['required', 'numeric', 'min:0.01'],
            'type'    => ['required', 'string', Rule::in(['prepayment', 'deposit'])],
            'method'  => ['required', 'string', Rule::in(['cash', 'card', 'transfer', 'other'])],
            'paid_at' => ['required', 'date'],
            'notes'   => ['nullable', 'string', 'max:500'],
        ]);

        $booking->payments()->create([
            'amount'  => $validated['amount'],
            'type'    => $validated['type'],
            'method'  => $validated['method'],
            'paid_at' => $validated['paid_at'],
            'notes'   => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Платёж добавлен');
    }
}
