<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $query = Booking::with(['guest', 'room.roomType'])
            ->orderBy('check_in_date', 'desc');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->query('search')) {
            $query->whereHas('guest', function ($q) use ($search) {
                $q->where(function ($inner) use ($search) {
                    $inner->whereRaw('first_name ILIKE ?', ["%{$search}%"])
                          ->orWhereRaw('last_name ILIKE ?', ["%{$search}%"])
                          ->orWhereRaw('phone ILIKE ?', ["%{$search}%"]);
                });
            });
        }

        if ($checkIn = $request->query('check_in')) {
            $query->where('check_in_date', '>=', $checkIn);
        }

        if ($checkOut = $request->query('check_out')) {
            $query->where('check_out_date', '<=', $checkOut);
        }

        $bookings = $query->paginate(20)->appends($request->query());
        $statuses = BookingStatus::cases();

        return view('bookings.index', [
            'bookings'  => $bookings,
            'statuses'  => $statuses,
            'search'    => $request->query('search', ''),
            'status'    => $request->query('status', ''),
            'check_in'  => $request->query('check_in', ''),
            'check_out' => $request->query('check_out', ''),
        ]);
    }

    public function show(Booking $booking): View
    {
        $booking->load([
            'guest',
            'room.roomType',
            'payments' => fn($q) => $q->orderBy('paid_at', 'asc'),
            'creator',
        ]);

        $paymentStatus = $booking->paymentStatus();

        return view('bookings.show', compact('booking', 'paymentStatus'));
    }

    public function create()
    {
        abort(404);
    }

    public function store(Request $request)
    {
        abort(404);
    }

    public function edit(Booking $booking)
    {
        abort(404);
    }

    public function update(Request $request, Booking $booking)
    {
        abort(404);
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        abort(404);
    }
}
