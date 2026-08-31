<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserGift extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sender_id',
        'gift_id',
        'quantity',
        'coins_per_unit',
        'total_coins',
        'call_session_id',
        'context',
    ];

    protected $casts = [
        'user_id'        => 'integer',
        'sender_id'      => 'integer',
        'gift_id'        => 'integer',
        'quantity'       => 'integer',
        'coins_per_unit' => 'integer',
        'total_coins'    => 'integer',
    ];

    protected $appends = [
        'formatted_coins_per_unit',
        'formatted_total_coins',
        'count_label',
    ];

    public function getFormattedCoinsPerUnitAttribute(): string
    {
        return Gift::formatCoins($this->coins_per_unit ?: ($this->gift ? $this->gift->coins : 0));
    }

    public function getFormattedTotalCoinsAttribute(): string
    {
        return Gift::formatCoins($this->total_coins);
    }

    public function getCountLabelAttribute(): string
    {
        return 'x' . ($this->quantity ?: 1);
    }

    /**
     * Receiver / Host user relationship.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Sender / Gifter user relationship.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Gift reference.
     */
    public function gift()
    {
        return $this->belongsTo(Gift::class, 'gift_id');
    }
}
