<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'coins',
        'category',
        'image',
        'animation_url',
        'animation_type',
        'sound_url',
        'sort_order',
        'is_active',
        'is_broadcast',
        'badge',
        'description',
    ];

    protected $casts = [
        'coins'        => 'integer',
        'sort_order'   => 'integer',
        'is_active'    => 'boolean',
        'is_broadcast' => 'boolean',
    ];

    protected $appends = [
        'image_url',
        'formatted_coins',
        'display_coins',
        'animation_full_url',
    ];

    /**
     * Get the full URL for the gift image.
     */
    public function getImageUrlAttribute(): string
    {
        if (empty($this->image)) {
            return asset('assets/images/gifts/gift-box-default.png');
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        $clean = ltrim($this->image, '/');
        return asset($clean);
    }

    /**
     * Get full URL for animation if available.
     */
    public function getAnimationFullUrlAttribute(): ?string
    {
        if (empty($this->animation_url)) {
            return null;
        }

        if (str_starts_with($this->animation_url, 'http://') || str_starts_with($this->animation_url, 'https://')) {
            return $this->animation_url;
        }

        return asset(ltrim($this->animation_url, '/'));
    }

    /**
     * Format coins for mobile app badges (e.g. 17700 -> 17.70K, 5550 -> 5.55K, 500 -> 500).
     */
    public function getFormattedCoinsAttribute(): string
    {
        return self::formatCoins($this->coins);
    }

    public function getDisplayCoinsAttribute(): string
    {
        return self::formatCoins($this->coins);
    }

    /**
     * Static coin formatter matching Chinchins Live UI.
     */
    public static function formatCoins($coins): string
    {
        $num = (float) $coins;
        if ($num >= 1000000) {
            $val = $num / 1000000;
            return (floor($val) == $val ? number_format($val, 0) : rtrim(rtrim(number_format($val, 2), '0'), '.')) . 'M';
        }
        if ($num >= 1000) {
            $val = $num / 1000;
            $formatted = number_format($val, 2);
            if (substr($formatted, -3) === '.00') {
                return number_format($val, 0) . 'K';
            }
            return $formatted . 'K';
        }
        return (string) (int) $num;
    }

    /**
     * Relationship: User Gifts received.
     */
    public function userGifts()
    {
        return $this->hasMany(UserGift::class);
    }
}
