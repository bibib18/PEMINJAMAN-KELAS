<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ScheduleController;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(auth()->check() ? 'dashboard' : 'login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/jadwal', [ScheduleController::class, 'index'])->name('schedule.index');

    // Ruang kelas: semua pengguna boleh melihat, hanya admin yang mengelola.
    Route::get('/ruang', [RoomController::class, 'index'])->name('rooms.index');

    Route::middleware(EnsureAdmin::class)->group(function () {
        Route::get('/ruang/tambah', [RoomController::class, 'create'])->name('rooms.create');
        Route::post('/ruang', [RoomController::class, 'store'])->name('rooms.store');
        Route::get('/ruang/{room}/ubah', [RoomController::class, 'edit'])->name('rooms.edit');
        Route::put('/ruang/{room}', [RoomController::class, 'update'])->name('rooms.update');
        Route::delete('/ruang/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');

        Route::get('/persetujuan', [ApprovalController::class, 'index'])->name('approvals.index');
        Route::patch('/persetujuan/{booking}/setujui', [ApprovalController::class, 'approve'])->name('approvals.approve');
        Route::patch('/persetujuan/{booking}/tolak', [ApprovalController::class, 'reject'])->name('approvals.reject');
    });

    Route::get('/peminjaman', [BookingController::class, 'index'])->name('bookings.index');
    Route::get('/peminjaman/ajukan', [BookingController::class, 'create'])->name('bookings.create');
    Route::get('/peminjaman/ketersediaan', [BookingController::class, 'availability'])->name('bookings.availability');
    Route::post('/peminjaman', [BookingController::class, 'store'])->name('bookings.store');
    Route::get('/peminjaman/{booking}', [BookingController::class, 'show'])->name('bookings.show');
    Route::patch('/peminjaman/{booking}/batal', [BookingController::class, 'cancel'])->name('bookings.cancel');
});
