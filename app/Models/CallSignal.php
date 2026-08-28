<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallSignal extends Model
{
    use HasFactory;

    protected $table = 'call_signals';

    protected $fillable = [
        'call_session_id',
        'channel_name',
        'sender_id',
        'receiver_id',
        'type', // 'offer', 'answer', 'candidate', 'ping', 'bye', 'leave'
        'payload',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'payload' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Call session relationship.
     */
    public function callSession()
    {
        return $this->belongsTo(CallSession::class, 'call_session_id');
    }

    /**
     * Sender user relationship.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Receiver user relationship.
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
