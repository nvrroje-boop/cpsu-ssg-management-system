<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, 'index'])->name('welcome');
Route::middleware('auth')->group(function (): void {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
});
Route::get('/attendance/qr/{token}', [AttendanceController::class, 'showQr'])->name('attendance.qr.show');
Route::get('/attendance/qr/{token}/image', [AttendanceController::class, 'qrImage'])->name('attendance.qr.image');
Route::get('/attendance/qr/{token}/download', [AttendanceController::class, 'downloadQr'])->name('attendance.qr.download');

Route::middleware(['auth', 'role:admin,officer'])->group(function (): void {
    Route::match(['get', 'post'], '/attendance/scan/{token}', [AttendanceController::class, 'scan'])
        ->name('attendance.scan');
});
