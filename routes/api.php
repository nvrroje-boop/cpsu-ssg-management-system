<?php

use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\ConcernController;
use App\Http\Controllers\Api\DashboardController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth')->group(function () {
    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);

    // Attendance
    Route::post('/attendance/scan', [AttendanceController::class, 'scan']);
    Route::get('/events/{id}/attendance', [EventController::class, 'getAttendance']);
    Route::get('/events/{id}/attendance/export', [EventController::class, 'exportAttendance']);

    // Concerns
    Route::post('/concerns/{id}/reply', [ConcernController::class, 'addReply']);
    Route::get('/concerns/{id}/replies', [ConcernController::class, 'getReplies']);

    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);
});
