<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('role_name', 50);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('departments')) {
            Schema::create('departments', function (Blueprint $table) {
                $table->id();
                $table->string('department_name', 100);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('sections')) {
            Schema::create('sections', function (Blueprint $table) {
                $table->id();
                $table->string('section_name', 50);
                $table->foreignId('department_id')->nullable()->constrained('departments');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('roles');
    }
};
