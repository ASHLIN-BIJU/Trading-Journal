<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\Trade;
use App\Models\User;
use App\Services\AnalyticsService;
use App\Services\TradeCalculationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $calc     = app(TradeCalculationService::class);
        $analytics = app(AnalyticsService::class);

        // ── Create demo user ─────────────────────────────────────────────────
        $user = User::firstOrCreate(
            ['email' => 'demo@tradejournal.com'],
            [
                'name'            => 'Demo Trader',
                'password'        => Hash::make('password'),
                'plan'            => 'pro',
                'timezone'        => 'UTC',
            ]
        );

        // ── Create Demo Trading Account ──────────────────────────────────────
        $account = $user->accounts()->create([
            'name' => '100k Challenge',
            'initial_capital' => 100000.00,
            'balance' => 100000.00,
            'status' => 'active',
        ]);
        
        $user->update(['active_account_id' => $account->id]);

        // ── Create tags ──────────────────────────────────────────────────────
        $tagData = [
            ['name' => 'SMC',         'color' => '#00D4A8', 'category' => 'strategy'],
            ['name' => 'ICT',         'color' => '#4C9EEB', 'category' => 'strategy'],
            ['name' => 'Order Block', 'color' => '#FFB649', 'category' => 'setup'],
            ['name' => 'FVG',         'color' => '#9B59B6', 'category' => 'setup'],
            ['name' => 'FOMO',        'color' => '#FF4757', 'category' => 'mistake'],
            ['name' => 'Early Exit',  'color' => '#FF6B35', 'category' => 'mistake'],
            ['name' => 'News Trade',  'color' => '#2ECC71', 'category' => 'strategy'],
        ];

        $tags = [];
        foreach ($tagData as $td) {
            $tags[$td['name']] = Tag::firstOrCreate(
                ['user_id' => $user->id, 'name' => $td['name']],
                ['color' => $td['color'], 'category' => $td['category']]
            );
        }

        // ── Generate 60 sample trades ────────────────────────────────────────
        $assets = ['XAUUSD', 'EURUSD', 'GBPUSD', 'USDJPY', 'BTCUSD'];
        $sessions = ['London', 'New York', 'Tokyo'];
        $timeframes = ['1H', '4H', '15M'];

        $baseDate = now()->subDays(90);
        Trade::where('user_id', $user->id)->delete();

        for ($i = 0; $i < 60; $i++) {
            $asset = $assets[array_rand($assets)];
            $type  = rand(0, 1) ? 'buy' : 'sell';

            [$entry, $sl, $tp, $exit] = $this->generatePrices($asset, $type);

            $tradeDate = (clone $baseDate)->addDays($i * 1.5)->addHours(rand(0, 23));

            $data = [
                'asset'       => $asset,
                'type'        => $type,
                'entry_price' => $entry,
                'exit_price'  => $exit,
                'stop_loss'   => $sl,
                'take_profit' => $tp,
                'lot_size'    => [0.01, 0.05, 0.10, 0.20, 0.50][rand(0, 4)],
                'trade_date'  => $tradeDate,
            ];

            $calc_result = $calc->calculate($data);
            $status = $exit !== null ? 'closed' : 'open';

            $trade = $account->trades()->create(array_merge($data, $calc_result, [
                'user_id'   => $user->id,
                'status'    => $status,
                'closed_at' => $status === 'closed' ? $tradeDate->addHours(rand(1, 8)) : null,
                'session'   => $sessions[array_rand($sessions)],
                'timeframe' => $timeframes[array_rand($timeframes)],
                'notes'     => $this->randomNote($calc_result['result'] ?? 'open'),
            ]));

            // Attach 1-3 random tags
            $tagCount = rand(1, 3);
            $randomTags = array_rand($tags, min($tagCount, count($tags)));
            if (!is_array($randomTags)) $randomTags = [$randomTags];
            $trade->tags()->attach(array_map(fn($k) => $tags[$k]->id, $randomTags));
        }

        // ── Warm the analytics cache ─────────────────────────────────────────
        $analytics->refreshCache($account);

        $this->command->info("✅ Demo user created: demo@tradejournal.com / password");
        $this->command->info("✅ 60 sample trades seeded with analytics cached.");
    }

    private function generatePrices(string $asset, string $type): array
    {
        $bases = ['XAUUSD' => 2350, 'EURUSD' => 1.085, 'GBPUSD' => 1.27, 'USDJPY' => 149.5, 'BTCUSD' => 68000];
        $ranges = ['XAUUSD' => [5, 30], 'EURUSD' => [0.0005, 0.003], 'GBPUSD' => [0.0005, 0.003],
                   'USDJPY' => [0.05, 0.3], 'BTCUSD' => [200, 1500]];

        $base  = $bases[$asset] ?? 1.0;
        $range = $ranges[$asset] ?? [0.001, 0.01];
        $spread = rand(10, 100) / 100 * ($range[0] + $range[1]) / 2;

        $entry   = $base + (rand(-100, 100) / 100) * $spread * 2;
        $slDist  = $range[0] + (rand(0, 100) / 100) * ($range[1] - $range[0]);
        $tpDist  = $slDist * (1.5 + rand(0, 100) / 100);

        if ($type === 'buy') {
            $sl = $entry - $slDist;
            $tp = $entry + $tpDist;
        } else {
            $sl = $entry + $slDist;
            $tp = $entry - $tpDist;
        }

        // 60% win rate
        $isWin = rand(1, 10) <= 6;
        $exit  = null;

        if (rand(0, 10) > 1) { // 90% closed
            if ($isWin) {
                $exit = $type === 'buy' ? $entry + $tpDist * rand(60, 100) / 100 : $entry - $tpDist * rand(60, 100) / 100;
            } else {
                $exit = $type === 'buy' ? $entry - $slDist * rand(60, 100) / 100 : $entry + $slDist * rand(60, 100) / 100;
            }
        }

        return [round($entry, 5), round($sl, 5), round($tp, 5), $exit ? round($exit, 5) : null];
    }

    private function randomNote(string $result): string
    {
        $notes = [
            'win'  => [
                'Clean order block entry. Waited for confirmation and got a clean fill.',
                'Perfect ICT setup. HTF bias aligned with LTF entry. Took 2R.',
                'SMC structure break confirmed. Rode it to TP cleanly.',
                'Patience paid off. Waited for the FVG fill before entering.',
            ],
            'loss' => [
                'Got stopped out. Market faked out above the level. Need to refine entries.',
                'FOMO entry — missed the main move and entered late. Lesson: wait for retrace.',
                'News spike hit SL. Should check the economic calendar beforehand.',
                'Tight SL got hit. Need to use ATR-based stops.',
            ],
            'breakeven' => [
                'Moved to BE on first TP. Good risk management.',
                'Market stalled at resistance. Closed at BE rather than let it turn into a loss.',
            ],
            'open' => ['Trade still running. Watching price action closely.'],
        ];

        $pool = $notes[$result] ?? $notes['open'];
        return $pool[array_rand($pool)];
    }
}
