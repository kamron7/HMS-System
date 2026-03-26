# Hotel Management System — Design Spec
**Date:** 2026-03-26
**Status:** Approved

---

## 1. Project Overview

A staff-only Hotel Management System (HMS) for a single hotel with 100+ rooms. Built with Laravel + PostgreSQL + Blade + Alpine.js. Russian language only. Guest-facing booking portal and payment gateway integration (Payme, Click) are explicitly deferred to future phases.

**Core principle:** A hotel receptionist must understand every screen in under 5 seconds.

---

## 2. Scope (Phase 1)

**In scope:**
- Booking management (create, view, update, cancel, check-in, check-out)
- Guest management (create, search, reuse across bookings)
- Room & room type management
- Housekeeping status tracking
- Manual payment recording (no gateway)
- Expense tracking + financial summary
- Staff accounts with role-based access (Owner / Manager / Receptionist)
- Dashboard with today's key metrics and alerts

**Out of scope (deferred):**
- Guest-facing online booking portal
- Payment gateway integration (Payme, Click)
- Multi-hotel / multi-tenant support
- Reporting exports (PDF, Excel)
- SMS / email notifications

---

## 3. Tech Stack

| Layer      | Choice                          |
|------------|---------------------------------|
| Backend    | Laravel (latest)                |
| Database   | PostgreSQL                      |
| Frontend   | Blade + Alpine.js               |
| Language   | Russian (ru)                    |
| Auth       | Laravel built-in (session-based)|

---

## 4. Database Schema

### 4.1 `room_types`
Stores room categories with pricing and capacity.

| Column      | Type         | Notes                        |
|-------------|--------------|------------------------------|
| id          | bigint PK    |                              |
| name        | varchar(100) | e.g. "Стандарт", "Делюкс"   |
| base_price  | decimal(12,2)| Price per night              |
| capacity    | integer      | Max guests                   |
| description | text         | nullable                     |
| amenities   | jsonb        | nullable, flexible extras    |
| created_at  | timestamp    |                              |
| updated_at  | timestamp    |                              |

### 4.2 `rooms`
Physical rooms in the hotel.

| Column       | Type         | Notes                                          |
|--------------|--------------|------------------------------------------------|
| id           | bigint PK    |                                                |
| room_type_id | bigint FK    | → room_types                                   |
| number       | varchar(10)  | e.g. "101", "202A"                             |
| floor        | integer      | nullable                                       |
| status       | enum         | available / occupied / cleaning / maintenance  |
| notes        | text         | nullable, internal staff notes                 |
| created_at   | timestamp    |                                                |
| updated_at   | timestamp    |                                                |

### 4.3 `guests`
One record per real-world person. Reused across bookings.

| Column          | Type         | Notes                        |
|-----------------|--------------|------------------------------|
| id              | bigint PK    |                              |
| first_name      | varchar(100) |                              |
| last_name       | varchar(100) |                              |
| phone           | varchar(20)  | nullable                     |
| email           | varchar(150) | nullable                     |
| passport_number | varchar(50)  | nullable, indexed            |
| nationality     | varchar(100) | nullable                     |
| created_at      | timestamp    |                              |
| updated_at      | timestamp    |                              |

### 4.4 `bookings`
Core booking record.

| Column         | Type         | Notes                                                        |
|----------------|--------------|--------------------------------------------------------------|
| id             | bigint PK    |                                                              |
| room_id        | bigint FK    | → rooms                                                      |
| guest_id       | bigint FK    | → guests                                                     |
| check_in_date  | date         |                                                              |
| check_out_date | date         |                                                              |
| adults         | integer      | default 1                                                    |
| children       | integer      | default 0                                                    |
| status         | enum         | pending / confirmed / checked_in / checked_out / cancelled   |
| total_price    | decimal(12,2)| Calculated at booking time (nights × room base_price)        |
| notes          | text         | nullable                                                     |
| created_by     | bigint FK    | → users                                                      |
| created_at     | timestamp    |                                                              |
| updated_at     | timestamp    |                                                              |

### 4.5 `payments`
Individual payment transactions against a booking. Supports partial payments and future gateway integration.

| Column     | Type         | Notes                                    |
|------------|--------------|------------------------------------------|
| id         | bigint PK    |                                          |
| booking_id | bigint FK    | → bookings                               |
| amount     | decimal(12,2)|                                          |
| method     | varchar(50)  | cash / card / bank_transfer              |
| paid_at    | timestamp    |                                          |
| notes      | text         | nullable                                 |
| created_at | timestamp    |                                          |
| updated_at | timestamp    |                                          |

**Computed payment status** (derived on Booking model, not stored):
- `unpaid` — sum(payments.amount) = 0
- `partial` — 0 < sum(payments.amount) < total_price
- `paid`    — sum(payments.amount) >= total_price

**Payments are append-only.** Once recorded, a payment can only be deleted, not edited. There is no payment update route.

