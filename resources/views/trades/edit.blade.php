@extends('layouts.app')
@php $title = 'Edit Trade'; @endphp

@section('content')
<div style="max-width:900px;margin:0 auto;">
    <div class="card">
        <div class="card-header" style="margin-bottom:1.5rem;">
            <div>
                <div class="card-title" style="font-size:1.125rem;">Edit Trade — {{ $trade->asset }}</div>
                <div class="card-subtitle">Logged {{ $trade->trade_date->format('M d, Y H:i') }}</div>
            </div>
            <a href="{{ route('trades.show', $trade) }}" class="btn btn-ghost btn-sm">← Back</a>
        </div>

        <form method="POST" action="{{ route('trades.update', $trade) }}" enctype="multipart/form-data">
            @csrf @method('PUT')

            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Asset <span class="req">*</span></label>
                    <input type="text" name="asset" class="form-control" value="{{ old('asset', $trade->asset) }}" required list="asset-list2" id="edit-asset">
                    <datalist id="asset-list2">
                        <option>XAUUSD</option><option>EURUSD</option><option>GBPUSD</option>
                        <option>USDJPY</option><option>AUDUSD</option><option>BTCUSD</option>
                        <option>US30</option><option>NAS100</option>
                    </datalist>
                </div>
                <div class="form-group">
                    <label class="form-label">Direction <span class="req">*</span></label>
                    <select name="type" class="form-control" required id="edit-type">
                        <option value="buy" {{ old('type', $trade->type) == 'buy' ? 'selected' : '' }}>BUY (Long)</option>
                        <option value="sell" {{ old('type', $trade->type) == 'sell' ? 'selected' : '' }}>SELL (Short)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Trade Date <span class="req">*</span></label>
                    <input type="datetime-local" name="trade_date" class="form-control" required
                           value="{{ old('trade_date', $trade->trade_date->format('Y-m-d\TH:i')) }}" id="edit-date">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Entry Price <span class="req">*</span></label>
                    <input type="number" name="entry_price" step="0.00001" class="form-control form-control-mono"
                           value="{{ old('entry_price', $trade->entry_price) }}" required id="edit-entry">
                </div>
                <div class="form-group">
                    <label class="form-label">Exit Price</label>
                    <input type="number" name="exit_price" step="0.00001" class="form-control form-control-mono"
                           value="{{ old('exit_price', $trade->exit_price) }}" id="edit-exit">
                </div>
                <div class="form-group">
                    <label class="form-label">Stop Loss</label>
                    <input type="number" name="stop_loss" step="0.00001" class="form-control form-control-mono"
                           value="{{ old('stop_loss', $trade->stop_loss) }}" id="edit-sl">
                </div>
                <div class="form-group">
                    <label class="form-label">Take Profit</label>
                    <input type="number" name="take_profit" step="0.00001" class="form-control form-control-mono"
                           value="{{ old('take_profit', $trade->take_profit) }}" id="edit-tp">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Lot Size <span class="req">*</span></label>
                    <input type="number" name="lot_size" step="0.001" class="form-control form-control-mono"
                           value="{{ old('lot_size', $trade->lot_size) }}" required id="edit-lot">
                </div>
                <div class="form-group">
                    <label class="form-label">Session</label>
                    <select name="session" class="form-control" id="edit-session">
                        <option value="">Select session</option>
                        @foreach(['London','New York','Tokyo','Sydney','Other'] as $s)
                            <option value="{{ $s }}" {{ old('session', $trade->session) == $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Timeframe</label>
                    <select name="timeframe" class="form-control" id="edit-timeframe">
                        <option value="">Select TF</option>
                        @foreach(['1M','5M','15M','30M','1H','4H','1D','1W'] as $tf)
                            <option value="{{ $tf }}" {{ old('timeframe', $trade->timeframe) == $tf ? 'selected' : '' }}>{{ $tf }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            @if($tags->isNotEmpty())
            <div class="form-group">
                <label class="form-label">Tags</label>
                <div style="display:flex;flex-wrap:wrap;gap:0.5rem;">
                    @foreach($tags as $tag)
                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;padding:4px 10px;border-radius:999px;border:1px solid {{ $tag->color }};color:{{ $tag->color }};font-size:0.8125rem;">
                            <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}"
                                   {{ in_array($tag->id, old('tag_ids', $selectedTags)) ? 'checked' : '' }}>
                            {{ $tag->name }}
                        </label>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Current Images --}}
            @if($trade->images->isNotEmpty())
            <div class="form-group">
                <label class="form-label">Current Screenshots</label>
                <div style="display:flex;gap:0.75rem;flex-wrap:wrap;">
                    @foreach($trade->images as $img)
                        <div>
                            <img src="{{ asset('storage/'.$img->image_path) }}" style="height:80px;border-radius:6px;border:1px solid var(--border);">
                            <div style="font-size:0.7rem;color:var(--text-3);text-align:center;margin-top:2px;">{{ ucfirst($img->image_type) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group">
                    <label class="form-label">New Entry Screenshot</label>
                    <input type="file" name="entry_screenshot" accept="image/*" class="form-control" id="edit-entry-img">
                </div>
                <div class="form-group">
                    <label class="form-label">New Exit Screenshot</label>
                    <input type="file" name="exit_screenshot" accept="image/*" class="form-control" id="edit-exit-img">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="4" id="edit-notes">{{ old('notes', $trade->notes) }}</textarea>
            </div>

            <div style="display:flex;gap:0.75rem;justify-content:flex-end;padding-top:0.5rem;border-top:1px solid var(--border-dim);">
                <a href="{{ route('trades.show', $trade) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary" id="btn-update-trade">Update Trade</button>
            </div>
        </form>
    </div>
</div>
@endsection
