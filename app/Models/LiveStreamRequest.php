<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveStreamRequest extends Model
{
    use HasFactory;

    protected $table = 'live_stream_requests';

    protected $fillable = [
        'live_stream_id',
        'user_id',
        'status', // 'pending', 'accepted', 'rejected', 'ended'
    ];

    public function liveStream(): BelongsTo
    {
        return $this->belongsTo(LiveStream::class, 'live_stream_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
