<?php

use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\EventAttendanceController as EventAttendanceActionsController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\ConcernController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Student\DashboardController as ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('officer')
    ->name('officer.')
    ->middleware(['auth', 'password.changed', 'role:officer'])
    ->group(function (): void {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [ProfileController::class, 'profile'])->name('profile');
        Route::patch('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
        Route::post('/profile/password', [ProfileController::class, 'changePassword'])->name('profile.password');

        Route::controller(AnnouncementController::class)
            ->prefix('announcements')
            ->name('announcements.')
            ->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{announcement}', 'show')->name('show');
                Route::get('/{announcement}/edit', 'edit')->name('edit');
                Route::put('/{announcement}', 'update')->name('update');
                Route::delete('/{announcement}', 'destroy')->name('destroy');
                Route::post('/{announcement}/archive', 'archive')->name('archive');
                Route::post('/{announcement}/unarchive', 'unarchive')->name('unarchive');
                Route::post('/{announcement}/send', 'send')->name('send');
                Route::post('/{announcement}/resend-failed', 'resendFailed')->name('resend-failed');
                Route::post('/target-preview', 'getTargetPreview')->name('target-preview');
            });

        Route::controller(EventController::class)
            ->prefix('events')
            ->name('events.')
            ->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('/create', 'create')->name('create');
                Route::post('/', 'store')->name('store');
                Route::get('/{event}', 'show')->name('show');
                Route::get('/{event}/edit', 'edit')->name('edit');
                Route::put('/{event}', 'update')->name('update');
                Route::post('/{event}/resend-qr-emails', 'resendQrEmails')->name('resend-qr-emails');
                Route::delete('/{event}', 'destroy')->name('destroy');
                Route::post('/{event}/attendance/start', [EventAttendanceActionsController::class, 'start'])->name('attendance.start');
                Route::post('/{event}/attendance/stop', [EventAttendanceActionsController::class, 'stop'])->name('attendance.stop');
                Route::post('/{event}/attendance/extend', [EventAttendanceActionsController::class, 'extend'])->name('attendance.extend');
                Route::post('/{event}/attendance/scan', [EventAttendanceActionsController::class, 'scan'])->name('attendance.scan');
                Route::post('/{event}/attendance/manual', [EventAttendanceActionsController::class, 'upsert'])->name('attendance.manual');
            });

        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');

        Route::controller(ConcernController::class)
            ->prefix('concerns')
            ->name('concerns.')
            ->group(function (): void {
                Route::get('/', 'index')->name('index');
                Route::get('/{concern}', 'show')->name('show');
                Route::put('/{concern}', 'update')->name('update');
            });
    });
