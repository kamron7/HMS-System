<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\PaymentType;
use App\Enums\RoomStatus;
use App\Models\Booking;
use App\Models\CashierShift;
use App\Models\Guest;
use App\Models\GuestReview;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\ShiftNote;
use App\Models\WorkerShift;
use App\Services\BookingTotalsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function __construct(private BookingTotalsService $totals) {}

    public function index()
    {
        $roomStats = [
            'available'   => Room::where('status', RoomStatus::Available->value)->count(),
            'occupied'    => Room::where('status', RoomStatus::Occupied->value)->count(),
            'cleaning'    => Room::whereIn('status', [RoomStatus::Cleaning->value, RoomStatus::Dirty->value])->count(),
            'maintenance' => Room::where('status', RoomStatus::Maintenance->value)->count(),
            'total'       => Room::count(),
        ];

        $occupancyRate = $roomStats['total'] > 0
            ? round($roomStats['occupied'] / $roomStats['total'] * 100)
            : 0;

        $today = today();

        $checkInsToday = Booking::whereDate('check_in_date', $today)
            ->whereIn('status', [BookingStatus::Confirmed->value, BookingStatus::Pending->value, BookingStatus::CheckedIn->value])
            ->with(['guest', 'room'])
            ->orderBy('check_in_date')
            ->get();

        $checkOutsToday = Booking::whereDate('check_out_date', $today)
            ->where('status', BookingStatus::CheckedIn->value)
            ->with(['guest', 'room'])
            ->get();

        $lateCheckouts = Booking::where('check_out_date', '<', $today)
            ->where('status', BookingStatus::CheckedIn->value)
            ->with(['guest', 'room'])
            ->get();

        $revenueToday = (float) Payment::whereDate('paid_at', $today)
            ->where('type', PaymentType::Prepayment->value)
            ->sum('amount');

        $pendingBookings = Booking::where('status', BookingStatus::Pending->value)
            ->with(['guest', 'room.roomType'])
            ->orderBy('check_in_date')
            ->limit(5)
            ->get();

        $inquiryCount = Booking::where('status', BookingStatus::Inquiry->value)->count();

        // VIPs currently in-house
        $vipsInHouse = Booking::with(['guest', 'room'])
            ->where('status', BookingStatus::CheckedIn->value)
            ->whereHas('guest', fn($q) => $q->where('tag', 'vip'))
            ->get();

        // Dirty rooms waiting for cleaning
        $dirtyRooms = Room::with('roomType')
            ->where('status', RoomStatus::Dirty->value)
            ->orderBy('floor')
            ->orderBy('number')
            ->get();

        // Open maintenance tickets (urgent/high priority first)
        $openMaintenance = MaintenanceRequest::with(['room', 'guest'])
            ->whereIn('status', ['open', 'in_progress'])
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 0 WHEN 'high' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
            ->limit(8)
            ->get();

        // Cashier shifts today
        $cashierShiftsToday = CashierShift::with('user')
            ->whereDate('opened_at', today())
            ->orderBy('opened_at')
            ->get();
        $cashierTotalToday = $cashierShiftsToday->sum('cash_in');

        // Revenue vs target
        $revenueTarget = (float) env('DAILY_REVENUE_TARGET', 10000000 / 30);
        $revenuePercent = $revenueTarget > 0 ? min(round($revenueToday / $revenueTarget * 100), 999) : 0;

        $upcomingCheckIns = Booking::whereBetween('check_in_date', [
                $today->copy()->addDay(),
                $today->copy()->addDays(7),
            ])
            ->whereIn('status', [BookingStatus::Pending->value, BookingStatus::Confirmed->value])
            ->with(['guest', 'room.roomType'])
            ->orderBy('check_in_date')
            ->get();

        // --- Charts ---
        $totalRooms = $roomStats['total'];

        // Occupancy % — last 30 days (1 query)
        $occStart = today()->subDays(29);
        $activeStatuses = [
            BookingStatus::Pending->value,
            BookingStatus::Confirmed->value,
            BookingStatus::CheckedIn->value,
        ];
        $recentBookings = Booking::whereIn('status', $activeStatuses)
            ->where('check_in_date', '<=', today())
            ->where('check_out_date', '>', $occStart)
            ->get(['check_in_date', 'check_out_date']);

        $occupancyLabels = [];
        $occupancyValues = [];
        for ($i = 29; $i >= 0; $i--) {
            $day = today()->subDays($i);
            $booked = $recentBookings->filter(
                fn($b) => $b->check_in_date->lte($day) && $b->check_out_date->gt($day)
            )->count();
            $occupancyLabels[] = $day->format('d.m');
            $occupancyValues[] = $totalRooms > 0 ? round($booked / $totalRooms * 100) : 0;
        }

        // Revenue — last 12 months
        $revStart = now()->startOfMonth()->subMonths(11);
        $revenueRows = Payment::where('type', PaymentType::Prepayment->value)
            ->where('paid_at', '>=', $revStart)
            ->selectRaw("TO_CHAR(paid_at, 'YYYY-MM') as month, SUM(amount) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month');

        $revenueLabels = [];
        $revenueValues = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = now()->startOfMonth()->subMonths($i)->format('Y-m');
            $revenueLabels[] = now()->startOfMonth()->subMonths($i)->translatedFormat('M Y');
            $revenueValues[] = (float) ($revenueRows[$m] ?? 0);
        }

        // Bookings by status
        $statusCounts = Booking::selectRaw('status, COUNT(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $statusLabels = [];
        $statusValues = [];
        $statusColors = [];
        $colorMap = [
            'inquiry'     => '#a855f7',
            'pending'     => '#f59e0b',
            'confirmed'   => '#3b82f6',
            'checked_in'  => '#10b981',
            'checked_out' => '#6b7280',
            'cancelled'   => '#ef4444',
            'no_show'     => '#f97316',
        ];
        foreach ($statusCounts as $status => $cnt) {
            try {
                $statusLabels[] = BookingStatus::from($status)->label();
            } catch (\ValueError) {
                $statusLabels[] = $status;
            }
            $statusValues[] = (int) $cnt;
            $statusColors[] = $colorMap[$status] ?? '#94a3b8';
        }

        // Revenue by room type — current month
        $typeRevenue = Payment::where('payments.type', PaymentType::Prepayment->value)
            ->where('payments.paid_at', '>=', today()->startOfMonth())
            ->join('bookings', 'payments.booking_id', '=', 'bookings.id')
            ->join('rooms', 'bookings.room_id', '=', 'rooms.id')
            ->join('room_types', 'rooms.room_type_id', '=', 'room_types.id')
            ->selectRaw('room_types.name, SUM(payments.amount) as total')
            ->groupBy('room_types.name')
            ->pluck('total', 'name');

        $typeLabels = $typeRevenue->keys()->toArray();
        $typeValues = $typeRevenue->values()->map(fn($v) => (float) $v)->toArray();

        // Shift notes — last 3
        $lastShiftNotes = ShiftNote::with('user')
            ->where('created_at', '>=', now()->subDays(7))
            ->orderByDesc('created_at')
            ->limit(3)
            ->get();

        // My housekeeping tasks
        $myTasks = Room::with('roomType')
            ->where('assigned_to', auth()->id())
            ->whereIn('status', [RoomStatus::Cleaning->value, RoomStatus::Dirty->value])
            ->orderBy('floor')
            ->orderBy('number')
            ->get();

        // Debt summary (owner + manager only)
        $debtTotal = 0;
        $user = auth()->user();
        if (in_array($user->role->value, ['owner', 'manager'])) {
            $yesterday = today()->subDay();
            $debtBookings = Booking::with(['payments', 'charges'])
                ->whereIn('status', [BookingStatus::CheckedOut->value, BookingStatus::CheckedIn->value])
                ->get();

            foreach ($debtBookings as $b) {
                $grandTotal = $this->totals->grandTotal($b);
                $paid       = $this->totals->paidAmount($b);
                $balance    = $grandTotal - $paid;

                if ($balance <= 0) continue;

                if ($b->status->value === BookingStatus::CheckedOut->value) {
                    $debtTotal += $balance;
                } elseif ($b->status->value === BookingStatus::CheckedIn->value) {
                    $hasPrepayment = $b->payments
                        ->where('type', PaymentType::Prepayment->value)
                        ->sum('amount') > 0;
                    if (! $hasPrepayment && $b->check_in_date->lt($yesterday)) {
                        $debtTotal += $balance;
                    }
                }
            }
        }

        // ── Weather (Tashkent, cached 15 min) ──
        $weather = cache()->remember('weather_tashkent', now()->addMinutes(15), function () {
            $apiKey = env('WEATHER_API_KEY');
            if (! $apiKey) return null;
            try {
                $res = Http::timeout(3)->get('https://api.openweathermap.org/data/2.5/weather', [
                    'q'       => 'Tashkent,UZ',
                    'appid'   => $apiKey,
                    'units'   => 'metric',
                    'lang'    => 'ru',
                ]);
                if (! $res->ok()) return null;
                $d = $res->json();
                return [
                    'temp'        => round($d['main']['temp']),
                    'feels_like'  => round($d['main']['feels_like']),
                    'description' => ucfirst($d['weather'][0]['description']),
                    'icon'        => $d['weather'][0]['icon'],
                    'humidity'    => $d['main']['humidity'],
                ];
            } catch (\Throwable) {
                return null;
            }
        });

        // ── Recent guest reviews ──
        $recentReviews = GuestReview::with(['booking.guest', 'room'])
            ->orderByDesc('submitted_at')
            ->limit(5)
            ->get();

        $averageRating = $recentReviews->isNotEmpty()
            ? round(GuestReview::avg('rating'), 1)
            : null;

        // ── Guest retention / repeat rate ──
        $totalGuests = Guest::count();
        $repeatGuests = Guest::has('bookings', '>=', 2)->count();
        $retentionRate = $totalGuests > 0 ? round($repeatGuests / $totalGuests * 100) : 0;

        // ── Current user's shift status (for dashboard banner) ──
        $myOpenShift = WorkerShift::where('user_id', auth()->id())
            ->where('status', 'open')
            ->orderByDesc('started_at')
            ->first();

        return view('dashboard', compact(
            'roomStats',
            'occupancyRate',
            'checkInsToday',
            'checkOutsToday',
            'lateCheckouts',
            'revenueToday',
            'revenueTarget',
            'revenuePercent',
            'pendingBookings',
            'inquiryCount',
            'upcomingCheckIns',
            'vipsInHouse',
            'dirtyRooms',
            'openMaintenance',
            'cashierShiftsToday',
            'cashierTotalToday',
            'occupancyLabels',
            'occupancyValues',
            'revenueLabels',
            'revenueValues',
            'statusLabels',
            'statusValues',
            'statusColors',
            'typeLabels',
            'typeValues',
            'lastShiftNotes',
            'myTasks',
            'debtTotal',
            'weather',
            'recentReviews',
            'averageRating',
            'retentionRate',
            'myOpenShift',
        ));
    }
}
