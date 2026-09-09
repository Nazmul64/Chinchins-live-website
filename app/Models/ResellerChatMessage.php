<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResellerChatMessage extends Model
{
    use HasFactory;

    protected $table = 'reseller_chat_messages';

    protected $fillable = [
        'reseller_id',
        'user_id',
        'sender_type', // 'user', 'reseller', 'admin'
        'sender_id',
        'type', // 'text', 'image', 'voice', 'system', 'recharge_request', 'gift'
        'message',
        'media_url',
        'duration',
        'coins_amount',
        'amount_bdt',
        'target_account_id',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'duration' => 'integer',
        'coins_amount' => 'integer',
        'amount_bdt' => 'decimal:2',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    protected $appends = [
        'full_media_url',
    ];

    public function reseller()
    {
        return $this->belongsTo(Reseller::class, 'reseller_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
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
