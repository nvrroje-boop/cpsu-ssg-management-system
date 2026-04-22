<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('events', 'visibility')) {
            Schema::table('events', function (Blueprint $table) {
                $table->enum('visibility', ['public', 'private'])
                    ->default('public')
                    ->after('location');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('events', 'visibility')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropColumn('visibility');
            });
        }
    }
};
