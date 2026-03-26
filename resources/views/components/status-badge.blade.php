@props(['status'])

@php
    if ($status instanceof \App\Enums\BookingStatus || $status instanceof \App\Enums\RoomStatus) {
        $label = $status->label();
        $color = $status->color();
    } else {
        // Fallback for raw strings
        $label = $status;
        $color = 'gray';
    }

    $classes = match($color) {
        'green'  => 'bg-green-100 text-green-800',
        'blue'   => 'bg-blue-100 text-blue-800',
        'yellow' => 'bg-yellow-100 text-yellow-800',
        'red'    => 'bg-red-100 text-red-800',
        'gray'   => 'bg-gray-100 text-gray-700',
        default  => 'bg-gray-100 text-gray-700',
    };
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $classes }}">
    {{ $label }}
</span>
