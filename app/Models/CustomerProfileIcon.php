<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class CustomerProfileIcon extends Model
{
    use HasFactory;

    protected $table = 'customer_profile_icons';

    protected $fillable = [
        'key',
        'title',
        'subtitle',
        'category',
        'icon_path',
        'default_icon_path',
        'badge_text',
        'badge_color',
        'target_route',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active'  => 'boolean',
    ];

    protected $appends = [
        'icon_url',
        'default_icon_url',
        'is_custom',
        'has_badge',
    ];

    /**
     * Get the active display icon URL (custom uploaded if available, otherwise default).
     */
    public function getIconUrlAttribute(): string
    {
        if (!empty($this->icon_path)) {
            $fullPath = public_path($this->icon_path);
            if (File::exists($fullPath)) {
                return url($this->icon_path);
            }
            if (str_starts_with($this->icon_path, 'http://') || str_starts_with($this->icon_path, 'https://')) {
                return $this->icon_path;
            }
            return url($this->icon_path);
        }

        return $this->default_icon_url;
    }

    /**
     * Get the built-in default icon URL.
     */
    public function getDefaultIconUrlAttribute(): string
    {
        if (!empty($this->default_icon_path)) {
            if (str_starts_with($this->default_icon_path, 'http://') || str_starts_with($this->default_icon_path, 'https://')) {
                return $this->default_icon_path;
            }
            return url($this->default_icon_path);
        }

        return url("uploads/customer_profile_icon/default_{$this->key}.png");
    }

    /**
     * Check if a custom icon image has been uploaded by admin.
     */
    public function getIsCustomAttribute(): bool
    {
        return !empty($this->icon_path);
    }

    /**
     * Check if a badge is set.
     */
    public function getHasBadgeAttribute(): bool
    {
        return !empty($this->badge_text);
    }

    /**
     * Seed default icons (disabled - icons are managed manually via Admin Panel).
     */
    public static function seedDefaultIcons(): void
    {
        // No-op: Customer profile icons are managed manually via Admin Panel
    }
}
