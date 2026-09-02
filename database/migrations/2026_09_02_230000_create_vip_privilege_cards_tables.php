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
        Schema::create('vip_privilege_cards', function (Blueprint $table) {
            $table->id();
            $table->string('card_type')->index(); // 'new_user', 'super_monthly', 'luxury_monthly', 'super_weekly'
            $table->string('name'); // e.g. "New User Weekly Card"
            $table->string('badge_text')->nullable()->default('HOT'); // "50% OFF", "BEST VALUE", "Dev mode"
            $table->decimal('price_bdt', 10, 2)->default(300.00);
            $table->unsignedBigInteger('price_coins')->default(8100);
            $table->unsignedInteger('duration_days')->default(7); // 7, 30
            $table->unsignedBigInteger('instant_reward_coins')->default(8100);
            $table->unsignedBigInteger('daily_checkin_total_coins')->default(2020);
            $table->unsignedBigInteger('total_return_coins')->default(10120);
            $table->json('daily_schedule')->nullable(); // Array of daily reward breakdown
            $table->json('extra_rewards')->nullable(); // Array of extra perks (outfits, badges, free cards)
            $table->text('description')->nullable();
            $table->string('card_color')->nullable()->default('#FF4081');
            $table->string('banner_tag')->nullable()->default('Spend Less, Get More Gems!');
            $table->boolean('is_active')->default(true)->index();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('user_vip_card_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('vip_card_id')->constrained('vip_privilege_cards')->onDelete('cascade');
            $table->string('card_type')->index();
            $table->decimal('price_paid', 10, 2)->default(0.00);
            $table->string('payment_method')->default('coins'); // 'coins', 'wallet', 'bkash', 'nagad'
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('expires_at')->nullable()->index();
            $table->json('claimed_days')->nullable(); // e.g. [1, 2, 3]
            $table->timestamp('last_claimed_at')->nullable();
            $table->string('status')->default('active')->index(); // 'active', 'expired'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_vip_card_subscriptions');
        Schema::dropIfExists('vip_privilege_cards');
    }
};