### 4.6 `expenses`
Simple expense log for financial tracking.

**Expense categories** (fixed dropdown in UI, stored as-is in DB):
- Коммунальные услуги
- Расходные материалы
- Зарплата
- Техническое обслуживание
- Прочее

| Column       | Type         | Notes                                        |
|--------------|--------------|----------------------------------------------|
| id           | bigint PK    |                                              |
| category     | varchar(100) | UI renders as a select with fixed options (see below); stored as free-text for flexibility |
| description  | text         |                                              |
| amount       | decimal(12,2)|                                              |
| expense_date | date         |                                              |
| created_by   | bigint FK    | → users                                      |
| created_at   | timestamp    |                                              |
| updated_at   | timestamp    |                                              |

### 4.7 `users`
Staff accounts only.

| Column     | Type         | Notes                              |
|------------|--------------|------------------------------------|
| id         | bigint PK    |                                    |
| name       | varchar(150) |                                    |
| email      | varchar(150) | unique                             |
| password   | varchar      | hashed                             |
| role       | enum         | owner / manager / receptionist     |
| is_active  | boolean      | default true                       |
| created_at | timestamp    |                                    |
| updated_at | timestamp    |                                    |

### 4.8 Indexes

| Table    | Index                                           | Purpose                    |
|----------|-------------------------------------------------|----------------------------|
| bookings | (room_id, check_in_date, check_out_date)        | Availability queries       |
| bookings | (guest_id)                                      | Guest booking history      |
| bookings | (status)                                        | Status filtering           |
| bookings | (check_in_date)                                 | Today's arrivals           |
| guests   | (phone)                                         | Search by phone            |
| guests   | (passport_number)                               | Search by passport         |
| rooms    | (room_type_id, status)                          | Room filtering             |

---

## 5. Booking Architecture

**Approach: Book a specific room directly.**

A booking references a physical room (room_id). Availability check query:

```sql
SELECT * FROM rooms
WHERE id = :room_id
AND id NOT IN (
  SELECT room_id FROM bookings
  WHERE status NOT IN ('cancelled', 'checked_out')
  AND check_in_date  < :checkout
  AND check_out_date > :checkin
)
```

This is a single query, easy to understand, and covers all edge cases.

### 5.1 Booking Status State Machine

Allowed transitions only. Any other transition is rejected.

| From          | To            | Triggered by                        |
|---------------|---------------|-------------------------------------|
| pending       | confirmed     | Staff clicks "Confirm"              |
| pending       | checked_in    | Staff clicks "Check-in" (skip confirm) |
| pending       | cancelled     | Staff clicks "Cancel"               |
| confirmed     | checked_in    | Staff clicks "Check-in"             |
| confirmed     | cancelled     | Staff clicks "Cancel"               |
| checked_in    | checked_out   | Staff clicks "Check-out"            |
| checked_in    | cancelled     | Staff clicks "Cancel" (emergency)   |
| checked_out   | (terminal)    | No further transitions              |
| cancelled     | (terminal)    | No further transitions              |

Status changes are sent via `POST /bookings/{id}/status` with a `transition` param (e.g. `confirm`, `check_in`, `check_out`, `cancel`). The controller validates the transition is allowed for the current status before applying it.

### 5.2 Room Status Lifecycle

`rooms.status` is kept in sync with bookings automatically:

| Event                          | rooms.status transition         |
|--------------------------------|---------------------------------|
| Booking checked in             | → `occupied` (automatic)        |
| Booking checked out            | → `cleaning` (automatic)        |
| Housekeeping marks room clean  | → `available` (manual, via UI)  |
| Manager sets maintenance       | → `maintenance` (manual, via UI)|

`maintenance` is set manually by Owner or Manager via the Room detail page (`PATCH /rooms/{id}` with `status=maintenance`). No dedicated route — it uses the existing room update endpoint.

---

## 6. Laravel Structure

### Models & Relationships
```
RoomType    hasMany → rooms
Room        belongsTo → room_type
            hasMany → bookings
Guest       hasMany → bookings
Booking     belongsTo → room, guest, user (created_by)
            hasMany → payments
            computed: payment_status (unpaid/partial/paid)
Payment     belongsTo → booking
Expense     belongsTo → user (created_by)
User        hasMany → bookings (created_by), expenses (created_by)
```

### Controllers
```
DashboardController      → index
BookingController        → index, create, store, show, edit, update, destroy
GuestController          → index, create, store, show, edit, update
RoomController           → index, create, store, edit, update
RoomTypeController       → index, create, store, edit, update
PaymentController        → store, destroy
ExpenseController        → index, create, store, edit, update, destroy
UserController           → index, create, store, edit, update  (owner only)
HousekeepingController   → index, update
```

