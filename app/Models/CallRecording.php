<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallRecording extends Model
{
    use HasFactory;

    protected $table = 'call_recordings';

    protected $fillable = [
        'call_session_id',
        'caller_id',
        'receiver_id',
        'call_type',
        'duration_seconds',
        'recording_url',
        'snapshot_urls',
        'engine',
        'agora_sid',
        'agora_resource_id',
        'status',
    ];

    protected $casts = [
        'snapshot_urls' => 'array',
        'duration_seconds' => 'integer',
    ];

    public function caller()
    {
        return $this->belongsTo(User::class, 'caller_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
