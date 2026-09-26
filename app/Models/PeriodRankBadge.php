<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodRankBadge extends Model
{
    use HasFactory;

    protected $table = 'period_rank_badges';

    protected $fillable = [
        'badge_name',
        'period_type',
        'category',
        'rank_position',
        'min_required_coins',
        'badge_icon',
        'avatar_frame',
        'is_active',
    ];

    protected $casts = [
        'rank_position'      => 'integer',
        'min_required_coins' => 'integer',
        'is_active'          => 'boolean',
    ];

    protected $appends = [
        'badge_icon_url',
        'avatar_frame_url',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_period_badges', 'badge_id', 'user_id')
                    ->withPivot('awarded_date', 'is_equipped')
                    ->withTimestamps();
    }

    public function getBadgeIconUrlAttribute(): ?string
    {
        if (empty($this->badge_icon)) {
            return null;
        }

        if (str_starts_with($this->badge_icon, 'http://') || str_starts_with($this->badge_icon, 'https://')) {
            return $this->badge_icon;
        }

        return asset($this->badge_icon);
    }

    public function getAvatarFrameUrlAttribute(): ?string
    {
        if (empty($this->avatar_frame)) {
            return null;
        }

        if (str_starts_with($this->avatar_frame, 'http://') || str_starts_with($this->avatar_frame, 'https://')) {
            return $this->avatar_frame;
        }

        return asset($this->avatar_frame);
    }
}
