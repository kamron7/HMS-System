@props(['title', 'value', 'trend' => null])

<div class="bg-white rounded-xl border border-gray-200 p-6">
    <p class="text-sm font-medium text-gray-500">{{ $title }}</p>
    <p class="text-3xl font-bold text-gray-900 mt-1">{{ $value }}</p>
    @if($trend)
        <p class="text-sm mt-2 {{ str_starts_with($trend, '↑') ? 'text-green-600' : 'text-red-600' }}">
            {{ $trend }}
        </p>
    @endif
</div>
