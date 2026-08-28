<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'caller_id',
        'receiver_id',
        'channel_name',
        'call_type',
        'status',
        'rate_per_minute',
        'is_free_trial',
        'free_duration_seconds',
        'started_at',
        'ended_at',
        'duration_seconds',
        'coins_deducted',
        'host_earned_coins',
        'admin_revenue_coins',
        'caller_balance_after',
        'is_random_match',
    ];

    protected $casts = [
        'rate_per_minute' => 'integer',
        'duration_seconds' => 'integer',
        'coins_deducted' => 'integer',
        'host_earned_coins' => 'integer',
        'admin_revenue_coins' => 'integer',
        'caller_balance_after' => 'integer',
        'free_duration_seconds' => 'integer',
        'is_free_trial' => 'boolean',
        'is_random_match' => 'boolean',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    protected $appends = [
        'formatted_duration',
        'formatted_coins_deducted',
        'formatted_host_earned_coins',
        'status_badge_class',
    ];

    public function caller()
    {
        return $this->belongsTo(User::class, 'caller_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function getFormattedDurationAttribute(): string
    {
        $secs = $this->duration_seconds ?: 0;
        $mins = floor($secs / 60);
        $remSecs = $secs % 60;
        return sprintf('%02d:%02d', $mins, $remSecs);
    }

    public function getFormattedCoinsDeductedAttribute(): string
    {
        return number_format($this->coins_deducted) . ' Coins';
    }

    public function getFormattedHostEarnedCoinsAttribute(): string
    {
        return number_format($this->host_earned_coins) . ' Coins';
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'connected' => 'badge-soft-success',
            'ended' => 'badge-soft-secondary',
            'initiated', 'ringing' => 'badge-soft-warning',
            'rejected', 'declined', 'cancelled', 'missed', 'failed' => 'badge-soft-danger',
            default => 'badge-soft-info',
        };
    }
}
