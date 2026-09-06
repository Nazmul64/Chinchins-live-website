<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockedUser extends Model
{
    use HasFactory;

    protected $table = 'blocked_users';

    protected $fillable = [
        'user_id',
        'blocked_user_id',
        'reason',
    ];

    /**
     * The user who blocked.
     */
    public function blocker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The user who is blocked.
     */
    public function blockedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_user_id');
    }
}
