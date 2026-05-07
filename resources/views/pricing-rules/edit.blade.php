@extends('layouts.app')

@section('title', 'Редактировать тарифное правило')

@section('content')

<div class="flex items-center gap-3 mb-6">
    <a href="{{ route('pricing-rules.index') }}"
       class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
        Тарифы
    </a>
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-300"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
    <h1 class="text-xl font-bold text-slate-900">{{ $pricingRule->name }}</h1>
</div>

<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <form method="POST" action="{{ route('pricing-rules.update', $pricingRule) }}">
            @csrf @method('PUT')

            <div class="space-y-5">
                {{-- Name --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">
                        Название <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $pricingRule->name) }}" maxlength="100" required
                           class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none @error('name') border-red-400 @enderror">
                    @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Room Type --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">
                        Тип номера
                    </label>
                    <select name="room_type_id"
                            class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                        <option value="">— Все типы номеров —</option>
                        @foreach($roomTypes as $type)
                            <option value="{{ $type->id }}" @selected(old('room_type_id', $pricingRule->room_type_id) == $type->id)>
                                {{ $type->name }} ({{ number_format($type->base_price, 0, '.', ' ') }} сум/ночь)
                            </option>
                        @endforeach
                    </select>
                    @error('room_type_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Date range --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">
                            Дата начала <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="date_from" value="{{ old('date_from', $pricingRule->date_from->format('Y-m-d')) }}" required
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none @error('date_from') border-red-400 @enderror">
                        @error('date_from')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">
                            Дата окончания <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="date_to" value="{{ old('date_to', $pricingRule->date_to->format('Y-m-d')) }}" required
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none @error('date_to') border-red-400 @enderror">
                        @error('date_to')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Modifier --}}
                <div x-data="{ modType: '{{ old('modifier_type', $pricingRule->modifier_type) }}' }">
                    <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">
                        Тип изменения цены <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-3 mb-3">
                        <label class="flex-1 flex items-center gap-2 p-3 rounded-lg border cursor-pointer transition-colors"
                               :class="modType === 'percent' ? 'border-blue-500 bg-blue-50' : 'border-slate-200 hover:border-slate-300'">
                            <input type="radio" name="modifier_type" value="percent" x-model="modType" class="sr-only">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">Процент</p>
                                <p class="text-xs text-slate-500">+30% или −10% от базовой цены</p>
                            </div>
                        </label>
                        <label class="flex-1 flex items-center gap-2 p-3 rounded-lg border cursor-pointer transition-colors"
                               :class="modType === 'fixed' ? 'border-blue-500 bg-blue-50' : 'border-slate-200 hover:border-slate-300'">
                            <input type="radio" name="modifier_type" value="fixed" x-model="modType" class="sr-only">
                            <div>
                                <p class="text-sm font-semibold text-slate-800">Фиксированная цена</p>
                                <p class="text-xs text-slate-500">Заменяет базовую цену (сум/ночь)</p>
                            </div>
                        </label>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">
                            Значение <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" name="modifier_value"
                                   value="{{ old('modifier_value', $pricingRule->modifier_value) }}"
                                   step="0.01" required
                                   class="w-full border border-slate-200 rounded-lg px-3 py-2 pr-16 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none @error('modifier_value') border-red-400 @enderror">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-slate-400 font-semibold"
                                  x-text="modType === 'percent' ? '%' : 'сум'"></span>
                        </div>
                        @error('modifier_value')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Priority + Active --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1.5">
                            Приоритет
                        </label>
                        <input type="number" name="priority"
                               value="{{ old('priority', $pricingRule->priority) }}"
                               min="0" max="255"
                               class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:outline-none">
                        @error('priority')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex items-center">
                        <label class="flex items-center gap-3 cursor-pointer mt-5">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1"
                                   @checked(old('is_active', $pricingRule->is_active ? '1' : '0') === '1')
                                   class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500">
                            <span class="text-sm font-medium text-slate-700">Активно</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 mt-6 pt-5 border-t border-slate-100">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                    Сохранить изменения
                </button>
                <a href="{{ route('pricing-rules.index') }}"
                   class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200 transition-colors">
                    Отмена
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
