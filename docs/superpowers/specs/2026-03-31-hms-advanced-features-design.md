# HMS Advanced Features — Design Spec
**Date:** 2026-03-31
**Status:** Approved
**Scope:** 4-phase feature expansion of the Hotel Management System

---

## Overview

Expand the existing Laravel 11 HMS from a basic CRUD system into a full hotel PMS with operational tools, analytics, internal communication, financial documents, and a public client booking portal.

Stack remains unchanged: Laravel 11, PHP 8.2+, PostgreSQL 15, Blade + Alpine.js, Tailwind CSS CDN. One new Composer dependency: `barryvdh/laravel-dompdf` (Phase 2+4).

---

## Status Machine Extension

The `BookingStatus` enum gains one new value:

```
inquiry → pending → confirmed → checked_in → checked_out
        ↘ cancelled
```

- `inquiry` — client-submitted booking request, not yet a real booking
- `pending` — staff-created booking or accepted inquiry (existing flow)
- All existing transitions unchanged

A new `source` enum column on `bookings`:
```
source: enum('staff', 'client')   default: 'staff'
```

---

## Phase 1 — Operations

### 1.1 Gantt Booking Calendar

**Route:** `GET /calendar?start=YYYY-MM-DD&weeks=2`
**Access:** All authenticated roles

**Backend:**
- `CalendarController@index` queries all rooms with bookings overlapping the requested date range
- Returns rooms grouped by floor, bookings as positioned spans
- Date range: default today → +14 days, max 42 days (6 weeks)

**Frontend — CSS Grid layout:**
- Left sticky column: room number + current status dot (●) colored by `RoomStatus`
- Top sticky header row: dates with day-of-week, today highlighted blue, weekends slightly shaded
- Each date column header shows occupancy count: `8/10`
- Each booking = absolutely-positioned colored bar spanning its date columns
  - Color by `BookingStatus` (yellow/blue/green/gray/red)
  - Label: guest full name (truncated)
  - Payment indicator: small icon on bar (✓ paid, ½ partial, ✗ unpaid)
  - Rooms in `Maintenance` status: hatched/striped pattern on empty cells
- **Hover tooltip** (no click): guest name, phone, dates, nights, payment status
- **Click booking bar** → slide-over panel: booking summary + quick action buttons (confirm / check-in / check-out / cancel) that POST without page reload
- **Click empty cell** → redirect to `bookings/create?room_id=X&check_in=Y`

**Filters above grid:**
- Room type tabs: All / each RoomType name
- Floor filter dropdown
- Color legend strip (5 booking statuses)

**Navigation:**
- Previous/next week arrows
- "Сегодня" button (resets to today)
- Week / 2-week toggle

**Implementation:** Pure Alpine.js + CSS Grid. No external calendar library.

---

### 1.2 Extra Charges on Bookings

**New migration:** `booking_charges` table
```
id
booking_id          FK → bookings
description         string(200)
category            enum(minibar, laundry, room_service, parking, spa, other)
amount              decimal(12,2)
created_by          FK → users
created_at
```

**Routes:**
```
POST   /bookings/{booking}/charges            charges.store
DELETE /bookings/{booking}/charges/{charge}   charges.destroy  (manager/owner only)
```

**UI changes to `bookings/show`:**
- New "Дополнительные услуги" section (same card pattern as payments)
- Inline add-charge form: description + category select + amount
- Charge rows deletable by manager/owner
- Sidebar summary box updated:
  - Room cost: X nights × price
  - Charges total: sum of charges
  - **Grand total** = room cost + charges total
  - Paid: sum of payments
  - Balance due: grand total − paid

**`BookingTotalsService`** (new service class):
- `grandTotal(Booking $booking): float` — single source of truth
- Used by: show page, invoice PDF, debt tracker, booking wizard summary

---

### 1.3 Maintenance Requests

**New migrations:**

`maintenance_requests` table:
```
id
room_id             FK → rooms
title               string(150)
description         text nullable
priority            enum(low, medium, high, urgent)
status              enum(open, in_progress, resolved)
assigned_to         FK → users nullable
resolved_at         timestamp nullable
created_by          FK → users
created_at
updated_at
```

**New enums:** `MaintenancePriority`, `MaintenanceStatus`

**Routes:**
```
GET    /maintenance                   maintenance.index
GET    /maintenance/create            maintenance.create
POST   /maintenance                   maintenance.store
GET    /maintenance/{req}             maintenance.show
GET    /maintenance/{req}/edit        maintenance.edit
PUT    /maintenance/{req}             maintenance.update
PATCH  /maintenance/{req}/resolve     maintenance.resolve
```
Access: all authenticated roles can create; manager/owner can assign and resolve.

