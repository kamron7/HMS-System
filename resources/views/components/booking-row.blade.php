@props(['booking'])

<div class="border-b border-gray-100 hover:bg-gray-50 transition">
    <a href="{{ route('bookings.show', $booking) }}" class="block px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4 min-w-0">
                <span class="text-xs text-gray-400 font-mono">#{{ $booking->id }}</span>
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ $booking->guest->fullName }}</p>
                    <p class="text-xs text-gray-500">{{ $booking->guest->phone ?? $booking->guest->email ?? '—' }}</p>
                </div>
                <div class="hidden md:block text-sm text-gray-600">
                    {{ $booking->room->number }} · {{ $booking->room->roomType->name }}
                </div>
                <div class="hidden md:block text-sm text-gray-600">
                    {{ $booking->check_in_date->format('d M') }} → {{ $booking->check_out_date->format('d M') }}
                </div>
                <x-status-badge :status="$booking->status" />
            </div>
            <div class="flex items-center gap-3 ml-4">
                <span class="text-sm font-medium text-gray-900 whitespace-nowrap">
                    {{ number_format($booking->total_price, 0, '.', ' ') }} сум
                </span>
            </div>
        </div>
    </a>
    {{-- Inline action buttons --}}
    <div class="px-6 pb-3 flex gap-2" @click.stop>
        @foreach($booking->status->allowedTransitions() as $transition)
            @php
                $transitionKey = $transition->value;
                $labels = [
                    'confirmed'   => ['label' => '✓ Подтвердить', 'class' => 'bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100'],
                    'checked_in'  => ['label' => '✓ Заселить',    'class' => 'bg-green-50 text-green-700 border-green-200 hover:bg-green-100'],
                    'checked_out' => ['label' => '✓ Выселить',    'class' => 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100'],
                    'cancelled'   => ['label' => '✗ Отменить',    'class' => 'bg-red-50 text-red-600 border-red-200 hover:bg-red-100'],
                ];
                $btn = $labels[$transitionKey] ?? ['label' => $transitionKey, 'class' => 'bg-gray-50 text-gray-600 border-gray-200'];
            @endphp
            <form method="POST" action="{{ route('bookings.status', $booking) }}">
                @csrf
                <input type="hidden" name="transition" value="{{ $transitionKey }}">
                <button type="submit"
                    class="px-3 py-1 text-xs font-medium border rounded-lg {{ $btn['class'] }} transition">
                    {{ $btn['label'] }}
                </button>
            </form>
        @endforeach
    </div>
</div>
