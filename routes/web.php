<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BillingGroupController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\RoomCalendarController;
use App\Http\Controllers\RoomTypeController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Hotel Operations (Admin + Receptionist)
    Route::middleware('role:admin,receptionist')->group(function () {
        Route::resource('guests', GuestController::class)->only(['index', 'show']);
        Route::resource('room-types', RoomTypeController::class);
        Route::resource('rooms', RoomController::class);
        Route::get('room-calendar', [RoomCalendarController::class, 'index'])->name('room-calendar.index');

        Route::resource('bookings', BookingController::class);
        Route::post('bookings/{booking}/check-in', [BookingController::class, 'checkIn'])->name('bookings.check-in');
        Route::get('bookings/{booking}/check-in/receipt', [BookingController::class, 'printCheckInReceipt'])->name('bookings.check-in.receipt');
        Route::get('bookings/{booking}/check-in/receipt/download', [BookingController::class, 'downloadCheckInReceipt'])->name('bookings.check-in.receipt.download');
        Route::post('bookings/{booking}/check-out', [BookingController::class, 'checkOut'])->name('bookings.check-out');
        Route::post('bookings/{booking}/early-check-out', [BookingController::class, 'earlyCheckOut'])->name('bookings.early-check-out');
        Route::post('bookings/{booking}/payments', [BookingController::class, 'recordPayment'])->name('bookings.payments.store');
        Route::get('billing-groups', [BillingGroupController::class, 'index'])->name('billing-groups.index');
        Route::get('billing-groups/create', [BillingGroupController::class, 'create'])->name('billing-groups.create');
        Route::post('billing-groups', [BillingGroupController::class, 'store'])->name('billing-groups.store');
        Route::get('billing-groups/{billingGroup}', [BillingGroupController::class, 'show'])->name('billing-groups.show');
        Route::post('billing-groups/{billingGroup}/payments', [BillingGroupController::class, 'recordPayment'])->name('billing-groups.payments.store');
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
        Route::get('reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
        Route::get('shifts', [ShiftController::class, 'index'])->name('shifts.index');
        Route::post('shifts', [ShiftController::class, 'open'])->name('shifts.open');
        Route::get('shifts/{shift}', [ShiftController::class, 'show'])->name('shifts.show');
        Route::post('shifts/{shift}/close', [ShiftController::class, 'close'])->name('shifts.close');
    });

    Route::middleware('role:admin,warehouse')->prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [InventoryController::class, 'index'])->name('index');
        Route::get('items/create', [InventoryController::class, 'createItem'])->name('items.create');
        Route::post('items', [InventoryController::class, 'storeItem'])->name('items.store');
        Route::get('items/{item}/edit', [InventoryController::class, 'editItem'])->name('items.edit');
        Route::put('items/{item}', [InventoryController::class, 'updateItem'])->name('items.update');
        Route::delete('items/{item}', [InventoryController::class, 'destroyItem'])->name('items.destroy');
        Route::get('categories', [InventoryController::class, 'categories'])->name('categories.index');
        Route::post('categories', [InventoryController::class, 'storeCategory'])->name('categories.store');
        Route::put('categories/{category}', [InventoryController::class, 'updateCategory'])->name('categories.update');
        Route::delete('categories/{category}', [InventoryController::class, 'destroyCategory'])->name('categories.destroy');
        Route::get('transactions', [InventoryController::class, 'transactions'])->name('transactions.index');
        Route::get('transactions/create', [InventoryController::class, 'createTransaction'])->name('transactions.create');
        Route::post('transactions', [InventoryController::class, 'storeTransaction'])->name('transactions.store');
    });

    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->except(['show', 'destroy']);
        Route::post('users/{user}/deactivate', [UserController::class, 'deactivate'])->name('users.deactivate');
    });
});
