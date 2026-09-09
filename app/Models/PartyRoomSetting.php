<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartyRoomSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'default_voice_rate_per_minute',
        'default_video_rate_per_minute',
        'host_commission_percentage',
        'admin_commission_percentage',
        'max_guests_per_room',
        'is_party_room_enabled',
        'available_topic_tags',
        'default_announcement',
    ];

    protected $casts = [
        'default_voice_rate_per_minute' => 'integer',
        'default_video_rate_per_minute' => 'integer',
        'host_commission_percentage' => 'float',
        'admin_commission_percentage' => 'float',
        'max_guests_per_room' => 'integer',
        'is_party_room_enabled' => 'boolean',
        'available_topic_tags' => 'array',
    ];

    /**
     * Get or initialize default settings singleton.
     */
    public static function getSettings(): self
    {
        $settings = static::first();
        if (!$settings) {
            $settings = static::create([
                'default_voice_rate_per_minute' => 100,
                'default_video_rate_per_minute' => 100,
                'host_commission_percentage' => 50.00,
                'admin_commission_percentage' => 50.00,
                'max_guests_per_room' => 10,
                'is_party_room_enabled' => true,
                'available_topic_tags' => [
                    ['id' => 'singing', 'name' => 'Singing 🎤', 'tag' => 'Singing'],
                    ['id' => 'dating', 'name' => 'Dating ❤️', 'tag' => 'Dating'],
                    ['id' => 'party', 'name' => 'Party 💃', 'tag' => 'Party'],
                    ['id' => 'chitchat', 'name' => 'ChitChat 💬', 'tag' => 'ChitChat'],
                    ['id' => 'gaming', 'name' => 'Gaming 🎮', 'tag' => 'Gaming'],
                    ['id' => 'latenight', 'name' => 'Late Night 🌙', 'tag' => 'Late Night'],
                ],
                'default_announcement' => 'Welcome to our Live Fun Hangout 🥳✨! Please be respectful to everyone in the room.',
            ]);
        }

        return $settings;
    }
}
