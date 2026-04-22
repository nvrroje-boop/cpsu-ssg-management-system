<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add notification system columns to announcements table
        if (Schema::hasTable('announcements') && !Schema::hasColumn('announcements', 'message')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->longText('message')->nullable()->after('title');
                $table->json('target_filters')->nullable()->after('message');
                $table->timestamp('send_at')->nullable()->after('target_filters');
                $table->timestamp('sent_at')->nullable()->after('send_at');
                $table->enum('status', ['draft', 'scheduled', 'sent', 'failed'])->default('draft')->after('sent_at');
                $table->integer('total_recipients')->default(0)->after('status');
                $table->integer('sent_count')->default(0)->after('total_recipients');
                $table->integer('failed_count')->default(0)->after('sent_count');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('announcements')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->dropColumn([
                    'message',
                    'target_filters',
                    'send_at',
                    'sent_at',
                    'status',
                    'total_recipients',
                    'sent_count',
                    'failed_count',
                ]);
            });
        }
    }
};
