<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\RoomStatus;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Room;

class DashboardController extends Controller
{
    public function index()
    {
        $roomStats = [
            'available'   => Room::where('status', RoomStatus::Available->value)->count(),
            'occupied'    => Room::where('status', RoomStatus::Occupied->value)->count(),
            'cleaning'    => Room::where('status', RoomStatus::Cleaning->value)->count(),
            'maintenance' => Room::where('status', RoomStatus::Maintenance->value)->count(),
            'total'       => Room::count(),
        ];

        $occupancyRate = $roomStats['total'] > 0
            ? round($roomStats['occupied'] / $roomStats['total'] * 100)
            : 0;

        $today = today();

        $checkInsToday = Booking::whereDate('check_in_date', $today)
            ->whereIn('status', [BookingStatus::Confirmed->value, BookingStatus::CheckedIn->value])
            ->with(['guest', 'room'])
            ->get();

        $checkOutsToday = Booking::whereDate('check_out_date', $today)
            ->where('status', BookingStatus::CheckedIn->value)
            ->with(['guest', 'room'])
            ->get();

        $revenueToday = (float) Payment::whereDate('paid_at', $today)->sum('amount');

        $pendingBookings = Booking::where('status', BookingStatus::Pending->value)
            ->with(['guest', 'room.roomType'])
            ->orderBy('check_in_date')
            ->limit(5)
            ->get();

        $upcomingCheckIns = Booking::whereBetween('check_in_date', [
                $today->copy()->addDay(),
                $today->copy()->addDays(7),
            ])
            ->whereIn('status', [BookingStatus::Pending->value, BookingStatus::Confirmed->value])
            ->with(['guest', 'room.roomType'])
            ->orderBy('check_in_date')
            ->get();

        return view('dashboard', compact(
            'roomStats',
            'occupancyRate',
            'checkInsToday',
            'checkOutsToday',
            'revenueToday',
            'pendingBookings',
            'upcomingCheckIns',
        ));
    }
}
