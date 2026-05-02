@extends('layouts.app')
@php $title = 'Performance Analytics'; @endphp

@section('content')
<div style="display:flex;flex-direction:column;gap:1.25rem;">

    {{-- Core Stats --}}
    <div class="kpi-grid">
        <div class="stat-card accent">
            <div class="stat-label">Win Streak</div>
            <div class="stat-value positive">{{ $stats->max_win_streak }}</div>
            <div class="stat-meta">Max consecutive wins</div>
        </div>
        <div class="stat-card danger">
            <div class="stat-label">Loss Streak</div>
            <div class="stat-value negative">{{ $stats->max_loss_streak }}</div>
            <div class="stat-meta">Max consecutive losses</div>
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

    {{-- Equity + Monthly Charts --}}
    <div class="chart-grid">
        <div class="card">
            <div class="card-header"><div class="card-title">Equity Curve</div></div>
            <div id="analytics-equity" style="min-height:240px;"></div>
        </div>
        <div class="card">
            <div class="card-header"><div class="card-title">Monthly P&L</div></div>
            <div id="monthly-chart" style="min-height:240px;"></div>
        </div>
    </div>

    {{-- Long vs Short --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
        <div class="card">
            <div class="card-header"><div class="card-title">Long vs Short Analysis</div></div>
            <div id="longshort-chart" style="min-height:200px;"></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;margin-top:1rem;">
                @foreach($longShort as $dir => $data)
                <div class="result-box">
                    <div class="stat-label">{{ strtoupper($dir) }}</div>
                    <div style="font-size:1.25rem;font-weight:700;color:{{ $dir=='buy' ? 'var(--accent)' : 'var(--danger)' }};font-family:monospace;">
                        {{ $data['win_rate'] }}%
                    </div>
                    <div class="stat-meta">{{ $data['total'] }} trades · ${{ number_format($data['total_pnl'],2) }}</div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Per-Asset Breakdown --}}
        <div class="card">
            <div class="card-header"><div class="card-title">Asset Performance</div></div>
            @if($assetStats->isEmpty())
                <p style="color:var(--text-3);text-align:center;padding:2rem;">No data yet</p>
            @else
                <div style="overflow-y:auto;max-height:360px;">
                    <table class="data-table">
                        <thead><tr><th>Asset</th><th>Trades</th><th>Win%</th><th>P&L</th><th>Avg P&L</th></tr></thead>
                        <tbody>
                            @foreach($assetStats as $row)
                            <tr>
                                <td style="font-weight:700;">{{ $row->asset }}</td>
                                <td class="mono">{{ $row->total }}</td>
                                <td class="mono">{{ $row->total > 0 ? round($row->wins/$row->total*100,1) : 0 }}%</td>
                                <td class="mono" style="font-weight:600;color:{{ $row->pnl >= 0 ? 'var(--accent)' : 'var(--danger)' }}">
                                    {{ $row->pnl >= 0 ? '+' : '' }}${{ number_format((float)$row->pnl,2) }}
                                </td>
                                <td class="mono" style="color:{{ $row->avg_pnl >= 0 ? 'var(--accent)' : 'var(--danger)' }}">
                                    {{ $row->avg_pnl >= 0 ? '+' : '' }}${{ number_format((float)$row->avg_pnl,2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Best / Worst Days --}}
    <div class="card">
        <div class="card-header"><div class="card-title">Best & Worst Trading Days</div></div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="result-box">
                <div class="stat-label">Best Day</div>
                @if($bestWorst['best'])
                    <div style="font-size:1.5rem;font-weight:800;color:var(--accent);font-family:monospace;">
                        +${{ number_format($bestWorst['best']['pnl'],2) }}
                    </div>
                    <div class="stat-meta">{{ $bestWorst['best']['date'] }}</div>
                @else
                    <div style="color:var(--text-3);">No data</div>
                @endif
            </div>
            <div class="result-box">
                <div class="stat-label">Worst Day</div>
                @if($bestWorst['worst'])
                    <div style="font-size:1.5rem;font-weight:800;color:var(--danger);font-family:monospace;">
                        {{ $bestWorst['worst']['pnl'] >= 0 ? '+' : '' }}${{ number_format($bestWorst['worst']['pnl'],2) }}
                    </div>
                    <div class="stat-meta">{{ $bestWorst['worst']['date'] }}</div>
                @else
                    <div style="color:var(--text-3);">No data</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const accent = '#00D4A8', danger = '#FF4757', info = '#4C9EEB', warn = '#FFB649';
    const fontColor = '#8B949E', gridColor = '#21262D';

    const equityData = @json($equityCurve);
    if (equityData.length > 0) {
        new ApexCharts(document.getElementById('analytics-equity'), {
            chart: { type: 'area', height: 240, background: 'transparent', toolbar: { show: false } },
            series: [{ name: 'Balance', data: equityData }],
            colors: [accent],
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.2, opacityTo: 0 } },
            stroke: { curve: 'smooth', width: 2 },
            xaxis: { type: 'datetime', labels: { style: { colors: fontColor } }, axisBorder: { show: false } },
            yaxis: { labels: { style: { colors: fontColor }, formatter: v => '$' + v.toLocaleString() } },
            grid: { borderColor: gridColor, strokeDashArray: 4 },
            tooltip: { theme: 'dark', y: { formatter: v => '$' + v.toLocaleString() } },
            dataLabels: { enabled: false }, theme: { mode: 'dark' }
        }).render();
    } else {
        document.getElementById('analytics-equity').innerHTML = '<p style="color:var(--text-3);text-align:center;padding:3rem;">No closed trades yet</p>';
    }

    const monthly = @json($monthly);
    if (monthly.length > 0) {
        new ApexCharts(document.getElementById('monthly-chart'), {
            chart: { type: 'bar', height: 240, background: 'transparent', toolbar: { show: false } },
            series: [{ name: 'P&L', data: monthly.map(m => ({ x: m.month, y: m.pnl })) }],
            colors: [accent],
            plotOptions: { bar: { borderRadius: 4, colors: { ranges: [{ from: -999999, to: 0, color: danger }] } } },
            xaxis: { labels: { style: { colors: fontColor } }, axisBorder: { show: false } },
            yaxis: { labels: { style: { colors: fontColor }, formatter: v => '$' + v.toLocaleString() } },
            grid: { borderColor: gridColor, strokeDashArray: 4 },
            tooltip: { theme: 'dark', y: { formatter: v => '$' + v.toLocaleString() } },
            dataLabels: { enabled: false }, theme: { mode: 'dark' }
        }).render();
    } else {
        document.getElementById('monthly-chart').innerHTML = '<p style="color:var(--text-3);text-align:center;padding:3rem;">No data</p>';
    }

    const ls = @json($longShort);
    new ApexCharts(document.getElementById('longshort-chart'), {
        chart: { type: 'bar', height: 200, background: 'transparent', toolbar: { show: false } },
        series: [
            { name: 'P&L', data: [ls.buy?.total_pnl || 0, ls.sell?.total_pnl || 0] },
            { name: 'Trades', data: [ls.buy?.total || 0, ls.sell?.total || 0] },
        ],
        xaxis: { categories: ['Long (Buy)', 'Short (Sell)'], labels: { style: { colors: fontColor } } },
        yaxis: { labels: { style: { colors: fontColor } } },
        colors: [accent, info],
        plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } },
        grid: { borderColor: gridColor, strokeDashArray: 4 },
        tooltip: { theme: 'dark' }, dataLabels: { enabled: false }, theme: { mode: 'dark' },
        legend: { labels: { colors: fontColor } }
    }).render();
});
</script>
@endpush
