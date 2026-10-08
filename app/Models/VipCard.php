<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VipCard extends VipPrivilegeCard
{
    use HasFactory;

    protected $table = 'vip_privilege_cards';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'card_type',
        'name',
        'category_name',
        'badge_text',
        'price',
        'price_bdt',
        'original_price_bdt',
        'price_coins',
        'cost_diamonds',
        'diamonds_reward',
        'daily_checkin_diamonds',
        'perks',
        'outfits',
        'duration_days',
        'instant_reward_coins',
        'instant_reward_text',
        'daily_checkin_total_coins',
        'daily_checkin_text',
        'total_return_coins',
        'daily_schedule',
        'extra_rewards',
        'description',
        'card_color',
        'banner_tag',
        'icon_url',
        'animation_url',
        'bg_image_url',
        'format',
        'is_active',
        'sort_order',
    ];
}
