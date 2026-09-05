<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSpendLessSubscription extends Model
{
    use HasFactory;

    protected $table = 'user_spend_less_subscriptions';

    protected $fillable = [
        'user_id',
        'spend_less_card_id',
        'card_type',
        'status',
        'started_at',
        'expires_at',
        'last_claimed_at',
        'claimed_days_count',
        'claimed_days',
    ];

    protected $casts = [
        'started_at'         => 'datetime',
        'expires_at'         => 'datetime',
        'last_claimed_at'    => 'datetime',
        'claimed_days_count' => 'integer',
        'claimed_days'       => 'array',
    ];

    protected $appends = [
        'is_active',
        'remaining_seconds',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function card()
    {
        return $this->belongsTo(SpendLessCard::class, 'spend_less_card_id');
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active' && $this->expires_at && $this->expires_at->isFuture();
    }

    public function getRemainingSecondsAttribute(): int
    {
        if (!$this->is_active) {
            return 0;
        }
        return max(0, Carbon::now()->diffInSeconds($this->expires_at, false));
    }

    public function getCurrentDayNumber(): int
    {
        if (!$this->started_at) {
            return 1;
        }
        $daysPassed = $this->started_at->diffInDays(Carbon::now());
        $maxDays = $this->card ? $this->card->duration_days : 30;
        return min((int) ($daysPassed + 1), $maxDays);
    }

    public function hasClaimedToday(): bool
    {
        if (!$this->last_claimed_at) {
            return false;
        }
        return $this->last_claimed_at->isToday();
    }
}
