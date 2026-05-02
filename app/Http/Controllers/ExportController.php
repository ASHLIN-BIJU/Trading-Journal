<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExportController extends Controller
{
    public function csv(Request $request)
    {
        $user  = Auth::user();
        $query = $user->getActiveAccount()->trades()->with('tags')->orderByDesc('trade_date');

        // Apply same filters as trade index
        if ($request->filled('asset'))  $query->byAsset($request->asset);
        if ($request->filled('result')) $query->byResult($request->result);
        if ($request->filled('from') || $request->filled('to')) {
            $query->dateRange($request->from, $request->to);
        }

        $trades = $query->get();

        $filename = 'trades_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($trades) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'Date', 'Asset', 'Type', 'Entry', 'Exit', 'SL', 'TP',
                'Lot Size', 'P&L ($)', 'P&L (%)', 'Risk ($)', 'R:R',
                'Pips', 'Result', 'Session', 'Timeframe', 'Tags', 'Notes',
            ]);

            foreach ($trades as $trade) {
                fputcsv($file, [
                    $trade->trade_date->format('Y-m-d H:i'),
                    $trade->asset,
                    strtoupper($trade->type),
                    $trade->entry_price,
                    $trade->exit_price ?? '',
                    $trade->stop_loss ?? '',
                    $trade->take_profit ?? '',
                    $trade->lot_size,
                    $trade->profit_loss ?? '',
                    $trade->profit_loss_pct ?? '',
                    $trade->risk_amount ?? '',
                    $trade->risk_reward ?? '',
                    $trade->pips ?? '',
                    strtoupper($trade->result),
                    $trade->session ?? '',
                    $trade->timeframe ?? '',
                    $trade->tags->pluck('name')->implode(', '),
                    $trade->notes ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
