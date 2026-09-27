<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodRankBadge extends Model
{
    use HasFactory;

    protected $table = 'period_rank_badges';

    protected $fillable = [
        'badge_name',
        'period_type',
        'category',
        'rank_position',
        'min_required_coins',
        'badge_icon',
        'avatar_frame',
        'is_active',
    ];

    protected $casts = [
        'rank_position'      => 'integer',
        'min_required_coins' => 'integer',
        'is_active'          => 'boolean',
    ];

    protected $appends = [
        'badge_icon_url',
        'avatar_frame_url',
    ];

    const CACHE_KEY_ACTIVE = 'app_period_rank_badges';
    protected static ?\Illuminate\Support\Collection $_staticCachedBadges = null;

    protected static function booted(): void
    {
        static::saved(function () {
            static::clearBadgesCache();
        });

        static::deleted(function () {
            static::clearBadgesCache();
        });
    }

    public static function clearBadgesCache(): void
    {
        static::$_staticCachedBadges = null;
        \Illuminate\Support\Facades\Cache::forget(static::CACHE_KEY_ACTIVE);
    }

    public static function allCachedBadges(): \Illuminate\Support\Collection
    {
        if (static::$_staticCachedBadges !== null) {
            return static::$_staticCachedBadges;
        }

        static::$_staticCachedBadges = \Illuminate\Support\Facades\Cache::remember(
            static::CACHE_KEY_ACTIVE,
            3600,
            function () {
                return static::where('is_active', true)
                    ->orderBy('rank_position', 'asc')
                    ->get();
            }
        );

        return static::$_staticCachedBadges;
    }

    public static function getBadgeForRank(string $period = 'daily', string $category = 'rich', int $rank = 1): ?self
    {
        $badges = static::allCachedBadges();
        return $badges->where('period_type', strtolower($period))
            ->where('category', strtolower($category))
            ->where('rank_position', $rank)
            ->first();
    }
}
