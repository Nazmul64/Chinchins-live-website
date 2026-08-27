<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'account_type',
        'account_number',
        'instructions',
        'icon',
        'qr_code',
        'min_deposit',
        'max_deposit',
        'rate_coins',
        'rate_bdt',
        'rate_per_bdt',
        'bonus_coins',
        'offer_tag',
        'supports_withdraw',
        'min_withdraw',
        'max_withdraw',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'min_deposit' => 'decimal:2',
        'max_deposit' => 'decimal:2',
        'supports_withdraw' => 'boolean',
        'min_withdraw' => 'decimal:2',
        'max_withdraw' => 'decimal:2',
        'rate_coins' => 'integer',
        'rate_bdt' => 'decimal:2',
        'rate_per_bdt' => 'float',
        'bonus_coins' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'icon_url',
        'qr_code_url',
        'exchange_rate_text',
        'total_rate_coins',
        'bonus_text',
        'bonus_percentage',
    ];

    public function depositRequests()
    {
        return $this->hasMany(DepositRequest::class);
    }

    public function withdrawRequests()
    {
        return $this->hasMany(WithdrawRequest::class);
    }

    public function getIconUrlAttribute(): ?string
    {
        if (!empty($this->icon)) {
            if (str_starts_with($this->icon, 'http://') || str_starts_with($this->icon, 'https://') || str_starts_with($this->icon, 'data:')) {
                return $this->icon;
            }

            $clean = ltrim(str_replace('public/', '', $this->icon), '/');
            return asset($clean);
        }

        // Fallback standard CDN / local icons based on payment method code
        $code = strtolower($this->code ?: $this->name);
        if (str_contains($code, 'bkash')) {
            return asset('assets/images/bkash.png');
        }
        if (str_contains($code, 'nagad')) {
            return asset('assets/images/nagad.png');
        }
        if (str_contains($code, 'rocket')) {
            return asset('assets/images/rocket.png');
        }
        if (str_contains($code, 'upay')) {
            return asset('assets/images/upay.png');
        }

        return null;
    }

    public function getQrCodeUrlAttribute(): ?string
    {
        if (empty($this->qr_code)) {
            return null;
        }

        if (str_starts_with($this->qr_code, 'http://') || str_starts_with($this->qr_code, 'https://')) {
            return $this->qr_code;
        }

        return asset('uploads/' . ltrim($this->qr_code, '/'));
    }

    public function getExchangeRateTextAttribute(): string
    {
        $coins = $this->rate_coins ?: (($this->rate_per_bdt ?: 10) * 10);
        $bdt = $this->rate_bdt ?: 10;
        $bonus = $this->bonus_coins ?: 0;
        if ($bonus > 0) {
            return "{$coins} + {$bonus} Bonus = ৳{$bdt} BDT";
        }
        return "{$coins} Coins = ৳{$bdt} BDT";
    }

    public function getTotalRateCoinsAttribute(): int
    {
        return (int) (($this->rate_coins ?: (($this->rate_per_bdt ?: 10) * 10)) + ($this->bonus_coins ?: 0));
    }

    public function getBonusTextAttribute(): ?string
    {
        if (!empty($this->bonus_coins) && $this->bonus_coins > 0) {
            return "+{$this->bonus_coins} Bonus";
        }
        return null;
    }

    public function getBonusPercentageAttribute(): int
    {
        $base = $this->rate_coins ?: (($this->rate_per_bdt ?: 10) * 10);
        if ($base > 0 && $this->bonus_coins > 0) {
            return (int) round(($this->bonus_coins / $base) * 100);
        }
        return 0;
    }
}
