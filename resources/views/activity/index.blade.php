@extends('layouts.app')

@section('title', 'Журнал активности')

@section('content')

<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Журнал активности</h1>
        <p class="text-slate-500 mt-1 text-sm">История всех действий в системе</p>
    </div>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('activity.index') }}" class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Сотрудник</label>
            <select name="user_id" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Все</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Раздел</label>
            <select name="action" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Все действия</option>
                @foreach($actionGroups as $prefix => $label)
                    <option value="{{ $prefix }}" {{ request('action') === $prefix ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Дата от</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Дата до</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                   class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
    </div>
    <div class="flex gap-2 mt-3">
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
            Применить
        </button>
        <a href="{{ route('activity.index') }}" class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-semibold rounded-lg hover:bg-slate-200 transition-colors">
            Сбросить
        </a>
    </div>
</form>

{{-- Log feed --}}
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    @if($logs->isEmpty())
        <div class="px-6 py-12 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 mx-auto text-slate-300 mb-3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/>
            </svg>
            <p class="text-sm text-slate-400">Нет записей по выбранным фильтрам</p>
        </div>
    @else
        <div class="divide-y divide-slate-100">
            @foreach($logs as $log)
            @php
                $actionColor = match(true) {
                    str_ends_with($log->action, '.created')        => 'bg-emerald-100 text-emerald-700',
                    str_ends_with($log->action, '.deleted')        => 'bg-red-100 text-red-700',
                    str_contains($log->action, 'status_changed')   => 'bg-blue-100 text-blue-700',
                    default                                        => 'bg-slate-100 text-slate-600',
                };
                $actionLabel = match($log->action) {
                    'booking.created'          => 'Создано бронирование',
                    'booking.updated'          => 'Бронирование изменено',
                    'booking.status_changed'   => 'Статус бронирования изменён',
                    'booking.deleted'          => 'Бронирование удалено',
                    'payment.created'          => 'Оплата добавлена',
                    'payment.updated'          => 'Оплата изменена',
                    'payment.deleted'          => 'Оплата удалена',
                    'guest.created'            => 'Гость добавлен',
                    'guest.updated'            => 'Гость изменён',
                    'guest.deleted'            => 'Гость удалён',
                    'expense.created'          => 'Расход добавлен',
                    'expense.updated'          => 'Расход изменён',
                    'expense.deleted'          => 'Расход удалён',
                    'room.status_changed'      => 'Статус номера изменён',
                    'user.created'             => 'Сотрудник добавлен',
                    'user.updated'             => 'Сотрудник изменён',
                    'user.deleted'             => 'Сотрудник удалён',
                    'maintenance.created'      => 'Заявка на ремонт создана',
                    'maintenance.updated'      => 'Заявка на ремонт изменена',
                    'maintenance.status_changed' => 'Статус заявки изменён',
                    'maintenance.deleted'      => 'Заявка на ремонт удалена',
                    default                    => $log->action,
                };
            @endphp
            <div class="flex items-start gap-4 px-5 py-4 hover:bg-slate-50 transition-colors">
                {{-- Avatar --}}
                <div class="w-8 h-8 bg-slate-700 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                    <span class="text-xs font-semibold text-white">
                        {{ $log->user ? substr($log->user->name, 0, 1) : '?' }}
                    </span>
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-sm font-semibold text-slate-900">
                            {{ $log->user?->name ?? 'Система' }}
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $actionColor }}">
                            {{ $actionLabel }}
                        </span>
                    </div>
                    <p class="text-sm text-slate-600 mt-0.5">{{ $log->subject_label }}</p>

                    @if($log->old_values || $log->new_values)
                    @php
                        $keyMap = [
                            'status'        => 'Статус',
                            'room_id'       => 'Номер',
                            'guest_id'      => 'Гость',
                            'check_in_date' => 'Заезд',
                            'check_out_date'=> 'Выезд',
                            'total_price'   => 'Сумма',
                            'adults'        => 'Взрослых',
                            'children'      => 'Детей',
                            'amount'        => 'Сумма',
                            'type'          => 'Тип',
                            'method'        => 'Метод',
                            'priority'      => 'Приоритет',
                            'description'   => 'Описание',
                            'name'          => 'Название',
                            'number'        => 'Номер',
                        ];
                        $valueMap = [
                            // Booking statuses
                            'inquiry'     => 'Запрос',
                            'pending'     => 'Ожидает',
                            'confirmed'   => 'Подтверждён',
                            'checked_in'  => 'Заселён',
                            'checked_out' => 'Выехал',
                            'cancelled'   => 'Отменён',
                            'no_show'     => 'Не явился',
                            // Room statuses
                            'available'   => 'Свободен',
                            'occupied'    => 'Занят',
                            'dirty'       => 'Требует уборки',
                            'cleaning'    => 'На уборке',
                            'maintenance' => 'Техобслуживание',
                            'out_of_order'=> 'Не работает',
                            // Payment methods
                            'cash'        => 'Наличные',
                            'card'        => 'Карта',
                            'transfer'    => 'Перевод',
                            'other'       => 'Прочее',
                            // Payment types
                            'prepayment'  => 'Предоплата',
                            'deposit'     => 'Залог',
                        ];
                        $skipKeys = ['updated_at', 'created_at', 'remember_token', 'email_verified_at', 'avatar', 'invoice_number', 'booking_ref'];
                        $fmt = fn($arr) => collect($arr)
                            ->except($skipKeys)
                            ->map(function ($v, $k) use ($keyMap, $valueMap) {
                                $label = $keyMap[$k] ?? $k;
                                // Format datetime strings as d.m.Y
                                if (is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}( \d{2}:\d{2}(:\d{2})?)?$/', $v)) {
                                    $v = \Carbon\Carbon::parse($v)->format('d.m.Y');
                                }
                                $v = $valueMap[$v] ?? $v;
                                return "$label: $v";
                            })
                            ->filter()
                            ->implode(' · ');
                    @endphp
                    <div class="mt-1.5 flex gap-3 text-xs text-slate-500">
                        @if($log->old_values)
                        <span class="bg-red-50 text-red-700 px-1.5 py-0.5 rounded">
                            {{ $fmt($log->old_values) }}
                        </span>
                        @endif
                        @if($log->new_values)
                        <span class="bg-emerald-50 text-emerald-700 px-1.5 py-0.5 rounded">
                            {{ $fmt($log->new_values) }}
                        </span>
                        @endif
                    </div>
                    @endif
                </div>

                {{-- Time + IP --}}
                <div class="text-right flex-shrink-0">
                    <p class="text-xs text-slate-500">{{ $log->created_at->format('d.m.Y H:i') }}</p>
                    @if($log->ip_address)
                    <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $log->ip_address }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($logs->hasPages())
        <div class="px-5 py-4 border-t border-slate-100">
            {{ $logs->links() }}
        </div>
        @endif
    @endif
</div>

@endsection
