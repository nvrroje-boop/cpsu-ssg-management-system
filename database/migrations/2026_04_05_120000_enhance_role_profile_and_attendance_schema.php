<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table): void {
                if (! Schema::hasColumn('roles', 'name')) {
                    $table->string('name', 50)->nullable()->after('id');
                }
            });

            DB::table('roles')
                ->whereNull('name')
                ->update([
                    'name' => DB::raw('role_name'),
                ]);
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table): void {
                if (! Schema::hasColumn('users', 'phone')) {
                    $table->string('phone', 30)->nullable()->after('email');
                }

                if (! Schema::hasColumn('users', 'course')) {
                    $table->string('course', 100)->nullable()->after('phone');
                }
            });
        }

        if (Schema::hasTable('event_attendances')) {
            Schema::table('event_attendances', function (Blueprint $table): void {
                if (! Schema::hasColumn('event_attendances', 'token')) {
                    $table->string('token', 128)->nullable()->after('student_id');
                }

                if (! Schema::hasColumn('event_attendances', 'scanned_by_user_id')) {
                    $table->foreignId('scanned_by_user_id')
                        ->nullable()
                        ->after('token')
                        ->constrained('users');
                }
            });

            try {
                Schema::table('event_attendances', function (Blueprint $table): void {
                    $table->unique('token');
                });
            } catch (\Throwable) {
                //
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('event_attendances')) {
            Schema::table('event_attendances', function (Blueprint $table): void {
                if (Schema::hasColumn('event_attendances', 'scanned_by_user_id')) {
                    try {
                        $table->dropForeign(['scanned_by_user_id']);
                    } catch (\Throwable) {
                        //
                    }
                }

                try {
                    $table->dropUnique(['token']);
                } catch (\Throwable) {
                    //
                }

                $columns = array_values(array_filter([
                    Schema::hasColumn('event_attendances', 'token') ? 'token' : null,
                    Schema::hasColumn('event_attendances', 'scanned_by_user_id') ? 'scanned_by_user_id' : null,
                ]));

                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table): void {
                $columns = array_values(array_filter([
                    Schema::hasColumn('users', 'phone') ? 'phone' : null,
                    Schema::hasColumn('users', 'course') ? 'course' : null,
                ]));

                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }

        if (Schema::hasTable('roles') && Schema::hasColumn('roles', 'name')) {
            Schema::table('roles', function (Blueprint $table): void {
                $table->dropColumn('name');
            });
        }
    }
};
