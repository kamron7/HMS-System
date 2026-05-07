<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $q = trim($request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $results = [];

        // Guests
        Guest::where('first_name', 'ILIKE', "%{$q}%")
            ->orWhere('last_name', 'ILIKE', "%{$q}%")
            ->orWhere('phone', 'ILIKE', "%{$q}%")
            ->limit(5)
            ->get()
            ->each(function (Guest $g) use (&$results) {
                $results[] = [
                    'type'  => 'guest',
                    'icon'  => 'user',
                    'label' => $g->first_name . ' ' . $g->last_name,
                    'sub'   => $g->phone,
                    'url'   => route('guests.show', $g),
                ];
            });

        // Bookings by invoice or guest name
        Booking::with('guest')
            ->where(function ($q2) use ($q) {
                $q2->where('invoice_number', 'ILIKE', "%{$q}%")
                   ->orWhereHas('guest', fn($gq) => $gq
                       ->where('first_name', 'ILIKE', "%{$q}%")
                       ->orWhere('last_name', 'ILIKE', "%{$q}%")
                       ->orWhere('phone', 'ILIKE', "%{$q}%")
                   );
            })
            ->limit(5)
            ->get()
            ->each(function (Booking $b) use (&$results) {
                $results[] = [
                    'type'  => 'booking',
                    'icon'  => 'calendar',
                    'label' => 'Брон. #' . $b->id . ($b->invoice_number ? ' — ' . $b->invoice_number : ''),
                    'sub'   => optional($b->guest)->first_name . ' ' . optional($b->guest)->last_name,
                    'url'   => route('bookings.show', $b),
                ];
            });

        // Rooms by number
        Room::with('roomType')
            ->where('number', 'ILIKE', "%{$q}%")
            ->limit(3)
            ->get()
            ->each(function (Room $r) use (&$results) {
                $results[] = [
                    'type'  => 'room',
                    'icon'  => 'door',
                    'label' => 'Номер ' . $r->number,
                    'sub'   => optional($r->roomType)->name . ' · Этаж ' . $r->floor,
                    'url'   => route('rooms.edit', $r),
                ];
            });

        return response()->json(array_slice($results, 0, 12));
    }
}
