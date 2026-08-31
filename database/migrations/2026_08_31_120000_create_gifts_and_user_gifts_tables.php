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
        // 1. System Gifts Catalog Table
        Schema::create('gifts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('coins')->default(100); // e.g. 500, 5550, 6660, 9990, 17700
            $table->string('category')->default('popular'); // popular, luxury, romantic, effects, anime, vip
            $table->string('image'); // stored in uploads/gifts/xxx.png or full URL
            $table->string('animation_url')->nullable(); // SVGA, JSON Lottie, WebP or MP4 animation
            $table->string('animation_type')->nullable()->default('image'); // image, svga, lottie, webp, mp4
            $table->string('sound_url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_broadcast')->default(false); // Global whole-app screen banner animation
            $table->string('badge')->nullable(); // e.g. HOT, NEW, 3D, LUXURY
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. User Received Gifts Ledger & Collection Table
        Schema::create('user_gifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Receiver / Host
            $table->foreignId('sender_id')->nullable()->constrained('users')->onDelete('set null'); // Fan / Gifter
            $table->foreignId('gift_id')->constrained('gifts')->onDelete('cascade');
            $table->unsignedInteger('quantity')->default(1); // e.g. 1, 2, 4, 10, 32, 43
            $table->unsignedInteger('coins_per_unit')->default(0);
            $table->unsignedBigInteger('total_coins')->default(0);
            $table->unsignedBigInteger('call_session_id')->nullable(); // If sent during call
            $table->string('context')->default('profile'); // profile, live_call, chat, random_match
            $table->timestamps();

            // Indexes for fast querying on profile & gifts received screen
            $table->index(['user_id', 'gift_id']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_gifts');
        Schema::dropIfExists('gifts');
    }
};
