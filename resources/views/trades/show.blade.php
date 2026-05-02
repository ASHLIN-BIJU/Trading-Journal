@extends('layouts.app')
@php $title = 'Trade Detail'; @endphp

@section('content')
<div style="max-width:960px;margin:0 auto;display:flex;flex-direction:column;gap:1rem;">

    {{-- Header actions --}}
    <div style="display:flex;gap:0.5rem;align-items:center;">
        <a href="{{ route('trades.index') }}" class="btn btn-ghost btn-sm">← Back</a>
        <a href="{{ route('trades.edit', $trade) }}" class="btn btn-secondary btn-sm">Edit</a>
        <form action="{{ route('trades.destroy', $trade) }}" method="POST">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
        </form>
    </div>

    {{-- Trade Header Card --}}
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;">
            <div>
                <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.25rem;">
                    <span style="font-size:1.5rem;font-weight:800;color:var(--text-1);">{{ $trade->asset }}</span>
                    <span class="badge badge-{{ $trade->type }}" style="font-size:0.8125rem;padding:4px 12px;">{{ strtoupper($trade->type) }}</span>
                    <span class="badge badge-{{ $trade->result }}" style="font-size:0.8125rem;padding:4px 12px;">{{ strtoupper($trade->result) }}</span>
                    @if($trade->status === 'open')
                        <span class="badge badge-open">OPEN</span>
                    @endif
                </div>
                <div style="color:var(--text-2);font-size:0.875rem;">{{ $trade->trade_date->format('l, F j Y \a\t H:i') }}</div>
                @if($trade->session || $trade->timeframe)
                    <div style="margin-top:4px;">
                        @if($trade->session)<span class="tag-pill">{{ $trade->session }}</span>@endif
                        @if($trade->timeframe)<span class="tag-pill">{{ $trade->timeframe }}</span>@endif
                    </div>
                @endif
            </div>
            <div style="text-align:right;">
                <div style="font-size:2rem;font-weight:800;letter-spacing:-0.04em;font-family:monospace;
                     color:{{ (float)$trade->profit_loss >= 0 ? 'var(--accent)' : 'var(--danger)' }}">
                    {{ $trade->pnl_formatted }}
                </div>
                @if($trade->profit_loss_pct !== null)
                    <div style="font-size:0.875rem;color:var(--text-2);">{{ number_format((float)$trade->profit_loss_pct, 2) }}%</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:0.75rem;">
        @foreach([
            ['Entry', number_format((float)$trade->entry_price, 5)],
            ['Exit', $trade->exit_price ? number_format((float)$trade->exit_price, 5) : '—'],
            ['Stop Loss', $trade->stop_loss ? number_format((float)$trade->stop_loss, 5) : '—'],
            ['Take Profit', $trade->take_profit ? number_format((float)$trade->take_profit, 5) : '—'],
            ['Lot Size', $trade->lot_size],
            ['Risk Amount', $trade->risk_amount ? '$'.number_format((float)$trade->risk_amount, 2) : '—'],
            ['R:R Ratio', $trade->risk_reward ? number_format((float)$trade->risk_reward, 2).':1' : '—'],
            ['Pips', $trade->pips ? number_format((float)$trade->pips, 1) : '—'],
        ] as [$label, $value])
        <div class="stat-card">
            <div class="stat-label">{{ $label }}</div>
            <div style="font-family:monospace;font-size:1.1rem;font-weight:600;color:var(--text-1);margin-top:4px;">{{ $value }}</div>
        </div>
        @endforeach
    </div>

    {{-- Screenshots --}}
    @if($trade->images->isNotEmpty())
    <div class="card">
        <div class="card-title" style="margin-bottom:1rem;">Screenshots</div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:1rem;">
            @foreach($trade->images as $img)
            <div>
                <div style="font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;color:var(--text-2);margin-bottom:0.5rem;">
                    {{ ucfirst($img->image_type) }} Screenshot
                </div>
                <a href="{{ asset('storage/'.$img->image_path) }}" target="_blank">
                    <img src="{{ asset('storage/'.$img->image_path) }}"
                         style="width:100%;border-radius:8px;border:1px solid var(--border);cursor:zoom-in;"
                         alt="{{ $img->image_type }} screenshot">
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Tags --}}
    @if($trade->tags->isNotEmpty())
    <div class="card">
        <div class="card-title" style="margin-bottom:0.75rem;">Tags</div>
        <div style="display:flex;flex-wrap:wrap;gap:0.5rem;">
            @foreach($trade->tags as $tag)
                <span class="tag-pill" style="border-color:{{ $tag->color }};color:{{ $tag->color }};font-size:0.875rem;padding:4px 12px;">
                    {{ $tag->name }}
                    <span style="opacity:0.6;font-size:0.7rem;margin-left:2px;">({{ $tag->category }})</span>
                </span>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Notes --}}
    @if($trade->notes)
    <div class="card">
        <div class="card-title" style="margin-bottom:0.75rem;">Journal Notes</div>
        <div style="color:var(--text-1);line-height:1.7;white-space:pre-wrap;font-size:0.9rem;">{{ $trade->notes }}</div>
    </div>
    @endif
</div>
@endsection
