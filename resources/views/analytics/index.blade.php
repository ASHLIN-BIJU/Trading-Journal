@extends('layouts.app')
@php $title = 'Performance Analytics'; @endphp

@section('content')
<div style="display:flex;flex-direction:column;gap:1.5rem;">

    {{-- Filter Bar --}}
    <div class="card" style="padding: 0.875rem 1.25rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
            <div style="display:flex;align-items:center;gap:1rem;">
                <span style="font-size:0.75rem;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:0.1em;">Analysis Period</span>
                <div style="display:flex;background:var(--bg-surface);padding:4px;border-radius:10px;border:1px solid var(--border);">
                    @foreach(['all' => 'All', '1y' => '1Y', '6m' => '6M', '3m' => '3M', '2m' => '2M', '1m' => '1M'] as $val => $label)
                        <a href="{{ route('analytics.index', ['timeframe' => $val]) }}" 
                           class="btn-sm" 
                           style="border-radius:7px; font-weight:600; text-decoration:none; padding: 5px 12px; transition:all 0.2s; font-size: 0.75rem;
                                  {{ $timeframe == $val ? 'background:var(--accent); color:#fff; box-shadow: 0 2px 8px var(--accent-glow);' : 'color:var(--text-2);' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>
            <div style="font-size:0.8125rem;color:var(--text-2); display:flex; align-items:center; gap:0.5rem;">
                <div style="width:8px; height:8px; border-radius:50%; background:var(--accent);"></div>
                Data Scoped: <span style="color:var(--text-1);font-weight:600;">{{ $timeframe == 'all' ? 'Entire History' : 'Past ' . str_replace(['y','m'],[' Year',' Months'], $timeframe) }}</span>
            </div>
        </div>
    </div>

    {{-- Cross Analysis (Day of Week) --}}
    <div class="card" style="padding:0;">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-dim); display:flex; justify-content:space-between; align-items:center;">
            <div>
                <h3 style="margin:0; font-size:1rem; font-weight:700; color:var(--text-1);">Cross Analysis</h3>
                <p style="margin:4px 0 0; font-size:0.75rem; color:var(--text-2);">Profitability distribution across the week</p>
            </div>
            <div style="display:flex; gap:0.5rem;">
                <div style="padding:4px 12px; background:var(--bg-surface); border-radius:6px; border:1px solid var(--border); font-size:0.75rem; color:var(--text-2); font-weight:600;">P&L</div>
            </div>
        </div>
        
        <div style="overflow:hidden;">
            <table style="width:100%; border-collapse:collapse; table-layout: fixed;">
                <tbody>
                    @php
                        $maxAbsPnl = collect($dayPerf)->map(fn($d) => abs($d['pnl']))->max() ?: 1;
                    @endphp
                    @foreach($dayPerf as $row)
                        @php
                            $isProfit = $row['pnl'] >= 0;
                            $absPnl = abs($row['pnl']);
                            $intensity = min(1, $absPnl / $maxAbsPnl);
                            
                            // Define colours based on user request: green, light green, red
                            if ($isProfit) {
                                // Green for high profit, light green for low profit
                                if ($intensity > 0.5) {
                                    $bgColor = 'rgba(29, 187, 124, 0.25)'; // Deep Green
                                    $barColor = '#1dbb7c';
                                } else {
                                    $bgColor = 'rgba(42, 92, 78, 0.3)'; // Light Green / Dark Teal
                                    $barColor = '#2a5c4e';
                                }
                            } else {
                                $bgColor = 'rgba(217, 67, 72, 0.15)'; // Red
                                $barColor = '#d94348';
                            }
                        @endphp
                        <tr style="border-bottom: 1px solid var(--border-dim); transition: background 0.2s;">
                            <td style="padding: 1.25rem 1.5rem; width: 180px; font-weight: 600; color: var(--text-2); font-size: 0.875rem;">
                                {{ $row['day'] }}
                            </td>
                            <td style="position: relative; padding: 0;">
                                {{-- The background "bar" --}}
                                <div style="position: absolute; right: 0; top: 4px; bottom: 4px; width: {{ $intensity * 100 }}%; background: {{ $bgColor }}; border-radius: 4px 0 0 4px; transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);"></div>
                                
                                <div style="position: relative; z-index: 1; display: flex; justify-content: flex-end; align-items: center; padding: 1.25rem 1.5rem; gap: 2rem;">
                                    <div style="display:flex; flex-direction:column; align-items: flex-end;">
                                        <span class="mono" style="font-size: 0.9375rem; font-weight: 700; color: {{ $isProfit ? 'var(--profit-text)' : 'var(--loss-text)' }}">
                                            {{ $isProfit ? '+' : '' }}${{ number_format($row['pnl'], 2) }}
                                        </span>
                                        <span style="font-size: 0.6875rem; color: var(--text-3); font-weight: 600;">{{ $row['trades'] }} Trades · {{ $row['win_rate'] }}% Win</span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Core Stats Grid --}}
    <div class="kpi-grid">
        <div class="stat-card accent">
            <div class="stat-label">Win Streak</div>
            <div class="stat-value positive">{{ $stats->max_win_streak }}</div>
            <div class="stat-meta">Consecutive wins</div>
        </div>
        <div class="stat-card danger">
            <div class="stat-label">Loss Streak</div>
            <div class="stat-value negative">{{ $stats->max_loss_streak }}</div>
            <div class="stat-meta">Consecutive losses</div>
        </div>
        <div class="stat-card info">
            <div class="stat-label">Avg Win</div>
            <div class="stat-value neutral">${{ number_format((float)$stats->avg_win, 2) }}</div>
            <div class="stat-meta">Per winning trade</div>
        </div>
        <div class="stat-card warn">
            <div class="stat-label">Avg Loss</div>
            <div class="stat-value" style="color:var(--warn);">-${{ number_format((float)$stats->avg_loss, 2) }}</div>
            <div class="stat-meta">Per losing trade</div>
        </div>
    </div>

    {{-- Secondary Charts --}}
    <div class="chart-grid">
        <div class="card">
            <div class="card-header"><div class="card-title">Equity Growth</div></div>
            <div id="analytics-equity" style="min-height:240px;"></div>
        </div>
        <div class="card">
            <div class="card-header"><div class="card-title">Monthly Comparison</div></div>
            <div id="monthly-chart" style="min-height:240px;"></div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">
        {{-- Asset Analysis --}}
        <div class="card" style="padding:0;">
            <div style="padding: 1.25rem; border-bottom: 1px solid var(--border-dim);">
                <h3 style="margin:0; font-size:0.875rem; font-weight:700;">Asset Performance</h3>
            </div>
            @if($assetStats->isEmpty())
                <p style="color:var(--text-3);text-align:center;padding:3rem;">Insufficient trade data</p>
            @else
                <div style="overflow-y:auto;max-height:400px;">
                    <table class="data-table">
                        <thead><tr><th>Asset</th><th>Win%</th><th>Net P&L</th></tr></thead>
                        <tbody>
                            @foreach($assetStats as $row)
                            <tr>
                                <td style="font-weight:700;">{{ $row->asset }}</td>
                                <td class="mono">{{ $row->total > 0 ? round($row->wins/$row->total*100,1) : 0 }}%</td>
                                <td class="mono" style="font-weight:700;color:{{ $row->pnl >= 0 ? 'var(--profit-text)' : 'var(--loss-text)' }}">
                                    {{ $row->pnl >= 0 ? '+' : '' }}${{ number_format((float)$row->pnl,2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Long vs Short --}}
        <div class="card">
            <div class="card-header"><div class="card-title">Side Bias Analysis</div></div>
            <div id="longshort-chart" style="min-height:200px;"></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-top:1.5rem;">
                @foreach($longShort as $dir => $data)
                <div class="result-box" style="padding: 1rem; border-radius: 12px; border-left: 4px solid {{ $dir=='buy' ? 'var(--profit-text)' : 'var(--loss-text)' }}">
                    <div class="stat-label" style="margin-bottom:0.25rem;">{{ strtoupper($dir) }}</div>
                    <div style="font-size:1.125rem;font-weight:800;color:var(--text-1); font-family: 'JetBrains Mono';">
                        {{ $data['win_rate'] }}% <span style="font-size:0.75rem; font-weight:500; color:var(--text-3);">Win Rate</span>
                    </div>
                    <div class="stat-meta" style="color:{{ $data['total_pnl'] >= 0 ? 'var(--profit-text)' : 'var(--loss-text)' }}">
                        {{ $data['total_pnl'] >= 0 ? '+' : '' }}${{ number_format($data['total_pnl'],2) }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const accent = '#3b5ef8', danger = '#ff4d4f', profit = '#1dbb7c', loss = '#d94348', info = '#00D4A8', warn = '#FFB649';
    const fontColor = '#8B949E', gridColor = '#282e3f';

    const equityData = @json($equityCurve);
    if (equityData.length > 0) {
        new ApexCharts(document.getElementById('analytics-equity'), {
            chart: { type: 'area', height: 240, background: 'transparent', toolbar: { show: false }, animations: { enabled: true } },
            series: [{ name: 'Balance', data: equityData }],
            colors: [accent],
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.25, opacityTo: 0 } },
            stroke: { curve: 'smooth', width: 3 },
            xaxis: { type: 'datetime', labels: { style: { colors: fontColor, fontSize: '11px' } }, axisBorder: { show: false } },
            yaxis: { labels: { style: { colors: fontColor, fontSize: '11px' }, formatter: v => '$' + v.toLocaleString() } },
            grid: { borderColor: gridColor, strokeDashArray: 4 },
            tooltip: { theme: 'dark', x: { format: 'dd MMM yyyy' }, y: { formatter: v => '$' + v.toLocaleString() } },
            dataLabels: { enabled: false }, theme: { mode: 'dark' }
        }).render();
    }

    const monthly = @json($monthly);
    if (monthly.length > 0) {
        new ApexCharts(document.getElementById('monthly-chart'), {
            chart: { type: 'bar', height: 240, background: 'transparent', toolbar: { show: false } },
            series: [{ name: 'Net P&L', data: monthly.map(m => ({ x: m.month, y: m.pnl })) }],
            colors: [accent],
            plotOptions: { bar: { borderRadius: 5, columnWidth: '50%', colors: { ranges: [{ from: -99999999, to: 0, color: loss }] } } },
            xaxis: { labels: { style: { colors: fontColor, fontSize: '11px' } }, axisBorder: { show: false } },
            yaxis: { labels: { style: { colors: fontColor, fontSize: '11px' }, formatter: v => '$' + v.toLocaleString() } },
            grid: { borderColor: gridColor, strokeDashArray: 4 },
            tooltip: { theme: 'dark' }, dataLabels: { enabled: false }, theme: { mode: 'dark' }
        }).render();
    }

    const ls = @json($longShort);
    new ApexCharts(document.getElementById('longshort-chart'), {
        chart: { type: 'bar', height: 200, background: 'transparent', toolbar: { show: false } },
        series: [
            { name: 'P&L', data: [ls.buy?.total_pnl || 0, ls.sell?.total_pnl || 0] },
            { name: 'Trades', data: [ls.buy?.total || 0, ls.sell?.total || 0] },
        ],
        xaxis: { categories: ['Longs', 'Shorts'], labels: { style: { colors: fontColor } }, axisBorder: { show: false } },
        yaxis: { labels: { style: { colors: fontColor } } },
        colors: [accent, info],
        plotOptions: { bar: { borderRadius: 6, columnWidth: '40%', dataLabels: { position: 'top' } } },
        grid: { borderColor: gridColor, strokeDashArray: 4 },
        tooltip: { theme: 'dark' }, theme: { mode: 'dark' },
        legend: { position: 'top', horizontalAlign: 'right', labels: { colors: fontColor } }
    }).render();
});
</script>
@endpush
