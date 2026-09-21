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
        // 1. Firebase Apps Table
        if (!Schema::hasTable('firebase_apps')) {
            Schema::create('firebase_apps', function (Blueprint $table) {
                $table->id();
                $table->string('app_name', 150);
                $table->string('package_name', 200)->unique();
                $table->text('service_account_json')->nullable();
                $table->string('service_account_path')->nullable();
                $table->text('server_key')->nullable();
                $table->string('project_id', 150)->nullable();
                $table->string('client_email', 200)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index('is_active');
            });
        }

        // 2. Push Notifications Logs / History Table
        if (!Schema::hasTable('push_notifications')) {
            Schema::create('push_notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('firebase_app_id')->nullable()->constrained('firebase_apps')->onDelete('set null');
                $table->string('platform', 30)->default('firebase'); // onesignal, firebase, both
                $table->string('title');
                $table->text('message');
                $table->text('image_url')->nullable();
                $table->string('action_url')->nullable();
                $table->string('send_to', 30)->default('all'); // all, specific
                $table->json('target_user_ids')->nullable();
                $table->unsignedInteger('sent_count')->default(0);
                $table->unsignedInteger('failed_count')->default(0);
                $table->unsignedInteger('total_target')->default(0);
                $table->string('status', 30)->default('delivered'); // delivered, partial, failed, pending
                $table->longText('response_payload')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();

                $table->index(['platform', 'status']);
                $table->index('created_at');
            });
        }

        // 3. Enhance Device Registrations with Firebase App ID
        if (Schema::hasTable('device_registrations')) {
            Schema::table('device_registrations', function (Blueprint $table) {
                if (!Schema::hasColumn('device_registrations', 'firebase_app_id')) {
                    $table->foreignId('firebase_app_id')->nullable()->after('user_id')->constrained('firebase_apps')->onDelete('set null');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('device_registrations')) {
            Schema::table('device_registrations', function (Blueprint $table) {
                if (Schema::hasColumn('device_registrations', 'firebase_app_id')) {
                    $table->dropForeign(['firebase_app_id']);
                    $table->dropColumn('firebase_app_id');
                }
            });
        }
        Schema::dropIfExists('push_notifications');
        Schema::dropIfExists('firebase_apps');
    }
};
