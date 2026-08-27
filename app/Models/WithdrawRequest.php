<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payment_method_id',
        'payment_method_name',
        'coins',
        'rate_per_bdt',
        'gross_amount',
        'commission_percent',
        'commission_amount',
        'net_payable_amount',
        'account_number',
        'account_type',
        'user_note',
        'status',
        'transaction_id',
        'admin_note',
        'approved_by',
        'approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'coins' => 'integer',
        'rate_per_bdt' => 'decimal:2',
        'gross_amount' => 'decimal:2',
        'commission_percent' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'net_payable_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    protected $appends = [
        'formatted_coins',
        'formatted_gross_amount',
        'formatted_commission_amount',
        'formatted_net_payable_amount',
        'status_badge_class',
        'payment_method_icon_url',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getFormattedCoinsAttribute(): string
    {
        return number_format($this->coins) . ' Coins';
    }

    public function getFormattedGrossAmountAttribute(): string
    {
        return '৳' . number_format($this->gross_amount, 2);
    }

    public function getFormattedCommissionAmountAttribute(): string
    {
        return '৳' . number_format($this->commission_amount, 2) . " ({$this->commission_percent}%)";
    }

    public function getFormattedNetPayableAmountAttribute(): string
    {
        return '৳' . number_format($this->net_payable_amount, 2);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'badge-soft-success',
            'rejected' => 'badge-soft-danger',
            default => 'badge-soft-warning',
        };
    }

    public function getPaymentMethodIconUrlAttribute(): ?string
    {
        if ($this->paymentMethod) {
            return $this->paymentMethod->icon_url;
        }

        $code = strtolower($this->payment_method_name ?: '');
        if (str_contains($code, 'bkash')) return asset('assets/images/bkash.png');
        if (str_contains($code, 'nagad')) return asset('assets/images/nagad.png');
        if (str_contains($code, 'rocket')) return asset('assets/images/rocket.png');
        if (str_contains($code, 'upay')) return asset('assets/images/upay.png');

        return null;
    }
}
