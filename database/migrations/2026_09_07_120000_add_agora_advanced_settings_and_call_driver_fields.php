<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Enhance Streaming Settings
        if (Schema::hasTable('streaming_settings')) {
            Schema::table('streaming_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('streaming_settings', 'is_agora_enabled')) {
                    $table->boolean('is_agora_enabled')->default(true)->after('active_driver');
                }
                if (!Schema::hasColumn('streaming_settings', 'agora_debug_mode')) {
                    $table->boolean('agora_debug_mode')->default(false)->after('agora_manual_channel');
                }
                if (!Schema::hasColumn('streaming_settings', 'agora_sdk_logging')) {
                    $table->boolean('agora_sdk_logging')->default(true)->after('agora_debug_mode');
                }
                if (!Schema::hasColumn('streaming_settings', 'agora_log_level')) {
                    $table->string('agora_log_level', 20)->default('info')->after('agora_sdk_logging'); // error, warning, info, verbose
                }
            });
        }

        // 2. Enhance Calls table
        if (Schema::hasTable('calls')) {
            Schema::table('calls', function (Blueprint $table) {
                if (!Schema::hasColumn('calls', 'driver')) {
                    $table->string('driver', 30)->default('vps_webrtc')->after('call_type');
                }
                if (!Schema::hasColumn('calls', 'channel_name')) {
                    $table->string('channel_name', 191)->nullable()->after('driver');
                }
                if (!Schema::hasColumn('calls', 'caller_uid')) {
                    $table->unsignedBigInteger('caller_uid')->nullable()->after('channel_name');
                }
                if (!Schema::hasColumn('calls', 'receiver_uid')) {
                    $table->unsignedBigInteger('receiver_uid')->nullable()->after('caller_uid');
                }
            });
        }

        // 3. Enhance Call Sessions table
        if (Schema::hasTable('call_sessions')) {
            Schema::table('call_sessions', function (Blueprint $table) {
                if (!Schema::hasColumn('call_sessions', 'driver')) {
                    $table->string('driver', 30)->default('vps_webrtc')->after('call_type');
                }
                if (!Schema::hasColumn('call_sessions', 'caller_uid')) {
                    $table->unsignedBigInteger('caller_uid')->nullable()->after('driver');
                }
                if (!Schema::hasColumn('call_sessions', 'receiver_uid')) {
                    $table->unsignedBigInteger('receiver_uid')->nullable()->after('caller_uid');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('streaming_settings')) {
            Schema::table('streaming_settings', function (Blueprint $table) {
                $table->dropColumn(['is_agora_enabled', 'agora_debug_mode', 'agora_sdk_logging', 'agora_log_level']);
            });
        }

        if (Schema::hasTable('calls')) {
            Schema::table('calls', function (Blueprint $table) {
                $table->dropColumn(['driver', 'channel_name', 'caller_uid', 'receiver_uid']);
            });
        }

        if (Schema::hasTable('call_sessions')) {
            Schema::table('call_sessions', function (Blueprint $table) {
                $table->dropColumn(['driver', 'caller_uid', 'receiver_uid']);
            });
        }
    }
};
