<?php

namespace App\Http\Controllers;

use App\Models\PromoCode;
use App\Models\RoomType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromoCodeController extends Controller
{
    public function index(): View
    {
        $codes = PromoCode::orderByDesc('created_at')->get();
        return view('promo-codes.index', compact('codes'));
    }

    public function create(): View
    {
        $roomTypes = RoomType::orderBy('name')->get();
        return view('promo-codes.create', compact('roomTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code'             => ['required', 'string', 'max:50', 'unique:promo_codes,code'],
            'discount_percent' => ['required', 'numeric', 'min:1', 'max:100'],
            'valid_from'       => ['nullable', 'date'],
            'valid_to'         => ['nullable', 'date', 'after_or_equal:valid_from'],
            'max_uses'         => ['nullable', 'integer', 'min:1'],
            'is_active'        => ['boolean'],
            'room_type_ids'    => ['nullable', 'array'],
            'room_type_ids.*'  => ['integer', 'exists:room_types,id'],
        ]);

        $validated['code']          = strtoupper($validated['code']);
        $validated['is_active']     = $request->boolean('is_active', true);
        $validated['room_type_ids'] = ! empty($validated['room_type_ids']) ? $validated['room_type_ids'] : null;

        PromoCode::create($validated);

        return redirect()->route('promo-codes.index')
            ->with('success', 'Промокод создан.');
    }

    public function edit(PromoCode $promoCode): View
    {
        $roomTypes = RoomType::orderBy('name')->get();
        return view('promo-codes.edit', compact('promoCode', 'roomTypes'));
    }

    public function update(Request $request, PromoCode $promoCode): RedirectResponse
    {
        $validated = $request->validate([
            'code'             => ['required', 'string', 'max:50', "unique:promo_codes,code,{$promoCode->id}"],
            'discount_percent' => ['required', 'numeric', 'min:1', 'max:100'],
            'valid_from'       => ['nullable', 'date'],
            'valid_to'         => ['nullable', 'date', 'after_or_equal:valid_from'],
            'max_uses'         => ['nullable', 'integer', 'min:1'],
            'is_active'        => ['boolean'],
            'room_type_ids'    => ['nullable', 'array'],
            'room_type_ids.*'  => ['integer', 'exists:room_types,id'],
        ]);

        $validated['code']          = strtoupper($validated['code']);
        $validated['is_active']     = $request->boolean('is_active', false);
        $validated['room_type_ids'] = ! empty($validated['room_type_ids']) ? $validated['room_type_ids'] : null;

        $promoCode->update($validated);

        return redirect()->route('promo-codes.index')
            ->with('success', 'Промокод обновлён.');
    }

    public function destroy(PromoCode $promoCode): RedirectResponse
    {
        $promoCode->delete();
        return redirect()->route('promo-codes.index')
            ->with('success', 'Промокод удалён.');
    }
}
