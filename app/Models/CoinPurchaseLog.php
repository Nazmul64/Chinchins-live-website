<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CoinPurchaseLog extends Model
{
    use HasFactory;

    protected $table = 'coin_purchase_logs';

    protected $fillable = [
        'user_id',
        'package_id',
        'coins',
        'amount_paid',
        'currency',
        'payment_method',
    ];

    protected $casts = [
        'user_id'     => 'integer',
        'package_id'  => 'integer',
        'coins'       => 'integer',
        'amount_paid' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function package()
    {
        return $this->belongsTo(CoinPackage::class, 'package_id');
    }
}
