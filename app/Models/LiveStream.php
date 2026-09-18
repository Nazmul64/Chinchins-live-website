<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LiveStream extends Model
{
    use HasFactory;

    protected $table = 'live_streams';

    protected $fillable = [
        'host_id',
        'channel_name',
        'title',
        'cover_image',
        'status',
        'viewer_count',
        'likes_count',
        'total_diamonds_earned',
        'agora_token',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'viewer_count'          => 'integer',
        'likes_count'           => 'integer',
        'total_diamonds_earned' => 'integer',
        'started_at'            => 'datetime',
        'ended_at'              => 'datetime',
    ];

    /**
     * The host user who started the broadcast.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    /**
     * Active and past participants (host, guests, viewers).
     */
    public function participants(): HasMany
    {
        return $this->hasMany(LiveParticipant::class, 'live_stream_id');
    }

    /**
     * Active co-hosting guests.
     */
    public function guests(): HasMany
    {
        return $this->hasMany(LiveParticipant::class, 'live_stream_id')->where('role', 'guest')->whereNull('left_at');
    }

    /**
     * Active viewers.
     */
    public function viewers(): HasMany
    {
        return $this->hasMany(LiveParticipant::class, 'live_stream_id')->where('role', 'viewer')->whereNull('left_at');
    }

    /**
     * Join requests sent by viewers to co-host.
     */
    public function joinRequests(): HasMany
    {
        return $this->hasMany(LiveJoinRequest::class, 'live_stream_id');
    }

    /**
     * Chat and gift messages sent in this live stream.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(LiveMessage::class, 'live_stream_id');
    }

    /**
     * Helper to get full cover image URL.
     */
    public function getCoverImageUrlAttribute(): ?string
    {
        if (empty($this->cover_image)) {
            return $this->host?->avatar_url ?? url('assets/images/default_avatar.png');
        }
        if (str_starts_with($this->cover_image, 'http')) {
            return $this->cover_image;
        }
        return url(ltrim($this->cover_image, '/'));
    }
}
