<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAdminSupportMessage extends Model
{
    use HasFactory;

    protected $table = 'user_admin_support_messages';

    protected $fillable = [
        'user_id',
        'admin_id',
        'sender_type', // 'user' or 'admin'
        'type', // 'text', 'image', 'voice', 'system'
        'message',
        'media_url',
        'is_read_by_admin',
        'is_read_by_user',
        'read_at',
    ];

    protected $casts = [
        'is_read_by_admin' => 'boolean',
        'is_read_by_user' => 'boolean',
        'read_at' => 'datetime',
    ];

    protected $appends = [
        'full_media_url',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function getFullMediaUrlAttribute(): ?string
    {
        if (!empty($this->media_url)) {
            if (str_starts_with($this->media_url, 'http://') || str_starts_with($this->media_url, 'https://')) {
                return $this->media_url;
            }
            return asset(ltrim(str_replace('public/', '', $this->media_url), '/'));
        }
        return null;
    }
}
