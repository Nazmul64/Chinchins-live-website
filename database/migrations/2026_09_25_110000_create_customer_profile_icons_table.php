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
        if (!Schema::hasTable('customer_profile_icons')) {
            Schema::create('customer_profile_icons', function (Blueprint $table) {
                $table->id();
                $table->string('key', 50)->unique();
                $table->string('title', 100);
                $table->string('subtitle', 255)->nullable();
                $table->string('category', 50)->default('action_menu'); // wallet_card, banner_card, action_menu
                $table->string('icon_path', 255)->nullable();
                $table->string('default_icon_path', 255)->nullable();
                $table->string('badge_text', 50)->nullable();
                $table->string('badge_color', 50)->nullable();
                $table->string('target_route', 100)->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['key', 'is_active']);
                $table->index(['category', 'sort_order']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_profile_icons');
    }
};
