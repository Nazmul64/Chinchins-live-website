<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBagItem extends Model
{
    use HasFactory;

    protected $table = 'user_bag_items';

    protected $fillable = [
        'user_id',
        'bag_item_id',
        'quantity',
        'status',
        'is_equipped',
        'acquired_from',
        'sender_id',
        'started_at',
        'expires_at',
        'used_at',
        'metadata',
    ];

    protected $casts = [
        'quantity'    => 'integer',
        'is_equipped' => 'boolean',
        'started_at'  => 'datetime',
        'expires_at'  => 'datetime',
        'used_at'     => 'datetime',
        'metadata'    => 'array',
    ];

    protected $appends = [
        'is_valid',
        'remaining_seconds',
        'remaining_days_text',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function bagItem()
    {
        return $this->belongsTo(BagItem::class, 'bag_item_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Check if item is valid and not expired.
     */
    public function getIsValidAttribute(): bool
    {
        if ($this->status === 'expired') return false;
        if ($this->expires_at === null) return true;
        return $this->expires_at->isFuture();
    }

    /**
     * Remaining seconds until expiration.
     */
    public function getRemainingSecondsAttribute(): int
    {
        if ($this->expires_at === null) return 9999999;
        return max(0, Carbon::now()->diffInSeconds($this->expires_at, false));
    }

    /**
     * Formatted remaining duration text (e.g., "6 Days Left", "Expired", "Permanent").
     */
    public function getRemainingDaysTextAttribute(): string
    {
        if ($this->status === 'expired' || ($this->expires_at && $this->expires_at->isPast())) {
            return 'Expired';
        }
        if ($this->expires_at === null) {
            return 'Permanent';
        }

        $days = Carbon::now()->diffInDays($this->expires_at);
        if ($days >= 1) {
            return $days . ' Days Left';
        }

        $hours = Carbon::now()->diffInHours($this->expires_at);
        if ($hours >= 1) {
            return $hours . ' Hours Left';
        }

        $mins = Carbon::now()->diffInMinutes($this->expires_at);
        return max(1, $mins) . ' Mins Left';
    }
}
