<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PartyRoomMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'party_room_id',
        'user_id',
        'type',
        'message',
        'image_url',
        'gift_id',
        'gift_count',
        'coins_amount',
        'receiver_id',
        'extra_data',
    ];

    protected $casts = [
        'gift_count' => 'integer',
        'coins_amount' => 'integer',
        'extra_data' => 'array',
    ];

    protected $appends = [
        'full_image_url',
        'user_profile',
        'receiver_profile',
    ];

    public function partyRoom(): BelongsTo
    {
        return $this->belongsTo(PartyRoom::class, 'party_room_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function gift(): BelongsTo
    {
        return $this->belongsTo(Gift::class, 'gift_id');
    }

    /**
     * Full URL for Image Attachment.
     */
    public function getFullImageUrlAttribute(): ?string
    {
        if (empty($this->image_url)) {
            return null;
        }

        if (Str::startsWith($this->image_url, ['http://', 'https://'])) {
            return $this->image_url;
        }

        return url($this->image_url);
    }

    /**
     * Compact User Profile of Sender.
     */
    public function getUserProfileAttribute(): ?array
    {
        if (!$this->user) {
            return null;
        }

        return [
            'id' => $this->user->id,
            'account_id' => $this->user->account_id,
            'name' => $this->user->display_name ?? $this->user->name ?? 'User',
            'avatar_url' => $this->user->avatar_url,
            'avatar_frame_url' => $this->user->avatar_frame_url,
            'level' => (int) ($this->user->level ?? 1),
        ];
    }

    /**
     * Compact User Profile of Receiver (for gifts).
     */
    public function getReceiverProfileAttribute(): ?array
    {
        if (!$this->receiver) {
            return null;
        }

        return [
            'id' => $this->receiver->id,
            'account_id' => $this->receiver->account_id,
            'name' => $this->receiver->display_name ?? $this->receiver->name ?? 'User',
            'avatar_url' => $this->receiver->avatar_url,
            'avatar_frame_url' => $this->receiver->avatar_frame_url,
            'level' => (int) ($this->receiver->level ?? 1),
        ];
    }
}
