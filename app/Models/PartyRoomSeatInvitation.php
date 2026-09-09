<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartyRoomSeatInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'party_room_id',
        'host_id',
        'user_id',
        'seat_index',
        'status',
    ];

    protected $casts = [
        'seat_index' => 'integer',
    ];

    protected $appends = [
        'host_profile',
        'user_profile',
    ];

    public function partyRoom(): BelongsTo
    {
        return $this->belongsTo(PartyRoom::class, 'party_room_id');
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getHostProfileAttribute(): ?array
    {
        if (!$this->host) return null;
        return [
            'id' => $this->host->id,
            'account_id' => $this->host->account_id,
            'name' => $this->host->display_name ?? $this->host->name,
            'avatar_url' => $this->host->avatar_url,
        ];
    }

    public function getUserProfileAttribute(): ?array
    {
        if (!$this->user) return null;
        return [
            'id' => $this->user->id,
            'account_id' => $this->user->account_id,
            'name' => $this->user->display_name ?? $this->user->name,
            'avatar_url' => $this->user->avatar_url,
        ];
    }
}
