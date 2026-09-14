<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveParticipant extends Model
{
    use HasFactory;

    protected $table = 'live_participants';

    protected $fillable = [
        'live_stream_id',
        'user_id',
        'role',          // 'host', 'guest', 'viewer'
        'is_muted',
        'video_enabled',
        'joined_at',
        'left_at',
    ];

    protected $casts = [
        'is_muted'      => 'boolean',
        'video_enabled' => 'boolean',
        'joined_at'     => 'datetime',
        'left_at'       => 'datetime',
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
