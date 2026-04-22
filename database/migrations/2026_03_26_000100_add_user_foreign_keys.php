<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('role_id')->references('id')->on('roles');
            });
        } catch (Throwable) {
            // Foreign key may already exist when bootstrapping from a manual schema.
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('department_id')->references('id')->on('departments');
            });
        } catch (Throwable) {
            // Foreign key may already exist when bootstrapping from a manual schema.
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('section_id')->references('id')->on('sections');
            });
        } catch (Throwable) {
            // Foreign key may already exist when bootstrapping from a manual schema.
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['role_id']);
            });
        } catch (Throwable) {
            // Ignore when the key does not exist.
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['department_id']);
            });
        } catch (Throwable) {
            // Ignore when the key does not exist.
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['section_id']);
            });
        } catch (Throwable) {
            // Ignore when the key does not exist.
        }
    }
};
