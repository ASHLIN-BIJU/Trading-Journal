<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    public function __construct(private AnalyticsService $analytics) {}

    public function index(Request $request)
    {
        $user      = Auth::user();
        $account   = $user->getActiveAccount();
        $timeframe = $request->get('timeframe', 'all');
        $stats    = $this->analytics->getSummary($account);
        $longShort = $this->analytics->getLongShortStats($account);
        $monthly  = $this->analytics->getMonthlyPerformance($account);
        $bestWorst = $this->analytics->getBestWorstDays($account);
        $equityCurve = $this->analytics->getEquityCurve($account);
        $drawdownSeries = $this->analytics->getDrawdownSeries($account);
        $dayPerf   = $this->analytics->getDayOfWeekPerformance($account, $timeframe);

        // Per-asset breakdown
        $assetStats = $account->trades()
            ->closed()
            ->selectRaw('asset, COUNT(*) as total, SUM(profit_loss) as pnl, AVG(profit_loss) as avg_pnl,
                         SUM(CASE WHEN result = "win" THEN 1 ELSE 0 END) as wins')
            ->groupBy('asset')
            ->orderByDesc('pnl')
            ->get();

        return view('analytics.index', compact(
            'stats', 'longShort', 'monthly', 'bestWorst',
            'assetStats', 'equityCurve', 'drawdownSeries', 'dayPerf', 'timeframe'
        ));
    }
}
