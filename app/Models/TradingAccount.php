<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TradingAccount extends Model
{
    protected $fillable = [
        'user_id', 'name', 'initial_capital', 'balance', 'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function trades()
    {
        return $this->hasMany(Trade::class);
    }

    public function statsCache()
    {
        return $this->hasOne(StatsCache::class);
    }
}
