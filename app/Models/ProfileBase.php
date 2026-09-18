<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProfileBase extends Model
{
    use HasFactory;

    protected $table = 'profile_bases';

    protected $fillable = [
        'level',
        'name',
        'required_coins',
        'base_frame_image',
        'badge_icon',
        'badge_color',
        'glow_color',
        'privilege_text',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'level'          => 'integer',
        'required_coins' => 'integer',
        'is_active'      => 'boolean',
        'sort_order'     => 'integer',
    ];

    protected $appends = [
        'base_frame_image_url',
        'frame_url',
    ];

    /**
     * Accessor for full URL of the Avatar Base Frame SVG / PNG.
     */
    public function getBaseFrameImageUrlAttribute(): ?string
    {
        if (empty($this->base_frame_image)) {
            return null;
        }

        return User::resolveImageUrl($this->base_frame_image);
    }

    /**
     * Alias for mobile and frontend consistency.
     */
    public function getFrameUrlAttribute(): ?string
    {
        return $this->getBaseFrameImageUrlAttribute();
    }

    /**
     * Cache key for active level bases.
     */
    const CACHE_KEY_ACTIVE = 'chinchins_profile_bases_active_v2';
    protected static ?\Illuminate\Support\Collection $_staticCachedBases = null;

    /**
     * Boot model events for automatic cache invalidation.
     */
    protected static function booted(): void
    {
        static::saved(function () {
            static::clearBasesCache();
        });

        static::deleted(function () {
            static::clearBasesCache();
        });
    }

    /**
     * Clear all caches for profile bases.
     */
    public static function clearBasesCache(): void
    {
        static::$_staticCachedBases = null;
        \Illuminate\Support\Facades\Cache::forget(static::CACHE_KEY_ACTIVE);
    }

    /**
     * Get all active bases from memory / cache in 0 DB queries.
     */
    public static function allCachedBases(): \Illuminate\Support\Collection
    {
        if (static::$_staticCachedBases !== null) {
            return static::$_staticCachedBases;
        }

        static::$_staticCachedBases = \Illuminate\Support\Facades\Cache::remember(
            static::CACHE_KEY_ACTIVE,
            3600,
            function () {
                if (static::count() === 0) {
                    static::seedDefaultBases();
                }
                return static::where('is_active', true)
                    ->orderBy('level', 'asc')
                    ->get();
            }
        );

        return static::$_staticCachedBases;
    }

    /**
     * Seed default 11 Levels (0 to 10) if table is empty.
     */
    public static function seedDefaultBases(bool $force = false): void
    {
        if (!$force && static::count() > 0) {
            return;
        }
        $defaultBases = [
            [
                'level'            => 0,
                'name'             => 'Level 0 - Novice Cadet',
                'required_coins'   => 0,
                'base_frame_image' => 'uploads/bases/profile_base_novice_cadet.svg',
                'badge_icon'       => 'user',
                'badge_color'      => '#94a3b8',
                'glow_color'       => 'rgba(148, 163, 184, 0.3)',
                'privilege_text'   => 'Standard Profile Frame',
                'is_active'        => true,
                'sort_order'       => 0,
            ],
            [
                'level'            => 1,
                'name'             => 'Level 1 - Royal Bronze Crown & Laurel Wings',
                'required_coins'   => 1000,
                'base_frame_image' => 'uploads/bases/profile_base_bronze_star.svg',
                'badge_icon'       => 'crown',
                'badge_color'      => '#10b981',
                'glow_color'       => 'rgba(16, 185, 129, 0.45)',
                'privilege_text'   => 'Unlocks Royal Bronze Crown & Golden Laurel Wings Avatar Frame',
                'is_active'        => true,
                'sort_order'       => 1,
            ],
            [
                'level'            => 2,
                'name'             => 'Level 2 - Royal Dollar Crown & Fatima Gold Ring',
                'required_coins'   => 5000,
                'base_frame_image' => 'uploads/bases/profile_base_dollar_ring.svg',
                'badge_icon'       => 'dollar-sign',
                'badge_color'      => '#10b981',
                'glow_color'       => 'rgba(16, 185, 129, 0.55)',
                'privilege_text'   => 'Unlocks Royal Gold Crown, Dollar Gemstones & Emerald Wings Frame',
                'is_active'        => true,
                'sort_order'       => 2,
            ],
            [
                'level'            => 3,
                'name'             => 'Level 3 - Royal Gentleman Crown & Maryam Gold Ribbon',
                'required_coins'   => 15000,
                'base_frame_image' => 'uploads/bases/profile_base_circus_gentleman.svg',
                'badge_icon'       => 'crown',
                'badge_color'      => '#ef4444',
                'glow_color'       => 'rgba(239, 68, 68, 0.5)',
                'privilege_text'   => 'Unlocks Royal Ruby Gentleman Crown & Rich Gold Banner Frame',
                'is_active'        => true,
                'sort_order'       => 3,
            ],
            [
                'level'            => 4,
                'name'             => 'Level 4 - Royal Sapphire Crown & Abdul TOP 3 Stage',
                'required_coins'   => 50000,
                'base_frame_image' => 'uploads/bases/profile_base_top3_spotlight.svg',
                'badge_icon'       => 'crown',
                'badge_color'      => '#a855f7',
                'glow_color'       => 'rgba(168, 85, 247, 0.7)',
                'privilege_text'   => 'Unlocks Royal Sapphire Tiara Crown & TOP 3 Stage Spotlight Frame',
                'is_active'        => true,
                'sort_order'       => 4,
            ],
            [
                'level'            => 5,
                'name'             => 'Level 5 - Royal Naval Crown & Momo Captain Wheel',
                'required_coins'   => 100000,
                'base_frame_image' => 'uploads/bases/profile_base_blue_captain.svg',
                'badge_icon'       => 'crown',
                'badge_color'      => '#00f0ff',
                'glow_color'       => 'rgba(0, 240, 255, 0.6)',
                'privilege_text'   => 'Unlocks Royal Naval Cyan Crown, Captain Wheel & Anchor Frame',
                'is_active'        => true,
                'sort_order'       => 5,
            ],
            [
                'level'            => 6,
                'name'             => 'Level 6 - Royal Magma Crown & Omar Devil Horns',
                'required_coins'   => 250000,
                'base_frame_image' => 'uploads/bases/profile_base_devil_horns.svg',
                'badge_icon'       => 'fire',
                'badge_color'      => '#ef4444',
                'glow_color'       => 'rgba(239, 68, 68, 0.65)',
                'privilege_text'   => 'Unlocks Royal Horned Magma Crown & Fiery Wings Frame',
                'is_active'        => true,
                'sort_order'       => 6,
            ],
            [
                'level'            => 7,
                'name'             => 'Level 7 - Royal Cricket Superstar Gold Crown & Helmet',
                'required_coins'   => 500000,
                'base_frame_image' => 'uploads/bases/profile_base_cricket_superstar.svg',
                'badge_icon'       => 'trophy',
                'badge_color'      => '#eab308',
                'glow_color'       => 'rgba(234, 179, 8, 0.65)',
                'privilege_text'   => 'Unlocks Royal Cricket Crown, Gold Helmet, Bat & Ball Frame',
                'is_active'        => true,
                'sort_order'       => 7,
            ],
            [
                'level'            => 8,
                'name'             => 'Level 8 - Royal Cyber Speedometer Crown & Zainab Shield',
                'required_coins'   => 1000000,
                'base_frame_image' => 'uploads/bases/profile_base_cyber_speedometer.svg',
                'badge_icon'       => 'bolt',
                'badge_color'      => '#38bdf8',
                'glow_color'       => 'rgba(56, 189, 248, 0.7)',
                'privilege_text'   => 'Unlocks Royal Cyber Tiara Crown & Cyan Speedometer Shield Frame',
                'is_active'        => true,
                'sort_order'       => 8,
            ],
            [
                'level'            => 9,
                'name'             => 'Level 9 - Royal Rose Tiara Crown & Priya Queen Locket',
                'required_coins'   => 2500000,
                'base_frame_image' => 'uploads/bases/profile_base_rose_garden.svg',
                'badge_icon'       => 'crown',
                'badge_color'      => '#f43f5e',
                'glow_color'       => 'rgba(244, 63, 94, 0.75)',
                'privilege_text'   => 'Unlocks Royal Blooming Rose Diamond Tiara Crown & Heart Locket Frame',
                'is_active'        => true,
                'sort_order'       => 9,
            ],
            [
                'level'            => 10,
                'name'             => 'Level 10 - QUEEN Imperial Diamond Wings',
                'required_coins'   => 5000000,
                'base_frame_image' => 'uploads/bases/profile_base_queen_imperial.svg',
                'badge_icon'       => 'crown',
                'badge_color'      => '#ec4899',
                'glow_color'       => 'rgba(236, 72, 153, 0.85)',
                'privilege_text'   => 'Unlocks QUEEN Imperial Diamond Crown, Wings Base & 3D Plaque',
                'is_active'        => true,
                'sort_order'       => 10,
            ],
            [
                'level'            => 11,
                'name'             => 'Level 11 - KING Golden Royal Winged Crown',
                'required_coins'   => 8000000,
                'base_frame_image' => 'uploads/bases/profile_base_king_royal.svg',
                'badge_icon'       => 'crown',
                'badge_color'      => '#f59e0b',
                'glow_color'       => 'rgba(245, 158, 11, 0.85)',
                'privilege_text'   => 'Supreme KING 24K Gold Emperor Wings Base & Global Shout',
                'is_active'        => true,
                'sort_order'       => 11,
            ],
            [
                'level'            => 12,
                'name'             => 'Level 12 - Superbike Nitro Racer Helmet',
                'required_coins'   => 12000000,
                'base_frame_image' => 'uploads/bases/profile_base_superbike_rider.svg',
                'badge_icon'       => 'bolt',
                'badge_color'      => '#6366f1',
                'glow_color'       => 'rgba(99, 102, 241, 0.85)',
                'privilege_text'   => 'Unlocks Superbike Helmet, Spinning Wheel & Exhaust Flame Frame',
                'is_active'        => true,
                'sort_order'       => 12,
            ],
            [
                'level'            => 13,
                'name'             => 'Level 13 - Fire Phoenix Immortal',
                'required_coins'   => 18000000,
                'base_frame_image' => 'uploads/bases/profile_base_phoenix_immortal.svg',
                'badge_icon'       => 'fire',
                'badge_color'      => '#f97316',
                'glow_color'       => 'rgba(249, 115, 22, 0.85)',
                'privilege_text'   => 'Unlocks Blazing Phoenix Wings & Solar Flare Avatar Frame',
                'is_active'        => true,
                'sort_order'       => 13,
            ],
            [
                'level'            => 14,
                'name'             => 'Level 14 - Ocean Leviathan Poseidon',
                'required_coins'   => 25000000,
                'base_frame_image' => 'uploads/bases/profile_base_ocean_poseidon.svg',
                'badge_icon'       => 'water',
                'badge_color'      => '#06b6d4',
                'glow_color'       => 'rgba(6, 182, 212, 0.9)',
                'privilege_text'   => 'Unlocks Poseidon Golden Trident & Deep Sea Wave Frame',
                'is_active'        => true,
                'sort_order'       => 14,
            ],
            [
                'level'            => 15,
                'name'             => 'Level 15 - Supreme Sovereign God-Tier',
                'required_coins'   => 40000000,
                'base_frame_image' => 'uploads/bases/profile_base_supreme_sovereign.svg',
                'badge_icon'       => 'crown',
                'badge_color'      => '#ffd700',
                'glow_color'       => 'rgba(255, 215, 0, 0.95)',
                'privilege_text'   => 'God-Tier Supreme 24K Sunburst Aura & Diamond Floating Gems',
                'is_active'        => true,
                'sort_order'       => 15,
            ],
        ];

        foreach ($defaultBases as $base) {
            static::updateOrCreate(['level' => $base['level']], $base);
        }

        static::clearBasesCache();
    }

    /**
     * Find the level base model for a specific level from in-memory cache (0 DB queries).
     */
    public static function getBaseForLevel(int $level): ?self
    {
        $bases = static::allCachedBases();
        return $bases->firstWhere('level', $level) ?? $bases->firstWhere('level', 0);
    }

    /**
     * Find the highest level achieved based on earned/spent coins from in-memory cache (0 DB queries).
     */
    public static function getBaseForCoins(int $coins): ?self
    {
        $bases = static::allCachedBases();
        return $bases->where('required_coins', '<=', $coins)
            ->sortByDesc('level')
            ->first() ?? $bases->firstWhere('level', 0);
    }

    /**
     * Find the next level base above current level from in-memory cache (0 DB queries).
     */
    public static function getNextBase(int $currentLevel): ?self
    {
        $bases = static::allCachedBases();
        return $bases->where('level', '>', $currentLevel)
            ->sortBy('level')
            ->first();
    }

    /**
     * Calculate comprehensive level progress statistics from in-memory cache (0 DB queries).
     */
    public static function calculateLevelProgress(int $earnedCoins, ?int $explicitLevel = null): array
    {
        $bases = static::allCachedBases();
        $currentBase = null;

        if ($explicitLevel !== null && $explicitLevel > 0) {
            $currentBase = $bases->firstWhere('level', $explicitLevel);
        }

        if (!$currentBase) {
            $currentBase = static::getBaseForCoins($earnedCoins) ?? $bases->firstWhere('level', 0);
        }

        $currentLevel = $currentBase ? (int) $currentBase->level : 0;
        $nextBase = static::getNextBase($currentLevel);

        $currentReq = $currentBase ? (int) $currentBase->required_coins : 0;
        $nextReq = $nextBase ? (int) $nextBase->required_coins : $currentReq;

        $progressPercent = 100.0;
        $coinsNeeded = 0;

        if ($nextBase && $nextReq > $currentReq) {
            $range = $nextReq - $currentReq;
            $earnedInRange = max(0, $earnedCoins - $currentReq);
            $progressPercent = min(100.0, round(($earnedInRange / $range) * 100, 2));
            $coinsNeeded = max(0, $nextReq - $earnedCoins);
        }

        return [
            'current_level'              => $currentLevel,
            'level_name'                 => $currentBase?->name ?? "Level {$currentLevel}",
            'earned_coins'               => $earnedCoins,
            'current_base'               => $currentBase,
            'next_level'                 => $nextBase ? (int) $nextBase->level : null,
            'next_base'                  => $nextBase,
            'coins_for_current_level'    => $currentReq,
            'coins_for_next_level'       => $nextBase ? $nextReq : null,
            'coins_needed_to_level_up'   => $coinsNeeded,
            'progress_percentage'        => $progressPercent,
            'avatar_frame_url'           => $currentBase?->base_frame_image_url,
            'badge_color'                => $currentBase?->badge_color ?? '#f59e0b',
            'glow_color'                 => $currentBase?->glow_color ?? 'rgba(245, 158, 11, 0.45)',
            'badge_icon'                 => $currentBase?->badge_icon ?? 'star',
            'privilege_text'             => $currentBase?->privilege_text ?? '',
            'is_max_level'               => $nextBase === null,
        ];
    }
}
