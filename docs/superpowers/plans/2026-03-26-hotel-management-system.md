# Hotel Management System Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan phase-by-phase. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a staff-only Hotel Management System for a single hotel — bookings, guests, rooms, housekeeping, payments, and finances.

**Architecture:** Laravel 11 MVC, Blade + Alpine.js, PostgreSQL, session-based auth with role middleware (owner / manager / receptionist). All server-rendered, no API layer.

**Tech Stack:** PHP 8.2+, Laravel 11, PostgreSQL 15+, Blade, Alpine.js 3 (CDN), Tailwind CSS 3 (CDN)

**Spec:** `docs/superpowers/specs/2026-03-26-hotel-management-system-design.md`

---

## Phase 1 — Foundation

**Deliverable:** Running Laravel app with full schema, auth, and empty navigation shell.

### Task 1.1 — Laravel Installation & Database Setup

**Files:**
- Create: `.env`
- Modify: `config/database.php` (default connection → pgsql)
- Modify: `phpunit.xml` (test DB env vars)

- [ ] Install Laravel into project root
  ```bash
  composer create-project laravel/laravel "g:/Hotel InfoStructure" --prefer-dist
  ```
- [ ] Configure `.env` for PostgreSQL
  ```
  DB_CONNECTION=pgsql
  DB_HOST=127.0.0.1
  DB_PORT=5432
  DB_DATABASE=hotel_hms
  DB_USERNAME=postgres
  DB_PASSWORD=secret
  APP_LOCALE=ru
  APP_TIMEZONE=Asia/Tashkent
  ```
- [ ] Add test database to `phpunit.xml`
  ```xml
  <env name="DB_DATABASE" value="hotel_hms_test"/>
  ```
- [ ] Create both databases in PostgreSQL
  ```sql
  CREATE DATABASE hotel_hms;
  CREATE DATABASE hotel_hms_test;
  ```
- [ ] Verify connection
  ```bash
  php artisan migrate
  ```
  Expected: `migrations` table created, no errors.
- [ ] Commit
  ```bash
  git add . && git commit -m "feat: initialize Laravel project with PostgreSQL config"
  ```

---

### Task 1.2 — Database Migrations

**Files:**
- Create: `database/migrations/` — 6 new migration files
- Modify: existing `create_users_table` migration (add `role`, `is_active`)

- [ ] Modify `create_users_table` migration — add `role` (string, default `receptionist`) and `is_active` (boolean, default true)
- [ ] Create migration: `create_room_types_table`
  - Columns: `name` varchar(100), `base_price` decimal(12,2), `capacity` integer, `description` text nullable, `amenities` jsonb nullable
- [ ] Create migration: `create_rooms_table`
  - Columns: `room_type_id` FK, `number` varchar(10), `floor` integer nullable, `status` string default `available`, `notes` text nullable
  - Index: `(room_type_id, status)`
- [ ] Create migration: `create_guests_table`
  - Columns: `first_name`, `last_name` varchar(100), `phone` varchar(20) nullable, `email` varchar(150) nullable, `passport_number` varchar(50) nullable, `nationality` varchar(100) nullable
  - Indexes: `(phone)`, `(passport_number)`
- [ ] Create migration: `create_bookings_table`
  - Columns: `room_id` FK, `guest_id` FK, `check_in_date` date, `check_out_date` date, `adults` integer default 1, `children` integer default 0, `status` string default `pending`, `total_price` decimal(12,2), `notes` text nullable, `created_by` FK → users
  - Indexes: `(room_id, check_in_date, check_out_date)`, `(guest_id)`, `(status)`, `(check_in_date)`
- [ ] Create migration: `create_payments_table`
  - Columns: `booking_id` FK, `amount` decimal(12,2), `method` varchar(50), `paid_at` timestamp, `notes` text nullable
- [ ] Create migration: `create_expenses_table`
  - Columns: `category` varchar(100), `description` text, `amount` decimal(12,2), `expense_date` date, `created_by` FK → users
- [ ] Run all migrations
  ```bash
  php artisan migrate:fresh
  ```
- [ ] Commit
  ```bash
  git commit -m "feat: add all HMS database migrations"
  ```

---

### Task 1.3 — PHP Enums & Models

**Files:**
- Create: `app/Enums/BookingStatus.php`, `RoomStatus.php`, `UserRole.php`
- Create/Modify: `app/Models/` — all 7 models

