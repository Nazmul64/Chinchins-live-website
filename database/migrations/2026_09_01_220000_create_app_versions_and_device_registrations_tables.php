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
        // 1. App Versions & OTA Updates Table
        if (!Schema::hasTable('app_versions')) {
            Schema::create('app_versions', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('version_code')->default(1);
                $table->string('version_name', 30)->default('1.0.0');
                $table->string('min_supported_version', 30)->default('1.0.0');
                $table->boolean('force_update')->default(false);
                $table->string('title')->default('New Update Available 🚀');
                $table->text('changelog')->nullable();
                $table->text('download_url')->nullable();
                $table->string('file_size', 50)->nullable()->default('25 MB');
                $table->string('platform', 20)->default('android'); // android, ios, all
                $table->boolean('is_active')->default(true);
                $table->json('remote_flags')->nullable(); // Dynamic features without rebuilding APK
                $table->timestamp('release_date')->nullable();
                $table->timestamps();

                $table->index(['platform', 'is_active']);
                $table->index('version_code');
            });
        }

        // 2. Device Registrations Table for Universal Push & FCM Dispatching
        if (!Schema::hasTable('device_registrations')) {
            Schema::create('device_registrations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
                $table->text('fcm_token');
                $table->string('device_id', 100)->nullable();
                $table->string('device_type', 30)->default('android'); // android, ios, web
                $table->string('device_brand', 80)->nullable(); // Samsung, Xiaomi, Vivo, Oppo, Apple, etc.
                $table->string('device_model', 100)->nullable();
                $table->string('os_version', 50)->nullable(); // Android 14, iOS 17.5
                $table->string('app_version', 30)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_active_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'is_active']);
                $table->index('device_type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_registrations');
        Schema::dropIfExists('app_versions');
    }
};
