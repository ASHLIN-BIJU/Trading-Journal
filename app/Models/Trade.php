<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Trade extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'asset', 'type', 'entry_price', 'exit_price',
        'stop_loss', 'take_profit', 'lot_size', 'profit_loss',
        'profit_loss_pct', 'risk_amount', 'reward_amount', 'risk_reward',
        'pips', 'result', 'status', 'notes', 'trade_date', 'closed_at',
        'session', 'timeframe',
    ];

    protected function casts(): array
    {
        return [
            'trade_date'      => 'datetime',
            'closed_at'       => 'datetime',
            'entry_price'     => 'float',
            'exit_price'      => 'float',
            'stop_loss'       => 'float',
            'take_profit'     => 'float',
            'lot_size'        => 'float',
            'profit_loss'     => 'float',
            'profit_loss_pct' => 'float',
            'risk_amount'     => 'float',
            'reward_amount'   => 'float',
            'risk_reward'     => 'float',
            'pips'            => 'float',
        ];
    }

    // ── Relationships ────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(TradeImage::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'trade_tag');
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByAsset($query, string $asset)
    {
        return $query->where('asset', $asset);
    }

    public function scopeByResult($query, string $result)
    {
        return $query->where('result', $result);
    }

    public function scopeDateRange($query, ?string $from, ?string $to)
    {
        if ($from) $query->whereDate('trade_date', '>=', $from);
        if ($to)   $query->whereDate('trade_date', '<=', $to);
        return $query;
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getResultColorAttribute(): string
    {
        if ($this->result === 'win') return 'text-emerald-400';
        if ($this->result === 'loss') return 'text-red-400';
        if ($this->result === 'breakeven') return 'text-yellow-400';
        return 'text-gray-400';
    }

    public function getResultBadgeAttribute(): string
    {
        $classes = [
            'win'       => 'badge-win',
            'loss'      => 'badge-loss',
            'breakeven' => 'badge-be',
        ];
        return $classes[$this->result] ?? 'badge-open';
    }

    public function getPnlFormattedAttribute(): string
    {
        if ($this->profit_loss === null) return '—';
        $sign = $this->profit_loss >= 0 ? '+' : '';
        return $sign . number_format((float)$this->profit_loss, 2);
    }

    public function getTypeColorAttribute(): string
    {
        return $this->type === 'buy' ? 'text-emerald-400' : 'text-red-400';
    }
}
