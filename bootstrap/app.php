<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnforcePasswordChange;
use App\Http\Middleware\OfficerMiddleware;
use App\Http\Middleware\StudentMiddleware;
use App\Models\Notification;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Request;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: [
            __DIR__.'/../routes/web.php',
            __DIR__.'/../routes/auth.php',
            __DIR__.'/../routes/admin.php',
            __DIR__.'/../routes/officer.php',
            __DIR__.'/../routes/student.php',
        ],
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('announcements:process')->everyMinute();
        $schedule->command('attendance:dispatch-alerts')->everyMinute();

        $schedule->call(function (): void {
            Notification::query()
                ->where('created_at', '<', now()->subMonths(3))
                ->delete();

            Log::info('Cleaned up old notification logs');
        })->daily();
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
        );

        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'officer' => OfficerMiddleware::class,
            'password.changed' => EnforcePasswordChange::class,
            'role' => CheckRole::class,
            'student' => StudentMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
