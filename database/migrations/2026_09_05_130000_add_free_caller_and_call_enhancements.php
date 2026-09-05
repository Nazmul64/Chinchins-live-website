<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add is_free_caller to users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_free_caller')) {
                $table->boolean('is_free_caller')->default(false)->after('auto_call_enabled');
            }
        });

        // 2. Add free caller tracking fields to call_sessions
        Schema::table('call_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('call_sessions', 'is_caller_free')) {
                $table->boolean('is_caller_free')->default(false)->after('is_free_trial');
            }
            if (!Schema::hasColumn('call_sessions', 'charged_user_id')) {
                $table->unsignedBigInteger('charged_user_id')->nullable()->after('is_caller_free');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_free_caller')) {
                $table->dropColumn('is_free_caller');
            }
        });

        Schema::table('call_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('call_sessions', 'is_caller_free')) {
                $table->dropColumn('is_caller_free');
            }
            if (Schema::hasColumn('call_sessions', 'charged_user_id')) {
                $table->dropColumn('charged_user_id');
            }
        });
    }
};
