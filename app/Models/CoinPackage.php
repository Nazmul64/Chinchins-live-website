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
        'image_url',
        'png_url',
        'svg_url',
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
     * Resolve asset path to a valid public URL reachable by mobile apps.
     */
    public static function resolveAssetUrl(?string $path, string $fallback = ''): string
    {
        $src = $path ?: $fallback;
        if (empty($src)) return '';

        // If it's already an absolute URL
        if (preg_match('#^https?://#i', $src)) {
            if (preg_match('#^https?://(localhost|127\.0\.0\.1)(:\d+)?/(.*)$#i', $src, $matches)) {
                $host = (request() && !in_array(request()->getHost(), ['localhost', '127.0.0.1']))
                    ? request()->getSchemeAndHttpHost()
                    : 'https://chinchins.live';
                return rtrim($host, '/') . '/' . ltrim($matches[3], '/');
            }
            return $src;
        }

        // Relative path
        $cleanPath = ltrim($src, '/');
        
        $host = (request() && !in_array(request()->getHost(), ['localhost', '127.0.0.1']))
            ? request()->getSchemeAndHttpHost()
            : (config('app.url') && !in_array(parse_url(config('app.url'), PHP_URL_HOST), ['localhost', '127.0.0.1'])
                ? config('app.url')
                : 'https://chinchins.live');

        return rtrim($host, '/') . '/' . $cleanPath;
    }

    /**
     * Full URL for image / icon
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->getIconFullUrlAttribute();
    }

    /**
     * Direct PNG URL
     */
    public function getPngUrlAttribute(): ?string
    {
        $src = $this->icon_url;
        if (empty($src)) return self::resolveAssetUrl('uploads/coin_packages/gem_tier1_single.svg');
        $pngPath = preg_replace('/\.svg(\?.*)?$/i', '.png$1', $src);
        return self::resolveAssetUrl($pngPath);
    }

    /**
     * Direct SVG URL
     */
    public function getSvgUrlAttribute(): ?string
    {
        $src = $this->icon_url;
        if (empty($src)) return self::resolveAssetUrl('uploads/coin_packages/gem_tier1_single.svg');
        return self::resolveAssetUrl($src);
    }

    /**
     * Full URL for icon
     */
    public function getIconFullUrlAttribute(): ?string
    {
        if (empty($this->icon_url)) {
            return self::resolveAssetUrl('uploads/coin_packages/gem_tier1_single.svg');
        }
        return self::resolveAssetUrl($this->icon_url);
    }

    /**
     * Full URL for Animation / Lottie / SVGA / GIF
     */
    public function getAnimationFullUrlAttribute(): ?string
    {
        if (empty($this->animation_url)) {
            return null;
        }
        return self::resolveAssetUrl($this->animation_url);
    }

    /**
     * Seed or repair default 6 Coin Packages with proper SVG artwork
     */
    public static function seedDefaultPackages(): void
    {
        $defaults = [
            1 => [
                'title' => 'Starter Pack',
                'coins' => 7560,
                'bonus_coins' => 0,
                'price' => 150.00,
                'badge' => '50% off',
                'badge_color' => 'danger',
                'is_popular' => true,
                'is_active' => true,
                'sort_order' => 1,
                'icon_url' => 'uploads/coin_packages/gem_tier1_single.svg',
            ],
            2 => [
                'title' => 'Basic Pack',
                'coins' => 8100,
                'bonus_coins' => 0,
                'price' => 300.00,
                'badge' => '17% off',
                'badge_color' => 'pink',
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 2,
                'icon_url' => 'uploads/coin_packages/gem_tier2_double.svg',
            ],
            3 => [
                'title' => 'Popular Pack',
                'coins' => 16380,
                'bonus_coins' => 0,
                'price' => 600.00,
                'badge' => '17% off',
                'badge_color' => 'pink',
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 3,
                'icon_url' => 'uploads/coin_packages/gem_tier3_triple.svg',
            ],
            4 => [
                'title' => 'Super Pack',
                'coins' => 32940,
                'bonus_coins' => 0,
                'price' => 1200.00,
                'badge' => '30% off',
                'badge_color' => 'pink',
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 4,
                'icon_url' => 'uploads/coin_packages/gem_tier4_stack.svg',
            ],
            5 => [
                'title' => 'Mega Pack',
                'coins' => 66600,
                'bonus_coins' => 0,
                'price' => 2400.00,
                'badge' => '60% off',
                'badge_color' => 'pink',
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 5,
                'icon_url' => 'uploads/coin_packages/gem_tier5_tray.svg',
            ],
            6 => [
                'title' => 'VIP King Pack',
                'coins' => 167400,
                'bonus_coins' => 0,
                'price' => 6100.00,
                'badge' => '80% off',
                'badge_color' => 'danger',
                'is_popular' => false,
                'is_active' => true,
                'sort_order' => 6,
                'icon_url' => 'uploads/coin_packages/gem_tier6_chest.svg',
            ],
        ];

        foreach ($defaults as $sort => $data) {
            $data['currency'] = 'BDT';
            $data['format'] = 'image';
            self::updateOrCreate(
                ['coins' => $data['coins']],
                $data
            );
        }
    }
}
