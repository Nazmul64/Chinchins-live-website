<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'type', // text, image, voice, video_call, audio_call
        'message',
        'media_url',
        'duration',
        'is_read',
        'read_at',
        'is_free',
        'coin_cost',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_free' => 'boolean',
        'duration' => 'integer',
        'coin_cost' => 'integer',
        'read_at' => 'datetime',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Scope query to a specific conversation between two users.
     */
    public function scopeBetween($query, int $user1, int $user2)
    {
        return $query->where(function ($q) use ($user1, $user2) {
            $q->where('sender_id', $user1)->where('receiver_id', $user2);
        })->orWhere(function ($q) use ($user1, $user2) {
            $q->where('sender_id', $user2)->where('receiver_id', $user1);
        });
    }
}
