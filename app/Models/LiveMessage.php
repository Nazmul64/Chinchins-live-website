<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveMessage extends Model
{
    use HasFactory;

    protected $table = 'live_messages';

    protected $fillable = [
        'live_stream_id',
        'user_id',
        'message',
        'type',          // 'text', 'gift', 'system', 'audio', 'join'
        'gift_id',
        'coin_amount',
        'metadata',
    ];

    protected $casts = [
        'coin_amount' => 'integer',
        'metadata'    => 'array',
    ];

    public function liveStream(): BelongsTo
    {
        return $this->belongsTo(LiveStream::class, 'live_stream_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function gift(): BelongsTo
    {
        return $this->belongsTo(Gift::class, 'gift_id');
    }
}