**Integration:**
- When a `high` or `urgent` request is created on a room, a flash prompt suggests setting room status to `Maintenance`
- Housekeeping grid: rooms with open requests show a wrench icon (🔧 → SVG) on their card
- Dashboard: count of open `urgent` requests shown as red stat card (owner/manager only)

---

### 1.4 Quick Check-in / Check-out from Dashboard

- Today's arrivals and departures on dashboard get inline status action buttons
- Buttons POST to `bookings/{booking}/status` via `fetch()` with CSRF token
- On success: status badge updates in place, button disappears — Alpine.js reactive
- No page reload required
- Confirmation prompt for check-out only ("Выселить гостя?")

---

### 1.5 Client Booking Portal

**Public routes (no auth, throttled):**
```
GET  /book                  Public booking form (3-step wizard)
GET  /book/rooms            JSON: available rooms for dates (rate-limited)
POST /book                  Submit inquiry → creates booking with status=inquiry, source=client
GET  /book/confirmed/{ref}  Thank-you page with reference number
```

**Rate limiting:** `throttle:3,10` on POST (3 submissions per 10 min per IP)
**Anti-spam:** honeypot hidden field (`_email_confirm`, must be empty)

**Public page layout:**
- Standalone layout (no sidebar — clean minimal page with hotel name/logo from config)
- Same 3-step Alpine.js wizard pattern as staff booking:
  1. Dates → nights calculator
  2. Available rooms → pick one (shows room type, capacity, price/night)
  3. Personal details: first_name, last_name, phone, email, adults, children, notes
- Confirmation page: "Ваш запрос принят! Номер запроса: #REQ-00042. Мы свяжемся с вами для подтверждения."

**On submit:**
- Creates `booking` record: `status=inquiry`, `source=client`
- Creates or stores raw client data in `booking_inquiries` side table (first_name, last_name, phone, email) — not yet linked to `guests` table
- No guest record created until staff accepts

**`booking_inquiries` table:**
```
id
booking_id          FK → bookings
first_name, last_name, phone, email
created_at
```

**Staff — accepting an inquiry:**
- Dashboard: "Новые запросы" alert card (count of inquiries)
- Bookings index: "Запросы" filter tab
- Inquiry row: Accept / Reject buttons inline
- **Accept flow:** modal — "Привязать к существующему гостю или создать нового?"
  - If existing: search autocomplete → link `bookings.guest_id`
  - If new: auto-creates `Guest` from inquiry data
  - Booking moves to `pending`
- **Reject flow:** one-click + optional reason note → `cancelled`

**New `booking_inquiries` nav item** in sidebar (with badge count) — visible to all roles.

---

### 1.6 Code Quality (Phase 1)

| Improvement | Detail |
|-------------|--------|
| `RoomAvailabilityService` | Extract availability overlap check from `BookingController` — used by calendar, booking wizard, client portal |
| `BookingTotalsService` | Single source of truth for grand total (room + charges) |
| Form Request classes | `StoreBookingRequest`, `UpdateBookingRequest`, `StoreChargeRequest`, `StoreMaintenanceRequest` — move validation out of controllers |
| `findOrFail` guards | Several controllers use `find()` without 404 handling |

---

## Phase 2 — Reporting & Analytics

### 2.1 Dashboard Charts

**Library:** Chart.js via CDN (added to `layouts/app.blade.php`)

Four new chart widgets on dashboard (below existing stat cards):

| Widget | Chart type | Data source |
|--------|-----------|-------------|
| Occupancy % — last 30 days | Line | Daily `occupied / total` ratio |
| Revenue — last 12 months | Bar | Monthly `payments.sum(amount)` |
| Bookings by status | Doughnut | Count per `BookingStatus` |
| Revenue by room type | Horizontal bar | Payments joined to bookings→rooms→room_types, current month |

Data computed in `DashboardController`, passed as JSON to Blade. No AJAX on load.

Charts are responsive, use the existing slate/blue color palette, tooltips in Russian.

---

### 2.2 Occupancy Forecast

**Route:** `GET /reports/forecast`

90-day heatmap calendar view:
- 3-month grid, one cell per day
- Cell color: white (0%) → light blue (50%) → blue-600 (100%) — based on `booked_rooms / total_rooms`
- Hover tooltip: "X из Y номеров занято"
- Counts only `confirmed` + `checked_in` bookings (not inquiries or pending)
- Helps managers identify gaps for promotions and peak periods for pricing rules

---

### 2.3 Reports Hub

