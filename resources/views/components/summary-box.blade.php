@props([
    'roomName'   => '',
    'checkIn'    => '',
    'checkOut'   => '',
    'nights'     => 0,
    'totalPrice' => 0,
])

<div class="bg-blue-50 border border-blue-200 rounded-xl p-5">
    <h3 class="text-sm font-semibold text-blue-900 mb-3">Итог бронирования</h3>
    <div class="space-y-2 text-sm text-blue-800">
        <div class="flex justify-between">
            <span class="text-blue-600">Номер:</span>
            <span class="font-medium">{{ $roomName ?: '—' }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-blue-600">Заезд:</span>
            <span class="font-medium">{{ $checkIn ?: '—' }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-blue-600">Выезд:</span>
            <span class="font-medium">{{ $checkOut ?: '—' }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-blue-600">Ночей:</span>
            <span class="font-medium">{{ $nights ?: '—' }}</span>
        </div>
        <div class="flex justify-between border-t border-blue-200 pt-2 mt-2">
            <span class="font-semibold text-blue-900">Итого:</span>
            <span class="font-bold text-blue-900">{{ $totalPrice ? number_format($totalPrice, 0, '.', ' ') . ' сум' : '—' }}</span>
        </div>
    </div>
</div>
