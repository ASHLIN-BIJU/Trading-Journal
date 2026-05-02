<?php

namespace App\Services;

use App\Models\Trade;

class TradeCalculationService
{
    /**
     * Pip value per standard lot by asset type.
     * Returns dollar value per pip for 1 standard lot (100,000 units).
     */
    private array $pipValues = [
        // Forex pairs where USD is the quote currency
        'EURUSD' => 10.0, 'GBPUSD' => 10.0, 'AUDUSD' => 10.0,
        'NZDUSD' => 10.0,
        // Forex pairs where USD is the base currency
        'USDJPY' => 9.09, 'USDCHF' => 9.09, 'USDCAD' => 7.69,
        // Cross pairs (approximate)
        'GBPJPY' => 9.09, 'EURJPY' => 9.09, 'GBPAUD' => 7.0,
        // Metals & CFDs
        'XAUUSD' => 1.0,   // Gold: $1 per 0.01 price move per 0.01 lot
        'XAGUSD' => 50.0,  // Silver
        'XBRUSD' => 10.0,  // Brent Oil
        // Indices (approximate)
        'US30'   => 1.0,
        'NAS100' => 1.0,
        'SPX500' => 1.0,
        // Crypto
        'BTCUSD' => 1.0,
        'ETHUSD' => 1.0,
    ];

    /**
     * Pip size (decimal places for 1 pip) by asset type.
     */
    private array $pipSizes = [
        'XAUUSD' => 0.01,  // Gold: 1 pip = $0.01
        'XAGUSD' => 0.001,
        'USDJPY' => 0.01, 'GBPJPY' => 0.01, 'EURJPY' => 0.01,
        'US30'   => 1.0,
        'NAS100' => 0.25,
        'BTCUSD' => 1.0,
        'ETHUSD' => 0.1,
    ];

    /**
     * Compute all trade metrics and return an array of calculated fields.
     */
    public function calculate(array $data): array
    {
        $asset     = strtoupper($data['asset'] ?? '');
        $type      = strtolower($data['type'] ?? 'buy');
        $entry     = (float) ($data['entry_price'] ?? 0);
        $exit      = isset($data['exit_price']) ? (float) $data['exit_price'] : null;
        $sl        = isset($data['stop_loss']) ? (float) $data['stop_loss'] : null;
        $tp        = isset($data['take_profit']) ? (float) $data['take_profit'] : null;
        $lotSize   = (float) ($data['lot_size'] ?? 0);

        $pipSize  = $this->pipSizes[$asset]  ?? 0.0001;
        $pipValue = $this->pipValues[$asset] ?? 10.0;

        $calc = [
            'profit_loss'     => null,
            'profit_loss_pct' => null,
            'risk_amount'     => null,
            'reward_amount'   => null,
            'risk_reward'     => null,
            'pips'            => null,
            'result'          => 'open',
        ];

        // ── Risk (SL distance) ───────────────────────────────────────────────
        if ($sl !== null && $entry > 0) {
            $slDistance     = abs($entry - $sl);
            $slPips         = $slDistance / $pipSize;
            $calc['risk_amount'] = round($slPips * $pipValue * $lotSize, 2);
        }

        // ── Reward (TP distance) ─────────────────────────────────────────────
        if ($tp !== null && $entry > 0) {
            $tpDistance          = abs($tp - $entry);
            $tpPips              = $tpDistance / $pipSize;
            $calc['reward_amount'] = round($tpPips * $pipValue * $lotSize, 2);
        }

        // ── R:R Ratio ────────────────────────────────────────────────────────
        if ($calc['risk_amount'] > 0 && $calc['reward_amount'] !== null) {
            $calc['risk_reward'] = round($calc['reward_amount'] / $calc['risk_amount'], 4);
        }

        // ── P&L (if trade is closed) ─────────────────────────────────────────
        if ($exit !== null && $entry > 0 && $lotSize > 0) {
            $priceDiff = $type === 'buy' ? ($exit - $entry) : ($entry - $exit);
            $pips      = $priceDiff / $pipSize;

            $calc['pips']        = round($pips, 2);
            $calc['profit_loss'] = round($pips * $pipValue * $lotSize, 2);

            // P&L %: relative to risk amount if available, else vs entry price
            if ($calc['risk_amount'] > 0) {
                $calc['profit_loss_pct'] = round(
                    ($calc['profit_loss'] / $calc['risk_amount']) * 100, 4
                );
            } elseif ($entry > 0) {
                $calc['profit_loss_pct'] = round(
                    (($priceDiff / $entry) * 100), 4
                );
            }

            // ── Result ───────────────────────────────────────────────────────
            $calc['result'] = match(true) {
                $calc['profit_loss'] > 0  => 'win',
                $calc['profit_loss'] < 0  => 'loss',
                default                   => 'breakeven',
            };
        }

        return $calc;
    }

    /**
     * Recalculate an existing Trade model and update it.
     */
    public function recalculate(Trade $trade): Trade
    {
        $data   = $trade->toArray();
        $result = $this->calculate($data);

        $trade->fill($result);

        // Only mark closed if exit price is present
        if ($trade->exit_price !== null && $trade->status === 'open') {
            $trade->status    = 'closed';
            $trade->closed_at = $trade->closed_at ?? now();
        }

        return $trade;
    }

    // ── Static Calculator Methods (for Tools page) ────────────────────────────

    /**
     * Position size calculator.
     * Returns lot size and dollar risk.
     */
    public function positionSize(
        float $accountSize,
        float $riskPercent,
        float $slPips,
        string $asset = 'EURUSD'
    ): array {
        $riskAmount = $accountSize * ($riskPercent / 100);
        $pipValue   = $this->pipValues[$asset] ?? 10.0;

        $lotSize = $slPips > 0 && $pipValue > 0
            ? round($riskAmount / ($slPips * $pipValue), 2)
            : 0;

        return [
            'risk_amount' => round($riskAmount, 2),
            'lot_size'    => $lotSize,
        ];
    }

    /**
     * Risk:Reward ratio calculator.
     */
    public function riskReward(float $entry, float $sl, float $tp, string $type = 'buy'): array
    {
        $risk   = abs($entry - $sl);
        $reward = abs($tp - $entry);
        $rr     = $risk > 0 ? round($reward / $risk, 2) : 0;

        return [
            'risk'   => round($risk, 5),
            'reward' => round($reward, 5),
            'rr'     => $rr,
        ];
    }

    /**
     * Profit/loss calculator (in pips and dollars).
     */
    public function profitCalc(float $lotSize, float $pips, string $asset = 'EURUSD'): array
    {
        $pipValue = $this->pipValues[$asset] ?? 10.0;
        $profit   = round($pips * $pipValue * $lotSize, 2);
        return ['profit' => $profit, 'pips' => $pips];
    }

    /**
     * Drawdown recovery calculator.
     */
    public function drawdownCalc(float $balance, float $drawdownPct): array
    {
        $loss      = $balance * ($drawdownPct / 100);
        $remaining = $balance - $loss;
        $recovery  = $remaining > 0
            ? round(($loss / $remaining) * 100, 2)
            : null;

        return [
            'loss'             => round($loss, 2),
            'remaining'        => round($remaining, 2),
            'recovery_needed'  => $recovery,
        ];
    }
}
