<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\RoomPortalController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\BookingCalendarController;
use App\Http\Controllers\BookingChargeController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ClientBookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\FinancesController;
use App\Http\Controllers\GroupBookingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PromoCodeController;
use App\Http\Controllers\PricingRuleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\GuestMailController;
use App\Http\Controllers\HousekeepingController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomTypeController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ShiftNoteController;
use App\Http\Controllers\GuestPortalController;
use App\Http\Controllers\GuestServiceRequestController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserMailController;
use Illuminate\Support\Facades\Route;

// Auth (unauthenticated)
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Post-stay feedback (signed URL — no auth required)
Route::get('/feedback/{booking}',  [FeedbackController::class, 'show'])->name('feedback.show');
Route::post('/feedback/{booking}', [FeedbackController::class, 'store'])->name('feedback.store');

// Guest magic-link portal (signed URL — no auth required)
Route::get('/guest/booking/{booking}',          [GuestPortalController::class, 'show'])->name('guest.booking.show');
Route::post('/guest/booking/{booking}/upsell',  [GuestPortalController::class, 'upsell'])->name('guest.booking.upsell');

// Room QR portal (permanent, public — no auth, no signed URL)
Route::get('/r/{token}',          [RoomPortalController::class, 'show'])->name('room-portal.show');
Route::get('/r/{token}/qr.svg',   [RoomPortalController::class, 'qrImage'])->name('room-portal.qr-image');
Route::get('/r/{token}/qr.png',   [RoomPortalController::class, 'qrPng'])->name('room-portal.qr-png');
Route::get('/r/{token}/verify',   [RoomPortalController::class, 'verifyForm'])->name('room-portal.verify');
Route::post('/r/{token}/verify',  [RoomPortalController::class, 'verify'])->name('room-portal.verify.post')->middleware('throttle:50,5');
Route::post('/r/{token}/order',   [RoomPortalController::class, 'order'])->name('room-portal.order')->middleware('throttle:10,1');
Route::post('/r/{token}/feedback',[RoomPortalController::class, 'feedback'])->name('room-portal.feedback')->middleware('throttle:5,10');
Route::post('/r/{token}/maintenance', [RoomPortalController::class, 'maintenance'])->name('room-portal.maintenance')->middleware('throttle:10,1');

// Public client booking portal (rate-limited)
Route::get('/book', [ClientBookingController::class, 'index'])->name('book.index');
Route::get('/book/rooms', [ClientBookingController::class, 'rooms'])->name('book.rooms')->middleware('throttle:20,1');
Route::get('/book/promo', [ClientBookingController::class, 'checkPromo'])->name('book.promo')->middleware('throttle:10,1');
Route::post('/book', [ClientBookingController::class, 'store'])->name('book.store')->middleware('throttle:50,1');
Route::get('/book/confirmed/{ref}', [ClientBookingController::class, 'confirmed'])->name('book.confirmed');

