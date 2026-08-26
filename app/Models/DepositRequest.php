<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepositRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payment_method_id',
        'payment_method_name',
        'amount',
        'coins',
        'sender_number',
        'transaction_id',
        'screenshot',
        'user_note',
        'status',
        'admin_note',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'coins' => 'integer',
        'approved_at' => 'datetime',
    ];

    protected $appends = [
        'screenshot_url',
        'status_badge_class',
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

    public function getScreenshotUrlAttribute(): ?string
    {
        if (empty($this->screenshot)) {
            return null;
        }

        if (str_starts_with($this->screenshot, 'http://') || str_starts_with($this->screenshot, 'https://')) {
            return $this->screenshot;
        }

        return asset('uploads/' . ltrim($this->screenshot, '/'));
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'approved' => 'badge-success',
            'rejected' => 'badge-danger',
            default => 'badge-warning',
        };
    }
}
