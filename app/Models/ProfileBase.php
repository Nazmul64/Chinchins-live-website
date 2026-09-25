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
        // No-op: Profile bases are created manually via Admin Panel
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
