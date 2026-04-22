<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('event_attendances')) {
            Schema::create('event_attendances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('event_id')->constrained('events');
                $table->foreignId('student_id')->constrained('users');
                $table->timestamp('scanned_at')->useCurrent();
                $table->timestamps();
                $table->unique(['event_id', 'student_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('event_attendances');
    }
};
