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
                static::seedDefaultBases();
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
    public static function seedDefaultBases(): void
    {
        if (static::count() > 0) {
            return;
        }

        $defaultBases = [
            [
                'level'            => 0,
                'name'             => 'Level 0 - Novice Cadet',
                'required_coins'   => 0,
                'base_frame_image' => 'uploads/bases/profile_base_royal_gold.svg',
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
                'base_frame_image' => 'uploads/bases/profile_base_royal_gold.svg',
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
                'base_frame_image' => 'uploads/bases/profile_base_diamond_wings.svg',
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
                'base_frame_image' => 'uploads/bases/profile_base_royal_gold.svg',
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
                'base_frame_image' => 'uploads/bases/profile_base_cyber_neon.svg',
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
                'base_frame_image' => 'uploads/bases/profile_base_fire_dragon.svg',
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
                'base_frame_image' => 'uploads/bases/profile_base_diamond_wings.svg',
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
                'base_frame_image' => 'uploads/bases/profile_base_royal_gold.svg',
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
                'base_frame_image' => 'uploads/bases/profile_base_svip_crown.svg',
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
                'base_frame_image' => 'uploads/bases/profile_base_cyber_neon.svg',
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
                'base_frame_image' => 'uploads/bases/profile_base_svip_crown.svg',
                'badge_icon'       => 'crown',
                'badge_color'      => '#f43f5e',
                'glow_color'       => 'rgba(244, 63, 94, 0.8)',
                'privilege_text'   => 'Supreme Mythic Emperor God-Tier Base Frame & Global Shout',
                'is_active'        => true,
                'sort_order'       => 10,
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
