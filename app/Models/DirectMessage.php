<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DirectMessage extends Model
{
    use HasFactory;

    protected $table = 'direct_messages';

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'receiver_id',
        'message',
        'attachment_path',
        'type',
        'is_read',
    ];

    protected $casts = [
        'conversation_id' => 'integer',
        'sender_id'       => 'integer',
        'receiver_id'     => 'integer',
        'is_read'         => 'boolean',
    ];

    protected $appends = [
        'image_url',
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

    public function getImageUrlAttribute(): ?string
    {
        return $this->attachment_path;
    }
}
