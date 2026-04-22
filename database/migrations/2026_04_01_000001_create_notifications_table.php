<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('announcement_id')->constrained('announcements')->onDelete('cascade');
                $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
                $table->enum('status', ['queued', 'sent', 'failed', 'bounced'])->default('queued');
                $table->string('email')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamp('sent_at')->nullable();
                $table->integer('retry_count')->default(0);
                $table->timestamp('last_attempt_at')->nullable();
                $table->timestamps();

                $table->unique(['announcement_id', 'student_id']);
                $table->index('status');
                $table->index('announcement_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
