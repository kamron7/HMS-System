<?php

namespace Database\Seeders;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Enums\GuestTag;
use App\Enums\LostItemStatus;
use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceStatus;
use App\Enums\PaymentType;
use App\Models\Booking;
use App\Models\BookingCharge;
use App\Models\BookingInquiry;
use App\Models\Expense;
use App\Models\Guest;
use App\Models\GuestReview;
use App\Models\LostItem;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Room;
use App\Models\ShiftNote;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class AliveSeeder extends Seeder
{
    // roomId => [[checkIn, checkOut], ...]
    private array $taken = [];

    public function run(): void
    {
        $rooms  = Room::with('roomType')->get();
        $users  = User::all();
        $today  = Carbon::today();

        $owner       = $users->firstWhere('email', 'owner@hotel.uz');
        $manager     = $users->firstWhere('email', 'manager@hotel.uz');
        $recept      = $users->firstWhere('email', 'admin@hotel.uz');
        $recept2     = $users->firstWhere('email', 'receptionist2@hotel.uz');
        $hkeeper     = $users->firstWhere('email', 'housekeeper@hotel.uz');
        $hkeeper2    = $users->firstWhere('email', 'housekeeper2@hotel.uz');
        $accountant  = $users->firstWhere('email', 'accountant@hotel.uz');

        // Load existing bookings to avoid conflicts
        Booking::withTrashed()->each(function ($b) {
            $this->taken[$b->room_id][] = [
                $b->check_in_date->toDateString(),
                $b->check_out_date->toDateString(),
            ];
        });

        // Pool of guests (existing + new)
        $guests = Guest::all()->merge($this->addGuests());

        $staff = collect([$recept, $recept2, $manager]);

        // Fill every room with a full year of bookings
        foreach ($rooms as $room) {
            $this->fillRoom($room, $guests, $staff, $manager, $today);
        }

        $this->seedExpenses($manager, $accountant);
        $this->seedMaintenance($rooms, $recept, $hkeeper, $hkeeper2, $manager);
        $this->seedLostItems($rooms, $guests, $recept, $recept2, $hkeeper);
        $this->seedShiftNotes($users, $today);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Availability helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function isAvailable(int $roomId, string $checkIn, string $checkOut): bool
    {
        foreach ($this->taken[$roomId] ?? [] as [$in, $out]) {
            if ($checkIn < $out && $checkOut > $in) {
                return false;
            }
        }
        return true;
    }

    private function isDateFree(int $roomId, string $date): bool
    {
        foreach ($this->taken[$roomId] ?? [] as [$in, $out]) {
            if ($date >= $in && $date < $out) {
                return false;
            }
        }
        return true;
    }

    private function reserve(int $roomId, string $checkIn, string $checkOut): void
    {
        $this->taken[$roomId][] = [$checkIn, $checkOut];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Main booking generator per room
    // ─────────────────────────────────────────────────────────────────────────

    private function fillRoom(Room $room, Collection $guests, Collection $staff, User $manager, Carbon $today): void
    {
        $typeName  = $room->roomType->name;
        $basePrice = (float) $room->roomType->base_price;
        $cursor    = Carbon::create(2026, 1, 1);
        $yearEnd   = Carbon::create(2026, 12, 31);

        while ($cursor->lte($yearEnd)) {
            $dateStr = $cursor->toDateString();

            if (!$this->isDateFree($room->id, $dateStr)) {
                $cursor->addDay();
                continue;
            }

            // Probabilistic: skip some days (gap / low season)
            if (mt_rand(1, 100) > $this->occupancyRate($cursor->month)) {
                $cursor->addDays(mt_rand(1, 2));
                continue;
            }

            $nights   = $this->stayLength($typeName);
            $checkIn  = $dateStr;
            $checkOut = $cursor->copy()->addDays($nights)->toDateString();

            if ($checkOut > '2027-01-20') break;
            if (!$this->isAvailable($room->id, $checkIn, $checkOut)) {
                $cursor->addDay();
                continue;
            }

            $checkInDate  = Carbon::parse($checkIn);
            $checkOutDate = Carbon::parse($checkOut);

            // Determine status
            if ($checkOutDate->lt($today)) {
                // Occasionally no-show or cancelled instead of checked_out
                $r = mt_rand(1, 100);
                $status = $r <= 4 ? BookingStatus::Cancelled
                        : ($r <= 6 ? BookingStatus::NoShow
                        : BookingStatus::CheckedOut);
            } elseif ($checkInDate->lte($today) && $checkOutDate->gt($today)) {
                $status = BookingStatus::CheckedIn;
            } else {
                // Future
                $r = mt_rand(1, 100);
                $status = $r <= 8  ? BookingStatus::Cancelled
                        : ($r <= 18 ? BookingStatus::Inquiry
                        : ($r <= 45 ? BookingStatus::Pending
                        : BookingStatus::Confirmed));
            }

            $price  = $this->seasonalPrice($basePrice, $cursor->month, $nights);
            $source = mt_rand(1, 100) <= 70 ? BookingSource::Staff : BookingSource::Client;
            $creator = $source === BookingSource::Staff ? $staff->random() : $manager;
            $guest   = $guests->random();

            $capacity = $room->roomType->capacity;
            $adults   = mt_rand(1, min(2, $capacity));
            $children = ($capacity > 2 && mt_rand(1, 100) <= 20) ? mt_rand(1, 2) : 0;

            $booking = Booking::create([
                'room_id'        => $room->id,
                'guest_id'       => $guest->id,
                'check_in_date'  => $checkIn,
                'check_out_date' => $checkOut,
                'adults'         => $adults,
                'children'       => $children,
                'status'         => $status->value,
                'source'         => $source->value,
                'total_price'    => $price,
                'created_by'     => $creator->id,
            ]);

            $this->addPayment($booking, $status, $price, $checkIn);

            if ($status === BookingStatus::CheckedOut && mt_rand(1, 100) <= 40) {
                $this->addCharges($booking, $creator);
            }

            if ($status === BookingStatus::CheckedOut && mt_rand(1, 100) <= 55) {
                $this->addReview($booking, $guest, $checkOut);
            }

            if ($status === BookingStatus::Inquiry && $source === BookingSource::Client) {
                BookingInquiry::create([
                    'booking_id' => $booking->id,
                    'first_name' => $guest->first_name,
                    'last_name'  => $guest->last_name,
                    'phone'      => $guest->phone,
                    'email'      => $guest->email,
                ]);
            }

            $this->reserve($room->id, $checkIn, $checkOut);
            $cursor = Carbon::parse($checkOut)->addDays(mt_rand(0, 2));
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function occupancyRate(int $month): int
    {
        return match($month) {
            1, 2    => 48,
            3       => 58,
            4       => 62,
            5       => 68,
            6, 7, 8 => 82,
            9, 10   => 66,
            11      => 55,
            12      => 68,
            default => 60,
        };
    }

    private function stayLength(string $typeName): int
    {
        return match(true) {
            str_contains($typeName, 'Президент') => mt_rand(3, 9),
            str_contains($typeName, 'Люкс')      => mt_rand(2, 7),
            str_contains($typeName, 'Делюкс')    => mt_rand(2, 5),
            default                              => mt_rand(1, 4),
        };
    }

    private function seasonalPrice(float $base, int $month, int $nights): float
    {
        $mult = match($month) {
            6, 7, 8 => 1.25,
            3, 4, 5 => 1.15,
            12      => 1.20,
            1       => 1.10,
            default => 1.00,
        };
        return round($base * $mult * $nights / 1000) * 1000;
    }

    private function addPayment(Booking $booking, BookingStatus $status, float $price, string $checkIn): void
    {
        $ratio = match($status) {
            BookingStatus::CheckedOut => 1.0,
            BookingStatus::CheckedIn  => mt_rand(1, 100) <= 60 ? 1.0 : 0.5,
            BookingStatus::Confirmed  => 0.5,
            default                   => 0.0,
        };

        if ($ratio === 0.0) return;

        $amount = round($price * $ratio / 1000) * 1000;
        if ($amount <= 0) return;

        $method = collect(['cash', 'card', 'transfer'])->random();
        $type   = $ratio < 1.0 ? PaymentType::Deposit->value : PaymentType::Prepayment->value;

        Payment::create([
            'booking_id' => $booking->id,
            'amount'     => $amount,
            'method'     => $method,
            'type'       => $type,
            'paid_at'    => Carbon::parse($checkIn)->subDays(mt_rand(0, 3)),
        ]);

        // Partial checkout payments: sometimes a second transaction for the balance
        if ($status === BookingStatus::CheckedOut && $ratio < 1.0) {
            Payment::create([
                'booking_id' => $booking->id,
                'amount'     => round($price * (1 - $ratio) / 1000) * 1000,
                'method'     => $method,
                'type'       => PaymentType::Prepayment->value,
                'paid_at'    => Carbon::parse($booking->check_out_date)->subDays(mt_rand(0, 1)),
            ]);
        }
    }

    private function addCharges(Booking $booking, User $creator): void
    {
        $templates = [
            ['Мини-бар',              'minibar',      [15000, 25000, 35000, 50000]],
            ['Стирка',                'laundry',      [20000, 30000, 45000]],
            ['Рум-сервис (ужин)',      'room_service', [60000, 80000, 120000]],
            ['Рум-сервис (завтрак)',   'room_service', [30000, 45000]],
            ['Парковка',              'parking',      [10000, 20000, 30000]],
            ['СПА-процедуры',         'spa',          [150000, 200000, 300000]],
            ['Дополнительная кровать','other',         [30000, 50000]],
            ['Поздний выезд',         'other',         [25000, 50000]],
            ['Аренда велосипеда',     'other',         [20000, 30000]],
        ];

        $count  = mt_rand(1, 3);
        $picked = collect($templates)->shuffle()->take($count);
        $stayDays = max(1, (int) Carbon::parse($booking->check_in_date)->diffInDays(Carbon::parse($booking->check_out_date)));

        foreach ($picked as [$desc, $cat, $amounts]) {
            BookingCharge::create([
                'booking_id'  => $booking->id,
                'description' => $desc,
                'category'    => $cat,
                'amount'      => $amounts[array_rand($amounts)],
                'created_by'  => $creator->id,
                'created_at'  => Carbon::parse($booking->check_in_date)->addDays(mt_rand(0, $stayDays - 1)),
            ]);
        }
    }

    private function addReview(Booking $booking, Guest $guest, string $checkOut): void
    {
        $byRating = [
            5 => [
                'Превосходный отель, всё безупречно — чистота, персонал, завтрак!',
                'Лучший отель в городе, вернёмся обязательно.',
                'Отличный сервис, персонал очень внимательный и отзывчивый.',
                'Номер уютный, кровать удобная, тишина по ночам — спасибо!',
                'Прекрасный вид, хорошая еда, рекомендую всем.',
                'Выше всяких ожиданий, спасибо команде!',
            ],
            4 => [
                'Хороший отель, понравилось. Небольшие замечания по завтраку.',
                'В целом отлично, персонал вежливый, номер чистый.',
                'Хорошее соотношение цена/качество.',
                'Приятный отель, порекомендую друзьям.',
                'Удобное расположение, быстрое заселение.',
                'Всё понравилось, небольшие пожелания по меню.',
            ],
            3 => [
                'Нормально, ничего особенного. Ожидал немного больше.',
                'Номер средний, Wi-Fi медленный.',
                'В целом приемлемо, но есть моменты для улучшения.',
                'Стандартный отель, без приятных сюрпризов.',
            ],
            2 => [
                'Разочарован уровнем уборки в номере.',
                'Обслуживание медленное, пришлось долго ждать.',
                'Шумно ночью из-за соседних номеров.',
            ],
            1 => [
                'Не соответствует описанию, остался недоволен.',
                'Много проблем, не рекомендую.',
            ],
        ];

        $rating = $this->weightedRating();

        GuestReview::create([
            'room_id'      => $booking->room_id,
            'booking_id'   => $booking->id,
            'guest_id'     => $guest->id,
            'rating'       => $rating,
            'comment'      => $byRating[$rating][array_rand($byRating[$rating])],
            'submitted_at' => Carbon::parse($checkOut)->addDays(mt_rand(0, 7)),
        ]);

        $booking->update(['feedback_sent' => true]);
    }

    private function weightedRating(): int
    {
        $r = mt_rand(1, 100);
        return match(true) {
            $r <=  3 => 1,
            $r <=  8 => 2,
            $r <= 22 => 3,
            $r <= 55 => 4,
            default  => 5,
        };
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Extra guests
    // ─────────────────────────────────────────────────────────────────────────

    private function addGuests(): Collection
    {
        $data = [
            ['Жасур',     'Раимов',      '+998 90 301 00 01', null,                       'AQ 1800017', 'Узбекистан', GuestTag::Regular],
            ['Камолиддин','Маматов',      '+998 91 302 00 02', 'kamol@mail.ru',             'AR 1900018', 'Узбекистан', GuestTag::Regular],
            ['Нигора',    'Ибрагимова',   '+998 93 303 00 03', 'nigora.i@gmail.com',        'AS 2000019', 'Узбекистан', GuestTag::Regular],
            ['Санобар',   'Хамидова',     '+998 94 304 00 04', null,                        'AT 2100020', 'Узбекистан', GuestTag::Vip],
            ['Дильшод',   'Умаров',       '+998 95 305 00 05', 'dilshod.u@gmail.com',       'AU 2200021', 'Узбекистан', GuestTag::Regular],
            ['Михаил',    'Захаров',      '+7 915 305 00 06',  'mz@yandex.ru',              'BА 0000001', 'Россия',     GuestTag::Regular],
            ['Дарья',     'Попова',       '+7 985 306 00 07',  'dasha.p@mail.ru',           'BB 0000002', 'Россия',     GuestTag::Regular],
            ['Алексей',   'Морозов',      '+7 926 307 00 08',  null,                        'BC 0000003', 'Россия',     GuestTag::Regular],
            ['Ольга',     'Кузнецова',    '+7 903 308 00 09',  'olga.k@gmail.com',          'BD 0000004', 'Россия',     GuestTag::Regular],
            ['Андрей',    'Новиков',      '+7 916 309 00 10',  'andrey.n@yandex.ru',        'BE 0000005', 'Россия',     GuestTag::Regular],
            ['Li',        'Wei',          '+86 138 0001 0001', 'li.wei@qq.com',             'CN 0000001', 'Китай',      GuestTag::Regular],
            ['Wang',      'Fang',         '+86 139 0002 0002', 'wang.fang@163.com',         'CN 0000002', 'Китай',      GuestTag::Regular],
            ['Kim',       'Minji',        '+82 10 0001 0001',  'minji.kim@naver.com',       'KR 0000001', 'Корея',      GuestTag::Regular],
            ['Lee',       'Junho',        '+82 10 0002 0002',  'junho.lee@kakao.com',       'KR 0000002', 'Корея',      GuestTag::Regular],
            ['Fatima',    'Al-Zahra',     '+971 55 001 0001',  'fatima.z@gmail.com',        'AE 0000001', 'ОАЭ',        GuestTag::Vip],
            ['Omar',      'Khalid',       '+971 50 002 0002',  null,                        'AE 0000002', 'ОАЭ',        GuestTag::Regular],
            ['Elena',     'Fischer',      '+49 162 001 0001',  'elena.f@gmail.com',         'DE 0000001', 'Германия',   GuestTag::Regular],
            ['Hans',      'Wagner',       '+49 170 002 0002',  'h.wagner@web.de',           'DE 0000002', 'Германия',   GuestTag::Regular],
            ['Marie',     'Dupont',       '+33 6 00 01 00 01', 'marie.d@gmail.com',         'FR 0000001', 'Франция',    GuestTag::Regular],
            ['Pierre',    'Bernard',      '+33 6 00 02 00 02', null,                        'FR 0000002', 'Франция',    GuestTag::Regular],
            ['Giuseppe',  'Ricci',        '+39 333 001 0001',  'g.ricci@libero.it',         'IT 0000002', 'Италия',     GuestTag::Regular],
            ['Yuki',      'Tanaka',       '+81 90 0001 0001',  'yuki.t@gmail.com',          'JP 0000001', 'Япония',     GuestTag::Regular],
            ['Kenji',     'Sato',         '+81 80 0002 0002',  null,                        'JP 0000002', 'Япония',     GuestTag::Regular],
            ['Emma',      'Wilson',       '+44 771 001 0001',  'emma.w@gmail.com',          'GB 0000001', 'Великобритания', GuestTag::Regular],
            ['Jack',      'Taylor',       '+44 772 002 0002',  'jack.t@hotmail.com',        'GB 0000002', 'Великобритания', GuestTag::Regular],
            ['Pablo',     'García',       '+34 612 001 001',   'pablo.g@gmail.com',         'ES 0000002', 'Испания',    GuestTag::Regular],
            ['Laura',     'Martínez',     '+34 613 002 002',   null,                        'ES 0000003', 'Испания',    GuestTag::Regular],
            ['Abdulla',   'Nazarov',      '+998 90 401 00 01', 'abdulla.n@mail.ru',         'AV 2300022', 'Узбекистан', GuestTag::Regular],
            ['Гулбаҳор',  'Рустамова',    '+998 91 402 00 02', null,                        'AW 2400023', 'Узбекистан', GuestTag::Regular],
            ['Хуршид',    'Тожибоев',     '+998 93 403 00 03', 'xurshid.t@gmail.com',       'AX 2500024', 'Узбекистан', GuestTag::Regular],
            ['Шахноза',   'Алиева',       '+998 94 404 00 04', 'shahnoza.a@yandex.ru',      'AY 2600025', 'Узбекистан', GuestTag::Regular],
            ['Акбар',     'Мирзаев',      '+998 95 405 00 05', null,                        'AZ 2700026', 'Узбекистан', GuestTag::Regular],
            ['Барно',     'Юсупова',      '+998 97 406 00 06', 'barno.y@gmail.com',         'BA 2800027', 'Узбекистан', GuestTag::Vip],
            ['Зафар',     'Холматов',     '+998 98 407 00 07', null,                        'BB 2900028', 'Узбекистан', GuestTag::Regular],
            ['Муроджон',  'Эргашев',      '+998 99 408 00 08', 'murod.e@mail.ru',           'BC 3000029', 'Узбекистан', GuestTag::Regular],
            ['Насиба',    'Матмусаева',   '+998 90 501 00 01', null,                        'BD 3100030', 'Узбекистан', GuestTag::Regular],
            ['Санжар',    'Ниёзов',       '+998 91 502 00 02', 'sanjarn@gmail.com',         'BE 3200031', 'Узбекистан', GuestTag::Regular],
            ['Дилором',   'Каримова',     '+998 93 503 00 03', null,                        'BF 3300032', 'Узбекистан', GuestTag::Regular],
            ['Umid',      'Toshev',       '+998 94 504 00 04', 'umid.t@gmail.com',          'BG 3400033', 'Узбекистан', GuestTag::Regular],
            ['Navbahor',  'Qodieva',      '+998 95 505 00 05', null,                        'BH 3500034', 'Узбекистан', GuestTag::Regular],
            ['Abdullah',  'Rahman',       '+966 55 001 0001',  'abd.r@gmail.com',           'SA 0000001', 'Саудовская Аравия', GuestTag::Vip],
            ['Nour',      'Hassan',       '+20 100 001 0001',  null,                        'EG 0000001', 'Египет',     GuestTag::Regular],
            ['Amir',      'Hosseini',     '+98 912 001 0001',  'amir.h@gmail.com',          'IR 0000001', 'Иран',       GuestTag::Regular],
            ['Astrid',    'Lindström',    '+46 70 001 00 01',  'astrid.l@gmail.com',        'SE 0000001', 'Швеция',     GuestTag::Regular],
            ['Luca',      'Bianchi',      '+39 347 003 0003',  null,                        'IT 0000003', 'Италия',     GuestTag::Regular],
            ['Иван',      'Соколов',      '+7 906 001 00 01',  'ivan.sokolov@yandex.ru',    'BF 0000001', 'Россия',     GuestTag::Regular],
            ['Марина',    'Волкова',      '+7 925 002 00 02',  null,                        'BG 0000002', 'Россия',     GuestTag::Regular],
            ['Григорий',  'Лебедев',      '+7 967 003 00 03',  'grig.l@mail.ru',            'BH 0000003', 'Россия',     GuestTag::Regular],
            ['Юлия',      'Козлова',      '+7 977 004 00 04',  null,                        'BI 0000004', 'Россия',     GuestTag::Regular],
            ['Виктор',    'Чернов',       '+7 916 005 00 05',  'viktor.ch@gmail.com',       'BJ 0000005', 'Россия',     GuestTag::Regular],
        ];

        $result = [];
        foreach ($data as [$fn, $ln, $phone, $email, $passport, $nationality, $tag]) {
            // Skip if phone already exists
            if (Guest::where('phone', $phone)->exists()) continue;
            $result[] = Guest::create([
                'first_name'      => $fn,
                'last_name'       => $ln,
                'phone'           => $phone,
                'email'           => $email,
                'passport_number' => $passport,
                'nationality'     => $nationality,
                'tag'             => $tag->value,
            ]);
        }
        return collect($result);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Expenses (full year)
    // ─────────────────────────────────────────────────────────────────────────

    private function seedExpenses(User $manager, ?User $accountant): void
    {
        $creator = $accountant ?? $manager;

        // Monthly recurring
        for ($m = 1; $m <= 12; $m++) {
            $date = Carbon::create(2026, $m, 1);
            if ($date->gt(Carbon::today()->addDays(5))) break; // don't create future expenses

            $lastDay = $date->copy()->endOfMonth()->format('Y-m-d');
            $mid     = $date->copy()->setDay(15)->format('Y-m-d');
            $day10   = $date->copy()->setDay(10)->format('Y-m-d');
            $day20   = $date->copy()->setDay(20)->format('Y-m-d');
            $day25   = $date->copy()->setDay(25)->format('Y-m-d');

            // Salary (end of month)
            Expense::create(['category' => 'salary',    'description' => "Зарплата персонала за {$this->monthName($m)}",         'amount' => mt_rand(5500, 6800) * 1000, 'expense_date' => $lastDay, 'created_by' => $creator->id]);

            // Utilities
            Expense::create(['category' => 'utilities', 'description' => "Электроэнергия {$this->monthName($m)}",                'amount' => mt_rand(750, 1100) * 1000,  'expense_date' => $day25,   'created_by' => $creator->id]);
            Expense::create(['category' => 'utilities', 'description' => "Водоснабжение и канализация {$this->monthName($m)}",   'amount' => mt_rand(180, 320) * 1000,   'expense_date' => $day20,   'created_by' => $creator->id]);

            // Supplies
            Expense::create(['category' => 'supplies',  'description' => 'Чистящие средства и расходники',                       'amount' => mt_rand(120, 250) * 1000,   'expense_date' => $day10,   'created_by' => $creator->id]);
            Expense::create(['category' => 'supplies',  'description' => 'Постельное бельё и полотенца',                          'amount' => mt_rand(200, 450) * 1000,   'expense_date' => $mid,     'created_by' => $creator->id]);

            // Food/F&B (bi-monthly)
            if ($m % 2 === 0) {
                Expense::create(['category' => 'food',   'description' => 'Закупка продуктов (завтрак, мини-бар)',                 'amount' => mt_rand(350, 600) * 1000,   'expense_date' => $day10,   'created_by' => $creator->id]);
            }
        }

        // Quarterly marketing
        foreach ([['2026-01-20', 400], ['2026-04-10', 600], ['2026-07-08', 750], ['2026-10-05', 500]] as [$d, $base]) {
            if (Carbon::parse($d)->gt(Carbon::today())) break;
            Expense::create(['category' => 'marketing', 'description' => 'Реклама: Instagram, Google Ads, 2GIS',                  'amount' => ($base + mt_rand(-50, 100)) * 1000, 'expense_date' => $d, 'created_by' => $manager->id]);
        }

        // One-off repairs/upgrades spread through year
        $repairs = [
            ['2026-01-28', 'other',    'Замена замков — 3 номера',                           280000],
            ['2026-02-15', 'other',    'Ремонт кондиционера (204)',                          420000],
            ['2026-03-10', 'supplies', 'Закупка новых подушек и одеял (20 шт)',               380000],
            ['2026-04-05', 'other',    'Замена телевизора в номере 106',                      320000],
            ['2026-05-20', 'other',    'Покраска коридоров 2-го этажа',                       650000],
            ['2026-06-12', 'other',    'Ремонт бассейна (фильтры)',                           900000],
            ['2026-07-01', 'supplies', 'Покупка нового кухонного оборудования',              1200000],
            ['2026-08-15', 'other',    'Замена ковровых дорожек в лифте',                     340000],
            ['2026-09-03', 'other',    'Обслуживание лифтов (ежегодное)',                     480000],
            ['2026-10-18', 'supplies', 'Закупка зимнего постельного белья',                   520000],
            ['2026-11-10', 'other',    'Замена сантехники — 2 номера',                        560000],
            ['2026-12-01', 'other',    'Новогоднее украшение холла и коридоров',              450000],
        ];
        foreach ($repairs as [$d, $cat, $desc, $amt]) {
            if (Carbon::parse($d)->gt(Carbon::today())) break;
            Expense::create(['category' => $cat, 'description' => $desc, 'amount' => $amt, 'expense_date' => $d, 'created_by' => $creator->id]);
        }
    }

    private function monthName(int $m): string
    {
        return ['январь','февраль','март','апрель','май','июнь','июль','август','сентябрь','октябрь','ноябрь','декабрь'][$m - 1];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Maintenance requests
    // ─────────────────────────────────────────────────────────────────────────

    private function seedMaintenance(Collection $rooms, User $recept, ?User $hkeeper, ?User $hkeeper2, User $manager): void
    {
        $assigned = array_filter([$hkeeper, $hkeeper2]);
        $assigned = empty($assigned) ? [$manager] : $assigned;

        $templates = [
            ['Не работает кондиционер',     'Кондиционер не охлаждает, требуется заправка фреоном.',         MaintenancePriority::High,   true],
            ['Засорился слив в душе',        'Вода медленно уходит, требуется прочистка.',                    MaintenancePriority::Medium, true],
            ['Перегорела лампочка в ванной', 'Нужна замена лампы LED.',                                       MaintenancePriority::Low,    true],
            ['Сломан замок шкафа',           'Дверца шкафа не закрывается, нужна замена петли.',               MaintenancePriority::Low,    true],
            ['Течёт кран в ванной',          'Капает горячая вода, необходима замена прокладки.',              MaintenancePriority::Medium, true],
            ['Не работает розетка',          'Розетка у кровати не работает, нужен электрик.',                 MaintenancePriority::High,   true],
            ['Скрипит кровать',              'Кровать скрипит при движении, нужна подтяжка болтов.',           MaintenancePriority::Low,    true],
            ['Проблема с Wi-Fi',             'Слабый сигнал в номере, нужно переместить точку доступа.',       MaintenancePriority::Medium, true],
            ['Засорился унитаз',             'Срочно! Унитаз не смывает.',                                     MaintenancePriority::Urgent, false],
            ['Сломался фен',                 'Фен не включается, нужна замена.',                               MaintenancePriority::Low,    true],
            ['Трещина на зеркале в ванной',  'Зеркало треснуто, требует замены.',                              MaintenancePriority::Medium, true],
            ['Не закрывается балконная дверь','Балконная дверь не защёлкивается.',                             MaintenancePriority::Medium, true],
            ['Протечка потолка',             'После дождя появилась влажное пятно на потолке.',                MaintenancePriority::Urgent, false],
            ['Поломка телефона в номере',    'Телефонный аппарат не работает.',                               MaintenancePriority::Low,    true],
            ['Неисправен мини-бар',          'Холодильник в мини-баре не охлаждает.',                         MaintenancePriority::Medium, true],
        ];

        // Spread ~3-4 per month, Jan through Apr (past & present)
        $roomList = $rooms->values();
        $months   = [1, 2, 3, 4];

        foreach ($months as $m) {
            $count = mt_rand(3, 5);
            for ($i = 0; $i < $count; $i++) {
                [$title, $desc, $priority, $canResolve] = $templates[array_rand($templates)];
                $room = $roomList->random();
                $createdAt = Carbon::create(2026, $m, mt_rand(1, 25));
                $isPast = $createdAt->lt(Carbon::today()->subDays(7));

                if ($isPast && $canResolve && mt_rand(1, 100) <= 80) {
                    $status     = MaintenanceStatus::Resolved;
                    $resolvedAt = $createdAt->copy()->addDays(mt_rand(1, 5));
                } elseif ($isPast && mt_rand(1, 100) <= 50) {
                    $status     = MaintenanceStatus::InProgress;
                    $resolvedAt = null;
                } else {
                    $status     = MaintenanceStatus::Open;
                    $resolvedAt = null;
                }

                MaintenanceRequest::create([
                    'room_id'     => $room->id,
                    'title'       => $title,
                    'description' => $desc,
                    'priority'    => $priority->value,
                    'status'      => $status->value,
                    'assigned_to' => $assigned[array_rand($assigned)]->id ?? null,
                    'resolved_at' => $resolvedAt?->toDateTimeString(),
                    'created_by'  => mt_rand(1, 100) <= 50 ? $recept->id : $manager->id,
                    'created_at'  => $createdAt,
                    'updated_at'  => $createdAt,
                ]);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Lost items
    // ─────────────────────────────────────────────────────────────────────────

    private function seedLostItems(Collection $rooms, Collection $guests, User $recept, User $recept2, ?User $hkeeper): void
    {
        $items = [
            ['Зарядное устройство для телефона', 'Белый зарядник Apple Lightning, найден под кроватью.',  'Shelf A-1'],
            ['Ноутбук Dell',                     'Чёрный ноутбук Dell в чехле, найден в шкафу.',           'Safe #1'],
            ['Солнцезащитные очки Ray-Ban',       'Коричневые очки в твёрдом чехле, найдены на тумбочке.', 'Shelf A-2'],
            ['Книга (на русском языке)',          'Книга «Мастер и Маргарита», оставлена на столе.',       'Shelf B-1'],
            ['Зонт',                             'Чёрный складной зонт, найден в ванной.',                 'Entrance rack'],
            ['Детская игрушка',                  'Мягкая игрушка зайца, найдена под подушкой.',            'Shelf B-2'],
            ['Косметичка',                       'Синяя косметичка с содержимым, найдена в ванной.',       'Safe #2'],
            ['Паспорт',                          'Паспорт гражданина России, найден на стойке регистрации.','Safe #1'],
            ['Часы наручные',                    'Часы Casio, найдены на прикроватной тумбочке.',          'Safe #2'],
            ['Шарф кашемировый',                 'Бежевый шарф, найден в коридоре 3-го этажа.',            'Shelf A-3'],
            ['Ключи от автомобиля',              'Связка ключей Toyota, найдена в лифте.',                 'Reception desk'],
            ['Кредитная карта',                  'Карта Visa, найдена в ресторане.',                       'Safe #1'],
            ['Детские сандалии',                 'Пара детских сандалий, найдены у бассейна.',             'Shelf B-3'],
            ['Планшет iPad',                     'iPad в чёрном чехле-клавиатуре, найден в номере.',       'Safe #1'],
            ['Кольцо золотое',                   'Золотое кольцо без надписи, найдено в ванной.',          'Safe #2'],
        ];

        $finders = array_filter([$recept, $recept2, $hkeeper]);
        $finders = empty($finders) ? [$recept] : array_values($finders);

        // ~2 per month Jan–Apr
        $months = [1, 2, 3, 4];
        foreach ($months as $m) {
            $count = mt_rand(1, 3);
            for ($i = 0; $i < $count; $i++) {
                [$title, $desc, $location] = $items[array_rand($items)];
                $foundAt  = Carbon::create(2026, $m, mt_rand(1, 25))->format('Y-m-d');
                $isPast   = Carbon::parse($foundAt)->lt(Carbon::today()->subDays(14));

                $r = mt_rand(1, 100);
                if ($isPast && $r <= 40) {
                    $status     = LostItemStatus::Returned;
                    $returnedAt = Carbon::parse($foundAt)->addDays(mt_rand(1, 10))->format('Y-m-d');
                } elseif ($isPast && $r <= 60) {
                    $status     = LostItemStatus::Stored;
                    $returnedAt = null;
                } else {
                    $status     = LostItemStatus::Found;
                    $returnedAt = null;
                }

                LostItem::create([
                    'title'            => $title,
                    'description'      => $desc,
                    'status'           => $status->value,
                    'found_by'         => $finders[array_rand($finders)]->id,
                    'room_id'          => $rooms->random()->id,
                    'guest_id'         => mt_rand(1, 100) <= 60 ? $guests->random()->id : null,
                    'storage_location' => $location,
                    'found_at'         => $foundAt,
                    'returned_at'      => $returnedAt,
                ]);
            }
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Shift notes
    // ─────────────────────────────────────────────────────────────────────────

    private function seedShiftNotes(Collection $users, Carbon $today): void
    {
        $notes = [
            'morning' => [
                'Заезд гостей прошёл без замечаний. Все номера готовы к 12:00.',
                'Ранний заезд согласован для 3 номеров, гости довольны.',
                'Завтрак прошёл штатно, жалоб нет.',
                'Обнаружена проблема с замком в 204, сообщено техслужбе.',
                'Гость из 302 просил дополнительное полотенце — выдано.',
                'Плановая смена постельного белья выполнена на 2-м и 3-м этажах.',
                'Гость из 403 поблагодарил персонал за отличный сервис.',
                'Новый заезд группы (3 номера), всё оформлено без задержек.',
            ],
            'evening' => [
                'Вечерняя смена прошла спокойно. Задержанных выездов нет.',
                'Жалоба из 205 на шум в коридоре — предупреждены гости.',
                'Поздний выезд для 301 согласован до 14:00.',
                'Гость потерял ключ-карту от 106, перевыпущена новая.',
                'Доставка ужина в номер 404 выполнена вовремя.',
                'Поступил запрос на детскую кроватку — установлена в 302.',
                'Гость из 201 интересовался экскурсиями, предоставлена информация.',
            ],
            'night' => [
                'Ночная смена без происшествий. Плановый обход выполнен.',
                'Парковка заполнена на 80%, место для VIP-гостя зарезервировано.',
                'Фиксация показаний счётчиков выполнена.',
                'Нарушений тишины не зафиксировано.',
                'Гость прибыл поздно (02:15), заселён в 103 по предварительному бронированию.',
                'Замечена протечка воды в коридоре 1-го этажа, устранена дежурным сантехником.',
            ],
        ];

        $noteUsers = $users->filter(fn($u) => in_array($u->email, [
            'admin@hotel.uz', 'receptionist2@hotel.uz', 'security@hotel.uz', 'housekeeper@hotel.uz',
        ]));
        if ($noteUsers->isEmpty()) $noteUsers = $users->take(3);

        // ~1 per shift per day for last 30 days
        for ($d = 30; $d >= 0; $d--) {
            $day = $today->copy()->subDays($d);
            foreach (['morning', 'evening', 'night'] as $shift) {
                if (mt_rand(1, 100) <= 70) {
                    $pool = $notes[$shift];
                    ShiftNote::create([
                        'user_id'    => $noteUsers->random()->id,
                        'shift'      => $shift,
                        'body'       => $pool[array_rand($pool)],
                        'created_at' => $day->copy()->setTime(
                            match($shift) { 'morning' => mt_rand(7, 11), 'evening' => mt_rand(15, 20), default => mt_rand(22, 23) },
                            mt_rand(0, 59)
                        ),
                    ]);
                }
            }
        }
    }
}
