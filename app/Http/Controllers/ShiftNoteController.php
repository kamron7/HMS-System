<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\MaintenanceRequest;
use App\Models\Room;
use App\Models\ShiftNote;
use Illuminate\Http\Request;

class ShiftNoteController extends Controller
{
    public function index()
    {
        $notes = ShiftNote::with('user')
            ->where('created_at', '>=', now()->subDays(7))
            ->orderByDesc('created_at')
            ->get();

        // Dashboard data
        $today = now()->toDateString();

        // Today's arrivals (Confirmed/Pending checking in today)
        $arrivals = Booking::with(['guest', 'room'])
            ->whereDate('check_in_date', $today)
            ->whereIn('status', [BookingStatus::Pending, BookingStatus::Confirmed])
            ->orderBy('check_in_date')
            ->get();

        // Today's departures
        $departures = Booking::with(['guest', 'room'])
            ->whereDate('check_out_date', $today)
            ->where('status', BookingStatus::CheckedIn->value)
            ->orderBy('check_out_date')
            ->get();

        // Currently checked-in guests (VIPs)
        $vips = Booking::with(['guest', 'room'])
            ->where('status', BookingStatus::CheckedIn->value)
            ->whereHas('guest', fn($q) => $q->where('tag', 'vip'))
            ->get();

        // Open maintenance tickets
        $openMaintenance = MaintenanceRequest::with(['room', 'guest'])
            ->whereIn('status', ['open', 'in_progress'])
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 0 WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
            ->get();

        // Dirty rooms waiting for cleaning
        $dirtyRooms = Room::with('roomType')
            ->where('status', 'dirty')
            ->orderBy('floor')
            ->orderBy('number')
            ->get();

        // Rooms currently being cleaned
        $cleaningRooms = Room::with('roomType')
            ->where('status', 'cleaning')
            ->orderBy('floor')
            ->orderBy('number')
            ->get();

        return view('shift-notes.index', compact(
            'notes', 'arrivals', 'departures', 'vips',
            'openMaintenance', 'dirtyRooms', 'cleaningRooms'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'body'  => ['required', 'string', 'max:2000'],
            'shift' => ['required', 'in:morning,evening,night'],
        ]);

        ShiftNote::create([
            'user_id'    => auth()->id(),
            'body'       => $request->body,
            'shift'      => $request->shift,
            'created_at' => now(),
        ]);

        return redirect()->route('shift-notes.index')->with('success', 'Заметка сохранена');
    }
}
