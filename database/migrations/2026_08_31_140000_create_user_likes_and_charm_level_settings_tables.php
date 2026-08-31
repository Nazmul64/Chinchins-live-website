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
        // 1. User Likes (Love / Hearts sent during video calls, profile, or live streams)
        if (!Schema::hasTable('user_likes')) {
            Schema::create('user_likes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Receiver (Host)
                $table->foreignId('sender_id')->constrained('users')->onDelete('cascade'); // Gifter (Fan)
                $table->unsignedInteger('likes_count')->default(1);
                $table->string('context')->default('call'); // call, profile, live, match
                $table->timestamps();

                $table->unique(['user_id', 'sender_id', 'context']);
            });
        }

        // 2. Charm Level Settings Table (Coins required per level)
        if (!Schema::hasTable('charm_level_settings')) {
            Schema::create('charm_level_settings', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('level'); // 1, 2, 3, 4, 5, 6...
                $table->string('title')->nullable(); // Bronze, Silver, Gold, Platinum, Diamond, Crown
                $table->unsignedBigInteger('required_coins'); // e.g. 10000, 20000, 30000...
                $table->string('badge_icon')->nullable(); // crown, diamond, fire, star
                $table->string('badge_color')->default('#8b5cf6');
                $table->timestamps();
            });

            // Seed default levels (10k coins per level as requested)
            $defaultLevels = [
                ['level' => 1, 'title' => 'Novice', 'required_coins' => 10000, 'badge_icon' => 'star', 'badge_color' => '#10b981'],
                ['level' => 2, 'title' => 'Rising Star', 'required_coins' => 20000, 'badge_icon' => 'star', 'badge_color' => '#06b6d4'],
                ['level' => 3, 'title' => 'Popular', 'required_coins' => 30000, 'badge_icon' => 'gem', 'badge_color' => '#3b82f6'],
                ['level' => 4, 'title' => 'Super Star', 'required_coins' => 40000, 'badge_icon' => 'gem', 'badge_color' => '#8b5cf6'],
                ['level' => 5, 'title' => 'Glamour Idol', 'required_coins' => 50000, 'badge_icon' => 'fire', 'badge_color' => '#ec4899'],
                ['level' => 6, 'title' => 'Diamond Queen', 'required_coins' => 60000, 'badge_icon' => 'crown', 'badge_color' => '#f59e0b'],
                ['level' => 7, 'title' => 'Royalty', 'required_coins' => 70000, 'badge_icon' => 'crown', 'badge_color' => '#ef4444'],
                ['level' => 8, 'title' => 'Emperor', 'required_coins' => 80000, 'badge_icon' => 'crown', 'badge_color' => '#eab308'],
                ['level' => 9, 'title' => 'Legend', 'required_coins' => 90000, 'badge_icon' => 'crown', 'badge_color' => '#a855f7'],
                ['level' => 10, 'title' => 'Mythic Sovereign', 'required_coins' => 100000, 'badge_icon' => 'crown', 'badge_color' => '#f43f5e'],
            ];

            foreach ($defaultLevels as $lvl) {
                DB::table('charm_level_settings')->insert(array_merge($lvl, [
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
        Schema::dropIfExists('charm_level_settings');
        Schema::dropIfExists('user_likes');
    }
};
