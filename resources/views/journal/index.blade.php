@extends('layouts.app')

@section('content')
<div class="journal-wrapper" style="display: grid; grid-template-columns: 1fr 320px; gap: 1.5rem; height: calc(100vh - 80px);">
    
    {{-- Main Calendar Area --}}
    <div class="calendar-main" style="display: flex; flex-direction: column; gap: 1.5rem;">
        
        {{-- Header --}}
        <div class="calendar-header card" style="padding: 1rem 1.5rem; display: flex; align-items: center; justify-content: space-between; border-radius: 12px; border: none;">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <a href="{{ route('journal.index', ['month' => $prevMonth]) }}" class="btn btn-secondary btn-sm" style="background: var(--bg-hover); border: none;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <h2 style="font-size: 1.125rem; font-weight: 600; min-width: 140px; text-align: center;">{{ $month }}</h2>
                <a href="{{ route('journal.index', ['month' => $nextMonth]) }}" class="btn btn-secondary btn-sm" style="background: var(--bg-hover); border: none;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"></path></svg>
                </a>
                <a href="{{ route('journal.index') }}" class="btn btn-secondary btn-sm" style="background: var(--bg-hover); border: none;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:4px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    Today
                </a>
            </div>
            
            <div style="display: flex; gap: 1rem; align-items: center;">
                <div class="badge" style="background: var(--bg-hover); border: none; padding: 0.5rem 1rem; font-size: 0.8125rem; text-transform: none; font-weight: 500;">
                    PnL: <span class="{{ $totalPnl >= 0 ? 'text-positive' : 'text-negative' }} mono" style="margin-left: 6px;">{{ $totalPnl >= 0 ? '+' : '' }}${{ number_format($totalPnl, 2) }}</span>
                </div>
                <div class="badge" style="background: var(--bg-hover); border: none; padding: 0.5rem 1rem; font-size: 0.8125rem; text-transform: none; font-weight: 500; color: var(--text-2);">
                    Days: <span style="color: var(--text-1); margin-left: 4px;">{{ $totalDays }}</span>
                </div>
            </div>
        </div>

        {{-- Grid --}}
        <div class="calendar-grid card" style="flex: 1; border: none; border-radius: 12px; display: flex; flex-direction: column;">
            <div style="display: grid; grid-template-columns: repeat(7, 1fr); padding: 1rem 0; border-bottom: 1px solid var(--border-dim); text-align: center; font-size: 0.75rem; font-weight: 600; color: var(--text-2); text-transform: uppercase; letter-spacing: 0.05em;">
                <div>Sun</div><div>Mon</div><div>Tue</div><div>Wed</div><div>Thu</div><div>Fri</div><div>Sat</div>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(7, 1fr); grid-auto-rows: minmax(80px, 1fr); gap: 1px; background: var(--border-dim); flex: 1;">
                @foreach ($calendar as $day)
                    @php
                        $bgClass = 'bg-card';
                        $textClass = 'text-2';
                        $pnlClass = '';
                        $isHoverable = false;
                        
                        if ($day['count'] > 0) {
                            $isHoverable = true;
                            if ($day['pnl'] >= 0) {
                                $bgClass = 'bg-profit';
                                $pnlClass = 'text-positive';
                                $textClass = 'text-1';
                            } else {
                                $bgClass = 'bg-loss';
                                $pnlClass = 'text-negative';
                                $textClass = 'text-1';
                            }
                        } else if (!$day['isCurrentMonth']) {
                            $bgClass = 'bg-base';
                            $textClass = 'text-3';
                        }
                    @endphp
                    
                    <div class="cal-cell {{ $bgClass }} {{ $isHoverable ? 'hoverable' : '' }}" style="padding: 0.75rem; display: flex; flex-direction: column; justify-content: space-between; position: relative;">
                        <span class="cal-date {{ $textClass }}" style="font-size: 0.875rem; font-weight: 500;">
                            {{ $day['date']->format('j') }}
                        </span>
                        
                        @if ($day['count'] > 0)
                            <div style="display: flex; flex-direction: column; align-items: flex-end;">
                                <span class="{{ $pnlClass }} mono" style="font-size: 0.8125rem; font-weight: 600;">
                                    {{ $day['pnl'] >= 0 ? '+' : '' }}${{ number_format($day['pnl'], 2) }}
                                </span>
                                <span style="font-size: 0.6875rem; color: rgba(255,255,255,0.5); display: flex; align-items: center; gap: 2px;">
                                    {{ $day['count'] }}
                                    <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 17L17 7M17 7H7M17 7V17"></path></svg>
                                </span>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Stats Row --}}
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
            <div class="card" style="border: none; border-radius: 12px;">
                <div class="stat-label" style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    Number of days
                </div>
                <div class="stat-value neutral">{{ $totalDays }}</div>
            </div>
            <div class="card" style="border: none; border-radius: 12px;">
                <div class="stat-label" style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path></svg>
                    Total Trades Taken
                </div>
                <div class="stat-value neutral">{{ $totalTradesCount }}</div>
            </div>
            <div class="card" style="border: none; border-radius: 12px;">
                <div class="stat-label" style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 3v18h18"></path><path d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3"></path></svg>
                    Total Lots Used
                </div>
                <div class="stat-value neutral mono">{{ number_format($totalLots, 2) }}</div>
            </div>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 2rem;">
            <div class="card" style="border: none; border-radius: 12px;">
                <div class="stat-label" style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                    <svg width="16" height="16" fill="none" stroke="var(--profit-text)" stroke-width="2" viewBox="0 0 24 24"><path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    Biggest Win
                </div>
                <div class="stat-value positive mono">+${{ number_format($biggestWin, 2) }}</div>
            </div>
            <div class="card" style="border: none; border-radius: 12px;">
                <div class="stat-label" style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                    <svg width="16" height="16" fill="none" stroke="var(--loss-text)" stroke-width="2" viewBox="0 0 24 24"><path d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"></path></svg>
                    Biggest Loss
                </div>
                <div class="stat-value negative mono">{{ $biggestLoss < 0 ? '' : '-' }}${{ number_format(abs($biggestLoss), 2) }}</div>
            </div>
        </div>

    </div>

    {{-- Right Sidebar --}}
    <div class="calendar-sidebar" style="display: flex; flex-direction: column; gap: 1rem;">
        <div class="card" style="border: none; border-radius: 12px; flex: 1;">
            <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--text-2); margin-bottom: 1.5rem;">Weekly Summary</h3>
            
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                @foreach ($weeklySummary as $week)
                    <div class="week-row" style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.875rem; font-weight: 600; color: var(--text-1);">{{ $week['label'] }}</span>
                            <span style="font-size: 0.75rem; color: var(--text-3);">{{ $week['range'] }}</span>
                        </div>
                        
                        @if ($week['days'] > 0)
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 0.8125rem; color: var(--text-2);">PnL: <span class="{{ $week['pnl'] >= 0 ? 'text-positive' : 'text-negative' }} mono" style="margin-left: 4px; font-weight: 600;">{{ $week['pnl'] >= 0 ? '+' : '' }}${{ number_format($week['pnl'], 2) }}</span></span>
                                <span style="font-size: 0.8125rem; color: var(--text-2);">Days: <span style="color: var(--text-1); font-weight: 600;">{{ $week['days'] }}</span></span>
                            </div>
                        @else
                            <div style="font-size: 0.8125rem; color: var(--text-3);">No trades</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<style>
    .bg-card { background: var(--bg-card); }
    .bg-base { background: var(--bg-base); }
    .bg-profit { background: var(--profit-bg); }
    .bg-loss { background: var(--loss-bg); }
    
    .cal-cell {
        transition: filter 0.2s, transform 0.2s;
    }
    .cal-cell.hoverable:hover {
        filter: brightness(1.2);
        z-index: 10;
        cursor: pointer;
    }
</style>
@endsection
