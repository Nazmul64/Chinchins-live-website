<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoinPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'coins',
        'bonus_coins',
        'price',
        'currency',
        'icon_url',
        'animation_url',
        'format',
        'badge',
        'badge_color',
        'is_popular',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'coins' => 'integer',
        'bonus_coins' => 'integer',
        'price' => 'decimal:2',
        'is_popular' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'total_coins',
        'bonus_text',
        'formatted_price',
        'bonus_percentage',
        'button_text',
        'icon_full_url',
        'animation_full_url',
    ];

    /**
     * Total coins calculated: base coins + bonus coins
     */
    public function getTotalCoinsAttribute(): int
    {
        return (int) ($this->coins + ($this->bonus_coins ?: 0));
    }

    /**
     * Formatted bonus text for display, e.g. "+8000 Bonus"
     */
    public function getBonusTextAttribute(): ?string
    {
        if (!empty($this->bonus_coins) && $this->bonus_coins > 0) {
            return "+{$this->bonus_coins} Bonus";
        }
        return null;
    }

    /**
     * Formatted price with BDT symbol, e.g. "৳550"
     */
    public function getFormattedPriceAttribute(): string
    {
        return '৳' . number_format($this->price, (floor($this->price) == $this->price ? 0 : 2));
    }

    /**
     * Calculate bonus percentage relative to base coins
     */
    public function getBonusPercentageAttribute(): int
    {
        if ($this->coins > 0 && $this->bonus_coins > 0) {
            return (int) round(($this->bonus_coins / $this->coins) * 100);
        }
        return 0;
    }

    /**
     * Button recharge text for mobile app, e.g. "Recharge 40000 Gems (৳550)"
     */
    public function getButtonTextAttribute(): string
    {
        $total = $this->total_coins;
        $price = $this->formatted_price;
        return "Recharge {$total} Gems ({$price})";
    }

    /**
     * Full URL for icon
     */
    public function getIconFullUrlAttribute(): ?string
    {
        if (empty($this->icon_url)) {
            return asset('assets/images/coins/gem-stack.png');
        }
        if (str_starts_with($this->icon_url, 'http://') || str_starts_with($this->icon_url, 'https://')) {
            return $this->icon_url;
        }
        return asset(ltrim($this->icon_url, '/'));
    }

    /**
     * Full URL for Animation / Lottie / SVGA / GIF
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
}
