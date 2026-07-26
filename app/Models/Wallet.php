<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'balance',
        'deposit_balance',
        'reward_balance',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'decimal:2',
            'deposit_balance' => 'decimal:2',
            'reward_balance' => 'decimal:2',
        ];
    }

    /**
     * Recalculate and sync the total balance from split balances.
     */
    public function syncTotalBalance(): void
    {
        $this->balance = bcadd($this->deposit_balance, $this->reward_balance, 2);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }
}
