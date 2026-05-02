<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'plan', 'timezone',
        'avatar', 'bio', 'account_balance', 'default_currency',
        'google_id'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'account_balance' => 'decimal:2',
        ];
    }

    public function trades()
    {
        return $this->hasMany(Trade::class);
    }

    public function accounts()
    {
        return $this->hasMany(TradingAccount::class);
    }

    public function activeAccount()
    {
        return $this->belongsTo(TradingAccount::class, 'active_account_id');
    }

    public function getActiveAccount()
    {
        if ($this->activeAccount) {
            return $this->activeAccount;
        }
        $account = $this->accounts()->first();
        if (!$account) {
            $account = $this->accounts()->create([
                'name' => 'Main Account',
                'initial_capital' => 10000.00,
                'balance' => 10000.00,
                'status' => 'active',
            ]);
        }
        $this->update(['active_account_id' => $account->id]);
        return $account;
    }

    public function tags()
    {
        return $this->hasMany(Tag::class);
    }


    public function subscription()
    {
        return $this->hasOne(Subscription::class)->latest();
    }

    public function isPro(): bool
    {
        return in_array($this->plan, ['pro', 'elite']);
    }

    public function isElite(): bool
    {
        return $this->plan === 'elite';
    }

    public function getPlanBadgeColorAttribute(): string
    {
        return match($this->plan) {
            'pro'   => 'text-blue-400 bg-blue-400/10 border-blue-400/30',
            'elite' => 'text-yellow-400 bg-yellow-400/10 border-yellow-400/30',
            default => 'text-gray-400 bg-gray-400/10 border-gray-400/30',
        };
    }
}
