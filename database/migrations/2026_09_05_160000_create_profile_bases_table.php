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
        if (!Schema::hasTable('profile_bases')) {
            Schema::create('profile_bases', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('level')->unique(); // 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10...
                $table->string('name'); // e.g. "Level 1 - Bronze Star"
                $table->unsignedBigInteger('required_coins')->default(0); // Lifetime coins threshold (earning/spending)
                $table->string('base_frame_image')->nullable(); // Overlay SVG/PNG avatar border frame
                $table->string('badge_icon')->nullable()->default('star'); // crown, gem, star, fire, dragon, bolt, shield
                $table->string('badge_color')->default('#f59e0b'); // Hex or CSS gradient for badge/border
                $table->string('glow_color')->nullable()->default('rgba(245, 158, 11, 0.45)');
                $table->string('privilege_text')->nullable(); // e.g. "Exclusive animated avatar frame & chat glow"
                $table->boolean('is_active')->default(true);
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });

            // Seed initial 11 level bases (Level 0 to Level 10)
            $initialBases = [
                [
                    'level'            => 0,
                    'name'             => 'Level 0 - Novice Cadet',
                    'required_coins'   => 0,
                    'base_frame_image' => 'uploads/all_image/profile_base_royal_gold.svg',
                    'badge_icon'       => 'user',
                    'badge_color'      => '#94a3b8',
                    'glow_color'       => 'rgba(148, 163, 184, 0.3)',
                    'privilege_text'   => 'Standard Profile Frame',
                    'is_active'        => true,
                    'sort_order'       => 0,
                ],
                [
                    'level'            => 1,
                    'name'             => 'Level 1 - Bronze Star',
                    'required_coins'   => 1000,
                    'base_frame_image' => 'uploads/all_image/profile_base_royal_gold.svg',
                    'badge_icon'       => 'star',
                    'badge_color'      => '#10b981',
                    'glow_color'       => 'rgba(16, 185, 129, 0.45)',
                    'privilege_text'   => 'Unlocks Bronze Star Animated Avatar Frame',
                    'is_active'        => true,
                    'sort_order'       => 1,
                ],
                [
                    'level'            => 2,
                    'name'             => 'Level 2 - Silver Wings',
                    'required_coins'   => 5000,
                    'base_frame_image' => 'uploads/all_image/profile_base_diamond_wings.svg',
                    'badge_icon'       => 'star',
                    'badge_color'      => '#06b6d4',
                    'glow_color'       => 'rgba(6, 182, 212, 0.45)',
                    'privilege_text'   => 'Unlocks Silver Wings Animated Avatar Frame',
                    'is_active'        => true,
                    'sort_order'       => 2,
                ],
                [
                    'level'            => 3,
                    'name'             => 'Level 3 - Golden Sparkle',
                    'required_coins'   => 15000,
                    'base_frame_image' => 'uploads/all_image/profile_base_royal_gold.svg',
                    'badge_icon'       => 'gem',
                    'badge_color'      => '#f59e0b',
                    'glow_color'       => 'rgba(245, 158, 11, 0.5)',
                    'privilege_text'   => 'Unlocks Golden Sparkle Avatar Frame & Profile Glow',
                    'is_active'        => true,
                    'sort_order'       => 3,
                ],
                [
                    'level'            => 4,
                    'name'             => 'Level 4 - Cyber Neon',
                    'required_coins'   => 50000,
                    'base_frame_image' => 'uploads/all_image/profile_base_cyber_neon.svg',
                    'badge_icon'       => 'bolt',
                    'badge_color'      => '#00f0ff',
                    'glow_color'       => 'rgba(0, 240, 255, 0.6)',
                    'privilege_text'   => 'Unlocks Cyber Neon Animated Avatar Frame & Blue Beam',
                    'is_active'        => true,
                    'sort_order'       => 4,
                ],
                [
                    'level'            => 5,
                    'name'             => 'Level 5 - Fire Dragon',
                    'required_coins'   => 100000,
                    'base_frame_image' => 'uploads/all_image/profile_base_fire_dragon.svg',
                    'badge_icon'       => 'fire',
                    'badge_color'      => '#ef4444',
                    'glow_color'       => 'rgba(239, 68, 68, 0.6)',
                    'privilege_text'   => 'Unlocks Flaming Fire Dragon Frame & Flame Chat Tag',
                    'is_active'        => true,
                    'sort_order'       => 5,
                ],
                [
                    'level'            => 6,
                    'name'             => 'Level 6 - Diamond Wings',
                    'required_coins'   => 250000,
                    'base_frame_image' => 'uploads/all_image/profile_base_diamond_wings.svg',
                    'badge_icon'       => 'gem',
                    'badge_color'      => '#3b82f6',
                    'glow_color'       => 'rgba(59, 130, 246, 0.6)',
                    'privilege_text'   => 'Unlocks Diamond Wings Luxury Avatar Frame & VIP Aura',
                    'is_active'        => true,
                    'sort_order'       => 6,
                ],
                [
                    'level'            => 7,
                    'name'             => 'Level 7 - Royal Gold Sovereign',
                    'required_coins'   => 500000,
                    'base_frame_image' => 'uploads/all_image/profile_base_royal_gold.svg',
                    'badge_icon'       => 'crown',
                    'badge_color'      => '#eab308',
                    'glow_color'       => 'rgba(234, 179, 8, 0.65)',
                    'privilege_text'   => 'Unlocks 24K Royal Gold Crown Frame & Golden Nickname',
                    'is_active'        => true,
                    'sort_order'       => 7,
                ],
                [
                    'level'            => 8,
                    'name'             => 'Level 8 - SVIP Supreme Crown',
                    'required_coins'   => 1000000,
                    'base_frame_image' => 'uploads/all_image/profile_base_svip_crown.svg',
                    'badge_icon'       => 'crown',
                    'badge_color'      => '#ec4899',
                    'glow_color'       => 'rgba(236, 72, 153, 0.7)',
                    'privilege_text'   => 'Unlocks SVIP Supreme Animated Crown & Entrance Broadcast',
                    'is_active'        => true,
                    'sort_order'       => 8,
                ],
                [
                    'level'            => 9,
                    'name'             => 'Level 9 - Galactic Sovereign',
                    'required_coins'   => 2500000,
                    'base_frame_image' => 'uploads/all_image/profile_base_cyber_neon.svg',
                    'badge_icon'       => 'crown',
                    'badge_color'      => '#a855f7',
                    'glow_color'       => 'rgba(168, 85, 247, 0.75)',
                    'privilege_text'   => 'Unlocks Galactic Sovereign Ultra Frame & Room Entry Jet',
                    'is_active'        => true,
                    'sort_order'       => 9,
                ],
                [
                    'level'            => 10,
                    'name'             => 'Level 10 - Mythic Emperor',
                    'required_coins'   => 5000000,
                    'base_frame_image' => 'uploads/all_image/profile_base_svip_crown.svg',
                    'badge_icon'       => 'crown',
                    'badge_color'      => '#f43f5e',
                    'glow_color'       => 'rgba(244, 63, 94, 0.8)',
                    'privilege_text'   => 'Supreme Mythic Emperor God-Tier Base Frame & Global Shout',
                    'is_active'        => true,
                    'sort_order'       => 10,
                ],
            ];

            foreach ($initialBases as $base) {
                DB::table('profile_bases')->insert(array_merge($base, [
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
        Schema::dropIfExists('profile_bases');
    }
};
