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
        'coin_price',
        'category',
        'image',
        'icon_url',
        'animation_url',
        'file_url',
        'animation_type',
        'format',
        'display_type',
        'sound_url',
        'sort_order',
        'is_active',
        'is_broadcast',
        'badge',
        'description',
    ];

    protected $casts = [
        'coins'        => 'integer',
        'coin_price'   => 'integer',
        'sort_order'   => 'integer',
        'is_active'    => 'boolean',
        'is_broadcast' => 'boolean',
    ];

    protected $appends = [
        'image_url',
        'icon_url',
        'file_url',
        'formatted_coins',
        'display_coins',
        'animation_full_url',
    ];

    /**
     * Get the full URL for the gift icon / image.
     */
    public function getImageUrlAttribute(): string
    {
        $src = $this->attributes['icon_url'] ?? $this->attributes['image'] ?? null;
        if (empty($src)) {
            return asset('assets/images/gifts/gift-box-default.png');
        }

        if (str_starts_with($src, 'http://') || str_starts_with($src, 'https://')) {
            return $src;
        }

        $clean = ltrim($src, '/');
        return asset($clean);
    }

    /**
     * Alias for icon_url accessor.
     */
    public function getIconUrlAttribute(): string
    {
        return $this->getImageUrlAttribute();
    }

    /**
     * Get full URL for animation / file_url (SVGA, JSON Lottie, WebP).
     */
    public function getFileUrlAttribute(): ?string
    {
        $src = $this->attributes['file_url'] ?? $this->attributes['animation_url'] ?? null;
        if (empty($src)) {
            return null;
        }

        if (str_starts_with($src, 'http://') || str_starts_with($src, 'https://')) {
            return $src;
        }

        return asset(ltrim($src, '/'));
    }

    /**
     * Alias for animation_full_url accessor.
     */
    public function getAnimationFullUrlAttribute(): ?string
    {
        return $this->getFileUrlAttribute();
    }

    /**
     * Getter for coin_price (fallback to coins).
     */
    public function getCoinPriceAttribute(): int
    {
        return (int) ($this->attributes['coin_price'] ?? $this->attributes['coins'] ?? 100);
    }

    /**
     * Format coins for mobile app badges (e.g. 17700 -> 17.70K, 5550 -> 5.55K, 500 -> 500).
     */
    public function getFormattedCoinsAttribute(): string
    {
        return self::formatCoins($this->coins ?: $this->coin_price);
    }

    public function getDisplayCoinsAttribute(): string
    {
        return self::formatCoins($this->coins ?: $this->coin_price);
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

    /**
     * Relationship: Live Gift Transactions.
     */
    public function giftTransactions()
    {
        return $this->hasMany(GiftTransaction::class, 'gift_id');
    }
}
