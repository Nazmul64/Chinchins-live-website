<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResellerDeposit extends Model
{
    use HasFactory;

    protected $table = 'reseller_deposits';

    protected $fillable = [
        'reseller_id',
        'payment_method',
        'sender_number',
        'transaction_id',
        'amount_bdt',
        'coins_requested',
        'screenshot',
        'notes',
        'admin_notes',
        'status',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'amount_bdt' => 'decimal:2',
        'coins_requested' => 'integer',
        'approved_at' => 'datetime',
    ];

    protected $appends = [
        'screenshot_url',
    ];

    public function reseller()
    {
        return $this->belongsTo(Reseller::class, 'reseller_id');
    }

    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getScreenshotUrlAttribute(): ?string
    {
        if (!empty($this->screenshot)) {
            if (str_starts_with($this->screenshot, 'http://') || str_starts_with($this->screenshot, 'https://')) {
                return $this->screenshot;
            }
            return asset(ltrim(str_replace('public/', '', $this->screenshot), '/'));
        }
        return null;
    }
}
