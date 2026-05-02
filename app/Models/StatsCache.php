<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatsCache extends Model
{
    protected $table = 'stats_cache';

    protected $fillable = [
        'trading_account_id', 'total_pnl', 'win_rate', 'loss_rate', 'profit_factor',
        'expectancy', 'avg_win', 'avg_loss', 'avg_rr', 'max_drawdown',
        'max_drawdown_amount', 'total_trades', 'winning_trades', 'losing_trades',
        'breakeven_trades', 'max_win_streak', 'max_loss_streak', 'computed_at',
    ];

    protected function casts(): array
    {
        return [
            'computed_at' => 'datetime',
        ];
    }

    public function tradingAccount(): BelongsTo
    {
        return $this->belongsTo(TradingAccount::class);
    }
}
