@extends('layouts.app')
@php $title = 'Dashboard'; @endphp

@section('content')
<div style="display:flex;flex-direction:column;gap:1.25rem;">

    {{-- KPI Cards --}}
    <div class="kpi-grid">
        {{-- Total P&L --}}
        <div class="stat-card {{ $stats->total_pnl >= 0 ? 'accent' : 'danger' }}">
            <div class="stat-label">Total P&amp;L</div>
            <div class="stat-value {{ $stats->total_pnl >= 0 ? 'positive' : 'negative' }}">
                {{ $stats->total_pnl >= 0 ? '+' : '' }}${{ number_format((float)$stats->total_pnl, 2) }}
            </div>
            <div class="stat-meta">{{ $stats->total_trades }} closed trades</div>
        </div>

        {{-- Win Rate --}}
        <div class="stat-card accent">
            <div class="stat-label">Win Rate</div>
            <div class="stat-value positive">{{ number_format((float)$stats->win_rate, 1) }}%</div>
            <div class="stat-meta">{{ $stats->winning_trades }}W / {{ $stats->losing_trades }}L / {{ $stats->breakeven_trades }}BE</div>
        </div>

        {{-- Profit Factor --}}
        <div class="stat-card {{ $stats->profit_factor >= 1 ? 'accent' : 'danger' }}">
            <div class="stat-label">Profit Factor</div>
            <div class="stat-value {{ $stats->profit_factor >= 1 ? 'positive' : 'negative' }}">
                {{ number_format((float)$stats->profit_factor, 2) }}
            </div>
            <div class="stat-meta">Gross win / gross loss</div>
        </div>

        {{-- Expectancy --}}
        <div class="stat-card {{ $stats->expectancy >= 0 ? 'accent' : 'danger' }}">
            <div class="stat-label">Expectancy</div>
            <div class="stat-value {{ $stats->expectancy >= 0 ? 'positive' : 'negative' }}">
                ${{ number_format((float)$stats->expectancy, 2) }}
            </div>
            <div class="stat-meta">Avg expected per trade</div>
        </div>

        {{-- Avg R:R --}}
        <div class="stat-card info">
            <div class="stat-label">Avg R:R</div>
            <div class="stat-value neutral">{{ number_format((float)$stats->avg_rr, 2) }}</div>
            <div class="stat-meta">Risk-reward ratio</div>
        </div>

        {{-- Max Drawdown --}}
        <div class="stat-card danger">
            <div class="stat-label">Max Drawdown</div>
            <div class="stat-value negative">{{ number_format((float)$stats->max_drawdown, 2) }}%</div>
            <div class="stat-meta">${{ number_format((float)$stats->max_drawdown_amount, 2) }}</div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="chart-grid">
        {{-- Equity Curve --}}
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Equity Curve</div>
                    <div class="card-subtitle">Running account balance</div>
                </div>
            </div>
            <div id="equity-chart" style="min-height:220px;"></div>
        </div>

        {{-- Win / Loss Donut --}}
        <div class="card">
            <div class="card-header">
                <div class="card-title">Win / Loss Breakdown</div>
            </div>
            <div id="winloss-chart" style="min-height:220px;"></div>
        </div>
    </div>

    {{-- Drawdown + Best/Worst --}}
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:1rem;">
        <div class="card">
            <div class="card-header">
                <div class="card-title">Drawdown</div>
            </div>
            <div id="drawdown-chart" style="min-height:180px;"></div>
        </div>
        <div class="card">
            <div class="card-header"><div class="card-title">Best & Worst Days</div></div>
            @if($bestWorst['best'])
                <div style="display:flex;flex-direction:column;gap:0.75rem;">
                    <div class="result-box">
                        <div class="stat-label">Best Day</div>
                        <div style="font-size:1.25rem;font-weight:700;color:var(--accent);font-family:monospace;">
                            +${{ number_format($bestWorst['best']['pnl'], 2) }}
                        </div>
                        <div class="stat-meta">{{ $bestWorst['best']['date'] }}</div>
                    </div>
                    <div class="result-box">
                        <div class="stat-label">Worst Day</div>
                        <div style="font-size:1.25rem;font-weight:700;color:var(--danger);font-family:monospace;">
                            -${{ number_format(abs($bestWorst['worst']['pnl']), 2) }}
                        </div>
                        <div class="stat-meta">{{ $bestWorst['worst']['date'] }}</div>
                    </div>
                </div>
            @else
                <p style="color:var(--text-3);text-align:center;padding:2rem 0;">No closed trades yet</p>
            @endif
        </div>
    </div>

    {{-- Recent Trades --}}
    <div class="card">
        <div class="section-header">
            <div class="section-title">Recent Trades</div>
            <a href="{{ route('trades.index') }}" class="btn btn-ghost btn-sm">View All →</a>
        </div>
        @if($recentTrades->isEmpty())
            <div style="text-align:center;padding:2.5rem;color:var(--text-3);">
                <div style="font-size:2rem;margin-bottom:0.5rem;">📊</div>
                <p>No trades yet. <a href="{{ route('trades.create') }}" style="color:var(--accent);">Log your first trade</a></p>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th><th>Asset</th><th>Type</th>
                            <th>Entry</th><th>Exit</th><th>P&L</th>
                            <th>R:R</th><th>Result</th><th>Tags</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentTrades as $trade)
                        <tr>
                            <td class="mono" style="color:var(--text-2);">{{ $trade->trade_date->format('M d, H:i') }}</td>
                            <td style="font-weight:600;">{{ $trade->asset }}</td>
                            <td><span class="badge badge-{{ $trade->type }}">{{ strtoupper($trade->type) }}</span></td>
                            <td class="mono">{{ number_format((float)$trade->entry_price, 4) }}</td>
                            <td class="mono">{{ $trade->exit_price ? number_format((float)$trade->exit_price, 4) : '—' }}</td>
                            <td class="mono {{ (float)$trade->profit_loss >= 0 ? 'text-positive' : 'text-negative' }}" style="font-weight:600;">
                                {{ $trade->pnl_formatted }}
                            </td>
                            <td class="mono">{{ $trade->risk_reward ? number_format((float)$trade->risk_reward, 2) : '—' }}</td>
                            <td><span class="badge badge-{{ $trade->result }}">{{ strtoupper($trade->result) }}</span></td>
                            <td>
                                @foreach($trade->tags->take(2) as $tag)
                                    <span class="tag-pill" style="border-color:{{ $tag->color }};color:{{ $tag->color }};">{{ $tag->name }}</span>
                                @endforeach
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = true;
    const fontColor = '#8B949E';
    const gridColor = '#21262D';
    const accentColor = '#00D4A8';
    const dangerColor = '#FF4757';

    // ── Equity Curve ──────────────────────────────────────────────────────
    const equityData = @json($equityCurve);
    if (equityData.length > 0) {
        new ApexCharts(document.getElementById('equity-chart'), {
            chart: { type: 'area', height: 220, background: 'transparent', toolbar: { show: false },
                     animations: { enabled: true, easing: 'easeinout', speed: 600 } },
            series: [{ name: 'Balance', data: equityData }],
            colors: [accentColor],
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.25, opacityTo: 0, stops: [0, 100] } },
            stroke: { curve: 'smooth', width: 2 },
            xaxis: { type: 'datetime', labels: { style: { colors: fontColor }, datetimeUTC: false }, axisBorder: { show: false } },
            yaxis: { labels: { style: { colors: fontColor }, formatter: v => '$' + v.toLocaleString() }, axisBorder: { show: false } },
            grid: { borderColor: gridColor, strokeDashArray: 4 },
            tooltip: { theme: 'dark', x: { format: 'MMM dd, HH:mm' }, y: { formatter: v => '$' + v.toLocaleString() } },
            dataLabels: { enabled: false },
            theme: { mode: 'dark' }
        }).render();
    } else {
        document.getElementById('equity-chart').innerHTML = '<p style="color:var(--text-3);text-align:center;padding:2rem;">No closed trades yet</p>';
    }

    // ── Win/Loss Donut ────────────────────────────────────────────────────
    const resultCounts = @json($resultCounts);
    new ApexCharts(document.getElementById('winloss-chart'), {
        chart: { type: 'donut', height: 220, background: 'transparent', toolbar: { show: false } },
        series: [resultCounts.win, resultCounts.loss, resultCounts.breakeven],
        labels: ['Win', 'Loss', 'Breakeven'],
        colors: [accentColor, dangerColor, '#FFB649'],
        legend: { labels: { colors: fontColor }, position: 'bottom' },
        dataLabels: { style: { colors: ['#0D1117'] } },
        plotOptions: { pie: { donut: { size: '65%',
            labels: { show: true, total: { show: true, label: 'Total', color: fontColor,
                formatter: w => w.globals.seriesTotals.reduce((a, b) => a + b, 0) } } } } },
        theme: { mode: 'dark' },
        tooltip: { theme: 'dark' }
    }).render();

    // ── Drawdown ──────────────────────────────────────────────────────────
    const ddData = @json($drawdownSeries);
    if (ddData.length > 0) {
        new ApexCharts(document.getElementById('drawdown-chart'), {
            chart: { type: 'area', height: 180, background: 'transparent', toolbar: { show: false } },
            series: [{ name: 'Drawdown', data: ddData }],
            colors: [dangerColor],
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0.05, stops: [0, 100] } },
            stroke: { curve: 'smooth', width: 1.5 },
            xaxis: { type: 'datetime', labels: { style: { colors: fontColor } }, axisBorder: { show: false } },
            yaxis: { labels: { style: { colors: fontColor }, formatter: v => v.toFixed(1) + '%' }, axisBorder: { show: false } },
            grid: { borderColor: gridColor, strokeDashArray: 4 },
            tooltip: { theme: 'dark', y: { formatter: v => v.toFixed(2) + '%' } },
            dataLabels: { enabled: false },
            theme: { mode: 'dark' }
        }).render();
    } else {
        document.getElementById('drawdown-chart').innerHTML = '<p style="color:var(--text-3);text-align:center;padding:2rem;">No drawdown data</p>';
    }
});
</script>
@endpush
