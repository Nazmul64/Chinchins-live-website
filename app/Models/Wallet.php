<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $table = 'wallets';

    protected $fillable = [
        'user_id',
        'balance',   // Gift Sender Balance / Recharge coins
        'earnings',  // Host Withdrawable Balance / Received gifts
    ];

    protected $casts = [
        'user_id'  => 'integer',
        'balance'  => 'integer',
        'earnings' => 'integer',
    ];

    /**
     * User owning this wallet.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