- [ ] Create `BookingStatus` enum with values: `pending`, `confirmed`, `checked_in`, `checked_out`, `cancelled`. Add `allowedTransitions()`, `canTransitionTo()`, `label()`, `color()` methods.
- [ ] Create `RoomStatus` enum with values: `available`, `occupied`, `cleaning`, `maintenance`. Add `label()` method.
- [ ] Create `UserRole` enum with values: `owner`, `manager`, `receptionist`.
- [ ] Create/update all models with relationships and casts:
  - `RoomType` → `hasMany(Room::class)`
  - `Room` → `belongsTo(RoomType::class)`, `hasMany(Booking::class)`, cast `status` → `RoomStatus`, add `isAvailable(string $checkIn, string $checkOut): bool` method using the availability query from spec Section 5
  - `Guest` → `hasMany(Booking::class)`, add `fullName()` accessor
  - `Booking` → `belongsTo(Room::class)`, `belongsTo(Guest::class)`, `belongsTo(User::class, 'created_by')`, `hasMany(Payment::class)`, cast `status` → `BookingStatus`, add `paymentStatus(): string` computed property (unpaid/partial/paid based on sum of payments vs total_price)
  - `Payment` → `belongsTo(Booking::class)`
  - `Expense` → `belongsTo(User::class, 'created_by')`
  - `User` → cast `role` → `UserRole`, add `is_active` cast to boolean, add `hasRole(UserRole $role): bool` helper
- [ ] Create model factories for all 7 models (needed for testing)
- [ ] Write and run unit tests:
  ```bash
  php artisan test --filter ModelTest
  ```
  Key assertions: `Room::isAvailable()` returns false on overlap, `Booking::paymentStatus()` returns correct value for 0/partial/full payments, `BookingStatus::canTransitionTo()` respects state machine.
- [ ] Commit
  ```bash
  git commit -m "feat: add enums and all Eloquent models with relationships"
  ```

---

### Task 1.4 — Authentication & Role Middleware

**Files:**
- Create: `app/Http/Middleware/RoleMiddleware.php`
- Create: `resources/views/auth/login.blade.php`
- Create: `app/Http/Controllers/Auth/LoginController.php`
- Modify: `bootstrap/app.php` (register middleware alias)
- Modify: `routes/web.php` (all routes)

- [ ] Create `LoginController` with `showLogin()`, `login()`, `logout()` methods. Validate email + password, check `is_active = true` before allowing login.
- [ ] Create `RoleMiddleware` — receives roles as parameters, checks `auth()->user()->role`, aborts 403 if not allowed.
- [ ] Register middleware alias `role` in `bootstrap/app.php`.
- [ ] Create login view: simple centered form (email, password, submit).
- [ ] Define all routes in `routes/web.php` grouped by auth + role middleware. Use groups:
  - `auth` middleware: all routes
  - `role:owner,manager`: rooms, room-types, finances, expenses
  - `role:owner`: users CRUD
- [ ] Include the complete route list in `web.php`. Critical routes to register (order matters for Laravel router):
  ```
  GET  /guests/search                              ← MUST be before GET /guests/{id}
  GET  /rooms/available                            ← JSON endpoint for booking wizard
  PATCH /housekeeping/{room}                       ← room status update
  POST  /bookings/{id}/status                      ← status transitions
  ```
  Plus all standard resource routes from spec Section 6.
- [ ] Write feature tests: unauthenticated redirect, wrong role → 403, correct role → 200, **inactive user (`is_active=false`) with correct credentials is blocked from login**.
- [ ] Commit
  ```bash
  git commit -m "feat: add login flow and role-based middleware"
  ```

---

### Task 1.5 — Base Layout & Blade Components

**Files:**
- Create: `resources/views/layouts/app.blade.php`
- Create: `resources/views/components/` — 5 component files

- [ ] Create `layouts/app.blade.php` with: sidebar navigation (links per role using `@can`/role checks), top bar with user name + logout, `@yield('content')` main area. Include Tailwind CSS and Alpine.js via CDN.
- [ ] Create `components/stat-card.blade.php` — props: `title`, `value`, `trend` (optional ↑↓ colored text).
- [ ] Create `components/status-badge.blade.php` — prop: `status` (BookingStatus or RoomStatus), renders colored pill using the enum's `color()` and `label()`.
- [ ] Create `components/room-card.blade.php` — props: `room` (Room model), renders number, type, status icon. Wraps in Alpine click handler for modal trigger.
- [ ] Create `components/booking-row.blade.php` — prop: `booking`, renders row with guest name, phone, room, dates, status badge, total price, inline action buttons (only valid transitions for current status).
- [ ] Create `components/summary-box.blade.php` — props passed via Alpine `x-bind`, renders room name, dates, nights, total price. Used in booking wizard.
- [ ] Commit
  ```bash
  git commit -m "feat: add base layout and reusable Blade components"
  ```

