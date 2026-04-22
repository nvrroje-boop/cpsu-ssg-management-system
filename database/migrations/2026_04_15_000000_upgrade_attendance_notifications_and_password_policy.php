<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table): void {
                if (! Schema::hasColumn('users', 'must_change_password')) {
                    $table->boolean('must_change_password')->default(false)->after('qr_token');
                }
            });

            DB::table('users')
                ->whereNull('qr_token')
                ->orderBy('id')
                ->get(['id'])
                ->each(function (object $user): void {
                    DB::table('users')
                        ->where('id', $user->id)
                        ->update([
                            'qr_token' => (string) Str::uuid(),
                            'updated_at' => now(),
                        ]);
                });
        }

        if (Schema::hasTable('events')) {
            Schema::table('events', function (Blueprint $table): void {
                if (! Schema::hasColumn('events', 'attendance_time_in_starts_at')) {
                    $table->time('attendance_time_in_starts_at')->nullable()->after('attendance_token_expires_at');
                }

                if (! Schema::hasColumn('events', 'attendance_time_in_ends_at')) {
                    $table->time('attendance_time_in_ends_at')->nullable()->after('attendance_time_in_starts_at');
                }

                if (! Schema::hasColumn('events', 'attendance_time_out_starts_at')) {
                    $table->time('attendance_time_out_starts_at')->nullable()->after('attendance_time_in_ends_at');
                }

                if (! Schema::hasColumn('events', 'attendance_time_out_ends_at')) {
                    $table->time('attendance_time_out_ends_at')->nullable()->after('attendance_time_out_starts_at');
                }

                if (! Schema::hasColumn('events', 'attendance_late_after')) {
                    $table->time('attendance_late_after')->nullable()->after('attendance_time_out_ends_at');
                }

                if (! Schema::hasColumn('events', 'attendance_active')) {
                    $table->boolean('attendance_active')->default(false)->after('attendance_late_after');
                }

                if (! Schema::hasColumn('events', 'attendance_started_at')) {
                    $table->timestamp('attendance_started_at')->nullable()->after('attendance_active');
                }

                if (! Schema::hasColumn('events', 'attendance_stopped_at')) {
                    $table->timestamp('attendance_stopped_at')->nullable()->after('attendance_started_at');
                }

                if (! Schema::hasColumn('events', 'attendance_started_by_user_id')) {
                    $table->foreignId('attendance_started_by_user_id')
                        ->nullable()
                        ->after('attendance_stopped_at')
                        ->constrained('users');
                }

                if (! Schema::hasColumn('events', 'event_reminder_sent_at')) {
                    $table->timestamp('event_reminder_sent_at')->nullable()->after('attendance_started_by_user_id');
                }

                if (! Schema::hasColumn('events', 'attendance_open_notified_at')) {
                    $table->timestamp('attendance_open_notified_at')->nullable()->after('event_reminder_sent_at');
                }

                if (! Schema::hasColumn('events', 'attendance_closing_notified_at')) {
                    $table->timestamp('attendance_closing_notified_at')->nullable()->after('attendance_open_notified_at');
                }

                if (! Schema::hasColumn('events', 'attendance_closed_notified_at')) {
                    $table->timestamp('attendance_closed_notified_at')->nullable()->after('attendance_closing_notified_at');
                }
            });

            DB::table('events')
                ->orderBy('id')
                ->get(['id', 'event_time'])
                ->each(function (object $event): void {
                    $baseTime = $event->event_time ?: '08:00:00';

                    try {
                        $moment = Carbon::createFromFormat('H:i:s', strlen((string) $baseTime) === 5 ? $baseTime.':00' : (string) $baseTime);
                    } catch (\Throwable) {
                        $moment = Carbon::createFromTimeString('08:00:00');
                    }

                    DB::table('events')
                        ->where('id', $event->id)
                        ->whereNull('attendance_time_in_starts_at')
                        ->update([
                            'attendance_time_in_starts_at' => $moment->copy()->subMinutes(30)->format('H:i:s'),
                            'attendance_time_in_ends_at' => $moment->copy()->addMinutes(10)->format('H:i:s'),
                            'attendance_time_out_starts_at' => $moment->copy()->addMinutes(110)->format('H:i:s'),
                            'attendance_time_out_ends_at' => $moment->copy()->addMinutes(150)->format('H:i:s'),
                            'attendance_late_after' => $moment->format('H:i:s'),
                            'updated_at' => now(),
                        ]);
                });
        }

        if (Schema::hasTable('event_attendances')) {
            Schema::table('event_attendances', function (Blueprint $table): void {
                if (! Schema::hasColumn('event_attendances', 'user_id')) {
                    $table->foreignId('user_id')->nullable()->after('event_id')->constrained('users');
                }

                if (! Schema::hasColumn('event_attendances', 'time_in')) {
                    $table->timestamp('time_in')->nullable()->after('token');
                }

                if (! Schema::hasColumn('event_attendances', 'time_out')) {
                    $table->timestamp('time_out')->nullable()->after('time_in');
                }

                if (! Schema::hasColumn('event_attendances', 'status')) {
                    $table->string('status', 30)->default('present')->after('time_out');
                }

                if (! Schema::hasColumn('event_attendances', 'attendance_method')) {
                    $table->string('attendance_method', 30)->default('kiosk')->after('status');
                }

                if (! Schema::hasColumn('event_attendances', 'recorded_by_user_id')) {
                    $table->foreignId('recorded_by_user_id')
                        ->nullable()
                        ->after('attendance_method')
                        ->constrained('users');
                }

                if (! Schema::hasColumn('event_attendances', 'last_scanned_at')) {
                    $table->timestamp('last_scanned_at')->nullable()->after('recorded_by_user_id');
                }
            });

            DB::table('event_attendances')
                ->whereNull('user_id')
                ->update([
                    'user_id' => DB::raw('student_id'),
                ]);

            DB::table('event_attendances')
                ->whereNull('time_in')
                ->update([
                    'time_in' => DB::raw('scanned_at'),
                ]);

            DB::table('event_attendances')
                ->whereNull('last_scanned_at')
                ->update([
                    'last_scanned_at' => DB::raw('scanned_at'),
                ]);

            DB::table('event_attendances')
                ->whereNull('recorded_by_user_id')
                ->update([
                    'recorded_by_user_id' => DB::raw('scanned_by_user_id'),
                ]);

            try {
                Schema::table('event_attendances', function (Blueprint $table): void {
                    $table->unique(['event_id', 'user_id']);
                });
            } catch (\Throwable) {
                //
            }

            try {
                Schema::table('event_attendances', function (Blueprint $table): void {
                    $table->index(['status', 'event_id']);
                });
            } catch (\Throwable) {
                //
            }
        }

        if (Schema::hasTable('system_notifications')) {
            Schema::table('system_notifications', function (Blueprint $table): void {
                if (! Schema::hasColumn('system_notifications', 'type')) {
                    $table->string('type', 40)->default('system')->after('message');
                }

                if (! Schema::hasColumn('system_notifications', 'target_role')) {
                    $table->string('target_role', 20)->nullable()->after('type');
                }

                if (! Schema::hasColumn('system_notifications', 'event_id')) {
                    $table->foreignId('event_id')->nullable()->after('target_role')->constrained('events');
                }

                if (! Schema::hasColumn('system_notifications', 'link')) {
                    $table->string('link', 255)->nullable()->after('event_id');
                }
            });

            try {
                Schema::table('system_notifications', function (Blueprint $table): void {
                    $table->index(['user_id', 'read_at']);
                });
            } catch (\Throwable) {
                //
            }

            try {
                Schema::table('system_notifications', function (Blueprint $table): void {
                    $table->index(['target_role', 'read_at']);
                });
            } catch (\Throwable) {
                //
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('system_notifications')) {
            Schema::table('system_notifications', function (Blueprint $table): void {
                foreach (['event_id'] as $foreignColumn) {
                    try {
                        $table->dropForeign([$foreignColumn]);
                    } catch (\Throwable) {
                        //
                    }
                }

                foreach ([
                    ['user_id', 'read_at'],
                    ['target_role', 'read_at'],
                ] as $indexColumns) {
                    try {
                        $table->dropIndex($indexColumns);
                    } catch (\Throwable) {
                        //
                    }
                }

                $columns = array_values(array_filter([
                    Schema::hasColumn('system_notifications', 'type') ? 'type' : null,
                    Schema::hasColumn('system_notifications', 'target_role') ? 'target_role' : null,
                    Schema::hasColumn('system_notifications', 'event_id') ? 'event_id' : null,
                    Schema::hasColumn('system_notifications', 'link') ? 'link' : null,
                ]));

                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }

        if (Schema::hasTable('event_attendances')) {
            Schema::table('event_attendances', function (Blueprint $table): void {
                foreach (['user_id', 'recorded_by_user_id'] as $foreignColumn) {
                    try {
                        $table->dropForeign([$foreignColumn]);
                    } catch (\Throwable) {
                        //
                    }
                }

                try {
                    $table->dropUnique(['event_id', 'user_id']);
                } catch (\Throwable) {
                    //
                }

                try {
                    $table->dropIndex(['status', 'event_id']);
                } catch (\Throwable) {
                    //
                }

                $columns = array_values(array_filter([
                    Schema::hasColumn('event_attendances', 'user_id') ? 'user_id' : null,
                    Schema::hasColumn('event_attendances', 'time_in') ? 'time_in' : null,
                    Schema::hasColumn('event_attendances', 'time_out') ? 'time_out' : null,
                    Schema::hasColumn('event_attendances', 'status') ? 'status' : null,
                    Schema::hasColumn('event_attendances', 'attendance_method') ? 'attendance_method' : null,
                    Schema::hasColumn('event_attendances', 'recorded_by_user_id') ? 'recorded_by_user_id' : null,
                    Schema::hasColumn('event_attendances', 'last_scanned_at') ? 'last_scanned_at' : null,
                ]));

                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }

        if (Schema::hasTable('events')) {
            Schema::table('events', function (Blueprint $table): void {
                try {
                    $table->dropForeign(['attendance_started_by_user_id']);
                } catch (\Throwable) {
                    //
                }

                $columns = array_values(array_filter([
                    Schema::hasColumn('events', 'attendance_time_in_starts_at') ? 'attendance_time_in_starts_at' : null,
                    Schema::hasColumn('events', 'attendance_time_in_ends_at') ? 'attendance_time_in_ends_at' : null,
                    Schema::hasColumn('events', 'attendance_time_out_starts_at') ? 'attendance_time_out_starts_at' : null,
                    Schema::hasColumn('events', 'attendance_time_out_ends_at') ? 'attendance_time_out_ends_at' : null,
                    Schema::hasColumn('events', 'attendance_late_after') ? 'attendance_late_after' : null,
                    Schema::hasColumn('events', 'attendance_active') ? 'attendance_active' : null,
                    Schema::hasColumn('events', 'attendance_started_at') ? 'attendance_started_at' : null,
                    Schema::hasColumn('events', 'attendance_stopped_at') ? 'attendance_stopped_at' : null,
                    Schema::hasColumn('events', 'attendance_started_by_user_id') ? 'attendance_started_by_user_id' : null,
                    Schema::hasColumn('events', 'event_reminder_sent_at') ? 'event_reminder_sent_at' : null,
                    Schema::hasColumn('events', 'attendance_open_notified_at') ? 'attendance_open_notified_at' : null,
                    Schema::hasColumn('events', 'attendance_closing_notified_at') ? 'attendance_closing_notified_at' : null,
                    Schema::hasColumn('events', 'attendance_closed_notified_at') ? 'attendance_closed_notified_at' : null,
                ]));

                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'must_change_password')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropColumn('must_change_password');
            });
        }
    }
};
