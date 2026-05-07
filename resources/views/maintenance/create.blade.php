@extends('layouts.app')

@section('title', 'Новая заявка')

@section('content')
<div class="max-w-lg mx-auto">

    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('maintenance.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            Техобслуживание
        </a>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-slate-300"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
        <h1 class="text-xl font-bold text-slate-900">Новая заявка</h1>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <form method="POST" action="{{ route('maintenance.store') }}" class="space-y-5">
            @csrf

            <div>
                <label for="room_id" class="block text-sm font-semibold text-slate-700 mb-1.5">Номер</label>
                <select id="room_id" name="room_id" required
                        class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $errors->has('room_id') ? 'border-red-400' : 'border-slate-200' }}">
                    <option value="">— Выберите номер —</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}" {{ old('room_id') == $room->id ? 'selected' : '' }}>
                            Номер {{ $room->number }} (этаж {{ $room->floor }})
                        </option>
                    @endforeach
                </select>
                @error('room_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="title" class="block text-sm font-semibold text-slate-700 mb-1.5">Описание проблемы</label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" required maxlength="150"
                       placeholder="Напр: Не работает кондиционер"
                       class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $errors->has('title') ? 'border-red-400' : 'border-slate-200' }}">
                @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-semibold text-slate-700 mb-1.5">Подробности <span class="text-slate-400 font-normal">(необязательно)</span></label>
                <textarea id="description" name="description" rows="3" maxlength="2000"
                          class="w-full px-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none">{{ old('description') }}</textarea>
            </div>

            <div>
                <label for="priority" class="block text-sm font-semibold text-slate-700 mb-1.5">Приоритет</label>
                <select id="priority" name="priority" required
                        class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ $errors->has('priority') ? 'border-red-400' : 'border-slate-200' }}">
                    <option value="">— Выберите приоритет —</option>
                    @foreach($priorities as $p)
                        <option value="{{ $p->value }}" {{ old('priority', 'medium') === $p->value ? 'selected' : '' }}>
                            {{ $p->label() }}
                        </option>
                    @endforeach
                </select>
                @error('priority')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Создать заявку
                </button>
                <a href="{{ route('maintenance.index') }}"
                   class="px-5 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200 transition-colors">
                    Отмена
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
