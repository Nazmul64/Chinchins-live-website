<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $table = 'messages';

    protected $fillable = [
        'client_uuid',
        'conversation_id',
        'call_id',
        'call_session_id',
        'sent_during_call',
        'sender_id',
        'receiver_id',
        'message',
        'type',
        'media_url',
        'is_read',
    ];

    protected $casts = [
        'conversation_id'  => 'integer',
        'sender_id'        => 'integer',
        'receiver_id'      => 'integer',
        'sent_during_call' => 'boolean',
        'is_read'          => 'boolean',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
