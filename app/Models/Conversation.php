<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $table = 'conversations';

    protected $fillable = [
        'is_group',
        'title',
        'last_message_id',
    ];

    protected $casts = [
        'is_group'        => 'boolean',
        'last_message_id' => 'integer',
    ];

    /**
     * Participants in the conversation.
     */
    public function participants()
    {
        return $this->belongsToMany(User::class, 'conversation_participants', 'conversation_id', 'user_id')
            ->withPivot('joined_at')
            ->withTimestamps();
    }

    /**
     * Messages in this conversation.
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