---

## Phase 2 — Rooms & Guests

**Deliverable:** Staff can fully manage room types, physical rooms, and guest records.

### Task 2.1 — Room Types CRUD

**Files:**
- Create: `app/Http/Controllers/RoomTypeController.php`
- Create: `resources/views/room-types/` — index, create, edit views

- [ ] Write feature tests: index lists types, create/store adds record, edit/update modifies, 403 for receptionist.
- [ ] Implement `RoomTypeController` (index, create, store, edit, update). No destroy — room types are never deleted (rooms reference them).
- [ ] Create views: index table, create/edit form (name, base_price, capacity, description, amenities as text).
- [ ] Run tests: `php artisan test --filter RoomTypeTest`
- [ ] Commit: `git commit -m "feat: room types CRUD"`

---

### Task 2.2 — Rooms CRUD

**Files:**
- Create: `app/Http/Controllers/RoomController.php`
- Create: `resources/views/rooms/` — index, create, edit views

- [ ] Write feature tests: index, create/store, edit/update, status update via PATCH.
- [ ] Implement `RoomController` (index, create, store, edit, update). Index groups rooms by floor.
- [ ] Create views: index as sortable table, create/edit form (number, floor, room_type, status, notes).
- [ ] Run tests and commit: `git commit -m "feat: rooms CRUD"`

---

### Task 2.3 — Housekeeping View

**Files:**
- Create: `app/Http/Controllers/HousekeepingController.php`
- Create: `resources/views/housekeeping/index.blade.php`

- [ ] Write feature test: housekeeping page shows room grid, PATCH status updates room.
- [ ] Implement `HousekeepingController@index`: eager-load rooms with `roomType` and the **active booking** (`with(['bookings' => fn($q) => $q->where('status', 'checked_in')->with('guest')])`). Group by floor.
- [ ] Implement `@update` (validates new status from `RoomStatus` enum, saves).
- [ ] Create view: filter tabs (All/Available/Occupied/Cleaning), room cards grid grouped by floor using `<x-room-card>`.
  - Occupied rooms: show hover preview (Alpine.js `@mouseenter`/`x-show`) with guest full name and check-out date, read from the eager-loaded active booking.
  - Each card clickable → Alpine.js modal with room info, "Mark as Cleaning" button, "View Booking" link (if occupied).
- [ ] Run tests and commit: `git commit -m "feat: housekeeping room grid with status updates and hover preview"`

---

### Task 2.4 — Guests CRUD + Search

**Files:**
- Create: `app/Http/Controllers/GuestController.php`
- Create: `resources/views/guests/` — index, create, edit, show views

- [ ] Write feature tests: CRUD, plus `GET /guests/search?q=Ivan` returns JSON array for autocomplete.
- [ ] Implement `GuestController`: standard CRUD + `search()` method returning JSON (id, full_name, phone) filtered by name or phone. Used by booking wizard.
- [ ] Create views: index with search bar, create/edit form (first_name, last_name, phone, email, passport_number, nationality), show with booking history.
- [ ] Run tests and commit: `git commit -m "feat: guests CRUD with search endpoint"`

---

## Phase 3 — Bookings & Payments

**Deliverable:** Full booking workflow from creation through check-out, with payment recording.

### Task 3.1 — Bookings List & Detail

**Files:**
- Create: `app/Http/Controllers/BookingController.php` (index, show)
- Create: `resources/views/bookings/index.blade.php`, `show.blade.php`

- [ ] Write feature tests: index lists bookings, filters by status/date work, show displays full booking detail.
- [ ] Implement `BookingController@index`: query bookings with `guest`, `room.roomType` eager loaded, apply status/date/search filters, paginate.
- [ ] Implement `BookingController@show`: load booking with all relations + payments. Compute `paymentStatus`.
- [ ] Create `index.blade.php`: sticky filter bar (search input, status select, date range), list of `<x-booking-row>` components.
- [ ] Create `show.blade.php`: booking details, guest info, payment history, add payment form, status action buttons.
- [ ] Run tests and commit: `git commit -m "feat: bookings list and detail pages"`

---

### Task 3.2 — Booking Creation (Alpine.js Wizard)

**Files:**
- Create: `resources/views/bookings/create.blade.php`
- Modify: `BookingController` (add `create`, `store`, `availableRooms` methods)

