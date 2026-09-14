<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftTransaction extends Model
{
    use HasFactory;

    protected $table = 'gift_transactions';

    protected $fillable = [
        'idempotency_key',
        'stream_id',
        'live_room_id',
        'sender_id',
        'receiver_id',
        'gift_id',
        'quantity',
        'total_coins',
        'coins_spent',
        'status',
    ];

    protected $casts = [
        'quantity'    => 'integer',
        'total_coins' => 'integer',
        'coins_spent' => 'integer',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function gift()
    {
        return $this->belongsTo(Gift::class, 'gift_id');
    }

    public function liveRoom()
    {
        return $this->belongsTo(LiveRoom::class, 'live_room_id');
    }

    public function liveStream()
    {
        return $this->belongsTo(LiveStream::class, 'stream_id');
    }
}
