<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('announcements')) {
            Schema::create('announcements', function (Blueprint $table) {
                $table->id();
                $table->string('title')->nullable();
                $table->text('description')->nullable();
                $table->enum('visibility', ['public', 'private'])->default('public');
                $table->foreignId('department_id')->nullable()->constrained('departments');
                $table->foreignId('created_by_user_id')->constrained('users');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