// All authenticated routes
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/', fn() => redirect('/dashboard'));

    // My Profile (all roles)
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');

    // Global search (command palette)
    Route::get('/search', [SearchController::class, 'search'])->name('search');

    // Smart room suggest (all roles — receptionists create bookings too)
    Route::get('/rooms/suggest', [RoomController::class, 'suggest'])->name('rooms.suggest');

    // Booking calendar (must be before /bookings/{booking})
    Route::get('/bookings/calendar', [BookingCalendarController::class, 'index'])->name('bookings.calendar');
    Route::get('/bookings/calendar/data', [BookingCalendarController::class, 'data'])->name('bookings.calendar.data');

    // Bookings
    Route::get('/bookings/timeline', [BookingController::class, 'timeline'])->name('bookings.timeline');
    Route::get('/bookings/export', [BookingController::class, 'export'])->name('bookings.export');
    Route::post('/bookings/bulk-status', [BookingController::class, 'bulkStatus'])->name('bookings.bulk-status');
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::get('/bookings/{booking}/invoice', [BookingController::class, 'invoice'])->name('bookings.invoice');
    Route::get('/bookings/{booking}/edit', [BookingController::class, 'edit'])->name('bookings.edit');
    Route::put('/bookings/{booking}', [BookingController::class, 'update'])->name('bookings.update');
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');
    Route::post('/bookings/{booking}/status', [BookingController::class, 'updateStatus'])->name('bookings.status');
    Route::post('/bookings/{booking}/send-confirmation', [BookingController::class, 'sendConfirmation'])->name('bookings.send-confirmation');
    Route::patch('/bookings/{booking}/move',       [BookingController::class, 'moveRoom'])->name('bookings.move');
    Route::patch('/bookings/{booking}/move-dates', [BookingController::class, 'moveDates'])->name('bookings.move-dates');
    Route::patch('/bookings/{booking}/extend',     [BookingController::class, 'extendStay'])->name('bookings.extend');

    // Inquiry accept / reject (all authenticated roles)
    Route::post('/bookings/{booking}/accept-inquiry', [BookingController::class, 'acceptInquiry'])->name('bookings.accept-inquiry');
    Route::post('/bookings/{booking}/reject-inquiry', [BookingController::class, 'rejectInquiry'])->name('bookings.reject-inquiry');

    // Booking charges
    Route::post('/bookings/{booking}/charges', [BookingChargeController::class, 'store'])->name('charges.store');
    Route::delete('/bookings/{booking}/charges/{charge}', [BookingChargeController::class, 'destroy'])->name('charges.destroy');

    // Guests — static routes MUST be before /guests/{guest}
    Route::get('/guests/export', [GuestController::class, 'export'])->name('guests.export');
    Route::get('/guests/search', [GuestController::class, 'search'])->name('guests.search');
    Route::post('/guests/quick', [GuestController::class, 'quickStore'])->name('guests.quick-store');
    Route::get('/guests/mail', [GuestMailController::class, 'index'])->name('guests.mail')->middleware('role:owner,manager');
    Route::post('/guests/mail/send', [GuestMailController::class, 'send'])->name('guests.mail.send')->middleware('role:owner,manager');
    Route::get('/guests', [GuestController::class, 'index'])->name('guests.index');
    Route::get('/guests/create', [GuestController::class, 'create'])->name('guests.create');
    Route::post('/guests', [GuestController::class, 'store'])->name('guests.store');
    Route::get('/guests/{guest}', [GuestController::class, 'show'])->name('guests.show');
    Route::get('/guests/{guest}/edit', [GuestController::class, 'edit'])->name('guests.edit');
    Route::put('/guests/{guest}', [GuestController::class, 'update'])->name('guests.update');
    Route::delete('/guests/{guest}', [GuestController::class, 'destroy'])->name('guests.destroy');

    // Housekeeping (all roles) — bulk must be BEFORE {room} to avoid conflict
    Route::patch('/housekeeping/bulk', [HousekeepingController::class, 'bulkUpdate'])->name('housekeeping.bulk');
    Route::get('/housekeeping', [HousekeepingController::class, 'index'])->name('housekeeping.index');
    Route::patch('/housekeeping/{room}', [HousekeepingController::class, 'update'])->name('housekeeping.update');

    // Activity log (owner + manager only)
    Route::middleware('role:owner,manager')->group(function () {
        Route::get('/activity', [ActivityController::class, 'index'])->name('activity.index');
    });

    // Notifications (all authenticated)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/count', [NotificationController::class, 'count'])->name('notifications.count');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');

    // Shift notes (all authenticated)
    Route::get('/shift-notes', [ShiftNoteController::class, 'index'])->name('shift-notes.index');
    Route::post('/shift-notes', [ShiftNoteController::class, 'store'])->name('shift-notes.store');

    // Attendance / Worker shifts (all authenticated)
    Route::get('/attendance', [\App\Http\Controllers\AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/start', [\App\Http\Controllers\AttendanceController::class, 'startShift'])->name('attendance.start');
    Route::post('/attendance/end', [\App\Http\Controllers\AttendanceController::class, 'endShift'])->name('attendance.end');
    Route::post('/attendance/break', [\App\Http\Controllers\AttendanceController::class, 'toggleBreak'])->name('attendance.break');
    Route::post('/attendance/shift/{shift}/close', [\App\Http\Controllers\AttendanceController::class, 'closeWorkerShift'])->name('attendance.shift.close');

    // Cashier shifts (receptionist+)
    Route::get('/cashier', [\App\Http\Controllers\CashierShiftController::class, 'index'])->name('cashier.index');
    Route::get('/cashier/daily', [\App\Http\Controllers\CashierShiftController::class, 'dailySummary'])->name('cashier.daily');
    Route::get('/cashier/{shift}', [\App\Http\Controllers\CashierShiftController::class, 'show'])->name('cashier.show');
    Route::post('/cashier/open', [\App\Http\Controllers\CashierShiftController::class, 'open'])->name('cashier.open');
    Route::post('/cashier/close', [\App\Http\Controllers\CashierShiftController::class, 'close'])->name('cashier.close');

    // Lost & Found
    Route::get('/lost-items', [\App\Http\Controllers\LostItemController::class, 'index'])->name('lost-items.index');
    Route::get('/lost-items/create', [\App\Http\Controllers\LostItemController::class, 'create'])->name('lost-items.create');
    Route::post('/lost-items', [\App\Http\Controllers\LostItemController::class, 'store'])->name('lost-items.store');
    Route::get('/lost-items/{item}', [\App\Http\Controllers\LostItemController::class, 'show'])->name('lost-items.show');
    Route::post('/lost-items/{item}/status', [\App\Http\Controllers\LostItemController::class, 'updateStatus'])->name('lost-items.status');
    Route::delete('/lost-items/{item}', [\App\Http\Controllers\LostItemController::class, 'destroy'])->name('lost-items.destroy');

    // Guest service requests (pending approval workflow)
    Route::get('/service-requests', [GuestServiceRequestController::class, 'index'])->name('service-requests.index');
    Route::get('/service-requests/count', [GuestServiceRequestController::class, 'count'])->name('service-requests.count');
    Route::post('/service-requests/{serviceRequest}/confirm', [GuestServiceRequestController::class, 'confirm'])->name('service-requests.confirm');
    Route::post('/service-requests/{serviceRequest}/decline', [GuestServiceRequestController::class, 'decline'])->name('service-requests.decline');

    // Maintenance (all roles can create; manager/owner can assign/resolve)
    Route::get('/maintenance', [MaintenanceController::class, 'index'])->name('maintenance.index');
    Route::get('/maintenance/create', [MaintenanceController::class, 'create'])->name('maintenance.create');
    Route::post('/maintenance', [MaintenanceController::class, 'store'])->name('maintenance.store');
    Route::get('/maintenance/{maintenance}', [MaintenanceController::class, 'show'])->name('maintenance.show');
    Route::get('/maintenance/{maintenance}/edit', [MaintenanceController::class, 'edit'])->name('maintenance.edit');
    Route::put('/maintenance/{maintenance}', [MaintenanceController::class, 'update'])->name('maintenance.update');
    Route::patch('/maintenance/{maintenance}/resolve', [MaintenanceController::class, 'resolve'])->name('maintenance.resolve');
    Route::patch('/maintenance/{maintenance}/status', [MaintenanceController::class, 'updateStatus'])->name('maintenance.status');

    // Payments (all roles) — append-only, no edit or delete
    Route::post('/bookings/{booking}/payments', [PaymentController::class, 'store'])->name('payments.store');

    // Owner + Manager only
    // Rooms available (JSON — all authenticated roles, used by booking forms)
    Route::get('/rooms/available', [RoomController::class, 'available'])->name('rooms.available');
    Route::get('/rooms/{room}/check', [RoomController::class, 'checkAvailability'])->name('rooms.check');

    // Room blocks (calendar date blocking)
    Route::post('/room-blocks', [\App\Http\Controllers\RoomBlockController::class, 'store'])->name('room-blocks.store');
    Route::delete('/room-blocks/{roomBlock}', [\App\Http\Controllers\RoomBlockController::class, 'destroy'])->name('room-blocks.destroy');

    // Owner + Manager only (room/property management)
    Route::middleware('role:owner,manager')->group(function () {

        // Room QR code download (SVG)
        Route::get('/rooms/{room}/qr', [RoomController::class, 'qrCode'])->name('rooms.qr');

        // Rooms
        Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
        Route::get('/rooms/create', [RoomController::class, 'create'])->name('rooms.create');
        Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
        Route::get('/rooms/{room}/edit', [RoomController::class, 'edit'])->name('rooms.edit');
        Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
        Route::post('/rooms/{room}/status', [RoomController::class, 'updateStatus'])->name('rooms.status');
        Route::delete('/rooms/{room}/images', [RoomController::class, 'deleteImage'])->name('rooms.images.destroy');

        // Reviews
        Route::get('/reviews', [RoomController::class, 'reviewsIndex'])->name('reviews.index');
        Route::delete('/reviews/{review}', [RoomController::class, 'deleteReview'])->name('reviews.destroy');

        // Room Types
        Route::get('/room-types', [RoomTypeController::class, 'index'])->name('room-types.index');
        Route::get('/room-types/create', [RoomTypeController::class, 'create'])->name('room-types.create');
        Route::post('/room-types', [RoomTypeController::class, 'store'])->name('room-types.store');
        Route::get('/room-types/{roomType}/edit', [RoomTypeController::class, 'edit'])->name('room-types.edit');
        Route::put('/room-types/{roomType}', [RoomTypeController::class, 'update'])->name('room-types.update');
    });

    // Owner + Manager + Accountant (finances & reports)
    Route::middleware('role:owner,manager,accountant')->group(function () {

        // Debt tracker
        Route::get('/finances/debt', [FinancesController::class, 'debt'])->name('finances.debt');

        // Finances (legacy redirect)
        Route::get('/finances', fn() => redirect()->route('reports.index'))->name('finances.index');

        // Reports hub + sub-reports
        Route::get('/reports',            [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/forecast',   [ReportController::class, 'forecast'])->name('reports.forecast');
        Route::get('/reports/revenue',    [ReportController::class, 'revenue'])->name('reports.revenue');
        Route::get('/reports/occupancy',  [ReportController::class, 'occupancy'])->name('reports.occupancy');
        Route::get('/reports/guests',     [ReportController::class, 'guests'])->name('reports.guests');
        Route::get('/reports/expenses',   [ReportController::class, 'expenses'])->name('reports.expenses');
        Route::get('/reports/unpaid',     [ReportController::class, 'unpaid'])->name('reports.unpaid');
        Route::get('/reports/sources',    [ReportController::class, 'sources'])->name('reports.sources');

        // Expenses
        Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
        Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
        Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
    });

    // Group bookings (all authenticated roles)
    Route::get('/group-bookings/create', [GroupBookingController::class, 'create'])->name('group-bookings.create');
    Route::post('/group-bookings', [GroupBookingController::class, 'store'])->name('group-bookings.store');
    Route::get('/group-bookings/{group}', [GroupBookingController::class, 'show'])->name('group-bookings.show');
    Route::get('/group-bookings/{group}/invoice', [GroupBookingController::class, 'invoice'])->name('group-bookings.invoice');
    Route::post('/group-bookings/{group}/check-in-all', [GroupBookingController::class, 'checkInAll'])->name('group-bookings.check-in-all');
    Route::post('/group-bookings/{group}/check-out-all', [GroupBookingController::class, 'checkOutAll'])->name('group-bookings.check-out-all');

    // Owner only
    Route::middleware('role:owner')->group(function () {
        // Restore soft-deleted booking
        Route::patch('/bookings/{id}/restore', [BookingController::class, 'restore'])->name('bookings.restore')->withTrashed();

        // Night audit
        Route::get('/audit',      [AuditController::class, 'show'])->name('audit.show');
        Route::post('/audit/run', [AuditController::class, 'run'])->name('audit.run');
        // Pricing rules
        Route::get('/pricing-rules', [PricingRuleController::class, 'index'])->name('pricing-rules.index');
        Route::get('/pricing-rules/create', [PricingRuleController::class, 'create'])->name('pricing-rules.create');
        Route::post('/pricing-rules', [PricingRuleController::class, 'store'])->name('pricing-rules.store');
        Route::get('/pricing-rules/{pricingRule}/edit', [PricingRuleController::class, 'edit'])->name('pricing-rules.edit');
        Route::put('/pricing-rules/{pricingRule}', [PricingRuleController::class, 'update'])->name('pricing-rules.update');
        Route::delete('/pricing-rules/{pricingRule}', [PricingRuleController::class, 'destroy'])->name('pricing-rules.destroy');

        // Promo codes (owner only)
        Route::get('/promo-codes', [PromoCodeController::class, 'index'])->name('promo-codes.index');
        Route::get('/promo-codes/create', [PromoCodeController::class, 'create'])->name('promo-codes.create');
        Route::post('/promo-codes', [PromoCodeController::class, 'store'])->name('promo-codes.store');
        Route::get('/promo-codes/{promoCode}/edit', [PromoCodeController::class, 'edit'])->name('promo-codes.edit');
        Route::put('/promo-codes/{promoCode}', [PromoCodeController::class, 'update'])->name('promo-codes.update');
        Route::delete('/promo-codes/{promoCode}', [PromoCodeController::class, 'destroy'])->name('promo-codes.destroy');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/mail', [UserMailController::class, 'index'])->name('users.mail');
        Route::post('/users/mail/send', [UserMailController::class, 'send'])->name('users.mail.send');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');

        // Delete guest reviews
        Route::delete('/reviews/{review}', [RoomController::class, 'deleteReview'])->name('reviews.destroy');
    });
});
