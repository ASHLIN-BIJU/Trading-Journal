<?php

namespace App\Services;

use App\Models\StatsCache;
use App\Models\TradingAccount;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Compute and cache all analytics for a trading account.
     * Returns the StatsCache record.
     */
    public function refreshCache(TradingAccount $account): StatsCache
    {
        $trades = $account->trades()->closed()->orderBy('trade_date')->get();
        $data   = $this->compute($trades, (float) $account->initial_capital);

        $account->update(['balance' => $account->initial_capital + $data['total_pnl']]);

        return StatsCache::updateOrCreate(
            ['trading_account_id' => $account->id],
            array_merge($data, ['computed_at' => now()])
        );
    }

    /**
     * Get summary stats — from cache if fresh (< 5 min old), else recompute.
     */
    public function getSummary(TradingAccount $account): StatsCache
    {
        $cache = $account->statsCache;

        if ($cache && $cache->computed_at && $cache->computed_at->diffInMinutes(now()) < 5) {
            return $cache;
        }

        return $this->refreshCache($account);
    }

    /**
     * Compute all stats from a collection of closed trades.
     */
    public function compute(Collection $trades, float $startBalance = 0): array
    {
        $total     = $trades->count();
        $wins      = $trades->where('result', 'win');
        $losses    = $trades->where('result', 'loss');
        $breakevens = $trades->where('result', 'breakeven');

        $winCount  = $wins->count();
        $lossCount = $losses->count();
        $beCount   = $breakevens->count();

        $totalPnl  = round($trades->sum('profit_loss'), 2);
        $totalWin  = $wins->sum('profit_loss');
        $totalLoss = abs($losses->sum('profit_loss'));

        $winRate   = $total > 0 ? round(($winCount / $total) * 100, 2) : 0;
        $lossRate  = $total > 0 ? round(($lossCount / $total) * 100, 2) : 0;

        $profitFactor = $totalLoss > 0 ? round($totalWin / $totalLoss, 4) : ($totalWin > 0 ? 999.0 : 0);
        $avgWin       = $winCount > 0  ? round($totalWin / $winCount, 2)   : 0;
        $avgLoss      = $lossCount > 0 ? round(-$losses->sum('profit_loss') / $lossCount, 2) : 0;

        $expectancy = $total > 0
            ? round(($winRate / 100 * $avgWin) - ($lossRate / 100 * $avgLoss), 4)
            : 0;

        $avgRR = $trades->whereNotNull('risk_reward')->count() > 0
            ? round($trades->whereNotNull('risk_reward')->avg('risk_reward'), 4)
            : 0;

        [$maxDrawdown, $maxDrawdownAmount] = $this->computeDrawdown($trades, $startBalance);
        [$maxWinStreak, $maxLossStreak]   = $this->computeStreaks($trades);

        return [
            'total_pnl'           => $totalPnl,
            'win_rate'            => $winRate,
            'loss_rate'           => $lossRate,
            'profit_factor'       => $profitFactor,
            'expectancy'          => $expectancy,
            'avg_win'             => $avgWin,
            'avg_loss'            => $avgLoss,
            'avg_rr'              => $avgRR,
            'max_drawdown'        => $maxDrawdown,
            'max_drawdown_amount' => $maxDrawdownAmount,
            'total_trades'        => $total,
            'winning_trades'      => $winCount,
            'losing_trades'       => $lossCount,
            'breakeven_trades'    => $beCount,
            'max_win_streak'      => $maxWinStreak,
            'max_loss_streak'     => $maxLossStreak,
        ];
    }

    /**
     * Build equity curve data: running P&L after each trade.
     * Returns [['date' => ..., 'balance' => ...], ...]
     */
    public function getEquityCurve(TradingAccount $account): array
    {
        $startBalance = (float) $account->initial_capital;
        $trades = $account->trades()->closed()->orderBy('trade_date')->get(['trade_date', 'profit_loss']);

        $curve   = [];
        $running = $startBalance;

        foreach ($trades as $trade) {
            $running += (float) $trade->profit_loss;
            $curve[] = [
                'x' => $trade->trade_date->format('Y-m-d H:i'),
                'y' => round($running, 2),
            ];
        }

        return $curve;
    }

    /**
     * Build drawdown series: drawdown % from peak at each trade.
     */
    public function getDrawdownSeries(TradingAccount $account): array
    {
        $startBalance = (float) $account->initial_capital;
        $trades = $account->trades()->closed()->orderBy('trade_date')->get(['trade_date', 'profit_loss']);

        $series  = [];
        $running = $startBalance;
        $peak    = $startBalance;

        foreach ($trades as $trade) {
            $running += (float) $trade->profit_loss;
            if ($running > $peak) $peak = $running;
            $drawdown = $peak > 0 ? round((($peak - $running) / $peak) * 100, 4) : 0;
            $series[] = [
                'x' => $trade->trade_date->format('Y-m-d H:i'),
                'y' => -abs($drawdown),
            ];
        }

        return $series;
    }

    /**
     * Daily P&L for heatmap/calendar.
     * Returns ['YYYY-MM-DD' => pnl_float]
     */
    public function getDailyPerformance(TradingAccount $account): array
    {
        return $account->trades()
            ->closed()
            ->select(DB::raw('DATE(trade_date) as day'), DB::raw('SUM(profit_loss) as pnl'))
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->pluck('pnl', 'day')
            ->map(fn($v) => round((float) $v, 2))
            ->toArray();
    }

    /**
     * Monthly P&L grouped by year-month.
     * Returns [['month' => 'Jan 2024', 'pnl' => float], ...]
     */
    public function getMonthlyPerformance(TradingAccount $account): array
    {
        $trades = $account->trades()->closed()->get(['trade_date', 'profit_loss']);

        return $trades->groupBy(fn($t) => $t->trade_date->format('Y-m'))
            ->sortKeys()
            ->map(fn($group, $ym) => [
                'month' => \Carbon\Carbon::createFromFormat('Y-m', $ym)->format('M Y'),
                'pnl'   => round((float) $group->sum('profit_loss'), 2),
            ])
            ->values()
            ->toArray();
    }

    /**
     * Long vs Short P&L and win rate breakdown.
     */
    public function getLongShortStats(TradingAccount $account): array
    {
        $result = [];
        foreach (['buy', 'sell'] as $type) {
            $group     = $account->trades()->closed()->where('type', $type)->get();
            $total     = $group->count();
            $wins      = $group->where('result', 'win')->count();
            $result[$type] = [
                'total'    => $total,
                'win_rate' => $total > 0 ? round(($wins / $total) * 100, 2) : 0,
                'total_pnl' => round($group->sum('profit_loss'), 2),
                'avg_pnl'  => $total > 0 ? round($group->avg('profit_loss'), 2) : 0,
            ];
        }
        return $result;
    }

    /**
     * Best and worst trading days.
     */
    public function getBestWorstDays(TradingAccount $account): array
    {
        $daily = $this->getDailyPerformance($account);

        if (empty($daily)) {
            return ['best' => null, 'worst' => null];
        }

        arsort($daily);
        $bestDay  = array_key_first($daily);
        $bestPnl  = $daily[$bestDay];

        asort($daily);
        $worstDay = array_key_first($daily);
        $worstPnl = $daily[$worstDay];

        return [
            'best'  => ['date' => $bestDay,  'pnl' => $bestPnl],
            'worst' => ['date' => $worstDay, 'pnl' => $worstPnl],
        ];
    }

    /**
     * Compute max drawdown from a collection of sorted trades.
     * Returns [drawdown_pct, drawdown_amount].
     */
    private function computeDrawdown(Collection $trades, float $startBalance = 0): array
    {
        $peak         = $startBalance;
        $running      = $startBalance;
        $maxDrawdown  = 0;
        $maxDDAmount  = 0;

        foreach ($trades as $trade) {
            $running += (float) $trade->profit_loss;
            if ($running > $peak) $peak = $running;
            $dd = $peak - $running;
            if ($dd > $maxDDAmount) {
                $maxDDAmount = $dd;
                $maxDrawdown = $peak > 0 ? round(($dd / $peak) * 100, 4) : 0;
            }
        }

        return [round($maxDrawdown, 4), round($maxDDAmount, 2)];
    }

    /**
     * Day of the week performance.
     * Returns [['day' => 'Monday', 'pnl' => float, 'trades' => int, 'win_rate' => float], ...]
     */
    public function getDayOfWeekPerformance(TradingAccount $account, ?string $timeframe = null): array
    {
        $query = $account->trades()->closed();

        if ($timeframe) {
            $date = match($timeframe) {
                '1m' => now()->subMonth(),
                '2m' => now()->subMonths(2),
                '3m' => now()->subMonths(3),
                '6m' => now()->subMonths(6),
                '1y' => now()->subYear(),
                default => null
            };
            if ($date) {
                $query->where('trade_date', '>=', $date);
            }
        }

        $trades = $query->get(['trade_date', 'profit_loss', 'result']);

        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $stats = [];

        foreach ($days as $day) {
            $group = $trades->filter(fn($t) => $t->trade_date->format('l') === $day);
            $total = $group->count();
            $wins  = $group->where('result', 'win')->count();

            $stats[] = [
                'day'      => $day,
                'pnl'      => round((float) $group->sum('profit_loss'), 2),
                'trades'   => $total,
                'win_rate' => $total > 0 ? round(($wins / $total) * 100, 2) : 0,
            ];
        }

        return $stats;
    }

    /**
     * Compute streaks...
     */
    private function computeStreaks(Collection $trades): array
    {
        $maxWin   = 0;
        $maxLoss  = 0;
        $curWin   = 0;
        $curLoss  = 0;

        foreach ($trades as $trade) {
            if ($trade->result === 'win') {
                $curWin++;
                $curLoss = 0;
                $maxWin  = max($maxWin, $curWin);
            } elseif ($trade->result === 'loss') {
                $curLoss++;
                $curWin  = 0;
                $maxLoss = max($maxLoss, $curLoss);
            } else {
                $curWin = $curLoss = 0;
            }
        }

        return [$maxWin, $maxLoss];
    }
}
