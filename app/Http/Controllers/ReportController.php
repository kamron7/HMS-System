<?php

namespace App\Http\Controllers;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Enums\PaymentType;
use App\Models\Booking;
use App\Models\Expense;
use App\Models\Guest;
use App\Models\Payment;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    // -------------------------------------------------------------------------
    // Hub
    // -------------------------------------------------------------------------
    public function index(Request $request)
    {
        $period = $request->get('period', 'month');
        [$start, $end] = $this->periodRange($period);

        $totalRooms = Room::count();
        $days = $start->diffInDays($end) + 1;

        $roomRevenue = (float) Payment::where('type', PaymentType::Prepayment->value)
            ->whereBetween('paid_at', [$start, $end->copy()->endOfDay()])
            ->sum('amount');

        $nightsSold = Booking::whereIn('status', [
                BookingStatus::CheckedIn->value,
                BookingStatus::CheckedOut->value,
                BookingStatus::Confirmed->value,
            ])
            ->where('check_in_date', '>=', $start)
            ->where('check_out_date', '<=', $end->copy()->addDay())
            ->selectRaw("SUM(check_out_date::date - check_in_date::date) as nights")
            ->value('nights') ?? 0;

        $adr    = $nightsSold > 0 ? round($roomRevenue / $nightsSold) : 0;
        $revpar = ($totalRooms * $days) > 0 ? round($roomRevenue / ($totalRooms * $days)) : 0;

        $totalBookings = Booking::whereBetween('created_at', [$start, $end->copy()->endOfDay()])->count();
        $totalGuests   = Guest::whereBetween('created_at', [$start, $end->copy()->endOfDay()])->count();
        $totalExpenses = (float) Expense::whereBetween('expense_date', [$start, $end])->sum('amount');

        return view('reports.index', compact(
            'period', 'start', 'end',
            'roomRevenue', 'nightsSold', 'adr', 'revpar',
            'totalBookings', 'totalGuests', 'totalExpenses',
        ));
    }

    // -------------------------------------------------------------------------
    // Forecast heatmap
    // -------------------------------------------------------------------------
    public function forecast()
    {
        $totalRooms = Room::count();
        $start = today();
        $end   = today()->addDays(89);

        $bookings = Booking::whereIn('status', [
                BookingStatus::Confirmed->value,
                BookingStatus::CheckedIn->value,
            ])
            ->where('check_in_date', '<=', $end)
            ->where('check_out_date', '>', $start)
            ->get(['check_in_date', 'check_out_date']);

        $days = [];
        for ($i = 0; $i <= 89; $i++) {
            $day    = today()->addDays($i);
            $booked = $bookings->filter(
                fn($b) => $b->check_in_date->lte($day) && $b->check_out_date->gt($day)
            )->count();
            $days[] = [
                'date'   => $day->toDateString(),
                'label'  => $day->format('d'),
                'month'  => $day->translatedFormat('F Y'),
                'weekday'=> (int) $day->format('N'), // 1=Mon…7=Sun
                'booked' => $booked,
                'total'  => $totalRooms,
                'pct'    => $totalRooms > 0 ? round($booked / $totalRooms * 100) : 0,
            ];
        }

        // Group by month for rendering
        $months = collect($days)->groupBy('month');

        return view('reports.forecast', compact('months', 'totalRooms'));
    }

    // -------------------------------------------------------------------------
    // Revenue report
    // -------------------------------------------------------------------------
    public function revenue(Request $request)
    {
        $period = $request->get('period', 'month');
        [$start, $end] = $this->periodRange($period);

        $rows = Payment::where('type', PaymentType::Prepayment->value)
            ->whereBetween('paid_at', [$start, $end->copy()->endOfDay()])
            ->with(['booking.room.roomType', 'booking.guest'])
            ->orderByDesc('paid_at')
            ->get();

        $total = $rows->sum('amount');

        if ($request->get('export') === 'csv') {
            return $this->csvResponse('revenue', ['Дата', 'Гость', 'Номер', 'Тип номера', 'Метод', 'Сумма'],
                $rows->map(fn($p) => [
                    $p->paid_at->format('d.m.Y'),
                    $p->booking->guest->fullName ?? '—',
                    $p->booking->room->number ?? '—',
                    $p->booking->room->roomType->name ?? '—',
                    $p->method,
                    number_format($p->amount, 2, '.', ''),
                ])->toArray()
            );
        }

        if ($request->get('export') === 'pdf') {
            return $this->pdfResponse('reports.pdf.revenue', compact('rows', 'total', 'start', 'end'), 'revenue');
        }

        return view('reports.revenue', compact('rows', 'total', 'period', 'start', 'end'));
    }

    // -------------------------------------------------------------------------
    // Occupancy report
    // -------------------------------------------------------------------------
    public function occupancy(Request $request)
    {
        $period = $request->get('period', 'month');
        [$start, $end] = $this->periodRange($period);

        $totalRooms = Room::count();
        $days = $start->diffInDays($end) + 1;

        // Build daily occupancy for the period
        $bookings = Booking::whereIn('status', [
                BookingStatus::CheckedIn->value,
                BookingStatus::CheckedOut->value,
                BookingStatus::Confirmed->value,
                BookingStatus::Pending->value,
            ])
            ->where('check_in_date', '<=', $end)
            ->where('check_out_date', '>', $start)
            ->get(['check_in_date', 'check_out_date']);

        $rows = [];
        $cur = $start->copy();
        while ($cur->lte($end)) {
            $booked = $bookings->filter(
                fn($b) => $b->check_in_date->lte($cur) && $b->check_out_date->gt($cur)
            )->count();
            $rows[] = [
                'date'   => $cur->toDateString(),
                'booked' => $booked,
                'total'  => $totalRooms,
                'pct'    => $totalRooms > 0 ? round($booked / $totalRooms * 100) : 0,
            ];
            $cur->addDay();
        }

        $avgPct = count($rows) > 0 ? round(collect($rows)->avg('pct')) : 0;

        if ($request->get('export') === 'csv') {
            return $this->csvResponse('occupancy', ['Дата', 'Занято', 'Всего', '%'],
                array_map(fn($r) => [$r['date'], $r['booked'], $r['total'], $r['pct']], $rows)
            );
        }

        if ($request->get('export') === 'pdf') {
            return $this->pdfResponse('reports.pdf.occupancy', compact('rows', 'avgPct', 'start', 'end', 'totalRooms'), 'occupancy');
        }

        return view('reports.occupancy', compact('rows', 'avgPct', 'period', 'start', 'end', 'totalRooms'));
    }

    // -------------------------------------------------------------------------
    // Guest statistics
    // -------------------------------------------------------------------------
    public function guests(Request $request)
    {
        $period = $request->get('period', 'month');
        [$start, $end] = $this->periodRange($period);

        $newGuests = Guest::whereBetween('created_at', [$start, $end->copy()->endOfDay()])->count();

        $repeatGuests = Guest::whereHas('bookings', fn($q) => $q->where('created_at', '<', $start))
            ->whereHas('bookings', fn($q) => $q->whereBetween('created_at', [$start, $end->copy()->endOfDay()]))
            ->count();

        $nationalityBreakdown = Guest::whereNotNull('nationality')
            ->selectRaw('nationality, COUNT(*) as cnt')
            ->groupBy('nationality')
            ->orderByDesc('cnt')
            ->limit(10)
            ->pluck('cnt', 'nationality');

        if ($request->get('export') === 'csv') {
            $rows = [['Новые гости', $newGuests], ['Повторные гости', $repeatGuests], ['', ''], ['Национальность', 'Количество']];
            foreach ($nationalityBreakdown as $nat => $cnt) {
                $rows[] = [$nat, $cnt];
            }
            return $this->csvResponse('guests', ['Параметр', 'Значение'], $rows);
        }

        return view('reports.guests', compact('newGuests', 'repeatGuests', 'nationalityBreakdown', 'period', 'start', 'end'));
    }

    // -------------------------------------------------------------------------
    // Expenses by category
    // -------------------------------------------------------------------------
    public function expenses(Request $request)
    {
        $period = $request->get('period', 'month');
        [$start, $end] = $this->periodRange($period);

        $rows = Expense::whereBetween('expense_date', [$start, $end])
            ->orderBy('expense_date')
            ->get();

        $byCategory = $rows->groupBy('category')->map->sum('amount');
        $total = $rows->sum('amount');

        if ($request->get('export') === 'csv') {
            return $this->csvResponse('expenses', ['Дата', 'Категория', 'Описание', 'Сумма'],
                $rows->map(fn($e) => [
                    $e->expense_date->format('d.m.Y'),
                    $e->category,
                    $e->description,
                    number_format($e->amount, 2, '.', ''),
                ])->toArray()
            );
        }

        if ($request->get('export') === 'pdf') {
            return $this->pdfResponse('reports.pdf.expenses', compact('rows', 'byCategory', 'total', 'start', 'end'), 'expenses');
        }

        return view('reports.expenses', compact('rows', 'byCategory', 'total', 'period', 'start', 'end'));
    }

    // -------------------------------------------------------------------------
    // Unpaid bookings
    // -------------------------------------------------------------------------
    public function unpaid(Request $request)
    {
        $bookings = Booking::whereIn('status', [
                BookingStatus::CheckedIn->value,
                BookingStatus::Confirmed->value,
                BookingStatus::Pending->value,
            ])
            ->with(['guest', 'room.roomType', 'payments'])
            ->get()
            ->filter(fn($b) => (float) $b->paymentStatus() !== 'paid')
            ->filter(function ($b) {
                $paid = (float) $b->payments->where('type', PaymentType::Prepayment->value)->sum('amount');
                return $paid < (float) $b->total_price;
            })
            ->values();

        if ($request->get('export') === 'pdf') {
            return $this->pdfResponse('reports.pdf.unpaid', compact('bookings'), 'unpaid');
        }

        return view('reports.unpaid', compact('bookings'));
    }

    // -------------------------------------------------------------------------
    // Booking sources
    // -------------------------------------------------------------------------
    public function sources(Request $request)
    {
        $period = $request->get('period', 'month');
        [$start, $end] = $this->periodRange($period);

        $rows = Booking::whereBetween('created_at', [$start, $end->copy()->endOfDay()])
            ->selectRaw('source, COUNT(*) as cnt')
            ->groupBy('source')
            ->pluck('cnt', 'source');

        $staffCount  = (int) ($rows[BookingSource::Staff->value]  ?? 0);
        $clientCount = (int) ($rows[BookingSource::Client->value] ?? 0);
        $total       = $staffCount + $clientCount;

        if ($request->get('export') === 'csv') {
            return $this->csvResponse('sources', ['Источник', 'Количество', '%'], [
                ['Персонал', $staffCount,  $total > 0 ? round($staffCount  / $total * 100) : 0],
                ['Клиентский портал', $clientCount, $total > 0 ? round($clientCount / $total * 100) : 0],
            ]);
        }

        return view('reports.sources', compact('staffCount', 'clientCount', 'total', 'period', 'start', 'end'));
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------
    private function periodRange(string $period): array
    {
        return match ($period) {
            'quarter' => [now()->startOfQuarter(), now()->endOfQuarter()],
            'year'    => [now()->startOfYear(),    now()->endOfYear()],
            default   => [now()->startOfMonth(),   now()->endOfMonth()],
        };
    }

    private function csvResponse(string $filename, array $headers, array $rows): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM for Excel
            fputcsv($handle, $headers, ';');
            foreach ($rows as $row) {
                fputcsv($handle, $row, ';');
            }
            fclose($handle);
        }, $filename . '_' . date('Y-m-d') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function pdfResponse(string $view, array $data, string $filename): \Illuminate\Http\Response
    {
        $pdf = app('dompdf.wrapper')->loadView($view, $data);
        return $pdf->download($filename . '_' . date('Y-m-d') . '.pdf');
    }
}
