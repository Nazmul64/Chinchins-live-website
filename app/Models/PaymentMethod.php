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
        'rate_per_bdt',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'min_deposit' => 'decimal:2',
        'max_deposit' => 'decimal:2',
        'rate_per_bdt' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'icon_url',
        'qr_code_url',
    ];

    public function depositRequests()
    {
        return $this->hasMany(DepositRequest::class);
    }

    public function getIconUrlAttribute(): ?string
    {
        if (empty($this->icon)) {
            $code = strtolower(($this->code ?? '') . ' ' . ($this->name ?? ''));
            if (str_contains($code, 'bkash')) {
                return 'https://freelogopng.com/images/all_img/1656234745bkash-app-logo.png';
            }
            if (str_contains($code, 'nagad')) {
                return 'https://freelogopng.com/images/all_img/1679248787Nagad-Logo.png';
            }
            if (str_contains($code, 'rocket')) {
                return 'https://seeklogo.com/images/D/dutch-bangla-rocket-logo-B4D1CC458D-seeklogo.com.png';
            }
            if (str_contains($code, 'upay')) {
                return 'https://play-lh.googleusercontent.com/O61_aF_n_wP508rC8v2y26Y92aM3u9z-m-B-f8x-y44';
            }
            return null;
        }

        if (str_starts_with($this->icon, 'http://') || str_starts_with($this->icon, 'https://')) {
            return $this->icon;
        }

        if (str_starts_with($this->icon, 'storage/')) {
            return asset($this->icon);
        }

        return asset('uploads/' . ltrim($this->icon, '/'));
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
}
