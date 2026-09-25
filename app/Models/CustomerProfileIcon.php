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
     * Seed and initialize the 10 standard Profile Icons required for the "Me" screen.
     */
    public static function seedDefaultIcons(): void
    {
        $destinationPath = public_path('uploads/customer_profile_icon');
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0777, true, true);
        }

        $defaultList = [
            [
                'key'               => 'my_gems',
                'title'             => 'My Gems',
                'subtitle'          => 'User Diamond & Gem Balance',
                'category'          => 'wallet_card',
                'default_icon_path' => 'uploads/customer_profile_icon/default_my_gems.png',
                'badge_text'        => null,
                'badge_color'       => null,
                'target_route'      => 'wallet/gems',
                'sort_order'        => 1,
                'is_active'         => true,
            ],
            [
                'key'               => 'beans_center',
                'title'             => 'Beans Center',
                'subtitle'          => 'Beans & Earnings Exchange',
                'category'          => 'wallet_card',
                'default_icon_path' => 'uploads/customer_profile_icon/default_beans_center.png',
                'badge_text'        => null,
                'badge_color'       => null,
                'target_route'      => 'wallet/beans',
                'sort_order'        => 2,
                'is_active'         => true,
            ],
            [
                'key'               => 'spend_less_card',
                'title'             => 'Spend Less, Get More Gems!',
                'subtitle'          => 'Update to New User Weekly Card',
                'category'          => 'banner_card',
                'default_icon_path' => 'uploads/customer_profile_icon/default_spend_less_card.png',
                'badge_text'        => 'big discount',
                'badge_color'       => '#FEF08A',
                'target_route'      => 'wallet/spend_less',
                'sort_order'        => 3,
                'is_active'         => true,
            ],
            [
                'key'               => 'svip',
                'title'             => 'SVIP',
                'subtitle'          => 'Exclusive SVIP & VIP Privileges',
                'category'          => 'action_menu',
                'default_icon_path' => 'uploads/customer_profile_icon/default_svip.png',
                'badge_text'        => null,
                'badge_color'       => null,
                'target_route'      => 'wallet/svip',
                'sort_order'        => 4,
                'is_active'         => true,
            ],
            [
                'key'               => 'my_bag',
                'title'             => 'My Bag',
                'subtitle'          => 'User Inventory & Backpack Items',
                'category'          => 'action_menu',
                'default_icon_path' => 'uploads/customer_profile_icon/default_my_bag.png',
                'badge_text'        => null,
                'badge_color'       => null,
                'target_route'      => 'bag/my_bag',
                'sort_order'        => 5,
                'is_active'         => true,
            ],
            [
                'key'               => 'gems_center',
                'title'             => 'Gems Center',
                'subtitle'          => 'Recharge Gems & Packages Store',
                'category'          => 'action_menu',
                'default_icon_path' => 'uploads/customer_profile_icon/default_gems_center.png',
                'badge_text'        => null,
                'badge_color'       => null,
                'target_route'      => 'wallet/gems_center',
                'sort_order'        => 6,
                'is_active'         => true,
            ],
            [
                'key'               => 'payment_details',
                'title'             => 'Payment details',
                'subtitle'          => 'Wallet, Transactions & Payouts',
                'category'          => 'action_menu',
                'default_icon_path' => 'uploads/customer_profile_icon/default_payment_details.png',
                'badge_text'        => null,
                'badge_color'       => null,
                'target_route'      => 'wallet/payment_details',
                'sort_order'        => 7,
                'is_active'         => true,
            ],
            [
                'key'               => 'my_level',
                'title'             => 'My Level',
                'subtitle'          => 'Level Badge, Experience & Privileges',
                'category'          => 'action_menu',
                'default_icon_path' => 'uploads/customer_profile_icon/default_my_level.png',
                'badge_text'        => null,
                'badge_color'       => null,
                'target_route'      => 'profile/my_level',
                'sort_order'        => 8,
                'is_active'         => true,
            ],
            [
                'key'               => 'sign_in',
                'title'             => 'Sign-In',
                'subtitle'          => 'Daily Check-in & Free Claim',
                'category'          => 'action_menu',
                'default_icon_path' => 'uploads/customer_profile_icon/default_sign_in.png',
                'badge_text'        => null,
                'badge_color'       => null,
                'target_route'      => 'daily_checkin',
                'sort_order'        => 9,
                'is_active'         => true,
            ],
            [
                'key'               => 'reward',
                'title'             => 'Reward',
                'subtitle'          => 'Tasks, Quests & Milestone Gifts',
                'category'          => 'action_menu',
                'default_icon_path' => 'uploads/customer_profile_icon/default_reward.png',
                'badge_text'        => null,
                'badge_color'       => null,
                'target_route'      => 'rewards',
                'sort_order'        => 10,
                'is_active'         => true,
            ],
        ];

        foreach ($defaultList as $item) {
            self::updateOrCreate(
                ['key' => $item['key']],
                [
                    'title'             => $item['title'],
                    'subtitle'          => $item['subtitle'],
                    'category'          => $item['category'],
                    'default_icon_path' => $item['default_icon_path'],
                    'badge_text'        => $item['badge_text'],
                    'badge_color'       => $item['badge_color'],
                    'target_route'      => $item['target_route'],
                    'sort_order'        => $item['sort_order'],
                    'is_active'         => $item['is_active'],
                ]
            );
        }
    }
}