- [ ] Write feature tests: `POST /bookings` creates booking, overlapping dates return validation error, total_price is calculated correctly (nights × base_price).
- [ ] Add `GET /rooms/available` route + `RoomController@available(request)` — returns rooms filtered by type, check-in, check-out as JSON. Used by wizard.
- [ ] Implement `BookingController@store`: validate, check availability, calculate total_price, create booking with `status=pending`, redirect to show.
- [ ] Create `create.blade.php` as Alpine.js wizard:
  - `x-data` holds: `step` (1/2/3), `guestId`, `roomTypeId`, `roomId`, `checkIn`, `checkOut`, `adults`, `children`, `totalPrice`
  - Step 1: guest search input with `fetch('/guests/search?q=...')` dropdown, "create new" link
  - Step 2: room type select + room select (fetches `/rooms/available` on change), conflict warning if empty result
  - Step 3: date pickers (default today/tomorrow), guest count (default from room capacity)
  - `<x-summary-box>` always visible, bound to Alpine data, calculates total live
  - Form submits all data via standard POST on Confirm click
- [ ] Run tests and commit: `git commit -m "feat: booking creation wizard with availability check"`

---

### Task 3.3 — Booking Status Transitions

**Files:**
- Modify: `BookingController` (add `updateStatus` method)
- Modify: `Room` model (auto-update room status on transition)

- [ ] Write feature tests for **all** valid transitions: pending→confirmed, pending→checked_in, confirmed→checked_in, checked_in→checked_out, confirmed→cancelled, **checked_in→cancelled** (emergency). Assert room status auto-updates on check-in (`occupied`) and check-out (`cleaning`).
- [ ] Write feature test for invalid transition (checked_out→confirmed) — expects 422.
- [ ] Implement `BookingController@updateStatus`: validate `transition` param against `BookingStatus::canTransitionTo()`, apply, then sync `rooms.status`:
  - `check_in` → room becomes `occupied`
  - `check_out` → room becomes `cleaning`
  - `cancel` from `checked_in` → room becomes `available` (guest is no longer occupying it)
- [ ] Run tests and commit: `git commit -m "feat: booking status transitions with room status sync"`

---

### Task 3.4 — Bookings Edit & Cancel

**Files:**
- Modify: `BookingController` (add `edit`, `update`, `destroy`)
- Create: `resources/views/bookings/edit.blade.php`

- [ ] Write feature tests: edit/update changes booking fields (recalculates total_price), `DELETE /bookings/{id}` triggers the `cancel` transition (not a DB delete — bookings are never deleted), receptionists cannot edit cancelled/checked-out bookings.
- [ ] Implement `edit` and `update` (allow changing guest, room, dates, notes on pending/confirmed bookings only). Recalculate `total_price` on update.
- [ ] Implement `destroy`: call `BookingStatus::canTransitionTo(cancelled)` — if allowed, set status to `cancelled`. Return 422 if already terminal. No DB delete.
- [ ] Create edit view (same form as create but pre-filled, no wizard — direct form).
- [ ] Run tests and commit: `git commit -m "feat: booking edit and cancel"`

---

### Task 3.5 — Payments

**Files:**
- Create: `app/Http/Controllers/PaymentController.php`
- Modify: `resources/views/bookings/show.blade.php` (add payment section)

- [ ] Write feature tests: `POST /payments` adds payment to booking, `DELETE /payments/{id}` removes it, payment_status on booking updates correctly.
- [ ] Implement `PaymentController@store`: validate `booking_id`, `amount` (> 0, ≤ remaining balance), `method`, set `paid_at = now()`, save.
- [ ] Implement `PaymentController@destroy`: delete payment, no further action needed.
- [ ] Add payment section to `bookings/show.blade.php`: payment history table (amount, method, date, delete button), add payment form (amount, method select: cash/card/bank_transfer, notes), payment status badge (unpaid/partial/paid) computed dynamically.
- [ ] Run tests and commit: `git commit -m "feat: manual payment recording on bookings"`

---

## Phase 4 — Finances & Dashboard

**Deliverable:** Expense tracking, financial summary with comparisons, and the main dashboard.

### Task 4.1 — Expenses CRUD

**Files:**
- Create: `app/Http/Controllers/ExpenseController.php`
- Create: `resources/views/expenses/` — index, create, edit views

