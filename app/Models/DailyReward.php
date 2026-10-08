<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyReward extends Model
{
    use HasFactory;

    protected $table = 'daily_rewards';

    protected $fillable = [
        'day_number',
        'reward_coins',
        'icon_image',
        'is_active',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'day_number'   => 'integer',
        'reward_coins' => 'integer',
    ];

    /**
     * Image URL Accessor for direct mobile app use.
     */
    public function getIconImageUrlAttribute(): ?string
    {
        if (!empty($this->icon_image)) {
            if (str_starts_with($this->icon_image, 'http://') || str_starts_with($this->icon_image, 'https://')) {
                return $this->icon_image;
            }
            return asset(ltrim($this->icon_image, '/'));
        }

        if (file_exists(public_path('uploads/claim/day_' . $this->day_number . '.svg'))) {
            return asset('uploads/claim/day_' . $this->day_number . '.svg');
        }

        if (file_exists(public_path('uploads/claim/day_' . $this->day_number . '.png'))) {
            return asset('uploads/claim/day_' . $this->day_number . '.png');
        }

        return asset('uploads/claim/day_' . $this->day_number . '.svg');
    }

    protected $appends = ['icon_image_url'];
}
