<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JournalController extends Controller
{
    public function __construct(private AnalyticsService $analytics) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        
        $currentDate = $request->has('month') ? Carbon::parse($request->month)->startOfMonth() : now()->startOfMonth();
        $month = $currentDate->format('F Y');
        $prevMonth = $currentDate->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentDate->copy()->addMonth()->format('Y-m');
        
        // Group trades by date for the calendar
        $tradesByDate = $user->getActiveAccount()->trades()
            ->with('tags')
            ->whereMonth('trade_date', $currentDate->month)
            ->whereYear('trade_date', $currentDate->year)
            ->orderByDesc('trade_date')
            ->get()
            ->groupBy(fn($t) => $t->trade_date->format('Y-m-d'));
            
        // Build calendar grid
        $startOfCalendar = $currentDate->copy()->startOfWeek(Carbon::SUNDAY);
        $endOfCalendar = $currentDate->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY)->subDay();
        
        $calendar = [];
        $date = $startOfCalendar->copy();
        while ($date <= $endOfCalendar) {
            $dateStr = $date->format('Y-m-d');
            $trades = $tradesByDate->get($dateStr, collect());
            $pnl = $trades->sum('profit_loss');
            
            $calendar[] = [
                'date' => $date->copy(),
                'isCurrentMonth' => $date->month === $currentDate->month,
                'trades' => $trades,
                'pnl' => $pnl,
                'count' => $trades->count(),
            ];
            $date->addDay();
        }

        // Weekly summary
        $weeklySummary = [];
        foreach (array_chunk($calendar, 7) as $weekNum => $weekDays) {
            $weekTrades = collect($weekDays)->pluck('trades')->flatten();
            $pnl = $weekTrades->sum('profit_loss');
            $daysTraded = collect($weekDays)->filter(fn($d) => $d['count'] > 0)->count();
            
            $firstDay = collect($weekDays)->first()['date']->format('M j');
            $lastDay = collect($weekDays)->last()['date']->format('M j');
            
            $weeklySummary[] = [
                'label' => 'Week ' . ['One', 'Two', 'Three', 'Four', 'Five', 'Six'][$weekNum] ?? ($weekNum+1),
                'range' => "$firstDay - $lastDay",
                'pnl' => $pnl,
                'days' => $daysTraded,
            ];
        }

        // Total stats for month
        $monthTrades = $tradesByDate->flatten();
        $totalPnl = $monthTrades->sum('profit_loss');
        $totalDays = $tradesByDate->count();
        $totalTradesCount = $monthTrades->count();
        $totalLots = $monthTrades->sum('lot_size');
        $biggestWin = $monthTrades->max('profit_loss') ?? 0;
        $biggestLoss = $monthTrades->min('profit_loss') ?? 0;

        return view('journal.index', compact(
            'month', 'currentDate', 'prevMonth', 'nextMonth', 'calendar', 'weeklySummary', 'tradesByDate',
            'totalPnl', 'totalDays', 'totalTradesCount', 'totalLots', 'biggestWin', 'biggestLoss'
        ));
    }
}
