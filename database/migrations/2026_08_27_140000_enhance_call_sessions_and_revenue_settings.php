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
        // 1. Add fields to call_sessions table
        Schema::table('call_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('call_sessions', 'call_type')) {
                $table->enum('call_type', ['video', 'audio'])->default('video')->after('channel_name');
            }
            if (!Schema::hasColumn('call_sessions', 'is_free_trial')) {
                $table->boolean('is_free_trial')->default(false)->after('rate_per_minute');
            }
            if (!Schema::hasColumn('call_sessions', 'free_duration_seconds')) {
                $table->unsignedInteger('free_duration_seconds')->default(0)->after('is_free_trial');
            }
            if (!Schema::hasColumn('call_sessions', 'host_earned_coins')) {
                $table->unsignedBigInteger('host_earned_coins')->default(0)->after('coins_deducted');
            }
            if (!Schema::hasColumn('call_sessions', 'admin_revenue_coins')) {
                $table->unsignedBigInteger('admin_revenue_coins')->default(0)->after('host_earned_coins');
            }
            if (!Schema::hasColumn('call_sessions', 'is_random_match')) {
                $table->boolean('is_random_match')->default(false)->after('admin_revenue_coins');
            }
        });

        // 2. Add free_calls_used and gender to users table if not exists
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'free_calls_used')) {
                $table->unsignedInteger('free_calls_used')->default(0)->after('coins');
            }
            if (!Schema::hasColumn('users', 'gender')) {
                $table->string('gender', 20)->default('female')->after('display_name');
            }
        });

        // 3. Call Settings Table
        if (!Schema::hasTable('call_settings')) {
            Schema::create('call_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('group')->default('call');
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_settings');

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'free_calls_used')) {
                $table->dropColumn('free_calls_used');
            }
        });

        Schema::table('call_sessions', function (Blueprint $table) {
            $cols = ['call_type', 'is_free_trial', 'free_duration_seconds', 'host_earned_coins', 'admin_revenue_coins', 'is_random_match'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('call_sessions', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
