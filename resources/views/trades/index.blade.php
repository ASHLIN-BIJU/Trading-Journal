@extends('layouts.app')
@php $title = 'Trade Log'; @endphp

@section('topnav-actions')
    <a href="{{ route('export.csv', request()->query()) }}" class="btn btn-secondary btn-sm" id="btn-export">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Export CSV
    </a>
@endsection

@section('content')
<div style="display:flex;flex-direction:column;gap:1rem;">

    {{-- Filters --}}
    <div class="card" x-data="{ open: {{ request()->hasAny(['asset','result','type','from','to','tag']) ? 'true' : 'false' }} }">
        <div class="card-header" @click="open = !open" style="cursor:pointer;">
            <div class="card-title">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline;margin-right:6px;"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                Filters
                @if(request()->hasAny(['asset','result','type','from','to','tag']))
                    <span class="badge badge-open" style="margin-left:6px;">Active</span>
                @endif
            </div>
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" :style="open ? 'transform:rotate(180deg)' : ''"><polyline points="6 9 12 15 18 9"/></svg>
        </div>

        <div x-show="open" x-collapse>
            <form method="GET" action="{{ route('trades.index') }}" id="filter-form">
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:0.75rem;margin-top:0.5rem;">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Asset</label>
                        <select name="asset" class="form-control" id="filter-asset">
                            <option value="">All Assets</option>
                            @foreach($assets as $a)
                                <option value="{{ $a }}" {{ request('asset') == $a ? 'selected' : '' }}>{{ $a }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Direction</label>
                        <select name="type" class="form-control" id="filter-type">
                            <option value="">Buy & Sell</option>
                            <option value="buy" {{ request('type') == 'buy' ? 'selected' : '' }}>Buy (Long)</option>
                            <option value="sell" {{ request('type') == 'sell' ? 'selected' : '' }}>Sell (Short)</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Result</label>
                        <select name="result" class="form-control" id="filter-result">
                            <option value="">All Results</option>
                            <option value="win" {{ request('result') == 'win' ? 'selected' : '' }}>Win</option>
                            <option value="loss" {{ request('result') == 'loss' ? 'selected' : '' }}>Loss</option>
                            <option value="breakeven" {{ request('result') == 'breakeven' ? 'selected' : '' }}>Breakeven</option>
                            <option value="open" {{ request('result') == 'open' ? 'selected' : '' }}>Open</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Tag</label>
                        <select name="tag" class="form-control" id="filter-tag">
                            <option value="">All Tags</option>
                            @foreach($tags as $tag)
                                <option value="{{ $tag->id }}" {{ request('tag') == $tag->id ? 'selected' : '' }}>{{ $tag->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">From</label>
                        <input type="date" name="from" class="form-control" value="{{ request('from') }}" id="filter-from">
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">To</label>
                        <input type="date" name="to" class="form-control" value="{{ request('to') }}" id="filter-to">
                    </div>
                </div>
                <div style="display:flex;gap:0.5rem;margin-top:0.75rem;">
                    <button type="submit" class="btn btn-primary btn-sm" id="btn-apply-filter">Apply Filters</button>
                    <a href="{{ route('trades.index') }}" class="btn btn-ghost btn-sm" id="btn-clear-filter">Clear</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Trade Table --}}
    <div class="card" style="padding:0;overflow:hidden;">
        <div class="section-header" style="padding:1rem 1.25rem;">
            <div>
                <div class="section-title">Trade Log</div>
                <div style="font-size:0.75rem;color:var(--text-2);">{{ $trades->total() }} trades found</div>
            </div>
            <a href="{{ route('trades.create') }}" class="btn btn-primary btn-sm" id="btn-new-trade">
                + New Trade
            </a>
        </div>

        @if($trades->isEmpty())
            <div style="text-align:center;padding:3rem;color:var(--text-3);">
                <div style="font-size:2.5rem;margin-bottom:0.75rem;">📋</div>
                <p>No trades found. <a href="{{ route('trades.create') }}" style="color:var(--accent);">Add your first trade</a></p>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th><th>Asset</th><th>Type</th>
                            <th>Entry</th><th>Exit</th><th>Lot</th>
                            <th>P&amp;L</th><th>R:R</th><th>Pips</th>
                            <th>Result</th><th>Tags</th><th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($trades as $trade)
                        <tr>
                            <td class="mono" style="color:var(--text-2);white-space:nowrap;">{{ $trade->trade_date->format('M d Y') }}<br><span style="font-size:0.7rem;">{{ $trade->trade_date->format('H:i') }}</span></td>
                            <td style="font-weight:700;">{{ $trade->asset }}</td>
                            <td><span class="badge badge-{{ $trade->type }}">{{ strtoupper($trade->type) }}</span></td>
                            <td class="mono">{{ number_format((float)$trade->entry_price, 4) }}</td>
                            <td class="mono">{!! $trade->exit_price ? number_format((float)$trade->exit_price, 4) : '<span style="color:var(--text-3)">Open</span>' !!}</td>
                            <td class="mono">{{ $trade->lot_size }}</td>
                            <td class="mono" style="font-weight:600;{{ (float)$trade->profit_loss >= 0 ? 'color:var(--accent)' : 'color:var(--danger)' }}">
                                {{ $trade->pnl_formatted }}
                            </td>
                            <td class="mono">{{ $trade->risk_reward ? number_format((float)$trade->risk_reward, 2) : '—' }}</td>
                            <td class="mono">{{ $trade->pips ? number_format((float)$trade->pips, 1) : '—' }}</td>
                            <td><span class="badge badge-{{ $trade->result }}">{{ strtoupper($trade->result) }}</span></td>
                            <td>
                                @foreach($trade->tags->take(2) as $tag)
                                    <span class="tag-pill" style="border-color:{{ $tag->color }};color:{{ $tag->color }};">{{ $tag->name }}</span>
                                @endforeach
                            </td>
                            <td style="white-space:nowrap;">
                                <a href="{{ route('trades.show', $trade) }}" class="btn btn-ghost btn-sm">View</a>
                                <a href="{{ route('trades.edit', $trade) }}" class="btn btn-ghost btn-sm">Edit</a>
                                <form action="{{ route('trades.destroy', $trade) }}" method="POST" style="display:inline;">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--danger);">Del</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding:0.75rem 1rem;">
                {{ $trades->links('vendor.pagination.custom') }}
            </div>
        @endif
    </div>
</div>
@endsection
