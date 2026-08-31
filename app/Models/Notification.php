<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'actor_id',
        'type',
        'title',
        'message',
        'data',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data'    => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeProfileViews($query)
    {
        return $query->where('type', 'profile_view');
    }

    /**
     * Send or create notification helper.
     */
    public static function createNotification(
        int $userId,
        ?int $actorId,
        string $type,
        string $title,
        string $message,
        array $data = []
    ): self {
        return self::create([
            'user_id'  => $userId,
            'actor_id' => $actorId,
            'type'     => $type,
            'title'    => $title,
            'message'  => $message,
            'data'     => $data,
            'is_read'  => false,
        ]);
    }
}
