<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CharmLevelSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'level',
        'title',
        'required_coins',
        'badge_icon',
        'badge_color',
    ];

    protected $casts = [
        'level'          => 'integer',
        'required_coins' => 'integer',
    ];

    protected $appends = [
        'formatted_required_coins',
    ];

    public function getFormattedRequiredCoinsAttribute(): string
    {
        return Gift::formatCoins($this->required_coins);
    }

    /**
     * Calculate Charm Level and progress for a user given their total received coins.
     */
    public static function calculateLevel(int $totalCoins): array
    {
        $levels = static::orderBy('level', 'asc')->get();

        if ($levels->isEmpty()) {
            // Default formula if table is empty (every 10,000 coins is 1 level)
            $lvl = max(1, (int) floor($totalCoins / 10000) + 1);
            $currentLvlCoins = ($lvl - 1) * 10000;
            $nextLvlCoins = $lvl * 10000;
            $progress = min(100, max(0, (int) ((($totalCoins - $currentLvlCoins) / 10000) * 100)));

            return [
                'level'            => $lvl,
                'level_tag'        => 'Lv' . $lvl,
                'title'            => 'Level ' . $lvl,
                'badge_icon'       => $lvl >= 6 ? 'crown' : ($lvl >= 3 ? 'gem' : 'star'),
                'badge_color'      => $lvl >= 6 ? '#f59e0b' : '#8b5cf6',
                'current_coins'    => $totalCoins,
                'required_coins'   => $nextLvlCoins,
                'formatted_needed' => Gift::formatCoins($nextLvlCoins),
                'progress'         => $progress,
            ];
        }

        $currentLevel = $levels->first();
        $nextLevel = $levels->count() > 1 ? $levels->get(1) : null;

        foreach ($levels as $index => $levelSetting) {
            if ($totalCoins >= $levelSetting->required_coins) {
                $currentLevel = $levelSetting;
                $nextLevel = $levels->get($index + 1);
            } else {
                break;
            }
        }

        $prevCoins = $currentLevel->required_coins;
        $nextCoins = $nextLevel ? $nextLevel->required_coins : ($prevCoins + 10000);
        $range = max(1, $nextCoins - $prevCoins);
        $progress = min(100, max(0, (int) ((($totalCoins - $prevCoins) / $range) * 100)));

        return [
            'level'            => $currentLevel->level,
            'level_tag'        => 'Lv' . $currentLevel->level,
            'title'            => $currentLevel->title,
            'badge_icon'       => $currentLevel->badge_icon ?: 'crown',
            'badge_color'      => $currentLevel->badge_color ?: '#f59e0b',
            'current_coins'    => $totalCoins,
            'required_coins'   => $nextCoins,
            'formatted_needed' => Gift::formatCoins($nextCoins),
            'progress'         => $progress,
        ];
    }
}
