<?php

namespace Database\Seeders;

use App\Enums\BookingSource;
use App\Enums\BookingStatus;
use App\Enums\GuestTag;
use App\Enums\MaintenancePriority;
use App\Enums\MaintenanceStatus;
use App\Enums\PaymentType;
use App\Enums\RoomStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\BookingInquiry;
use App\Models\Expense;
use App\Models\Guest;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\PricingRule;
use App\Models\PromoCode;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\ShiftNote;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Staff ────────────────────────────────────────────────────────────
        $owner = User::create([
            'name'            => 'Азиз Каримов',
            'email'           => 'owner@hotel.uz',
            'password'        => Hash::make('password'),
            'role'            => UserRole::Owner->value,
            'is_active'       => true,
            'phone'           => '+998 90 100 00 01',
            'position'        => 'Владелец / Директор',
            'hire_date'       => '2019-03-15',
            'birth_date'      => '1978-06-12',
            'passport_number' => 'AA 1234561',
        ]);

        $manager = User::create([
            'name'            => 'Нилуфар Рашидова',
            'email'           => 'manager@hotel.uz',
            'password'        => Hash::make('password'),
            'role'            => UserRole::Manager->value,
            'is_active'       => true,
            'phone'           => '+998 91 200 00 02',
            'position'        => 'Операционный менеджер',
            'hire_date'       => '2020-01-10',
            'birth_date'      => '1985-09-23',
            'passport_number' => 'AB 2234562',
        ]);

        $receptionist = User::create([
            'name'            => 'Санжар Алимов',
            'email'           => 'admin@hotel.uz',
            'password'        => Hash::make('password'),
            'role'            => UserRole::Receptionist->value,
            'is_active'       => true,
            'phone'           => '+998 93 300 00 03',
            'position'        => 'Старший администратор',
            'hire_date'       => '2021-05-01',
            'birth_date'      => '1993-02-14',
            'passport_number' => 'AC 3334563',
        ]);

        $receptionist2 = User::create([
            'name'            => 'Гулнора Усманова',
            'email'           => 'receptionist2@hotel.uz',
            'password'        => Hash::make('password'),
            'role'            => UserRole::Receptionist->value,
            'is_active'       => true,
            'phone'           => '+998 94 400 00 04',
            'position'        => 'Администратор (ночная смена)',
            'hire_date'       => '2022-08-15',
            'birth_date'      => '1997-11-30',
            'passport_number' => 'AD 4434564',
        ]);

        $housekeeper = User::create([
            'name'            => 'Мухаммад Турсунов',
            'email'           => 'housekeeper@hotel.uz',
            'password'        => Hash::make('password'),
            'role'            => UserRole::Housekeeper->value,
            'is_active'       => true,
            'phone'           => '+998 95 500 00 05',
            'position'        => 'Старший горничный',
            'hire_date'       => '2021-03-20',
            'birth_date'      => '1990-07-08',
            'passport_number' => 'AE 5534565',
        ]);

        $housekeeper2 = User::create([
            'name'            => 'Зарнигор Хасанова',
            'email'           => 'housekeeper2@hotel.uz',
            'password'        => Hash::make('password'),
            'role'            => UserRole::Housekeeper->value,
            'is_active'       => true,
            'phone'           => '+998 97 600 00 06',
            'position'        => 'Горничная',
            'hire_date'       => '2023-01-10',
            'birth_date'      => '1999-04-17',
            'passport_number' => 'AF 6634566',
        ]);

        $accountant = User::create([
            'name'            => 'Феруза Назарова',
            'email'           => 'accountant@hotel.uz',
            'password'        => Hash::make('password'),
            'role'            => UserRole::Accountant->value,
            'is_active'       => true,
            'phone'           => '+998 98 700 00 07',
            'position'        => 'Главный бухгалтер',
            'hire_date'       => '2020-06-01',
            'birth_date'      => '1983-12-05',
            'passport_number' => 'AG 7734567',
        ]);

        $security = User::create([
            'name'            => 'Отабек Юлдашев',
            'email'           => 'security@hotel.uz',
            'password'        => Hash::make('password'),
            'role'            => UserRole::Security->value,
            'is_active'       => true,
            'phone'           => '+998 99 800 00 08',
            'position'        => 'Начальник охраны',
            'hire_date'       => '2021-09-01',
            'birth_date'      => '1988-03-22',
            'passport_number' => 'AH 8834568',
        ]);

        // ── 2. Room types ───────────────────────────────────────────────────────
        $standard = RoomType::create([
            'name'        => 'Стандарт',
            'base_price'  => 150000,
            'capacity'    => 2,
            'description' => 'Уютный стандартный номер с двуспальной кроватью',
            'amenities'   => ['Wi-Fi', 'ТВ', 'Кондиционер', 'Холодильник'],
        ]);

        $deluxe = RoomType::create([
            'name'        => 'Делюкс',
            'base_price'  => 250000,
            'capacity'    => 2,
            'description' => 'Улучшенный номер с видом на город и расширенными удобствами',
            'amenities'   => ['Wi-Fi', 'ТВ', 'Кондиционер', 'Мини-бар', 'Сейф', 'Халат'],
        ]);

        $suite = RoomType::create([
            'name'        => 'Люкс',
            'base_price'  => 450000,
            'capacity'    => 4,
            'description' => 'Просторный люкс с отдельной гостиной и джакузи',
            'amenities'   => ['Wi-Fi', 'ТВ', 'Кондиционер', 'Мини-бар', 'Сейф', 'Джакузи', 'Халат', 'Фен'],
        ]);

        $presidential = RoomType::create([
            'name'        => 'Президентский люкс',
            'base_price'  => 900000,
            'capacity'    => 6,
            'description' => 'Эксклюзивный апартамент с панорамным видом, кухней и отдельными спальнями',
            'amenities'   => ['Wi-Fi', 'ТВ', 'Кондиционер', 'Кухня', 'Мини-бар', 'Сейф', 'Джакузи', 'Сауна', 'Халат', 'Фен', 'Консьерж'],
        ]);

        // ── 3. Rooms (4 floors, 24 rooms) ──────────────────────────────────────
        $rooms = [];
        $roomDefs = [
            // Floor 1 – Standard
            ['101', $standard->id,    1, RoomStatus::Available],
            ['102', $standard->id,    1, RoomStatus::Occupied],
            ['103', $standard->id,    1, RoomStatus::Available],
            ['104', $standard->id,    1, RoomStatus::Cleaning],
            ['105', $deluxe->id,      1, RoomStatus::Available],
            ['106', $standard->id,    1, RoomStatus::Occupied],
            // Floor 2 – Mix
            ['201', $deluxe->id,      2, RoomStatus::Available],
            ['202', $deluxe->id,      2, RoomStatus::Occupied],
            ['203', $deluxe->id,      2, RoomStatus::Available],
            ['204', $standard->id,    2, RoomStatus::Maintenance],
            ['205', $deluxe->id,      2, RoomStatus::Available],
            ['206', $standard->id,    2, RoomStatus::Available],
            // Floor 3 – Deluxe/Suite
            ['301', $suite->id,       3, RoomStatus::Available],
            ['302', $deluxe->id,      3, RoomStatus::Occupied],
            ['303', $suite->id,       3, RoomStatus::Available],
            ['304', $deluxe->id,      3, RoomStatus::Cleaning],
            ['305', $suite->id,       3, RoomStatus::Available],
            ['306', $deluxe->id,      3, RoomStatus::Available],
            // Floor 4 – Premium
            ['401', $suite->id,       4, RoomStatus::Available],
            ['402', $suite->id,       4, RoomStatus::Occupied],
            ['403', $presidential->id,4, RoomStatus::Available],
            ['404', $presidential->id,4, RoomStatus::Available],
            ['405', $suite->id,       4, RoomStatus::Available],
            ['406', $deluxe->id,      4, RoomStatus::Available],
        ];
        foreach ($roomDefs as [$number, $typeId, $floor, $status]) {
            $rooms[$number] = Room::create([
                'number'       => $number,
                'room_type_id' => $typeId,
                'floor'        => $floor,
                'status'       => $status->value,
            ]);
        }

        // ── 4. Guests ───────────────────────────────────────────────────────────
        $guestsData = [
            ['Иван',       'Петров',       '+998 90 111 11 11', 'ivan.petrov@gmail.com',    'AA 1000001', 'Россия',    GuestTag::Regular],
            ['Малика',     'Юсупова',      '+998 91 222 22 22', 'malika.y@mail.ru',          'AB 2000002', 'Узбекистан',GuestTag::Vip],
            ['Бобур',      'Каримов',      '+998 93 333 33 33', null,                        'AC 3000003', 'Узбекистан',GuestTag::Regular],
            ['Светлана',   'Иванова',      '+998 94 444 44 44', 'svetlana@example.com',      'AD 4000004', 'Россия',    GuestTag::Regular],
            ['Алишер',     'Назаров',      '+998 95 555 55 55', 'alisher.n@mail.com',        'AE 5000005', 'Узбекистан',GuestTag::Vip],
            ['Zhang',      'Wei',          '+86 135 0000 0001', 'zhang.wei@example.cn',      'G00000001',  'Китай',     GuestTag::Regular],
            ['Park',       'Ji-hoon',      '+82 10 0000 0001',  'park.jihoon@naver.com',     'M00000001',  'Корея',     GuestTag::Regular],
            ['Ahmed',      'Al-Rashid',    '+971 50 000 0001',  'ahmed.r@gmail.com',         'P00000001',  'ОАЭ',       GuestTag::Vip],
            ['Sabrina',    'Müller',       '+49 170 000 0001',  'sabrina.m@web.de',          'C00000001',  'Германия',  GuestTag::Regular],
            ['Dilnoza',    'Toshmatova',   '+998 97 666 66 66', 'dilnoza.t@mail.ru',         'AF 6000006', 'Узбекистан',GuestTag::Regular],
            ['Rustam',     'Ergashev',     '+998 98 777 77 77', null,                        'AG 7000007', 'Узбекистан',GuestTag::Regular],
            ['Tatiana',    'Sokolova',     '+7 916 000 00 01',  'tatiana.s@gmail.com',       'AH 8000008', 'Россия',    GuestTag::Regular],
            ['James',      'Mitchell',     '+44 771 000 0001',  'james.m@gmail.com',         'UK0000001',  'Великобритания', GuestTag::Regular],
            ['Sofia',      'Romano',       '+39 320 000 0001',  'sofia.r@gmail.com',         'IT0000001',  'Италия',    GuestTag::Regular],
            ['Odil',       'Xolmatov',     '+998 99 888 88 88', 'odil.x@gmail.com',          'AI 9000009', 'Узбекистан',GuestTag::Regular],
            ['Kamila',     'Mamatova',     '+998 90 999 99 99', 'kamila.m@mail.ru',          'AJ 1100010', 'Узбекистан',GuestTag::Vip],
            ['Шерзод',     'Бекмуродов',   '+998 91 100 00 10', null,                        'AK 1200011', 'Узбекистан',GuestTag::Regular],
            ['Rina',       'Yamamoto',     '+81 90 0000 0001',  'rina.y@yahoo.co.jp',        'J00000001',  'Япония',    GuestTag::Regular],
            ['Carlos',     'Fernández',    '+34 600 000 001',   'carlos.f@gmail.com',        'ES0000001',  'Испания',   GuestTag::Regular],
            ['Наталья',    'Смирнова',     '+7 903 000 00 01',  'natasha.s@mail.ru',         'AL 1300012', 'Россия',    GuestTag::Regular],
            ['Фарход',     'Мусаев',       '+998 93 200 00 20', null,                        'AM 1400013', 'Узбекистан',GuestTag::Regular],
            ['Анна',       'Белова',       '+7 985 000 00 01',  'anna.b@gmail.com',          'AN 1500014', 'Россия',    GuestTag::Regular],
            ['Husan',      'Qodirov',      '+998 94 300 00 30', 'husan.q@gmail.com',         'AO 1600015', 'Узбекистан',GuestTag::Regular],
            ['Mohammed',   'Hassan',       '+966 50 000 0001',  'm.hassan@gmail.com',        'SA0000001',  'Саудовская Аравия', GuestTag::Vip],
            ['Lena',       'Kovaleva',     '+7 926 000 00 01',  'lena.k@yandex.ru',          'AP 1700016', 'Россия',    GuestTag::Regular],
        ];

        $guests = [];
        foreach ($guestsData as [$fn, $ln, $phone, $email, $passport, $nationality, $tag]) {
            $guests[] = Guest::create([
                'first_name'      => $fn,
                'last_name'       => $ln,
                'phone'           => $phone,
                'email'           => $email,
                'passport_number' => $passport,
                'nationality'     => $nationality,
                'tag'             => $tag->value,
            ]);
        }

        // ── 5. Promo codes ──────────────────────────────────────────────────────
        PromoCode::insert([
            ['code' => 'WELCOME10', 'discount_percent' => 10.00, 'valid_from' => '2025-01-01', 'valid_to' => null,         'max_uses' => null, 'uses_count' => 14, 'is_active' => true,  'created_at' => now(), 'updated_at' => now()],
            ['code' => 'SPRING25',  'discount_percent' => 25.00, 'valid_from' => '2026-03-01', 'valid_to' => '2026-05-31', 'max_uses' => 50,   'uses_count' => 7,  'is_active' => true,  'created_at' => now(), 'updated_at' => now()],
            ['code' => 'VIP30',     'discount_percent' => 30.00, 'valid_from' => '2025-06-01', 'valid_to' => null,         'max_uses' => 20,   'uses_count' => 18, 'is_active' => true,  'created_at' => now(), 'updated_at' => now()],
            ['code' => 'WINTER20',  'discount_percent' => 20.00, 'valid_from' => '2025-11-01', 'valid_to' => '2026-02-28', 'max_uses' => null, 'uses_count' => 31, 'is_active' => false, 'created_at' => now(), 'updated_at' => now()],
            ['code' => 'FLASH50',   'discount_percent' => 50.00, 'valid_from' => '2026-04-01', 'valid_to' => '2026-04-15', 'max_uses' => 5,    'uses_count' => 3,  'is_active' => true,  'created_at' => now(), 'updated_at' => now()],
            ['code' => 'CORP15',    'discount_percent' => 15.00, 'valid_from' => '2025-01-01', 'valid_to' => null,         'max_uses' => null, 'uses_count' => 42, 'is_active' => true,  'created_at' => now(), 'updated_at' => now()],
        ]);

        // ── 6. Pricing rules ────────────────────────────────────────────────────
        $pricingRules = [
            ['Новогодние праздники',       null,              '2025-12-25', '2026-01-10', 'percent', 40.00, 10],
            ['Навруз – длинные выходные',  null,              '2026-03-19', '2026-03-24', 'percent', 30.00, 9],
            ['Весенний сезон',             $deluxe->id,       '2026-04-01', '2026-05-31', 'percent', 15.00, 5],
            ['Летний пик (все номера)',     null,              '2026-06-01', '2026-08-31', 'percent', 25.00, 7],
            ['Будни скидка – Стандарт',    $standard->id,     '2026-01-01', '2026-12-31', 'percent', -10.00, 1],
            ['Президентский – мин. 3 ночи',null,              '2026-01-01', '2026-12-31', 'fixed',   50000.00, 3],
        ];
        foreach ($pricingRules as [$name, $typeId, $from, $to, $modType, $modVal, $priority]) {
            PricingRule::create([
                'name'            => $name,
                'room_type_id'    => $typeId,
                'date_from'       => $from,
                'date_to'         => $to,
                'modifier_type'   => $modType,
                'modifier_value'  => $modVal,
                'priority'        => $priority,
                'is_active'       => true,
                'created_by'      => $manager->id,
            ]);
        }

        // ── 7. Bookings (historical + current + future) ─────────────────────────
        // Helper to create booking + payment
        $makeBooking = function (
            Room $room, Guest $guest, string $checkIn, string $checkOut,
            int $adults, int $children, BookingStatus $status, BookingSource $source,
            float $price, ?float $paid, User $creator, ?string $promo = null, ?float $discount = null
        ) {
            $booking = Booking::create([
                'room_id'            => $room->id,
                'guest_id'           => $guest->id,
                'check_in_date'      => $checkIn,
                'check_out_date'     => $checkOut,
                'adults'             => $adults,
                'children'           => $children,
                'status'             => $status->value,
                'source'             => $source->value,
                'total_price'        => $price,
                'applied_promo_code' => $promo,
                'discount_amount'    => $discount,
                'created_by'         => $creator->id,
            ]);
            if ($paid !== null && $paid > 0) {
                Payment::create([
                    'booking_id' => $booking->id,
                    'amount'     => $paid,
                    'method'     => collect(['cash', 'card', 'transfer'])->random(),
                    'type'       => PaymentType::Prepayment->value,
                    'paid_at'    => Carbon::parse($checkIn)->subDays(rand(0, 3)),
                ]);
            }
            return $booking;
        };

        $today = Carbon::today();

        // ── Past bookings (checked_out) – last 3 months ─────────────────────────
        // Jan
        $makeBooking($rooms['101'], $guests[0],  '2026-01-05', '2026-01-08', 2, 0, BookingStatus::CheckedOut, BookingSource::Staff,   450000,  450000, $receptionist);
        $makeBooking($rooms['201'], $guests[3],  '2026-01-07', '2026-01-11', 1, 0, BookingStatus::CheckedOut, BookingSource::Staff,  1000000, 1000000, $receptionist);
        $makeBooking($rooms['301'], $guests[5],  '2026-01-10', '2026-01-14', 2, 1, BookingStatus::CheckedOut, BookingSource::Client, 1800000, 1800000, $manager);
        $makeBooking($rooms['202'], $guests[7],  '2026-01-15', '2026-01-18', 2, 0, BookingStatus::CheckedOut, BookingSource::Staff,   750000,  750000, $receptionist, 'WELCOME10', 75000);
        $makeBooking($rooms['102'], $guests[9],  '2026-01-18', '2026-01-20', 1, 0, BookingStatus::CheckedOut, BookingSource::Staff,   300000,  300000, $receptionist2);
        $makeBooking($rooms['403'], $guests[23], '2026-01-20', '2026-01-24', 4, 0, BookingStatus::CheckedOut, BookingSource::Client, 3600000, 3600000, $manager);
        $makeBooking($rooms['302'], $guests[11], '2026-01-22', '2026-01-25', 2, 0, BookingStatus::CheckedOut, BookingSource::Staff,   750000,  750000, $receptionist);
        $makeBooking($rooms['205'], $guests[13], '2026-01-25', '2026-01-29', 2, 0, BookingStatus::CheckedOut, BookingSource::Staff,  1000000, 1000000, $receptionist);
        $makeBooking($rooms['103'], $guests[15], '2026-01-28', '2026-01-31', 1, 0, BookingStatus::CheckedOut, BookingSource::Staff,   450000,  450000, $receptionist2);

        // Feb
        $makeBooking($rooms['101'], $guests[2],  '2026-02-01', '2026-02-05', 2, 0, BookingStatus::CheckedOut, BookingSource::Staff,   600000,  600000, $receptionist);
        $makeBooking($rooms['201'], $guests[6],  '2026-02-03', '2026-02-07', 1, 0, BookingStatus::CheckedOut, BookingSource::Staff,  1000000, 1000000, $receptionist);
        $makeBooking($rooms['401'], $guests[8],  '2026-02-05', '2026-02-10', 2, 0, BookingStatus::CheckedOut, BookingSource::Client, 2250000, 2250000, $manager);
        $makeBooking($rooms['103'], $guests[10], '2026-02-08', '2026-02-11', 2, 1, BookingStatus::CheckedOut, BookingSource::Staff,   450000,  300000, $receptionist2);
        $makeBooking($rooms['202'], $guests[12], '2026-02-10', '2026-02-14', 2, 0, BookingStatus::CheckedOut, BookingSource::Staff,  1000000, 1000000, $receptionist);
        $makeBooking($rooms['301'], $guests[14], '2026-02-12', '2026-02-16', 2, 0, BookingStatus::CheckedOut, BookingSource::Staff,  1800000, 1800000, $manager, 'CORP15', 270000);
        $makeBooking($rooms['404'], $guests[23], '2026-02-15', '2026-02-19', 4, 2, BookingStatus::CheckedOut, BookingSource::Client, 3600000, 3600000, $manager);
        $makeBooking($rooms['206'], $guests[16], '2026-02-18', '2026-02-22', 2, 0, BookingStatus::CheckedOut, BookingSource::Staff,   600000,  600000, $receptionist2);
        $makeBooking($rooms['303'], $guests[18], '2026-02-20', '2026-02-23', 2, 0, BookingStatus::CheckedOut, BookingSource::Staff,  1350000, 1350000, $receptionist);
        $makeBooking($rooms['402'], $guests[1],  '2026-02-22', '2026-02-26', 2, 0, BookingStatus::CheckedOut, BookingSource::Client, 1800000, 1800000, $manager, 'VIP30', 540000);
        $makeBooking($rooms['105'], $guests[20], '2026-02-24', '2026-02-28', 1, 0, BookingStatus::CheckedOut, BookingSource::Staff,   500000,  500000, $receptionist);

        // Mar
        $makeBooking($rooms['101'], $guests[4],  '2026-03-01', '2026-03-04', 2, 0, BookingStatus::CheckedOut, BookingSource::Staff,   450000,  450000, $receptionist);
        $makeBooking($rooms['201'], $guests[17], '2026-03-03', '2026-03-07', 1, 0, BookingStatus::CheckedOut, BookingSource::Staff,  1000000, 1000000, $receptionist2);
        $makeBooking($rooms['303'], $guests[19], '2026-03-05', '2026-03-09', 2, 1, BookingStatus::CheckedOut, BookingSource::Staff,  1800000, 1800000, $manager);
        $makeBooking($rooms['203'], $guests[21], '2026-03-06', '2026-03-10', 2, 0, BookingStatus::CheckedOut, BookingSource::Staff,  1000000, 1000000, $receptionist);
        $makeBooking($rooms['403'], $guests[7],  '2026-03-10', '2026-03-14', 3, 0, BookingStatus::CheckedOut, BookingSource::Client, 3600000, 3600000, $manager, 'CORP15', 540000);
        $makeBooking($rooms['102'], $guests[22], '2026-03-12', '2026-03-15', 1, 0, BookingStatus::CheckedOut, BookingSource::Staff,   450000,  450000, $receptionist2);
        $makeBooking($rooms['302'], $guests[0],  '2026-03-14', '2026-03-18', 2, 0, BookingStatus::CheckedOut, BookingSource::Staff,  1000000, 1000000, $receptionist);
        $makeBooking($rooms['405'], $guests[15], '2026-03-16', '2026-03-20', 2, 0, BookingStatus::CheckedOut, BookingSource::Staff,  1800000, 1800000, $manager);
        $makeBooking($rooms['205'], $guests[3],  '2026-03-18', '2026-03-21', 2, 0, BookingStatus::CheckedOut, BookingSource::Staff,   750000,  750000, $receptionist);
        $makeBooking($rooms['106'], $guests[24], '2026-03-20', '2026-03-24', 1, 0, BookingStatus::CheckedOut, BookingSource::Staff,   600000,  600000, $receptionist2);
        $makeBooking($rooms['401'], $guests[9],  '2026-03-22', '2026-03-27', 2, 0, BookingStatus::CheckedOut, BookingSource::Client, 2250000, 2250000, $manager);
        $makeBooking($rooms['101'], $guests[11], '2026-03-25', '2026-03-29', 2, 0, BookingStatus::CheckedOut, BookingSource::Staff,   600000,  600000, $receptionist);
        $makeBooking($rooms['305'], $guests[1],  '2026-03-27', '2026-03-31', 2, 0, BookingStatus::CheckedOut, BookingSource::Client, 1350000, 1350000, $manager, 'VIP30', 405000);

        // ── Current bookings (checked_in) ────────────────────────────────────────
        $makeBooking($rooms['102'], $guests[0],  $today->copy()->subDays(1)->toDateString(), $today->copy()->addDays(2)->toDateString(), 2, 0, BookingStatus::CheckedIn, BookingSource::Staff,   450000,  150000, $receptionist);
        $makeBooking($rooms['202'], $guests[1],  $today->copy()->subDays(0)->toDateString(), $today->copy()->addDays(3)->toDateString(), 1, 0, BookingStatus::CheckedIn, BookingSource::Client,  750000,  375000, $manager);
        $makeBooking($rooms['402'], $guests[7],  $today->copy()->subDays(2)->toDateString(), $today->copy()->addDays(2)->toDateString(), 2, 0, BookingStatus::CheckedIn, BookingSource::Client, 1800000, 1800000, $manager, 'VIP30', 540000);
        $makeBooking($rooms['106'], $guests[5],  $today->copy()->subDays(1)->toDateString(), $today->copy()->addDays(1)->toDateString(), 2, 0, BookingStatus::CheckedIn, BookingSource::Staff,   300000,  300000, $receptionist2);
        $makeBooking($rooms['302'], $guests[23], $today->copy()->toDateString(),              $today->copy()->addDays(4)->toDateString(), 2, 2, BookingStatus::CheckedIn, BookingSource::Client, 1000000, 1000000, $manager);

        // ── Upcoming bookings (confirmed / pending) ──────────────────────────────
        $makeBooking($rooms['201'], $guests[2],  $today->copy()->addDays(2)->toDateString(), $today->copy()->addDays(5)->toDateString(),  2, 1, BookingStatus::Confirmed, BookingSource::Staff,   750000, 375000, $receptionist);
        $makeBooking($rooms['301'], $guests[4],  $today->copy()->addDays(3)->toDateString(), $today->copy()->addDays(7)->toDateString(),  2, 0, BookingStatus::Confirmed, BookingSource::Client, 1800000, 900000, $manager);
        $makeBooking($rooms['404'], $guests[7],  $today->copy()->addDays(5)->toDateString(), $today->copy()->addDays(10)->toDateString(), 4, 0, BookingStatus::Confirmed, BookingSource::Client, 4500000, 4500000, $manager);
        $makeBooking($rooms['103'], $guests[16], $today->copy()->addDays(4)->toDateString(), $today->copy()->addDays(6)->toDateString(),  2, 0, BookingStatus::Pending,   BookingSource::Staff,   300000, 0, $receptionist2);
        $makeBooking($rooms['203'], $guests[19], $today->copy()->addDays(6)->toDateString(), $today->copy()->addDays(9)->toDateString(),  1, 0, BookingStatus::Pending,   BookingSource::Staff,   750000, 0, $receptionist);
        $makeBooking($rooms['405'], $guests[6],  $today->copy()->addDays(8)->toDateString(), $today->copy()->addDays(12)->toDateString(), 2, 0, BookingStatus::Confirmed, BookingSource::Staff,  1800000, 900000, $manager, 'SPRING25', 450000);
        $makeBooking($rooms['206'], $guests[22], $today->copy()->addDays(10)->toDateString(),$today->copy()->addDays(13)->toDateString(), 2, 0, BookingStatus::Pending,   BookingSource::Staff,   450000, 0, $receptionist);

        // ── Inquiry booking ──────────────────────────────────────────────────────
        $inquiryBooking = $makeBooking($rooms['403'], $guests[13], $today->copy()->addDays(15)->toDateString(), $today->copy()->addDays(19)->toDateString(), 2, 0, BookingStatus::Inquiry, BookingSource::Client, 3600000, 0, $owner);
        BookingInquiry::create([
            'booking_id' => $inquiryBooking->id,
            'first_name' => $guests[13]->first_name,
            'last_name'  => $guests[13]->last_name,
            'phone'      => $guests[13]->phone,
            'email'      => $guests[13]->email,
        ]);

        // ── 8. Maintenance requests ─────────────────────────────────────────────
        MaintenanceRequest::create([
            'room_id'     => $rooms['204']->id,
            'title'       => 'Не работает кондиционер',
            'description' => 'Кондиционер включается, но не охлаждает. Требуется заправка фреоном.',
            'priority'    => MaintenancePriority::High->value,
            'status'      => MaintenanceStatus::Open->value,
            'created_by'  => $receptionist->id,
        ]);
        MaintenanceRequest::create([
            'room_id'     => $rooms['304']->id,
            'title'       => 'Засорился слив в душе',
            'description' => 'Вода медленно уходит в канализацию, требуется прочистка.',
            'priority'    => MaintenancePriority::Medium->value,
            'status'      => MaintenanceStatus::InProgress->value,
            'created_by'  => $housekeeper->id,
        ]);
        MaintenanceRequest::create([
            'room_id'     => $rooms['101']->id,
            'title'       => 'Сломан замок шкафа',
            'description' => 'Дверца шкафа не закрывается, нужна замена петли.',
            'priority'    => MaintenancePriority::Low->value,
            'status'      => MaintenanceStatus::Open->value,
            'created_by'  => $housekeeper2->id,
        ]);

        // ── 9. Expenses ─────────────────────────────────────────────────────────
        $expensesData = [
            // Jan
            ['salary',    'Зарплата персонала за январь',         5500000, '2026-01-31'],
            ['utilities', 'Электроэнергия январь',                 900000, '2026-01-25'],
            ['supplies',  'Постельное бельё и полотенца',          350000, '2026-01-15'],
            // Feb
            ['salary',    'Зарплата персонала за февраль',        5500000, '2026-02-28'],
            ['utilities', 'Коммунальные услуги февраль',           850000, '2026-02-22'],
            ['supplies',  'Чистящие средства и расходники',        180000, '2026-02-10'],
            ['other',     'Ремонт кондиционера (204)',              320000, '2026-02-18'],
            // Mar
            ['salary',    'Зарплата персонала за март',           5800000, '2026-03-31'],
            ['utilities', 'Электроэнергия и газ март',             920000, '2026-03-25'],
            ['supplies',  'Мини-бар расходники (Апрель)',          220000, '2026-03-20'],
            ['marketing', 'Реклама в Instagram / Google Ads',      500000, '2026-03-12'],
            // Apr (this month)
            ['salary',    'Аванс за апрель',                      2800000, $today->copy()->subDays(5)->toDateString()],
            ['utilities', 'Счёт за воду апрель',                   240000, $today->copy()->subDays(3)->toDateString()],
            ['supplies',  'Хозяйственные товары',                  160000, $today->copy()->subDays(1)->toDateString()],
        ];
        foreach ($expensesData as [$category, $desc, $amount, $date]) {
            Expense::create([
                'category'     => $category,
                'description'  => $desc,
                'amount'       => $amount,
                'expense_date' => $date,
                'created_by'   => $manager->id,
            ]);
        }

        // ── 10. Shift notes ──────────────────────────────────────────────────────
        $shiftNotes = [
            [$receptionist->id,  'morning', 'Заезд Ивана Петрова в 101, всё прошло гладко. Завтрак добавлен по запросу.', $today->copy()->subDays(1)],
            [$receptionist2->id, 'evening', 'Гость из 202 жаловался на шум из коридора около 22:00. Предупреждены соседние номера.', $today->copy()->subDays(1)],
            [$security->id,      'night',   'Плановый обход выполнен, нарушений не выявлено. Парковка свободна.', $today->copy()->subDays(1)],
            [$receptionist->id,  'morning', 'Поступил запрос на ранний заезд для 301. Согласовано с менеджером.', $today],
            [$housekeeper->id,   'morning', 'Номера 104 и 304 готовы к заселению. Уборка завершена к 11:30.', $today],
        ];
        foreach ($shiftNotes as [$userId, $shift, $body, $date]) {
            ShiftNote::create([
                'user_id'    => $userId,
                'shift'      => $shift,
                'body'       => $body,
                'created_at' => $date->setTime(rand(7, 22), rand(0, 59)),
            ]);
        }

        // Full-year 2026 data
        $this->call(AliveSeeder::class);
    }
}
