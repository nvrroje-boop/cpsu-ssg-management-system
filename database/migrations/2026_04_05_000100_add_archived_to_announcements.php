<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('announcements') && !Schema::hasColumn('announcements', 'archived_at')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->timestamp('archived_at')->nullable()->after('updated_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('announcements')) {
            Schema::table('announcements', function (Blueprint $table) {
                $table->dropColumn('archived_at');
            });
        }
    }
};
