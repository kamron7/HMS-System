<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChargeRequest;
use App\Models\Booking;
use App\Models\BookingCharge;
use Illuminate\Http\RedirectResponse;

class BookingChargeController extends Controller
{
    public function store(StoreChargeRequest $request, Booking $booking): RedirectResponse
    {
        $booking->charges()->create([
            'description' => $request->description,
            'category'    => $request->category,
            'amount'      => $request->amount,
            'created_by'  => auth()->id(),
        ]);

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Услуга добавлена.');
    }

    public function destroy(Booking $booking, BookingCharge $charge): RedirectResponse
    {
        abort_unless(
            auth()->user()->role->value === 'owner' || auth()->user()->role->value === 'manager',
            403
        );

        abort_unless($charge->booking_id === $booking->id, 404);

        $charge->delete();

        return redirect()->route('bookings.show', $booking)
            ->with('success', 'Услуга удалена.');
    }
}
