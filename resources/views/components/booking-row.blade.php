@props(['booking'])

<div class="border-b border-slate-100 dark:border-slate-700 last:border-b-0">
    <a href="{{ route('bookings.show', $booking) }}" class="block px-6 py-4 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-4 min-w-0 flex-1">
                <span class="text-xs text-slate-400 dark:text-slate-500 font-mono flex-shrink-0">#{{ $booking->id }}</span>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-100 truncate">{{ $booking->guest->fullName }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ $booking->guest->phone ?? $booking->guest->email ?? '—' }}</p>
                </div>
                <div class="hidden md:block text-sm text-slate-600 dark:text-slate-300 flex-shrink-0">
                    <span class="font-medium">{{ $booking->room->number }}</span>
                    <span class="text-slate-400 dark:text-slate-500 mx-1">·</span>
                    {{ $booking->room->roomType->name }}
                </div>
                <div class="hidden md:flex items-center gap-1.5 text-sm text-slate-500 dark:text-slate-400 flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                    {{ $booking->check_in_date->format('d M') }}
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3 h-3 text-slate-300 dark:text-slate-600"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                    {{ $booking->check_out_date->format('d M') }}
                </div>
                <x-status-badge :status="$booking->status" />
            </div>
            <div class="flex-shrink-0">
                <span class="text-sm font-semibold text-slate-900 dark:text-slate-100 whitespace-nowrap">
                    {{ number_format($booking->total_price, 0, '.', ' ') }} сум
                </span>
            </div>
        </div>
    </a>

    {{-- Inline action buttons --}}
    <div class="px-6 pb-3 flex flex-wrap gap-2" @click.stop>
        @foreach($booking->status->allowedTransitions() as $transition)
            @php
                $transitionKey = $transition->value;
                $labels = [
                    'confirmed'   => ['label' => 'Подтвердить', 'class' => 'px-3 py-1 text-xs font-semibold border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 bg-blue-50 dark:bg-blue-900/30 hover:bg-blue-100 dark:hover:bg-blue-900/50 rounded-md transition-colors'],
                    'checked_in'  => ['label' => 'Заселить',    'class' => 'px-3 py-1 text-xs font-semibold border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-900/30 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 rounded-md transition-colors'],
                    'checked_out' => ['label' => 'Выселить',    'class' => 'px-3 py-1 text-xs font-semibold border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-700 hover:bg-slate-100 dark:hover:bg-slate-600 rounded-md transition-colors'],
                    'cancelled'   => ['label' => 'Отменить',    'class' => 'px-3 py-1 text-xs font-semibold border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:hover:bg-red-900/50 rounded-md transition-colors'],
                    'no_show'     => ['label' => 'Не явился',   'class' => 'px-3 py-1 text-xs font-semibold border border-orange-200 dark:border-orange-800 text-orange-600 dark:text-orange-400 bg-orange-50 dark:bg-orange-900/20 hover:bg-orange-100 dark:hover:bg-orange-900/40 rounded-md transition-colors'],
                ];
                $btn = $labels[$transitionKey] ?? ['label' => $transitionKey, 'class' => 'px-3 py-1 text-xs font-semibold border border-slate-200 dark:border-slate-600 text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-700 hover:bg-slate-100 dark:hover:bg-slate-600 rounded-md transition-colors'];
            @endphp
            <form method="POST" action="{{ route('bookings.status', $booking) }}">
                @csrf
                <input type="hidden" name="transition" value="{{ $transitionKey }}">
                <button type="submit" class="{{ $btn['class'] }}">
                    {{ $btn['label'] }}
                </button>
            </form>
        @endforeach
    </div>
</div>
