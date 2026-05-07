<?php

namespace App\Http\Controllers;

use App\Enums\LostItemStatus;
use App\Models\Booking;
use App\Models\Guest;
use App\Models\LostItem;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class LostItemController extends Controller
{
    public function index(Request $request): View
    {
        $query = LostItem::with(['foundBy', 'room', 'guest', 'booking'])
            ->orderByDesc('found_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'ilike', "%{$request->search}%")
                  ->orWhere('description', 'ilike', "%{$request->search}%")
                  ->orWhere('storage_location', 'ilike', "%{$request->search}%");
            });
        }

        $items = $query->paginate(20);

        $counts = LostItem::selectRaw('status, count(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        return view('lost-items.index', compact('items', 'counts'));
    }

    public function create(Request $request): View
    {
        $rooms   = Room::orderBy('number')->get();
        $guests  = Guest::orderBy('last_name')->limit(50)->get();
        $booking = $request->booking_id ? Booking::with('guest')->find($request->booking_id) : null;

        return view('lost-items.create', compact('rooms', 'guests', 'booking'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'            => ['required', 'string', 'max:150'],
            'description'      => ['nullable', 'string', 'max:1000'],
            'room_id'          => ['nullable', 'integer', 'exists:rooms,id'],
            'guest_id'         => ['nullable', 'integer', 'exists:guests,id'],
            'booking_id'       => ['nullable', 'integer', 'exists:bookings,id'],
            'storage_location' => ['nullable', 'string', 'max:100'],
            'found_at'         => ['required', 'date'],
            'photos'           => ['nullable', 'array', 'max:5'],
            'photos.*'         => ['image', 'max:2048'],
        ]);

        $photos = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $photos[] = $file->store('lost-items', 'public');
            }
        }

        LostItem::create([
            'title'            => $validated['title'],
            'description'      => $validated['description'] ?? null,
            'room_id'          => $validated['room_id'] ?? null,
            'guest_id'         => $validated['guest_id'] ?? null,
            'booking_id'       => $validated['booking_id'] ?? null,
            'storage_location' => $validated['storage_location'] ?? null,
            'found_at'         => $validated['found_at'],
            'found_by'         => auth()->id(),
            'photos'           => $photos ? implode(',', $photos) : null,
        ]);

        return redirect()->route('lost-items.index')
            ->with('success', 'Вещь добавлена в журнал находок.');
    }

    public function show(LostItem $item): View
    {
        $item->load(['foundBy', 'room', 'guest', 'booking']);

        return view('lost-items.show', compact('item'));
    }

    public function updateStatus(Request $request, LostItem $item): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:found,stored,returned,discarded'],
        ]);

        $data = ['status' => $validated['status']];
        if ($validated['status'] === 'returned') {
            $data['returned_at'] = now();
        }

        $item->update($data);

        return redirect()->route('lost-items.show', $item)
            ->with('success', 'Статус обновлён.');
    }

    public function destroy(LostItem $item): RedirectResponse
    {
        if ($item->photos) {
            foreach (explode(',', $item->photos) as $photo) {
                Storage::disk('public')->delete($photo);
            }
        }

        $item->delete();

        return redirect()->route('lost-items.index')
            ->with('success', 'Запись удалена.');
    }
}
