<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFollow extends Model
{
    use HasFactory;

    protected $table = 'user_follows';

    protected $fillable = [
        'user_id',
        'follower_id',
        'source',
    ];

    /**
     * The user being followed.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The follower user.
     */
    public function follower()
    {
        return $this->belongsTo(User::class, 'follower_id');
    }
}
