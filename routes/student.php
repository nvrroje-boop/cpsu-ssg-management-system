<?php

use App\Http\Controllers\Student\AnnouncementController;
use App\Http\Controllers\Student\ConcernController;
use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\EventAttendanceController;
use App\Http\Controllers\Student\EventController;
use Illuminate\Support\Facades\Route;

Route::prefix('student')
    ->name('student.')
    ->middleware(['auth', 'password.changed', 'role:student'])
    ->group(function (): void {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
        Route::patch('/profile', [DashboardController::class, 'updateProfile'])->name('profile.update');
        Route::post('/profile/password', [DashboardController::class, 'changePassword'])->name('profile.password');
        Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
        Route::get('/announcements/{announcement}', [AnnouncementController::class, 'show'])->name('announcements.show');
        Route::get('/events', [EventController::class, 'index'])->name('events.index');
        Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
        Route::post('/events/{event}/attend', [EventController::class, 'attend'])->name('events.attend');
        Route::get('/events/{event}/self-scan', [EventAttendanceController::class, 'selfScan'])->name('events.self-scan');

        Route::controller(ConcernController::class)
            ->prefix('concerns')
            ->name('concerns.')
            ->group(function (): void {
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/', 'index')->name('index');
                Route::get('/{concern}', 'show')->name('show');
            });
    });
