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
        Schema::create('spend_less_cards', function (Blueprint $table) {
            $table->id();
            $table->string('card_type')->unique(); // new_user_weekly, super_monthly, luxury_monthly, super_weekly, custom
            $table->string('name');                // e.g. New User Weekly Card, Super Monthly Card
            $table->string('category_name')->nullable();
            $table->string('badge_text')->nullable(); // e.g. 60% OFF, 50% OFF, 30% OFF
            $table->decimal('price_bdt', 10, 2)->default(0.00);
            $table->decimal('original_price_bdt', 10, 2)->nullable();
            $table->unsignedBigInteger('price_coins')->default(0);
            $table->unsignedInteger('duration_days')->default(7); // 7, 30, etc.
            $table->unsignedBigInteger('instant_reward_coins')->default(0);
            $table->string('instant_reward_text')->nullable();
            $table->unsignedBigInteger('daily_checkin_total_coins')->default(0);
            $table->string('daily_checkin_text')->nullable();
            $table->unsignedBigInteger('total_return_coins')->default(0);
            $table->json('daily_schedule')->nullable();  // [{day: 1, coins: 8100, extra: "NEW STAR Frame"}, ...]
            $table->json('extra_rewards')->nullable();   // [{title: "7 Days NEW STAR Frame", validity: "7days", icon: "frame_diamond", image: "..."}]
            $table->text('description')->nullable();
            $table->string('card_color')->nullable()->default('#EC4899');
            $table->string('banner_tag')->nullable();
            $table->string('icon_url')->nullable();
            $table->string('animation_url')->nullable();
            $table->string('bg_image_url')->nullable();
            $table->string('format')->default('image'); // image, lottie, svga
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('user_spend_less_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('spend_less_card_id')->constrained('spend_less_cards')->onDelete('cascade');
            $table->string('card_type');
            $table->string('status')->default('active'); // active, expired, cancelled
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_claimed_at')->nullable();
            $table->unsignedInteger('claimed_days_count')->default(0);
            $table->json('claimed_days')->nullable(); // [1, 2, 3]
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_spend_less_subscriptions');
        Schema::dropIfExists('spend_less_cards');
    }
};
