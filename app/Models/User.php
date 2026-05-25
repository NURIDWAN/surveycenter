<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (empty($user->referral_code)) {
                $user->referral_code = self::generateReferralCode($user->name);
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'google_id',
        'google_avatar',
        'referral_code',
        'referred_by_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function surveys()
    {
        return $this->hasMany(Survey::class);
    }

    // app/Models/User.php
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function pointTransactions()
    {
        return $this->hasMany(PointTransaction::class);
    }

    public function rewardRedemptions()
    {
        return $this->hasMany(RewardRedemption::class);
    }

    /**
     * Get user's current point balance.
     */
    public function getPointBalanceAttribute(): int
    {
        $earned = $this->pointTransactions()->where('type', PointTransaction::TYPE_EARN)->sum('points');
        $redeemed = $this->pointTransactions()->where('type', PointTransaction::TYPE_REDEEM)->sum('points');

        return (int) ($earned - $redeemed);
    }

    /**
     * Get total points ever earned.
     */
    public function getTotalPointsEarnedAttribute(): int
    {
        return (int) $this->pointTransactions()->where('type', PointTransaction::TYPE_EARN)->sum('points');
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by_id');
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referred_by_id');
    }

    public function referralCommissions()
    {
        return $this->hasMany(ReferralCommission::class, 'referrer_id');
    }

    public function affiliateWithdrawals()
    {
        return $this->hasMany(AffiliateWithdrawal::class);
    }

    /**
     * Get the user's available affiliate balance in Rupiah.
     * Total commissions earned minus approved withdrawals.
     */
    public function getAffiliateBalanceAttribute(): int
    {
        $earned = (int) $this->referralCommissions()->sum('commission_amount');
        $withdrawn = (int) $this->affiliateWithdrawals()
            ->where('status', AffiliateWithdrawal::STATUS_APPROVED)
            ->sum('amount');
        $pending = (int) $this->affiliateWithdrawals()
            ->where('status', AffiliateWithdrawal::STATUS_PENDING)
            ->sum('amount');

        return max(0, $earned - $withdrawn - $pending);
    }

    /**
     * Generate a unique referral code from the user's name.
     */
    public static function generateReferralCode(?string $name = null): string
    {
        $base = $name
            ? Str::slug(Str::limit($name, 10, ''), '')
            : Str::random(6);

        $code = strtolower($base) . rand(10, 99);

        while (self::where('referral_code', $code)->exists()) {
            $code = strtolower($base) . rand(100, 999);
        }

        return $code;
    }

    /**
     * Get the user's referral URL.
     */
    public function getReferralUrlAttribute(): string
    {
        return url('/register?ref=' . $this->referral_code);
    }

    public function topupTransactions()
    {
        return $this->hasMany(TopupTransaction::class);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * Get user's current deposit balance.
     */
    public function getDepositBalanceAttribute(): int
    {
        if ($this->relationLoaded('wallet') && $this->wallet) {
            return (int) $this->wallet->balance;
        }

        $wallet = $this->wallet()->first();

        if ($wallet) {
            return (int) $wallet->balance;
        }

        $totalTopup = (int) $this->topupTransactions()
            ->where('status', TopupTransaction::STATUS_PAID)
            ->sum('amount');

        $totalSurveyPaid = (int) $this->transactions()
            ->where('status', Transaction::STATUS_PAID)
            ->where('payment_method', 'saldo')
            ->sum('amount');

        return max(0, $totalTopup - $totalSurveyPaid);
    }
}
