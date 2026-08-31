<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('app_settings')) {
            Schema::create('app_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->string('group')->default('general'); // general, app, branding, chat, call
                $table->string('description')->nullable();
                $table->timestamps();
            });

            // Seed default general settings
            $defaults = [
                ['key' => 'app_name', 'value' => 'Chinchins Live', 'group' => 'branding', 'description' => 'Mobile App Name'],
                ['key' => 'app_tagline', 'value' => 'Meet, Chat & Video Call Live', 'group' => 'branding', 'description' => 'App Tagline'],
                ['key' => 'app_logo', 'value' => 'assets/images/branding/logo.png', 'group' => 'branding', 'description' => 'App Logo for Login & Registration'],
                ['key' => 'app_icon', 'value' => 'assets/images/branding/icon.png', 'group' => 'branding', 'description' => 'App Icon'],
                ['key' => 'app_version', 'value' => '1.0.0', 'group' => 'general', 'description' => 'Current Mobile App Version'],
                ['key' => 'free_messages_limit', 'value' => '5', 'group' => 'chat', 'description' => 'Free messages before coin recharge prompt'],
                ['key' => 'message_coin_cost', 'value' => '5', 'group' => 'chat', 'description' => 'Cost per message after free limit'],
                ['key' => 'currency_symbol', 'value' => 'BDT', 'group' => 'general', 'description' => 'Currency symbol'],
            ];

            foreach ($defaults as $d) {
                DB::table('app_settings')->insert(array_merge($d, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
