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
        Schema::create('bag_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->index(); // 'coupon', 'avatar_frame', 'chat_style', 'profile_card', 'entrance_bubble', 'big_entrance'
            $table->string('code')->nullable()->unique();
            $table->unsignedInteger('price_coins')->default(0);
            $table->decimal('price_bdt', 10, 2)->default(0.00);
            $table->unsignedInteger('duration_days')->default(7); // 0 = permanent / one-time use
            $table->string('badge')->nullable(); // 'HOT', 'SALE', 'VIP', 'EXCLUSIVE', 'LIMITED'
            $table->unsignedInteger('discount_percent')->nullable(); // For coupons
            $table->unsignedInteger('coupon_value')->nullable(); // Discount coins or cashback
            $table->string('icon_url')->nullable();
            $table->string('image_url')->nullable();
            $table->string('preview_url')->nullable();
            $table->string('animation_url')->nullable();
            $table->string('format')->default('svg'); // 'svg', 'svga', 'lottie', 'webp', 'png', 'mp4'
            $table->string('effect_type')->nullable(); // 'frame_spin', 'bubble_glow', 'ride_drive', etc.
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_giftable')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('attributes')->nullable(); // Custom styling metadata (glow colors, font colors, vehicle type)
            $table->timestamps();
        });

        Schema::create('user_bag_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('bag_item_id')->constrained('bag_items')->onDelete('cascade');
            $table->unsignedInteger('quantity')->default(1);
            $table->string('status')->default('unused')->index(); // 'unused', 'used', 'expired'
            $table->boolean('is_equipped')->default(false)->index();
            $table->string('acquired_from')->default('purchase'); // 'purchase', 'gift', 'reward', 'vip', 'admin'
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('used_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_bag_items');
        Schema::dropIfExists('bag_items');
    }
};
