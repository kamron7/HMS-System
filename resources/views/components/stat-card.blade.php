@props(['title', 'value', 'trend' => null, 'color' => 'gray'])

@php
$valueColor = match($color) {
    'green'  => 'text-emerald-600',
    'red'    => 'text-red-600',
    'yellow' => 'text-amber-600',
    'blue'   => 'text-blue-600',
    default  => 'text-slate-900',
};

$accentColor = match($color) {
    'green'  => 'border-l-emerald-500',
    'red'    => 'border-l-red-500',
    'yellow' => 'border-l-amber-500',
    'blue'   => 'border-l-blue-500',
    default  => 'border-l-slate-300',
};
@endphp

<div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 border-l-4 {{ $accentColor }}">
    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $title }}</p>
    <p class="text-3xl font-bold {{ $valueColor }} mt-2">{{ $value }}</p>
    @if($trend)
        <p class="text-sm mt-2 flex items-center gap-1 {{ str_starts_with($trend, '↑') ? 'text-emerald-600' : 'text-red-500' }}">
            @if(str_starts_with($trend, '↑'))
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18"/></svg>
            @else
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3"/></svg>
            @endif
            {{ $trend }}
        </p>
    @endif
</div>
