<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartyRoomSeat extends Model
{
    use HasFactory;

    protected $fillable = [
        'party_room_id',
        'seat_index',
        'user_id',
        'role',
        'is_muted',
        'is_video_muted',
        'is_locked',
        'coins_spent',
        'joined_at',
        'last_billed_at',
        'status',
    ];

    protected $casts = [
        'seat_index' => 'integer',
        'is_muted' => 'boolean',
        'is_video_muted' => 'boolean',
        'is_locked' => 'boolean',
        'coins_spent' => 'integer',
        'joined_at' => 'datetime',
        'last_billed_at' => 'datetime',
    ];

    protected $appends = [
        'user_profile',
    ];

    public function partyRoom(): BelongsTo
    {
        return $this->belongsTo(PartyRoom::class, 'party_room_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Compact User Profile for Mobile App Seat Rendering.
     */
    public function getUserProfileAttribute(): ?array
    {
        if (!$this->user_id || !$this->user) {
            return null;
        }

        $user = $this->user;
        return [
            'id' => $user->id,
            'account_id' => $user->account_id,
            'name' => $user->display_name ?? $user->name ?? 'Guest',
            'avatar_url' => $user->avatar_url,
            'avatar_frame_url' => $user->avatar_frame_url,
            'level' => (int) ($user->level ?? 1),
            'coins' => (int) ($user->coins ?? 0),
            'gender' => $user->gender ?? 'unspecified',
            'is_host' => ($this->role === 'host' || $this->seat_index === 1),
        ];
    }
}
