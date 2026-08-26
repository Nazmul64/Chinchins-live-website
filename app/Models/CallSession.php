<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'caller_id',
        'receiver_id',
        'channel_name',
        'status',
        'rate_per_minute',
        'started_at',
        'ended_at',
        'duration_seconds',
        'coins_deducted',
        'caller_balance_after',
    ];

    protected $casts = [
        'rate_per_minute' => 'integer',
        'duration_seconds' => 'integer',
        'coins_deducted' => 'integer',
        'caller_balance_after' => 'integer',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
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
