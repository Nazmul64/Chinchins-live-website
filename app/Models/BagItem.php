<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class BagItem extends Model
{
    use HasFactory;

    protected $table = 'bag_items';

    protected $fillable = [
        'name',
        'category',
        'code',
        'price_coins',
        'price_bdt',
        'duration_days',
        'badge',
        'discount_percent',
        'coupon_value',
        'icon_url',
        'image_url',
        'preview_url',
        'animation_url',
        'format',
        'effect_type',
        'description',
        'is_active',
        'is_giftable',
        'sort_order',
        'attributes',
    ];

    protected $casts = [
        'price_coins'      => 'integer',
        'price_bdt'        => 'decimal:2',
        'duration_days'    => 'integer',
        'discount_percent' => 'integer',
        'coupon_value'     => 'integer',
        'is_active'        => 'boolean',
        'is_giftable'      => 'boolean',
        'sort_order'       => 'integer',
        'attributes'       => 'array',
    ];

    protected $appends = [
        'icon_full_url',
        'image_full_url',
        'preview_full_url',
        'animation_full_url',
        'png_url',
        'svg_url',
        'formatted_price',
        'duration_text',
        'category_name',
    ];

    const CACHE_KEY_ACTIVE = 'chinchins_bag_items_active_v1';
    protected static ?\Illuminate\Support\Collection $_staticCachedItems = null;

    /**
     * Clear active cache on save/delete.
     */
    protected static function booted(): void
    {
        static::saved(function () {
            static::clearCache();
        });
        static::deleted(function () {
            static::clearCache();
        });
    }

    public static function clearCache(): void
    {
        static::$_staticCachedItems = null;
        Cache::forget(static::CACHE_KEY_ACTIVE);
    }

    /**
     * Categories dictionary matching Flutter App My Bag tabs.
     */
    public static function categories(): array
    {
        return [
            'coupon'          => 'Coupon',
            'avatar_frame'    => 'Avatar Frame',
            'chat_style'      => 'Chat Style',
            'profile_card'    => 'Profile Card',
            'entrance_bubble' => 'Entrance Bubble',
            'big_entrance'    => 'Big Entrance',
        ];
    }

    /**
     * Category Icons dictionary.
     */
    public static function categoryIcons(): array
    {
        return [
            'coupon'          => 'fa-solid fa-ticket',
            'avatar_frame'    => 'fa-solid fa-circle-notch',
            'chat_style'      => 'fa-solid fa-comment-dots',
            'profile_card'    => 'fa-solid fa-id-card-clip',
            'entrance_bubble' => 'fa-solid fa-coins',
            'big_entrance'    => 'fa-solid fa-car-side',
        ];
    }

    public function getCategoryNameAttribute(): string
    {
        return static::categories()[$this->category] ?? ucfirst(str_replace('_', ' ', $this->category));
    }

    public function getIconFullUrlAttribute(): ?string
    {
        $src = $this->attributes['icon_url'] ?? $this->attributes['image_url'] ?? null;
        if (empty($src)) return null;
        return CoinPackage::resolveAssetUrl($src);
    }

    public function getImageFullUrlAttribute(): ?string
    {
        $src = $this->attributes['image_url'] ?? $this->attributes['icon_url'] ?? null;
        if (empty($src)) return null;
        return CoinPackage::resolveAssetUrl($src);
    }

    public function getIconUrlAttribute(): ?string
    {
        return $this->getIconFullUrlAttribute();
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->getImageFullUrlAttribute();
    }

    public function getPngUrlAttribute(): ?string
    {
        $src = $this->attributes['image_url'] ?? $this->attributes['icon_url'] ?? $this->attributes['preview_url'] ?? null;
        if (empty($src)) return null;
        $pngPath = preg_replace('/\.svg(\?.*)?$/i', '.png$1', $src);
        return CoinPackage::resolveAssetUrl($pngPath);
    }

    public function getSvgUrlAttribute(): ?string
    {
        $src = $this->attributes['image_url'] ?? $this->attributes['icon_url'] ?? $this->attributes['preview_url'] ?? null;
        if (empty($src)) return null;
        return CoinPackage::resolveAssetUrl($src);
    }

    public function getPreviewFullUrlAttribute(): ?string
    {
        $src = $this->attributes['preview_url'] ?? $this->attributes['image_url'] ?? $this->attributes['icon_url'] ?? null;
        if (empty($src)) return null;
        return CoinPackage::resolveAssetUrl($src);
    }

    public function getPreviewUrlAttribute(): ?string
    {
        return $this->getPreviewFullUrlAttribute();
    }

    public function getAnimationFullUrlAttribute(): ?string
    {
        $src = $this->attributes['animation_url'] ?? $this->attributes['preview_url'] ?? $this->attributes['image_url'] ?? null;
        if (empty($src)) return null;
        return CoinPackage::resolveAssetUrl($src);
    }

    public function getAnimationUrlAttribute(): ?string
    {
        return $this->getAnimationFullUrlAttribute();
    }

    public function getFormattedPriceAttribute(): string
    {
        if ($this->price_coins > 0) {
            return number_format($this->price_coins) . ' Gems';
        }
        if ($this->price_bdt > 0) {
            return '৳' . number_format($this->price_bdt, 2);
        }
        return 'Free';
    }

    public function getDurationTextAttribute(): string
    {
        if ($this->duration_days <= 0) {
            return 'Permanent / One-time';
        }
        return $this->duration_days . ' Days';
    }

    /**
     * User bag items ownership relation.
     */
    public function userBagItems()
    {
        return $this->hasMany(UserBagItem::class, 'bag_item_id');
    }

    /**
     * Serialize to API structure.
     */
    public function toApiArray(): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'category'         => $this->category,
            'category_name'    => $this->category_name,
            'code'             => $this->code,
            'price_coins'      => (int) $this->price_coins,
            'price_bdt'        => (float) $this->price_bdt,
            'duration_days'    => (int) $this->duration_days,
            'badge'            => $this->badge,
            'discount_percent' => (int) $this->discount_percent,
            'coupon_value'     => (int) $this->coupon_value,
            'icon_url'         => $this->icon_full_url,
            'image_url'        => $this->image_full_url,
            'preview_url'      => $this->preview_full_url,
            'animation_url'    => $this->animation_full_url,
            'png_url'          => $this->png_url,
            'svg_url'          => $this->svg_url,
            'format'           => $this->format,
            'effect_type'      => $this->effect_type,
            'description'      => $this->description,
            'is_active'        => (bool) $this->is_active,
            'is_giftable'      => (bool) $this->is_giftable,
            'sort_order'       => (int) $this->sort_order,
            'attributes'       => $this->attributes ?? [],
        ];
    }

    /**
     * Seed initial items (disabled - items are created manually via Admin Panel).
     */
    public static function seedDefaultItems(): void
    {
        // No-op: Bag items are created manually via Admin Panel
    }
}
