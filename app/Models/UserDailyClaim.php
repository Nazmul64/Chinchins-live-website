<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDailyClaim extends Model
{
    use HasFactory;

    protected $table = 'user_daily_claims';

    protected $fillable = [
        'user_id',
        'day_claimed',
        'coins_awarded',
        'claimed_at',
    ];

    protected $casts = [
        'day_claimed'   => 'integer',
        'coins_awarded' => 'integer',
        'claimed_at'    => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