- [ ] Write feature tests: CRUD, 403 for receptionist.
- [ ] Implement `ExpenseController` (index, create, store, edit, update, destroy). Index filtered by month.
- [ ] Create views: index as list with category + amount + date, create/edit form with:
  - `category` as `<select>` with exactly these options: `Коммунальные услуги`, `Расходные материалы`, `Зарплата`, `Техническое обслуживание`, `Прочее`
  - `description` text input, `amount` decimal, `expense_date` date picker
- [ ] Run tests and commit: `git commit -m "feat: expenses CRUD"`

---

### Task 4.2 — Finances Page

**Files:**
- Create: `app/Http/Controllers/FinancesController.php`
- Create: `resources/views/finances/index.blade.php`

- [ ] Write feature tests: revenue = sum of payments.amount in period, expenses = sum of expenses.amount, profit = revenue - expenses, comparisons vs previous period are correct, receptionist gets 403.
- [ ] Implement `FinancesController@index`:
  - `todayRevenue` = `Payment::whereDate('paid_at', today())->sum('amount')`
  - `todayExpenses` = `Expense::whereDate('expense_date', today())->sum('amount')`
  - `yesterdayRevenue`, `yesterdayExpenses` for comparison
  - `monthRevenue`, `monthExpenses`, `prevMonthRevenue`, `prevMonthExpenses` for monthly
  - `expenses` = this month's expense records (paginated)
  - Support `?month=YYYY-MM` query param for navigation
- [ ] Create `finances/index.blade.php`: today stat cards with trend arrows, monthly stat cards with trend arrows, expense list with + Add Expense button, month navigation links.
- [ ] Run tests and commit: `git commit -m "feat: finances page with revenue, expenses, profit and comparisons"`

---

### Task 4.3 — Dashboard

**Files:**
- Create: `app/Http/Controllers/DashboardController.php`
- Create: `resources/views/dashboard/index.blade.php`

- [ ] Write feature tests: dashboard shows today's check-in count, check-out count, occupancy, revenue. Alerts list correct arrivals with urgency labels.
- [ ] Implement `DashboardController@index`:
  - `todayArrivals` = bookings with `check_in_date = today` and `status IN (pending, confirmed)`, ordered by `check_in_date`. Include `room` and `guest`.
  - `todayCheckOuts` = bookings with `check_out_date = today` and `status = checked_in`
  - `occupancyCount` = rooms with `status = occupied`
  - `totalRooms` = Room::count()
  - `todayRevenue` = today's payments sum
  - For each arrival: compute urgency (`late` if check_in_date < now, `soon` if within 2h, `later` otherwise) — pass as collection with urgency flag
- [ ] Create `dashboard/index.blade.php`:
  - Quick action buttons: New Booking, Check-in (→ bookings index filtered to confirmed), Check-out (→ bookings index filtered to checked_in)
  - 4 `<x-stat-card>` components
  - Arrivals alert list: each row shows guest name, room number, urgency badge (🔴 LATE / 🟡 in Xh / 🟢 in Xh), clickable → booking show
  - Available rooms count with link to housekeeping
- [ ] Run tests and commit: `git commit -m "feat: dashboard with stat cards and arrival urgency alerts"`

---

### Task 4.4 — User Management (Owner only)

**Files:**
- Create: `app/Http/Controllers/UserController.php`
- Create: `resources/views/users/` — index, create, edit views

- [ ] Write feature tests: owner can create/edit/deactivate users, manager/receptionist gets 403.
- [ ] Implement `UserController` (index, create, store, edit, update). No destroy. `update` can toggle `is_active`. Password hashed via `Hash::make()`.
- [ ] Create views: index table (name, role, status active/inactive, edit link), create/edit form (name, email, role select, password, is_active toggle).
- [ ] Run tests and commit: `git commit -m "feat: user management for owner role"`

---

## Final Checklist

- [ ] Create `database/seeders/DatabaseSeeder.php` with:
  - 1 owner user (`owner@hotel.com` / `password`)
  - 1 manager user (`manager@hotel.com` / `password`)
  - 1 receptionist user (`reception@hotel.com` / `password`)
  - 3 room types: Стандарт (50,000 / 2 guests), Делюкс (90,000 / 2 guests), Люкс (150,000 / 4 guests)
  - 10 rooms across 2 floors
- [ ] `php artisan migrate:fresh --seed` — seeds cleanly with no errors
- [ ] `php artisan test` — all tests pass
- [ ] Manual walkthrough as owner: login → create booking → check-in → check-out → add payment → view finances → view dashboard
- [ ] Verify role restrictions: login as receptionist, confirm `/finances`, `/rooms/create`, `/users` return 403
