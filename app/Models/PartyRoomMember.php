<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartyRoomMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'party_room_id',
        'user_id',
        'role',
        'total_coins_spent',
        'joined_at',
        'last_active_at',
        'left_at',
        'status',
    ];

    protected $casts = [
        'total_coins_spent' => 'integer',
        'joined_at' => 'datetime',
        'last_active_at' => 'datetime',
        'left_at' => 'datetime',
    ];

    public function partyRoom(): BelongsTo
    {
        return $this->belongsTo(PartyRoom::class, 'party_room_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
