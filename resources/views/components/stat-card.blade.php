@props(['title', 'value', 'trend' => null, 'color' => 'gray'])

@php
$valueColor = match($color) {
    'green'  => 'text-green-600',
    'red'    => 'text-red-600',
    'yellow' => 'text-yellow-600',
    'blue'   => 'text-blue-600',
    default  => 'text-gray-900',
};
@endphp

<div class="bg-white rounded-xl border border-gray-200 p-6">
    <p class="text-sm font-medium text-gray-500">{{ $title }}</p>
    <p class="text-3xl font-bold {{ $valueColor }} mt-1">{{ $value }}</p>
    @if($trend)
        <p class="text-sm mt-2 {{ str_starts_with($trend, '↑') ? 'text-green-600' : 'text-red-600' }}">
            {{ $trend }}
        </p>
    @endif
</div>