**Route:** `GET /reports` (replaces current `/finances` as the analytics center — `/finances` redirects)
**Access:** owner + manager

Reports available:

| Report | Filters | Export |
|--------|---------|--------|
| Revenue by period | month / quarter / year | PDF, CSV |
| Occupancy report | period | PDF, CSV |
| Guest statistics (new vs repeat, nationality breakdown) | period | CSV |
| Expenses by category | period | PDF, CSV |
| Unpaid bookings | — | PDF |
| Booking sources (staff vs client portal) | period | CSV |

**CSV export:** Laravel `StreamedResponse` — no package needed
**PDF export:** `barryvdh/laravel-dompdf` — styled table with hotel header/footer

**KPI metrics on reports hub page:**
- ADR (Average Daily Rate) = total room revenue / total nights sold
- RevPAR (Revenue Per Available Room) = total room revenue / (total rooms × days in period)

---

## Phase 3 — Internal Operations

### 3.1 Activity Log

**New table:** `activity_logs`
```
id
user_id             FK → users
action              string(50)  e.g. "booking.status_changed"
subject_type        string      e.g. "Booking"
subject_id          unsignedBigInteger
subject_label       string      e.g. "Бронирование #42"
old_values          json nullable
new_values          json nullable
ip_address          string(45) nullable
created_at
```

**Implementation:** Laravel Observer pattern — one Observer per model, no controller changes needed.

Models observed: `Booking`, `Payment`, `Guest`, `Expense`, `Room`, `User`, `MaintenanceRequest`

**Route:** `GET /activity` (owner + manager)
**UI:** Filterable feed — filter by user, action type, date range.
Each entry: `[Avatar] Иванов изменил статус бронирования #42: confirmed → checked_in · 14:32`
Clicking subject label navigates to that record.

---

### 3.2 In-App Notification Bell

**New table:** `notifications`
```
id
user_id             FK → users
type                string(80)
title               string(150)
body                string(300)
url                 string nullable
read_at             timestamp nullable
created_at
```

**Notification types and triggers:**

| Type | Trigger | Roles |
|------|---------|-------|
| `checkin_unconfirmed` | Today's bookings still `pending` at 08:00 (checked on bell open) | receptionist, manager |
| `payment_overdue` | `checked_out` booking with balance > 0 | manager, owner |
| `maintenance_new` | New maintenance request created | manager, owner |
| `maintenance_urgent` | Urgent priority maintenance opened | owner |
| `inquiry_new` | New client inquiry submitted | manager, owner |
| `pending_stale` | Booking in `pending` for >24h without confirmation | manager |

**Implementation:** Generated on-demand when bell is clicked (DB queries, no queue/websocket). Badge count in sidebar nav. "Отметить все прочитанными" button.

---

### 3.3 Shift Handover Notes

**New table:** `shift_notes`
```
id
user_id             FK → users
body                text
shift               enum(morning, evening, night)
created_at
```

**Route:** `GET /shift-notes` · `POST /shift-notes`
**Access:** all roles

- Feed page shows notes from last 7 days, newest first
- Write note form: body textarea + shift selector
- Today's notes shown as a collapsible widget on dashboard (last 3 notes)
- Staff expected to write a handover note before logging out (soft reminder, not enforced)

---

### 3.4 Housekeeping Task Assignment

**Schema change:** add `assigned_to` (nullable FK → users) to `rooms` table.

**Housekeeping view changes:**
- Room cards with `status=cleaning` show an "Назначить" button
- Clicking opens a small dropdown: list of active staff members
- Assignment saved via `PATCH /housekeeping/{room}` (extends existing route)
- Assigned person's name shown on the card
- Assigned person sees "Мои задачи" widget on their dashboard (rooms assigned to them in cleaning status)
- Marking a task done → PATCH sets `status=available`, clears `assigned_to`

---

## Phase 4 — Financial

### 4.1 Invoice PDF

**Route:** `GET /bookings/{booking}/invoice` → streams PDF download

**Invoice content:**
- Hotel name + address block (from `config('hotel.name')`, `config('hotel.address')`)
- Invoice number: auto-incremented, formatted `INV-2026-0001` (new `invoice_number` column on `bookings`, set on first PDF generation)
- Generated date
- Guest details: name, passport number, nationality
- Booking details: room number, type, floor, dates, nights
- Line items table:
  - Room: X nights × base_price = subtotal
  - Each `booking_charge` as its own line
  - Pricing rule discount/surcharge if applied (Phase 4)
  - Subtotal
- Payments section: date, method, amount for each payment
- **Grand total** / **Paid** / **Balance due**
- Footer: hotel contact info

