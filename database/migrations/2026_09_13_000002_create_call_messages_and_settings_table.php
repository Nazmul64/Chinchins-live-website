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
        if (!Schema::hasTable('call_messages')) {
            Schema::create('call_messages', function (Blueprint $table) {
                $table->id();
                $table->string('call_session_id', 100)->nullable()->index();
                $table->unsignedBigInteger('sender_id')->index();
                $table->unsignedBigInteger('receiver_id')->index();
                $table->enum('type', ['text', 'image', 'gift', 'system'])->default('text');
                $table->text('message')->nullable();
                $table->string('image_url')->nullable();
                $table->string('file_path')->nullable();
                $table->json('metadata')->nullable();
                $table->boolean('is_read')->default(false);
                $table->timestamps();

                $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // Add security protection columns to app_settings if table exists
        if (Schema::hasTable('app_settings')) {
            Schema::table('app_settings', function (Blueprint $table) {
                if (!Schema::hasColumn('app_settings', 'screenshot_protection_enabled')) {
                    $table->boolean('screenshot_protection_enabled')->default(true)->after('id');
                }
                if (!Schema::hasColumn('app_settings', 'screen_recording_protection_enabled')) {
                    $table->boolean('screen_recording_protection_enabled')->default(true)->after('screenshot_protection_enabled');
                }
                if (!Schema::hasColumn('app_settings', 'camera_filters_enabled')) {
                    $table->boolean('camera_filters_enabled')->default(true)->after('screen_recording_protection_enabled');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('call_messages');
        if (Schema::hasTable('app_settings')) {
            Schema::table('app_settings', function (Blueprint $table) {
                if (Schema::hasColumn('app_settings', 'screenshot_protection_enabled')) {
                    $table->dropColumn('screenshot_protection_enabled');
                }
                if (Schema::hasColumn('app_settings', 'screen_recording_protection_enabled')) {
                    $table->dropColumn('screen_recording_protection_enabled');
                }
                if (Schema::hasColumn('app_settings', 'camera_filters_enabled')) {
                    $table->dropColumn('camera_filters_enabled');
                }
            });
        }
    }
};
