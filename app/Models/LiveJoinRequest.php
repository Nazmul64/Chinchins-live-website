<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveJoinRequest extends Model
{
    use HasFactory;

    protected $table = 'live_join_requests';

    protected $fillable = [
        'live_stream_id',
        'user_id',
        'status', // 'pending', 'accepted', 'rejected', 'cancelled'
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
