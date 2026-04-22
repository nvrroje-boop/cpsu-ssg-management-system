<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sections')) {
            Schema::table('sections', function (Blueprint $table): void {
                if (! Schema::hasColumn('sections', 'year_level')) {
                    $table->unsignedTinyInteger('year_level')->nullable()->after('section_name');
                }
            });

            DB::table('sections')
                ->select(['id', 'section_name'])
                ->orderBy('id')
                ->get()
                ->each(function (object $section): void {
                    if (preg_match('/\s([1-4])[A-Z]$/', (string) $section->section_name, $matches) === 1) {
                        DB::table('sections')
                            ->where('id', $section->id)
                            ->update(['year_level' => (int) $matches[1]]);
                    }
                });
        }

        if (Schema::hasTable('events')) {
            Schema::table('events', function (Blueprint $table): void {
                if (! Schema::hasColumn('events', 'attendance_token')) {
                    $table->string('attendance_token', 128)->nullable()->after('attendance_required');
                }

                if (! Schema::hasColumn('events', 'attendance_token_expires_at')) {
                    $table->timestamp('attendance_token_expires_at')->nullable()->after('attendance_token');
                }
            });

            try {
                Schema::table('events', function (Blueprint $table): void {
                    $table->unique('attendance_token');
                });
            } catch (\Throwable) {
                //
            }
        }

        if (Schema::hasTable('event_attendances')) {
            try {
                Schema::table('event_attendances', function (Blueprint $table): void {
                    $table->unique(['event_id', 'student_id']);
                });
            } catch (\Throwable) {
                //
            }
        }

        if (Schema::hasTable('system_notifications')) {
            Schema::table('system_notifications', function (Blueprint $table): void {
                if (! Schema::hasColumn('system_notifications', 'read_at')) {
                    $table->timestamp('read_at')->nullable()->after('message');
                }
            });

            if (Schema::hasColumn('system_notifications', 'is_read')) {
                DB::table('system_notifications')
                    ->where('is_read', true)
                    ->whereNull('read_at')
                    ->update(['read_at' => now()]);
            }

            try {
                Schema::table('system_notifications', function (Blueprint $table): void {
                    if (Schema::hasColumn('system_notifications', 'is_read')) {
                        $table->dropColumn('is_read');
                    }
                });
            } catch (\Throwable) {
                //
            }
        }

        if (Schema::hasTable('concerns')) {
            Schema::table('concerns', function (Blueprint $table): void {
                if (! Schema::hasColumn('concerns', 'source_type')) {
                    $table->string('source_type', 50)->nullable()->after('title');
                }

                if (! Schema::hasColumn('concerns', 'source_id')) {
                    $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
                }

                if (! Schema::hasColumn('concerns', 'reply_message')) {
                    $table->text('reply_message')->nullable()->after('description');
                }

                if (! Schema::hasColumn('concerns', 'replied_by_user_id')) {
                    $table->foreignId('replied_by_user_id')->nullable()->after('assigned_to_user_id')->constrained('users');
                }

                if (! Schema::hasColumn('concerns', 'replied_at')) {
                    $table->timestamp('replied_at')->nullable()->after('replied_by_user_id');
                }
            });
        }

        if (Schema::hasTable('email_logs') && Schema::hasColumn('email_logs', 'sent_at')) {
            try {
                Schema::table('email_logs', function (Blueprint $table): void {
                    $table->timestamp('sent_at')->nullable()->change();
                });
            } catch (\Throwable) {
                //
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('concerns')) {
            Schema::table('concerns', function (Blueprint $table): void {
                if (Schema::hasColumn('concerns', 'replied_by_user_id')) {
                    try {
                        $table->dropForeign(['replied_by_user_id']);
                    } catch (\Throwable) {
                        //
                    }
                }

                $columns = array_values(array_filter([
                    Schema::hasColumn('concerns', 'source_type') ? 'source_type' : null,
                    Schema::hasColumn('concerns', 'source_id') ? 'source_id' : null,
                    Schema::hasColumn('concerns', 'reply_message') ? 'reply_message' : null,
                    Schema::hasColumn('concerns', 'replied_by_user_id') ? 'replied_by_user_id' : null,
                    Schema::hasColumn('concerns', 'replied_at') ? 'replied_at' : null,
                ]));

                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }

        if (Schema::hasTable('system_notifications') && Schema::hasColumn('system_notifications', 'read_at')) {
            Schema::table('system_notifications', function (Blueprint $table): void {
                $table->dropColumn('read_at');
            });
        }

        if (Schema::hasTable('events')) {
            Schema::table('events', function (Blueprint $table): void {
                try {
                    $table->dropUnique(['attendance_token']);
                } catch (\Throwable) {
                    //
                }

                $columns = array_values(array_filter([
                    Schema::hasColumn('events', 'attendance_token') ? 'attendance_token' : null,
                    Schema::hasColumn('events', 'attendance_token_expires_at') ? 'attendance_token_expires_at' : null,
                ]));

                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }

        if (Schema::hasTable('event_attendances')) {
            try {
                Schema::table('event_attendances', function (Blueprint $table): void {
                    $table->dropUnique(['event_id', 'student_id']);
                });
            } catch (\Throwable) {
                //
            }
        }

        if (Schema::hasTable('sections') && Schema::hasColumn('sections', 'year_level')) {
            Schema::table('sections', function (Blueprint $table): void {
                $table->dropColumn('year_level');
            });
        }
    }
};
