<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('events')) {
            Schema::create('events', function (Blueprint $table) {
                $table->id();
                $table->string('event_title')->nullable();
                $table->text('event_description')->nullable();
                $table->date('event_date')->nullable();
                $table->time('event_time')->nullable();
                $table->string('location')->nullable();
                $table->enum('visibility', ['public', 'private'])->default('public');
                $table->boolean('attendance_required')->default(true);
                $table->foreignId('department_id')->nullable()->constrained('departments');
                $table->foreignId('created_by_user_id')->constrained('users');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
