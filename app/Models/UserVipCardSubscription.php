<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVipCardSubscription extends Model
{
    use HasFactory;

    protected $table = 'user_vip_card_subscriptions';

    protected $fillable = [
        'user_id',
        'vip_card_id',
        'card_type',
        'price_paid',
        'payment_method',
        'started_at',
        'expires_at',
        'claimed_days',
        'last_claimed_at',
        'status',
    ];

    protected $casts = [
        'price_paid'      => 'decimal:2',
        'started_at'      => 'datetime',
        'expires_at'      => 'datetime',
        'claimed_days'    => 'array',
        'last_claimed_at' => 'datetime',
    ];

    /**
     * User who owns this subscription.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The VIP card plan.
     */
    public function card()
    {
        return $this->belongsTo(VipPrivilegeCard::class, 'vip_card_id');
    }

    /**
     * Check if the subscription is actively ongoing.
     */
    public function getIsActiveAttribute(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->expires_at && Carbon::now()->greaterThan($this->expires_at)) {
            return false;
        }

        return true;
    }

    /**
     * Calculate current day number since subscription start (1-indexed).
     */
    public function getCurrentDayNumber(): int
    {
        if (!$this->started_at) {
            return 1;
        }

        $daysPassed = Carbon::now()->diffInDays($this->started_at);
        return (int) min(($daysPassed + 1), ($this->card?->duration_days ?? 30));
    }

    /**
     * Check if today's reward has already been claimed.
     */
    public function hasClaimedToday(): bool
    {
        $currentDay = $this->getCurrentDayNumber();
        $claimed = $this->claimed_days ?? [];
        return in_array($currentDay, $claimed);
    }
}
