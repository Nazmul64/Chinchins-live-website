<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Call extends Model
{
    use HasFactory;

    protected $table = 'calls';

    protected $fillable = [
        'caller_id',
        'receiver_id',
        'call_type',
        'status',
        'room_id',
        'started_at',
        'answered_at',
        'ended_at',
        'ended_by',
    ];

    protected $casts = [
        'started_at'  => 'datetime',
        'answered_at' => 'datetime',
        'ended_at'    => 'datetime',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    /**
     * Caller user relationship.
     */
    public function caller()
    {
        return $this->belongsTo(User::class, 'caller_id');
    }

    /**
     * Receiver user relationship.
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * User who terminated the call.
     */
    public function endedBy()
    {
        return $this->belongsTo(User::class, 'ended_by');
    }

    /**
     * Scope for active / pending calls.
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['calling', 'ringing', 'accepted']);
    }

    /**
     * Scope for a specific user's calls (as caller or receiver).
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('caller_id', $userId)
              ->orWhere('receiver_id', $userId);
        });
    }
}
