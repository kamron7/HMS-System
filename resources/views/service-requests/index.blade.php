@extends('layouts.app')

@section('title', 'Запросы услуг от гостей')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Page header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">Запросы услуг от гостей</h1>
            <p class="text-sm text-slate-400 mt-0.5">Заявки, поступившие через портал номера (QR-код)</p>
        </div>
    </div>

    {{-- Flash message --}}
    @if(session('success'))
    <div class="mb-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 rounded-xl px-4 py-3 flex items-center gap-3">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
        <p class="text-sm font-medium text-emerald-800 dark:text-emerald-300">{{ session('success') }}</p>
    </div>
    @endif

    {{-- Status filter tabs --}}
    @php
        $currentStatus = request('status', 'pending');
        $tabs = [
            'pending'   => 'Ожидают',
            'confirmed' => 'Подтверждены',
            'declined'  => 'Отклонены',
        ];
    @endphp
    <div class="flex gap-1 mb-5 bg-slate-100 dark:bg-slate-800 rounded-xl p-1 w-fit">
        @foreach($tabs as $key => $label)
        <a href="{{ route('service-requests.index', ['status' => $key]) }}"
           class="px-4 py-2 rounded-lg text-sm font-medium transition-colors
                  {{ $currentStatus === $key
                     ? 'bg-white dark:bg-slate-700 text-slate-900 dark:text-slate-100 shadow-sm'
                     : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        @if($requests->isEmpty())
        <div class="px-6 py-16 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600 mb-3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.076.721-.506 1.357-1.235 1.357H4.366c-.729 0-1.311-.636-1.235-1.357l1.263-12a1.125 1.125 0 0 1 1.118-1.007h12.976c.58 0 1.077.443 1.118 1.007Z"/>
            </svg>
            <p class="text-sm text-slate-400 dark:text-slate-500">Нет запросов со статусом «{{ $tabs[$currentStatus] }}»</p>
        </div>
        @else
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 dark:bg-slate-900/40">
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Гость</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Номер</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Услуга</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Кол-во</th>
                    <th class="text-right px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Сумма</th>
                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Время</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 dark:divide-slate-700/50">
                @foreach($requests as $req)
                <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-700/30 transition-colors">
                    <td class="px-5 py-3">
                        <p class="font-semibold text-slate-800 dark:text-slate-100">{{ $req->booking->guest->fullName }}</p>
                        <p class="text-xs text-slate-400 font-mono">{{ $req->booking->booking_ref }}</p>
                    </td>
                    <td class="px-5 py-3 text-slate-700 dark:text-slate-300 font-semibold">№{{ $req->room->number }}</td>
                    <td class="px-5 py-3 text-slate-700 dark:text-slate-200">{{ $req->label }}</td>
                    <td class="px-5 py-3 text-right text-slate-700 dark:text-slate-300">{{ $req->quantity }}</td>
                    <td class="px-5 py-3 text-right font-bold text-slate-900 dark:text-slate-100 tabular-nums whitespace-nowrap">
                        {{ number_format($req->total_price, 0, '.', ' ') }}
                        <span class="text-xs font-normal text-slate-400">сум</span>
                    </td>
                    <td class="px-5 py-3 text-xs text-slate-400 font-mono whitespace-nowrap">{{ $req->created_at->format('d.m H:i') }}</td>
                    <td class="px-5 py-3 text-right">
                        @if($req->status === 'pending')
                        <div class="flex items-center justify-end gap-2">
                            <form method="POST" action="{{ route('service-requests.confirm', $req) }}">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 text-xs font-semibold bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">Принять</button>
                            </form>
                            <form method="POST" action="{{ route('service-requests.decline', $req) }}">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 text-xs font-semibold bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors border border-red-200 dark:border-red-800">Отклонить</button>
                            </form>
                        </div>
                        @elseif($req->status === 'confirmed')
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">Принято</span>
                        @else
                        <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400">Отклонено</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- Pagination --}}
    @if($requests->hasPages())
    <div class="mt-4">
        {{ $requests->appends(request()->query())->links() }}
    </div>
    @endif

</div>
@endsection
