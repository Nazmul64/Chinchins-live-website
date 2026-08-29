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
        // 1. Add presence & device token fields to users table if not already present
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'last_seen_at')) {
                $table->timestamp('last_seen_at')->nullable()->after('is_active');
            }
            if (!Schema::hasColumn('users', 'online_status')) {
                $table->string('online_status', 30)->default('offline')->after('last_seen_at'); // online, offline, inactive, busy, in_call
            }
            if (!Schema::hasColumn('users', 'fcm_token')) {
                $table->text('fcm_token')->nullable()->after('online_status');
            }
            if (!Schema::hasColumn('users', 'device_token')) {
                $table->string('device_token', 255)->nullable()->after('fcm_token');
            }
            if (!Schema::hasColumn('users', 'device_type')) {
                $table->string('device_type', 30)->nullable()->after('device_token'); // android, ios, web
            }
        });

        // 2. Create user_presences table for logging and real-time session tracking
        if (!Schema::hasTable('user_presences')) {
            Schema::create('user_presences', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('status', 30)->default('offline'); // online, offline, inactive, busy, in_call
                $table->boolean('is_online')->default(false);
                $table->timestamp('last_seen_at')->nullable();
                $table->string('device_type', 30)->nullable();
                $table->text('fcm_token')->nullable();
                $table->string('device_token', 255)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status']);
                $table->index(['is_online', 'last_seen_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_presences');

        Schema::table('users', function (Blueprint $table) {
            $cols = ['last_seen_at', 'online_status', 'fcm_token', 'device_token', 'device_type'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('users', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
