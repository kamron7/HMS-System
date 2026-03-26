<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuestController extends Controller
{
    public function index(Request $request): View
    {
        $query = Guest::query()->orderBy('last_name');

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->orWhereRaw('first_name ILIKE ?', ["%{$search}%"])
                  ->orWhereRaw('last_name ILIKE ?', ["%{$search}%"])
                  ->orWhereRaw('phone ILIKE ?', ["%{$search}%"]);
            });
        }

        $guests = $query->paginate(20);

        return view('guests.index', compact('guests'));
    }

    public function search(Request $request): JsonResponse
    {
        $search = $request->query('q', '');

        if (empty($search)) {
            return response()->json([]);
        }

        $results = Guest::query()
            ->where(function ($q) use ($search) {
                $q->orWhereRaw('first_name ILIKE ?', ["%{$search}%"])
                  ->orWhereRaw('last_name ILIKE ?', ["%{$search}%"])
                  ->orWhereRaw('phone ILIKE ?', ["%{$search}%"]);
            })
            ->limit(10)
            ->get(['id', 'first_name', 'last_name', 'phone'])
            ->map(fn(Guest $guest) => [
                'id'        => $guest->id,
                'full_name' => $guest->full_name,
                'phone'     => $guest->phone,
            ]);

        return response()->json($results);
    }

    public function create(): View
    {
        return view('guests.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name'      => ['required', 'string', 'max:100'],
            'last_name'       => ['required', 'string', 'max:100'],
            'phone'           => ['nullable', 'string', 'max:20'],
            'email'           => ['nullable', 'email', 'max:150'],
            'passport_number' => ['nullable', 'string', 'max:50'],
            'nationality'     => ['nullable', 'string', 'max:100'],
        ]);

        $guest = Guest::create($validated);

        return redirect()->route('guests.show', $guest)
            ->with('success', 'Гость успешно добавлен.');
    }

    public function show(Guest $guest): View
    {
        $guest->load(['bookings' => fn($q) => $q->with('room.roomType')->orderBy('created_at', 'desc')]);

        return view('guests.show', compact('guest'));
    }

    public function edit(Guest $guest): View
    {
        return view('guests.edit', compact('guest'));
    }

    public function update(Request $request, Guest $guest): RedirectResponse
    {
        $validated = $request->validate([
            'first_name'      => ['required', 'string', 'max:100'],
            'last_name'       => ['required', 'string', 'max:100'],
            'phone'           => ['nullable', 'string', 'max:20'],
            'email'           => ['nullable', 'email', 'max:150'],
            'passport_number' => ['nullable', 'string', 'max:50'],
            'nationality'     => ['nullable', 'string', 'max:100'],
        ]);

        $guest->update($validated);

        return redirect()->route('guests.show', $guest)
            ->with('success', 'Данные гостя обновлены.');
    }
}