**Package:** `barryvdh/laravel-dompdf`
**Button:** added to `bookings/show` sidebar — "Скачать счёт (PDF)"

---

### 4.2 Seasonal / Dynamic Pricing Rules

**New table:** `pricing_rules`
```
id
name                string(100)
room_type_id        FK → room_types nullable  (null = applies to all types)
date_from           date
date_to             date
modifier_type       enum(fixed, percent)
modifier_value      decimal(10,2)             (percent: -15 = −15%, 30 = +30%)
priority            unsignedTinyInteger       default 0
is_active           boolean                   default true
created_by          FK → users
created_at, updated_at
```

**Routes:** Full CRUD under `/pricing-rules` (owner only)

**How pricing is applied:**
- `PricingService::adjustedPrice(RoomType $type, Carbon $checkIn, Carbon $checkOut): float`
- Checks for active rules overlapping the date range, highest priority wins
- `fixed` modifier: replaces base_price per night
- `percent` modifier: multiplies base_price × (1 + modifier_value/100)
- Called from booking wizard (staff + client portal) — shows adjusted price before confirming
- Booking wizard shows banner: "Действует сезонный тариф: Новогодние праздники (+30%)"

---

### 4.3 Debt / Unpaid Bookings Tracker

**Route:** `GET /finances/debt` (tab on finances page)
**Access:** manager + owner

**Criteria for appearing in debt list:**
- `status = checked_out` AND `grand_total > payments_sum`
- `status = checked_in` AND `payments_sum = 0` AND `check_in_date < today - 1 day`

**Table columns:**
- Guest name (link to guest profile)
- Room number
- Check-out date
- Grand total (room + charges)
- Paid
- **Balance due** (highlighted red)
- Days overdue

**Sorted by:** days overdue descending

**Inline actions:**
- "Добавить платёж" → payment form pre-filled with balance amount
- "Скачать счёт" → invoice PDF

**Dashboard stat card:**
- Total outstanding debt sum shown as red stat card for owner/manager
- Links to `/finances/debt`

---

## Database Migration Summary

| Phase | New tables | Modified tables |
|-------|-----------|-----------------|
| 1 | `booking_charges`, `booking_inquiries`, `maintenance_requests` | `bookings` (+source) |
| 2 | — | — |
| 3 | `activity_logs`, `notifications`, `shift_notes` | `rooms` (+assigned_to) |
| 4 | `pricing_rules` | `bookings` (+invoice_number) |

New enums: `MaintenancePriority`, `MaintenanceStatus`, `BookingSource` (staff/client)

`BookingStatus` gains: `inquiry`

---

## Route Summary

```
# Phase 1
GET    /calendar
GET    /book                          (public)
GET    /book/rooms                    (public, rate-limited)
POST   /book                          (public, rate-limited + honeypot)
GET    /book/confirmed/{ref}          (public)
POST   /bookings/{booking}/charges
DELETE /bookings/{booking}/charges/{charge}
GET    /maintenance
GET    /maintenance/create
POST   /maintenance
GET    /maintenance/{req}
GET    /maintenance/{req}/edit
PUT    /maintenance/{req}
PATCH  /maintenance/{req}/resolve

# Phase 2
GET    /reports
GET    /reports/forecast
GET    /reports/{type}/export         (PDF/CSV)

# Phase 3
GET    /activity
GET    /notifications
PATCH  /notifications/read-all
GET    /shift-notes
POST   /shift-notes

# Phase 4
GET    /bookings/{booking}/invoice
GET    /pricing-rules
GET    /pricing-rules/create
POST   /pricing-rules
GET    /pricing-rules/{rule}/edit
PUT    /pricing-rules/{rule}
DELETE /pricing-rules/{rule}
GET    /finances/debt
```

---

## New Service Classes

| Service | Responsibility |
|---------|---------------|
| `RoomAvailabilityService` | Check room availability for date range, exclude booking ID |
| `BookingTotalsService` | Grand total = room cost + charges; used by show, invoice, debt tracker |
| `PricingService` | Apply pricing rules to a room type + date range |
| `NotificationService` | Generate on-demand notifications per user |

---

## Dependencies

| Package | Version | Purpose | Phase |
|---------|---------|---------|-------|
| `barryvdh/laravel-dompdf` | ^3.0 | PDF generation (invoices + reports) | 2 + 4 |

No other new dependencies. Chart.js loaded via CDN.

---

## Out of Scope

- Email / SMS notifications (requires SMTP/Twilio setup — separate project)
- Real-time websocket notifications
- Channel management (Booking.com, Airbnb sync)
- Mobile app / PWA
- Multi-property support
- Online payment processing
