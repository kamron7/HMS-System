<?php

namespace App\Http\Controllers;

use App\Models\RoomType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomTypeController extends Controller
{
    public function index(): View
    {
        $roomTypes = RoomType::withCount('rooms')
            ->orderBy('name')
            ->get();

        return view('room-types.index', compact('roomTypes'));
    }

    public function create(): View
    {
        return view('room-types.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'base_price'  => ['required', 'numeric', 'min:0'],
            'capacity'    => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'amenities'   => ['nullable', 'string'],
        ]);

        $amenities = $this->parseAmenities($validated['amenities'] ?? null);
        unset($validated['amenities']);

        RoomType::create(array_merge($validated, ['amenities' => $amenities]));

        return redirect()->route('room-types.index')
            ->with('success', 'Тип номера успешно создан.');
    }

    public function edit(RoomType $roomType): View
    {
        return view('room-types.edit', compact('roomType'));
    }

    public function update(Request $request, RoomType $roomType): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'base_price'  => ['required', 'numeric', 'min:0'],
            'capacity'    => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'amenities'   => ['nullable', 'string'],
        ]);

        $amenities = $this->parseAmenities($validated['amenities'] ?? null);
        unset($validated['amenities']);

        $roomType->update(array_merge($validated, ['amenities' => $amenities]));

        return redirect()->route('room-types.index')
            ->with('success', 'Тип номера успешно обновлён.');
    }

    private function parseAmenities(?string $input): ?array
    {
        if ($input === null || trim($input) === '') {
            return null;
        }

        $items = array_filter(
            array_map('trim', explode(',', $input)),
            fn(string $item) => $item !== ''
        );

        return array_values($items) ?: null;
    }
}
