<?php

namespace App\Http\Controllers;

use App\Models\PricingRule;
use App\Models\RoomType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PricingRuleController extends Controller
{
    public function index(): View
    {
        $rules = PricingRule::with(['roomType', 'creator'])
            ->orderByDesc('priority')
            ->orderByDesc('created_at')
            ->get();

        return view('pricing-rules.index', compact('rules'));
    }

    public function create(): View
    {
        $roomTypes = RoomType::orderBy('name')->get();
        return view('pricing-rules.create', compact('roomTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:100'],
            'room_type_id'   => ['nullable', 'exists:room_types,id'],
            'date_from'      => ['required', 'date'],
            'date_to'        => ['required', 'date', 'after_or_equal:date_from'],
            'modifier_type'  => ['required', 'in:fixed,percent'],
            'modifier_value' => ['required', 'numeric'],
            'priority'       => ['nullable', 'integer', 'min:0', 'max:255'],
            'is_active'      => ['nullable', 'boolean'],
        ]);

        PricingRule::create([
            ...$validated,
            'priority'   => $validated['priority'] ?? 0,
            'is_active'  => $request->boolean('is_active', true),
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('pricing-rules.index')
            ->with('success', 'Тарифное правило создано.');
    }

    public function edit(PricingRule $pricingRule): View
    {
        $roomTypes = RoomType::orderBy('name')->get();
        return view('pricing-rules.edit', compact('pricingRule', 'roomTypes'));
    }

    public function update(Request $request, PricingRule $pricingRule): RedirectResponse
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:100'],
            'room_type_id'   => ['nullable', 'exists:room_types,id'],
            'date_from'      => ['required', 'date'],
            'date_to'        => ['required', 'date', 'after_or_equal:date_from'],
            'modifier_type'  => ['required', 'in:fixed,percent'],
            'modifier_value' => ['required', 'numeric'],
            'priority'       => ['nullable', 'integer', 'min:0', 'max:255'],
            'is_active'      => ['nullable', 'boolean'],
        ]);

        $pricingRule->update([
            ...$validated,
            'priority'  => $validated['priority'] ?? 0,
            'is_active' => $request->boolean('is_active', false),
        ]);

        return redirect()->route('pricing-rules.index')
            ->with('success', 'Тарифное правило обновлено.');
    }

    public function destroy(PricingRule $pricingRule): RedirectResponse
    {
        $pricingRule->delete();

        return redirect()->route('pricing-rules.index')
            ->with('success', 'Тарифное правило удалено.');
    }
}
