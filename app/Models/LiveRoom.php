<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveRoom extends Model
{
    use HasFactory;

    protected $table = 'live_rooms';

    protected $fillable = [
        'host_id',
        'channel_name',
        'title',
        'type',
        'status',
        'viewer_count',
        'total_diamonds_earned',
        'cover_image',
        'stream_token',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'host_id'               => 'integer',
        'viewer_count'          => 'integer',
        'total_diamonds_earned' => 'integer',
        'started_at'            => 'datetime',
        'ended_at'              => 'datetime',
    ];

    public function host()
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function participants()
    {
        return $this->hasMany(LiveParticipant::class, 'live_room_id');
    }

    public function activeParticipants()
    {
        return $this->hasMany(LiveParticipant::class, 'live_room_id')->whereNull('left_at');
    }

    public function messages()
    {
        return $this->hasMany(LiveMessage::class, 'live_room_id');
    }

    public function giftTransactions()
    {
        return $this->hasMany(GiftTransaction::class, 'live_room_id');
    }

    public function getCoverImageUrlAttribute()
    {
        if ($this->cover_image) {
            if (str_starts_with($this->cover_image, 'http')) {
                return $this->cover_image;
            }
            return url($this->cover_image);
        }
        return $this->host?->avatar_url ?? url('assets/images/default_live_cover.png');
    }
}
