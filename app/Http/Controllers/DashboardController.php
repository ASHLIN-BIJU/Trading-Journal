<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct(private AnalyticsService $analytics) {}

    public function index()
    {
        $user  = Auth::user();
        $account = $user->getActiveAccount();

        $stats = $this->analytics->getSummary($account);

        $equityCurve   = $this->analytics->getEquityCurve($account);
        $drawdownSeries = $this->analytics->getDrawdownSeries($account);
        $dailyPerf     = $this->analytics->getDailyPerformance($account);
        $bestWorst     = $this->analytics->getBestWorstDays($account);

        // Recent trades for the quick table
        $recentTrades = $account->trades()
            ->with('tags')
            ->orderByDesc('trade_date')
            ->limit(10)
            ->get();

        // Win/Loss/BE counts for donut chart
        $resultCounts = [
            'win'       => $stats->winning_trades,
            'loss'      => $stats->losing_trades,
            'breakeven' => $stats->breakeven_trades,
        ];

        return view('dashboard.index', compact(
            'stats', 'equityCurve', 'drawdownSeries',
            'dailyPerf', 'bestWorst', 'recentTrades', 'resultCounts'
        ));
    }
}
