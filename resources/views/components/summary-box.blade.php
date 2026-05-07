@props([
    'roomName'   => '',
    'checkIn'    => '',
    'checkOut'   => '',
    'nights'     => 0,
    'totalPrice' => 0,
])

<div class="bg-blue-600 rounded-xl p-5 text-white">
    <h3 class="text-sm font-semibold text-white mb-4">Итог бронирования</h3>
    <div class="space-y-2.5">
        <div class="flex justify-between items-center text-sm">
            <span class="text-blue-200">Номер:</span>
            <span class="font-semibold text-white">{{ $roomName ?: '—' }}</span>
        </div>
        <div class="flex justify-between items-center text-sm">
            <span class="text-blue-200">Заезд:</span>
            <span class="font-semibold text-white">{{ $checkIn ?: '—' }}</span>
        </div>
        <div class="flex justify-between items-center text-sm">
            <span class="text-blue-200">Выезд:</span>
            <span class="font-semibold text-white">{{ $checkOut ?: '—' }}</span>
        </div>
        <div class="flex justify-between items-center text-sm">
            <span class="text-blue-200">Ночей:</span>
            <span class="font-semibold text-white">{{ $nights ?: '—' }}</span>
        </div>
        <div class="flex justify-between items-center pt-3 mt-1 border-t border-blue-500">
            <span class="text-sm font-semibold text-white">Итого:</span>
            <span class="text-base font-bold text-white">{{ $totalPrice ? number_format($totalPrice, 0, '.', ' ') . ' сум' : '—' }}</span>
        </div>
    </div>
</div>
