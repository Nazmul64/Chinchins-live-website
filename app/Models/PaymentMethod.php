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
        $code = strtolower(($this->code ?? '') . ' ' . ($this->name ?? ''));
        $defaultLogo = null;
        if (str_contains($code, 'bkash')) {
            $defaultLogo = 'https://freelogopng.com/images/all_img/1656234745bkash-app-logo.png';
        } elseif (str_contains($code, 'nagad')) {
            $defaultLogo = 'https://freelogopng.com/images/all_img/1679248787Nagad-Logo.png';
        } elseif (str_contains($code, 'rocket')) {
            $defaultLogo = 'https://seeklogo.com/images/D/dutch-bangla-rocket-logo-B4D1CC458D-seeklogo.com.png';
        } elseif (str_contains($code, 'upay')) {
            $defaultLogo = 'https://play-lh.googleusercontent.com/O61_aF_n_wP508rC8v2y26Y92aM3u9z-m-B-f8x-y44';
        }

        if (empty($this->icon)) {
            return $defaultLogo;
        }

        if (str_starts_with($this->icon, 'http://') || str_starts_with($this->icon, 'https://') || str_starts_with($this->icon, 'data:')) {
            return $this->icon;
        }

        // Normalize path
        $cleanPath = ltrim(str_replace(['public/', 'uploads/'], '', $this->icon), '/');

        // Check if physical file exists in public/uploads/
        if (file_exists(public_path('uploads/' . $cleanPath))) {
            return asset('uploads/' . $cleanPath);
        }

        // Check if physical file exists in public/
        if (file_exists(public_path($this->icon))) {
            return asset($this->icon);
        }

        // Fallback to default brand logo if physical file missing on server disk
        return $defaultLogo ?: asset('uploads/' . $cleanPath);
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
