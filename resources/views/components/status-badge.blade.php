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
        'green'  => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
        'blue'   => 'bg-blue-50 text-blue-700 ring-1 ring-blue-200',
        'yellow' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
        'red'    => 'bg-red-50 text-red-700 ring-1 ring-red-200',
        'gray'   => 'bg-slate-100 text-slate-600 ring-1 ring-slate-200',
        'purple' => 'bg-purple-50 text-purple-700 ring-1 ring-purple-200',
        'orange' => 'bg-orange-50 text-orange-700 ring-1 ring-orange-200',
        default  => 'bg-slate-100 text-slate-600 ring-1 ring-slate-200',
    };
@endphp

<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $classes }}">
    {{ $label }}
</span>
