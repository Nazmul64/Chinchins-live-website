<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoinPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'coins',
        'bonus_coins',
        'price',
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
}
