<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class PartyRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'host_id',
        'room_title',
        'room_type',
        'topic_tag',
        'room_cover',
        'background_image',
        'channel_name',
        'max_seats',
        'coin_rate_per_minute',
        'host_commission_percentage',
        'admin_commission_percentage',
        'total_earned_coins',
        'total_admin_earned_coins',
        'status',
        'is_locked',
        'room_password',
        'announcement',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'max_seats' => 'integer',
        'coin_rate_per_minute' => 'integer',
        'host_commission_percentage' => 'float',
        'admin_commission_percentage' => 'float',
        'total_earned_coins' => 'integer',
        'total_admin_earned_coins' => 'integer',
        'is_locked' => 'boolean',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    protected $appends = [
        'room_cover_url',
        'background_image_url',
        'occupied_seats_count',
        'online_members_count',
    ];

    /**
     * Generate unique room ID (e.g. PR-XXXXXX or numeric).
     */
    public static function generateRoomId(): string
    {
        do {
            $roomId = 'PR' . strtoupper(Str::random(6));
        } while (static::where('room_id', $roomId)->exists());

        return $roomId;
    }

    /**
     * Get Room Host.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    /**
     * Get Room Seats (Seats 1 to 10).
     */
    public function seats(): HasMany
    {
        return $this->hasMany(PartyRoomSeat::class, 'party_room_id')->orderBy('seat_index', 'asc');
    }

    /**
     * Get Active Occupied Seats.
     */
    public function activeSeats(): HasMany
    {
        return $this->hasMany(PartyRoomSeat::class, 'party_room_id')
            ->where('status', 'occupied')
            ->whereNotNull('user_id')
            ->orderBy('seat_index', 'asc');
    }

    /**
     * Get In-Room Audience and Active Members.
     */
    public function members(): HasMany
    {
        return $this->hasMany(PartyRoomMember::class, 'party_room_id');
    }

    /**
     * Get Active Audience Members.
     */
    public function activeMembers(): HasMany
    {
        return $this->hasMany(PartyRoomMember::class, 'party_room_id')
            ->where('status', 'active');
    }

    /**
     * In-Room Chat, System, and Image Messages.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(PartyRoomMessage::class, 'party_room_id')->orderBy('id', 'asc');
    }

    /**
     * In-Room Invitations.
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(PartyRoomSeatInvitation::class, 'party_room_id');
    }

    /**
     * Accessor for full cover image URL.
     */
    public function getRoomCoverUrlAttribute(): ?string
    {
        if (empty($this->room_cover)) {
            return $this->host?->avatar_url ?? null;
        }

        if (Str::startsWith($this->room_cover, ['http://', 'https://'])) {
            return $this->room_cover;
        }

        return url($this->room_cover);
    }

    /**
     * Accessor for background image URL.
     */
    public function getBackgroundImageUrlAttribute(): ?string
    {
        if (empty($this->background_image)) {
            return null;
        }

        if (Str::startsWith($this->background_image, ['http://', 'https://'])) {
            return $this->background_image;
        }

        return url($this->background_image);
    }

    /**
     * Accessor for count of occupied seats.
     */
    public function getOccupiedSeatsCountAttribute(): int
    {
        return $this->seats()->where('status', 'occupied')->whereNotNull('user_id')->count();
    }

    /**
     * Accessor for active online audience/members count.
     */
    public function getOnlineMembersCountAttribute(): int
    {
        return $this->members()->where('status', 'active')->count();
    }

    /**
     * Auto initialize empty 10 seats for a newly created room.
     */
    public function initializeSeats(): void
    {
        $maxSeats = max(1, min(12, $this->max_seats ?: 10));

        for ($i = 1; $i <= $maxSeats; $i++) {
            if ($i === 1) {
                // Seat 1 is always the Host
                $this->seats()->create([
                    'seat_index' => 1,
                    'user_id' => $this->host_id,
                    'role' => 'host',
                    'is_muted' => false,
                    'is_video_muted' => false,
                    'is_locked' => false,
                    'status' => 'occupied',
                    'joined_at' => now(),
                ]);
            } else {
                // Seats 2..10 are empty guest seats
                $this->seats()->create([
                    'seat_index' => $i,
                    'user_id' => null,
                    'role' => 'guest',
                    'is_muted' => false,
                    'is_video_muted' => false,
                    'is_locked' => false,
                    'status' => 'empty',
                ]);
            }
        }
    }
}