### Routes
```
GET  /dashboard

GET  /bookings                     POST   /bookings
GET  /bookings/create
GET  /bookings/{id}                PUT    /bookings/{id}
GET  /bookings/{id}/edit           POST   /bookings/{id}/status   ← status transitions
DELETE /bookings/{id}

GET  /guests                       POST   /guests
GET  /guests/create
GET  /guests/{id}                  PUT    /guests/{id}
GET  /guests/{id}/edit

GET  /rooms                        POST   /rooms
GET  /rooms/create
GET  /rooms/{id}/edit              PUT    /rooms/{id}

GET  /room-types                   POST   /room-types
GET  /room-types/create
GET  /room-types/{id}/edit         PUT    /room-types/{id}

GET  /housekeeping

GET  /finances

POST /payments                     DELETE /payments/{id}

GET  /expenses                     POST   /expenses
GET  /expenses/create
GET  /expenses/{id}/edit           PUT    /expenses/{id}
DELETE /expenses/{id}

GET  /users                        POST   /users            (owner only)
GET  /users/create
GET  /users/{id}/edit              PUT    /users/{id}
```

### Blade Components
```
layouts/app.blade.php          → main shell (nav + content)
components/stat-card.blade.php → dashboard metric cards
components/status-badge.blade.php → colored status pill
components/room-card.blade.php → room grid card
components/booking-row.blade.php → booking list row with inline actions
components/summary-box.blade.php → booking form live summary
```

No repositories. No service classes until logic needs to be shared across controllers.

**Users are deactivated, never deleted** (`is_active = false`), to preserve audit references via `created_by` foreign keys on bookings and expenses. `UserController` has no `destroy` action.

---

## 7. Role Permissions

| Action                  | Owner | Manager | Receptionist |
|-------------------------|-------|---------|--------------|
| View dashboard          | ✅    | ✅      | ✅           |
| Manage bookings         | ✅    | ✅      | ✅           |
| Manage guests           | ✅    | ✅      | ✅           |
| View finances           | ✅    | ✅      | ❌           |
| Add expenses            | ✅    | ✅      | ❌           |
| Manage rooms/types      | ✅    | ✅      | ❌           |
| Manage housekeeping     | ✅    | ✅      | ✅           |
| Manage users            | ✅    | ❌      | ❌           |

---

## 8. UI Layout

### Dashboard
- Quick actions at top: New Booking, Check-in, Check-out
- 4 stat cards: Today's check-ins, check-outs, occupancy, revenue
- Arrivals alert list with urgency indicators:
  - 🔴 LATE — past check-in time
  - 🟡 in Xm/h — arriving within 2 hours
  - 🟢 in Xh — arriving later today
- Each alert is clickable → goes to booking detail

### Bookings List
- Sticky filter bar (search, status, date range)
- Each row: booking ID, guest name + phone/email, room, dates, color-coded status badge, total price
- Inline quick actions per row (valid for current status only):
  - Pending → Confirm, Check-in, Cancel
  - Confirmed → Check-in, Cancel
  - Checked In → Check-out, Cancel
- Full row clickable → booking detail

### New Booking Form (single-page wizard, 3 steps)
Alpine.js controls step visibility — no page reloads between steps.
1. Guest — search with returning guest autocomplete, create new if not found
2. Room — type selector then room selector (available only); inline conflict warning with alternatives
3. Dates — smart defaults (today / today+1, capacity-based guest count)
- Live summary box always visible: room, dates, nights, total price
- Auto-focus advances through steps

### Room Grid (Housekeeping)
- Filter tabs: All / Available / Occupied / Cleaning
- Cards grouped by floor
- Hover preview on occupied rooms: guest name + checkout date
- Click → modal with: guest info, View Booking button, Mark as Cleaning button

### Finances
**Revenue definition:** sum of `payments.amount` where `paid_at` falls within the period. This reflects actual cash received, not bookings made. Applied consistently to both today's stats card on the dashboard and the Finances page.

- Today row: Revenue, Expenses, Profit + comparison vs yesterday (↑↓ colored)
- Monthly row: Revenue, Expenses, Profit + comparison vs previous month
- Expenses list with + Add Expense button
- Month navigation (prev/next)

**Comparison color logic:**
- Revenue / Profit: ↑ = green, ↓ = red
- Expenses: ↓ = green, ↑ = red

---

## 9. Key UX Rules

1. Every page answers: "What should I do next?"
2. Max 2–3 clicks for key actions (create booking, check-in, check-out)
3. Inline actions eliminate navigation for common tasks
4. Color meanings: green = good/available, red = problem/late/cancelled, yellow = warning/pending, blue = confirmed, grey = done
5. Forms use smart defaults to minimize required typing
6. Conflict detection is proactive (shown before form submission)
7. Returning guest autocomplete avoids duplicate records

---

## 10. Future Phases (not in scope now)

- **Phase 2:** Guest-facing online booking portal
- **Phase 3:** Payment gateway integration (Payme, Click)
- **Phase 4:** Reporting (PDF exports, charts)
- **Phase 5:** Notifications (SMS, email)
