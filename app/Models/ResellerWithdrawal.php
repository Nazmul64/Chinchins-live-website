<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResellerWithdrawal extends Model
{
    use HasFactory;

    protected $table = 'reseller_withdrawals';

    protected $fillable = [
        'reseller_id',
        'payment_method',
        'account_number',
        'account_name',
        'coins_amount',
        'gross_bdt',
        'commission_percentage',
        'commission_amount',
        'net_bdt',
        'transaction_id',
        'reseller_notes',
        'admin_notes',
        'status',
        'processed_at',
        'processed_by',
    ];

    protected $casts = [
        'coins_amount' => 'integer',
        'gross_bdt' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'net_bdt' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function reseller()
    {
        return $this->belongsTo(Reseller::class, 'reseller_id');
    }

    public function processedByUser()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
