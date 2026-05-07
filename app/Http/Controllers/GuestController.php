<?php

namespace App\Http\Controllers;

use App\Enums\GuestTag;
use App\Models\Guest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
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

        if ($tag = $request->query('tag')) {
            $query->where('tag', $tag);
        }

        $guests = $query->withCount('bookings')->paginate(20)->appends($request->query());
        $tags   = GuestTag::cases();

        $totals = [
            'all'       => Guest::count(),
            'vip'       => Guest::where('tag', 'vip')->count(),
            'blacklist' => Guest::where('tag', 'blacklist')->count(),
        ];

        return view('guests.index', compact('guests', 'tags', 'totals'));
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
            ->withCount(['bookings as stays_count' => fn($q) => $q->where('status', 'checked_out')])
            ->withMax(['bookings' => fn($q) => $q->where('status', 'checked_out')], 'check_out_date')
            ->with(['bookings' => fn($q) => $q
                ->with('room')
                ->whereIn('status', ['checked_in', 'confirmed', 'pending'])
                ->orderByRaw("CASE status WHEN 'checked_in' THEN 0 WHEN 'confirmed' THEN 1 ELSE 2 END")
                ->limit(1),
            ])
            ->limit(10)
            ->get(['id', 'first_name', 'last_name', 'phone', 'tag'])
            ->map(fn(Guest $guest) => [
                'id'          => $guest->id,
                'full_name'   => $guest->full_name,
                'phone'       => $guest->phone,
                'tag'         => $guest->tag?->value,
                'tag_label'   => $guest->tag?->label(),
                'stays_count' => (int) $guest->stays_count,
                'last_stay'   => $guest->bookings_max_check_out_date
                    ? \Carbon\Carbon::parse($guest->bookings_max_check_out_date)->translatedFormat('d M Y')
                    : null,
                'active_booking' => $guest->bookings->first() ? [
                    'status'     => $guest->bookings->first()->status->value,
                    'status_label' => match($guest->bookings->first()->status->value) {
                        'checked_in' => 'Заселён',
                        'confirmed'  => 'Подтверждено',
                        'pending'    => 'Ожидает',
                        default      => $guest->bookings->first()->status->value,
                    },
                    'room'       => optional($guest->bookings->first()->room)->number,
                    'check_out'  => $guest->bookings->first()->check_out_date->toDateString(),
                ] : null,
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
            'tag'             => ['nullable', Rule::in(array_column(GuestTag::cases(), 'value'))],
        ]);

        $guest = Guest::create($validated);

        return redirect()->route('guests.show', $guest)
            ->with('success', 'Гость успешно добавлен.');
    }

    public function show(Guest $guest): View
    {
        $guest->load(['bookings' => fn($q) => $q->with('room.roomType', 'payments')->orderBy('check_in_date', 'desc')]);

        $stats = [
            'total_bookings' => $guest->bookings->count(),
            'total_stays'    => $guest->bookings->whereIn('status', ['checked_out'])->count(),
            'active'         => $guest->bookings->whereIn('status', ['inquiry', 'pending', 'confirmed', 'checked_in'])->count(),
            'total_spent'    => $guest->bookings->whereIn('status', ['checked_out', 'checked_in'])->sum('total_price'),
            'last_stay'      => $guest->bookings->where('status', 'checked_out')->max('check_out_date'),
        ];

        return view('guests.show', compact('guest', 'stats'));
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
            'tag'             => ['nullable', Rule::in(array_column(GuestTag::cases(), 'value'))],
        ]);

        $guest->update($validated);

        return redirect()->route('guests.show', $guest)
            ->with('success', 'Данные гостя обновлены.');
    }

    public function destroy(Guest $guest): RedirectResponse
    {
        if ($guest->bookings()->whereIn('status', ['checked_in', 'confirmed', 'pending'])->exists()) {
            return redirect()->back()->with('error', 'Нельзя удалить гостя с активными бронированиями.');
        }

        $guest->delete();

        return redirect()->route('guests.index')
            ->with('success', 'Гость удалён.');
    }

    public function quickStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'last_name'  => ['nullable', 'string', 'max:80'],
            'phone'      => ['nullable', 'string', 'max:30'],
            'email'      => ['nullable', 'email', 'max:150'],
        ]);

        $guest = Guest::create($validated);

        return response()->json([
            'id'        => $guest->id,
            'full_name' => $guest->fullName,
            'phone'     => $guest->phone ?? '',
        ]);
    }

    public function export(Request $request): Response
    {
        $query = Guest::withCount('bookings')->orderBy('last_name');

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->orWhereRaw('first_name ILIKE ?', ["%{$search}%"])
                  ->orWhereRaw('last_name ILIKE ?', ["%{$search}%"])
                  ->orWhereRaw('phone ILIKE ?', ["%{$search}%"]);
            });
        }

        if ($tag = $request->query('tag')) {
            $query->where('tag', $tag);
        }

        $guests = $query->get();

        $rows   = [];
        $rows[] = ['ID', 'Фамилия', 'Имя', 'Телефон', 'Email', 'Паспорт', 'Гражданство', 'Метка', 'Бронирований', 'Добавлен'];
        foreach ($guests as $g) {
            $rows[] = [
                $g->id,
                $g->last_name,
                $g->first_name,
                $g->phone,
                $g->email,
                $g->passport_number,
                $g->nationality,
                $g->tag?->label() ?? '',
                $g->bookings_count,
                $g->created_at->format('d.m.Y'),
            ];
        }

        $csv = implode("\n", array_map(fn($row) => implode(';', array_map(
            fn($v) => '"' . str_replace('"', '""', (string) $v) . '"',
            $row
        )), $rows));

        return response("\xEF\xBB\xBF" . $csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="guests_' . now()->format('Y-m-d') . '.csv"',
        ]);
    }
}
