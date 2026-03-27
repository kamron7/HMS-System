<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FinancesController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\HousekeepingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomTypeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Auth (unauthenticated)
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// All authenticated routes
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/', fn() => redirect('/dashboard'));

    // Bookings
    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::get('/bookings/{booking}/edit', [BookingController::class, 'edit'])->name('bookings.edit');
    Route::put('/bookings/{booking}', [BookingController::class, 'update'])->name('bookings.update');
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');
    Route::post('/bookings/{booking}/status', [BookingController::class, 'updateStatus'])->name('bookings.status');

    // Guests — /guests/search MUST be before /guests/{guest}
    Route::get('/guests/search', [GuestController::class, 'search'])->name('guests.search');
    Route::get('/guests', [GuestController::class, 'index'])->name('guests.index');
    Route::get('/guests/create', [GuestController::class, 'create'])->name('guests.create');
    Route::post('/guests', [GuestController::class, 'store'])->name('guests.store');
    Route::get('/guests/{guest}', [GuestController::class, 'show'])->name('guests.show');
    Route::get('/guests/{guest}/edit', [GuestController::class, 'edit'])->name('guests.edit');
    Route::put('/guests/{guest}', [GuestController::class, 'update'])->name('guests.update');

    // Housekeeping (all roles)
    Route::get('/housekeeping', [HousekeepingController::class, 'index'])->name('housekeeping.index');
    Route::patch('/housekeeping/{room}', [HousekeepingController::class, 'update'])->name('housekeeping.update');

    // Payments (all roles) — append-only, no edit or delete
    Route::post('/bookings/{booking}/payments', [PaymentController::class, 'store'])->name('payments.store');

    // Owner + Manager only
    Route::middleware('role:owner,manager')->group(function () {

        // Rooms available (JSON - must be before rooms/{room})
        Route::get('/rooms/available', [RoomController::class, 'available'])->name('rooms.available');

        // Rooms
        Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
        Route::get('/rooms/create', [RoomController::class, 'create'])->name('rooms.create');
        Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
        Route::get('/rooms/{room}/edit', [RoomController::class, 'edit'])->name('rooms.edit');
        Route::put('/rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');

        // Room Types
        Route::get('/room-types', [RoomTypeController::class, 'index'])->name('room-types.index');
        Route::get('/room-types/create', [RoomTypeController::class, 'create'])->name('room-types.create');
        Route::post('/room-types', [RoomTypeController::class, 'store'])->name('room-types.store');
        Route::get('/room-types/{roomType}/edit', [RoomTypeController::class, 'edit'])->name('room-types.edit');
        Route::put('/room-types/{roomType}', [RoomTypeController::class, 'update'])->name('room-types.update');

        // Finances
        Route::get('/finances', [FinancesController::class, 'index'])->name('finances.index');

        // Expenses
        Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('/expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
        Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
        Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
    });

    // Owner only
    Route::middleware('role:owner')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    });
});
