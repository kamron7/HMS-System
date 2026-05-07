<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingCharge;
use App\Enums\BookingStatus;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class GuestPortalController extends Controller
{
    public function show(Request $request, Booking $booking)
    {
        if (! $request->hasValidSignature()) {
            return view('guest.expired');
        }

        if (in_array($booking->status, [BookingStatus::CheckedOut, BookingStatus::Cancelled])) {
            return view('guest.expired');
        }

        $booking->load(['guest', 'room.roomType', 'payments', 'charges']);

        $nights    = $booking->check_in_date->diffInDays($booking->check_out_date);
        $roomCost  = $booking->total_price;
        $charges   = $booking->charges->sum('amount');
        $grandTotal = $roomCost + $charges;
        $paid      = $booking->payments->sum('amount');
        $balanceDue = max(0, $grandTotal - $paid);

        $upsells = collect(config('hotel.upsells'));

        return view('guest.booking', compact(
            'booking', 'nights', 'roomCost', 'charges', 'grandTotal', 'paid', 'balanceDue', 'upsells'
        ));
    }

    public function upsell(Request $request, Booking $booking)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Ссылка недействительна или истекла.');
        }

        if (in_array($booking->status, [BookingStatus::CheckedOut, BookingStatus::Cancelled])) {
            abort(403, 'Бронирование завершено.');
        }

        $validated = $request->validate([
            'key' => ['required', 'string'],
        ]);

        $upsells = collect(config('hotel.upsells'))->keyBy('key');
        $upsell  = $upsells->get($validated['key']);

        if (! $upsell) {
            abort(422, 'Услуга не найдена.');
        }

        BookingCharge::create([
            'booking_id'  => $booking->id,
            'description' => $upsell['label'],
            'category'    => 'other',
            'amount'      => $upsell['price'],
            'created_by'  => 1, // system / owner
        ]);

        return back()->with('upsell_added', $upsell['label']);
    }
}
