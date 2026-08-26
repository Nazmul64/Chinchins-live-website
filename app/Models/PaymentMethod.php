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
            return null;
        }

        if (str_starts_with($this->icon, 'http://') || str_starts_with($this->icon, 'https://') || str_starts_with($this->icon, 'data:')) {
            return $this->icon;
        }

        $clean = ltrim(str_replace('public/', '', $this->icon), '/');
        return asset($clean);
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
