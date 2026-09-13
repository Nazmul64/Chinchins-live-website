<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallMessage extends Model
{
    use HasFactory;

    protected $table = 'call_messages';

    protected $fillable = [
        'call_session_id',
        'sender_id',
        'receiver_id',
        'type',
        'message',
        'image_url',
        'file_path',
        'metadata',
        'is_read',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_read' => 'boolean',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
