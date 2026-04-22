<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('email_logs')) {
            return;
        }

        // Add new columns if they don't exist
        if (! Schema::hasColumn('email_logs', 'status')) {
            Schema::table('email_logs', function (Blueprint $table) {
                $table->enum('status', ['queued', 'sent', 'failed', 'bounced'])->default('queued')->after('message');
                $table->text('error_message')->nullable()->after('status');
                $table->integer('retry_count')->default(0)->after('error_message');
                $table->timestamp('last_attempt_at')->nullable()->after('retry_count');
                $table->string('email_type')->nullable()->after('subject');
                $table->index(['status', 'sent_at']);
                $table->index(['user_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('email_logs', 'status')) {
            Schema::table('email_logs', function (Blueprint $table) {
                if (Schema::hasColumn('email_logs', 'status') && Schema::hasColumn('email_logs', 'sent_at')) {
                    try {
                        $table->dropIndex('email_logs_status_sent_at_index');
                    } catch (\Throwable) {
                        //
                    }
                }

                if (Schema::hasColumn('email_logs', 'user_id') && Schema::hasColumn('email_logs', 'status')) {
                    try {
                        $table->dropIndex('email_logs_user_id_status_index');
                    } catch (\Throwable) {
                        //
                    }
                }

                $table->dropColumn(['status', 'error_message', 'retry_count', 'last_attempt_at', 'email_type']);
            });
        }
    }
};
