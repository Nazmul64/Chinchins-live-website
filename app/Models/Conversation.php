<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $table = 'conversations';

    protected $fillable = [
        'user_one',
        'user_two',
        'last_message',
        'last_message_at',
        'is_group',
        'title',
        'last_message_id',
    ];

    protected $casts = [
        'user_one'        => 'integer',
        'user_two'        => 'integer',
        'last_message_at' => 'datetime',
        'is_group'        => 'boolean',
        'last_message_id' => 'integer',
    ];

    public function userOne()
    {
        return $this->belongsTo(User::class, 'user_one');
    }

    public function userTwo()
    {
        return $this->belongsTo(User::class, 'user_two');
    }

    public function directMessages()
    {
        return $this->hasMany(DirectMessage::class, 'conversation_id');
    }

    /**
     * Legacy Participants in the conversation.
     */
    public function participants()
    {
        return $this->belongsToMany(User::class, 'conversation_participants', 'conversation_id', 'user_id')
            ->withPivot('joined_at')
            ->withTimestamps();
    }

    /**
     * Legacy Messages in this conversation.
     */
    public function messages()
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }

    /**
     * Latest message in conversation.
     */
    public function lastMessage()
    {
        return $this->belongsTo(Message::class, 'last_message_id');
    }
}
