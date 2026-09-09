<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResellerTransfer extends Model
{
    use HasFactory;

    protected $table = 'reseller_transfers';

    protected $fillable = [
        'reseller_id',
        'user_id',
        'target_account_id',
        'coins',
        'amount_bdt',
        'payment_method',
        'transaction_id',
        'screenshot',
        'notes',
        'status',
    ];

    protected $casts = [
        'coins' => 'integer',
        'amount_bdt' => 'decimal:2',
    ];

    protected $appends = [
        'screenshot_url',
    ];

    public function reseller()
    {
        return $this->belongsTo(Reseller::class, 'reseller_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
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
