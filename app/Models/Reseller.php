<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Reseller extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'resellers';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'level',
        'location',
        'age',
        'gender',
        'account_id',
        'bio',
        'discount_tag',
        'badge_title',
        'coins_balance',
        'total_sold_coins',
        'total_deposited_coins',
        'total_withdrawn_coins',
        'commission_rate',
        'is_active',
        'is_online',
        'last_seen_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'coins_balance' => 'integer',
        'total_sold_coins' => 'integer',
        'total_deposited_coins' => 'integer',
        'total_withdrawn_coins' => 'integer',
        'commission_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'is_online' => 'boolean',
        'last_seen_at' => 'datetime',
        'age' => 'integer',
    ];

    protected $appends = [
        'avatar_url',
        'status_text',
        'formatted_coins',
        'success_rate',
    ];

    public function getSuccessRateAttribute(): string
    {
        return '98.5%';
    }

    public function transfers()
    {
        return $this->hasMany(ResellerTransfer::class, 'reseller_id');
    }

    public function deposits()
    {
        return $this->hasMany(ResellerDeposit::class, 'reseller_id');
    }

    public function withdrawals()
    {
        return $this->hasMany(ResellerWithdrawal::class, 'reseller_id');
    }

    public function chatMessages()
    {
        return $this->hasMany(ResellerChatMessage::class, 'reseller_id');
    }

    public function getAvatarUrlAttribute(): string
    {
        if (!empty($this->avatar)) {
            if (str_starts_with($this->avatar, 'http://') || str_starts_with($this->avatar, 'https://') || str_starts_with($this->avatar, 'data:')) {
                return $this->avatar;
            }
            $clean = ltrim(str_replace('public/', '', $this->avatar), '/');
            return asset($clean);
        }

        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=f59e0b&color=fff&size=200&bold=true';
    }

    public function getStatusTextAttribute(): string
    {
        return $this->is_online ? 'Online' : 'Offline';
    }

    public function getFormattedCoinsAttribute(): string
    {
        return number_format($this->coins_balance);
    }
}
