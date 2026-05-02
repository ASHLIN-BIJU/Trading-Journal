@extends('layouts.app')
@php $title = 'Log Trade'; @endphp

@section('content')
<div style="max-width:900px;margin:0 auto;">
    <div class="card">
        <div class="card-header" style="margin-bottom:1.5rem;">
            <div>
                <div class="card-title" style="font-size:1.125rem;">Log New Trade</div>
                <div class="card-subtitle">Fill in trade details — P&L and R:R will be calculated automatically</div>
            </div>
        </div>

        <form method="POST" action="{{ route('trades.store') }}" enctype="multipart/form-data"
              x-data="tradeForm()" @submit.prevent="submit">
            @csrf

            {{-- Live Preview Banner --}}
            <div x-show="preview.hasData" class="result-box" style="margin-bottom:1.5rem;display:flex;gap:2rem;flex-wrap:wrap;">
                <div>
                    <div class="stat-label">Est. P&L</div>
                    <div class="stat-value" :class="preview.pnl >= 0 ? 'positive' : 'negative'" x-text="(preview.pnl >= 0 ? '+' : '') + '$' + preview.pnl.toFixed(2)"></div>
                </div>
                <div>
                    <div class="stat-label">Risk Amount</div>
                    <div class="stat-value neutral" x-text="'$' + preview.risk.toFixed(2)"></div>
                </div>
                <div>
                    <div class="stat-label">R:R</div>
                    <div class="stat-value neutral" x-text="preview.rr.toFixed(2) + ':1'"></div>
                </div>
            </div>

            {{-- Row 1: Asset, Type, Date --}}
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Asset <span class="req">*</span></label>
                    <input type="text" name="asset" class="form-control" placeholder="e.g. XAUUSD"
                           value="{{ old('asset') }}" required list="asset-list" id="field-asset"
                           x-model="asset" @input="calcPreview">
                    <datalist id="asset-list">
                        <option>XAUUSD</option><option>EURUSD</option><option>GBPUSD</option>
                        <option>USDJPY</option><option>AUDUSD</option><option>NZDUSD</option>
                        <option>GBPJPY</option><option>USDCAD</option><option>BTCUSD</option>
                        <option>ETHUSD</option><option>US30</option><option>NAS100</option>
                    </datalist>
                </div>
                <div class="form-group">
                    <label class="form-label">Direction <span class="req">*</span></label>
                    <select name="type" class="form-control" required id="field-type" x-model="type" @change="calcPreview">
                        <option value="buy" {{ old('type','buy') == 'buy' ? 'selected' : '' }}>BUY (Long)</option>
                        <option value="sell" {{ old('type') == 'sell' ? 'selected' : '' }}>SELL (Short)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Trade Date <span class="req">*</span></label>
                    <input type="datetime-local" name="trade_date" class="form-control" required
                           value="{{ old('trade_date', now()->format('Y-m-d\TH:i')) }}" id="field-trade-date">
                </div>
            </div>

            {{-- Row 2: Prices --}}
            <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Entry Price <span class="req">*</span></label>
                    <input type="number" name="entry_price" step="0.00001" class="form-control form-control-mono"
                           placeholder="2350.00" value="{{ old('entry_price') }}" required id="field-entry"
                           x-model="entry" @input="calcPreview">
                </div>
                <div class="form-group">
                    <label class="form-label">Exit Price</label>
                    <input type="number" name="exit_price" step="0.00001" class="form-control form-control-mono"
                           placeholder="Leave blank if open" value="{{ old('exit_price') }}" id="field-exit"
                           x-model="exit" @input="calcPreview">
                </div>
                <div class="form-group">
                    <label class="form-label">Stop Loss</label>
                    <input type="number" name="stop_loss" step="0.00001" class="form-control form-control-mono"
                           placeholder="2320.00" value="{{ old('stop_loss') }}" id="field-sl"
                           x-model="sl" @input="calcPreview">
                </div>
                <div class="form-group">
                    <label class="form-label">Take Profit</label>
                    <input type="number" name="take_profit" step="0.00001" class="form-control form-control-mono"
                           placeholder="2400.00" value="{{ old('take_profit') }}" id="field-tp"
                           x-model="tp" @input="calcPreview">
                </div>
            </div>

            {{-- Row 3: Lot, Session, Timeframe --}}
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Lot Size <span class="req">*</span></label>
                    <input type="number" name="lot_size" step="0.001" class="form-control form-control-mono"
                           placeholder="0.10" value="{{ old('lot_size') }}" required id="field-lot"
                           x-model="lot" @input="calcPreview">
                </div>
                <div class="form-group">
                    <label class="form-label">Session</label>
                    <select name="session" class="form-control" id="field-session">
                        <option value="">Select session</option>
                        @foreach(['London','New York','Tokyo','Sydney','Other'] as $s)
                            <option value="{{ $s }}" {{ old('session') == $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Timeframe</label>
                    <select name="timeframe" class="form-control" id="field-timeframe">
                        <option value="">Select TF</option>
                        @foreach(['1M','5M','15M','30M','1H','4H','1D','1W'] as $tf)
                            <option value="{{ $tf }}" {{ old('timeframe') == $tf ? 'selected' : '' }}>{{ $tf }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Tags --}}
            @if($tags->isNotEmpty())
            <div class="form-group">
                <label class="form-label">Tags</label>
                <div style="display:flex;flex-wrap:wrap;gap:0.5rem;">
                    @foreach($tags as $tag)
                        <label style="display:flex;align-items:center;gap:6px;cursor:pointer;padding:4px 10px;border-radius:999px;border:1px solid {{ $tag->color }};color:{{ $tag->color }};font-size:0.8125rem;">
                            <input type="checkbox" name="tag_ids[]" value="{{ $tag->id }}"
                                   {{ in_array($tag->id, old('tag_ids', [])) ? 'checked' : '' }}
                                   style="accent-color:{{ $tag->color }};">
                            {{ $tag->name }}
                        </label>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Screenshots --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                <div class="form-group">
                    <label class="form-label">Entry Screenshot</label>
                    <div class="upload-zone" @click="$refs.entryFile.click()"
                         @dragover.prevent="$el.classList.add('drag-over')"
                         @dragleave="$el.classList.remove('drag-over')"
                         @drop.prevent="handleDrop($event, 'entry')"
                         x-ref="entryZone">
                        <div x-show="!preview.entryImg">
                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 0.5rem;color:var(--text-3);"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            <p style="color:var(--text-3);font-size:0.8125rem;">Click or drag to upload entry screenshot</p>
                        </div>
                        <img x-show="preview.entryImg" :src="preview.entryImg" style="max-height:120px;border-radius:6px;margin:0 auto;">
                        <input type="file" name="entry_screenshot" accept="image/*" x-ref="entryFile" style="display:none;"
                               @change="previewImage($event, 'entry')" id="field-entry-img">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Exit Screenshot</label>
                    <div class="upload-zone" @click="$refs.exitFile.click()"
                         @dragover.prevent="$el.classList.add('drag-over')"
                         @dragleave="$el.classList.remove('drag-over')"
                         @drop.prevent="handleDrop($event, 'exit')"
                         x-ref="exitZone">
                        <div x-show="!preview.exitImg">
                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 0.5rem;color:var(--text-3);"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            <p style="color:var(--text-3);font-size:0.8125rem;">Click or drag to upload exit screenshot</p>
                        </div>
                        <img x-show="preview.exitImg" :src="preview.exitImg" style="max-height:120px;border-radius:6px;margin:0 auto;">
                        <input type="file" name="exit_screenshot" accept="image/*" x-ref="exitFile" style="display:none;"
                               @change="previewImage($event, 'exit')" id="field-exit-img">
                    </div>
                </div>
            </div>

            {{-- Notes --}}
            <div class="form-group">
                <label class="form-label">Notes / Journal Entry</label>
                <textarea name="notes" class="form-control" rows="4" placeholder="What was your setup? What did you learn?" id="field-notes">{{ old('notes') }}</textarea>
            </div>

            {{-- Submit --}}
            <div style="display:flex;gap:0.75rem;justify-content:flex-end;padding-top:0.5rem;border-top:1px solid var(--border-dim);">
                <a href="{{ route('trades.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary" id="btn-save-trade">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    Save Trade
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function tradeForm() {
    return {
        asset: '{{ old('asset', 'EURUSD') }}',
        type: '{{ old('type', 'buy') }}',
        entry: {{ old('entry_price', 0) }},
        exit: {{ old('exit_price', 0) }},
        sl: {{ old('stop_loss', 0) }},
        tp: {{ old('take_profit', 0) }},
        lot: {{ old('lot_size', 0.1) }},
        preview: { hasData: false, pnl: 0, risk: 0, rr: 0, entryImg: null, exitImg: null },

        pipSizes: {
            'XAUUSD': 0.01, 'XAGUSD': 0.001,
            'USDJPY': 0.01, 'GBPJPY': 0.01, 'EURJPY': 0.01,
            'US30': 1.0, 'NAS100': 0.25, 'BTCUSD': 1.0, 'ETHUSD': 0.1,
        },
        pipValues: {
            'EURUSD': 10, 'GBPUSD': 10, 'AUDUSD': 10, 'NZDUSD': 10,
            'USDJPY': 9.09, 'USDCHF': 9.09, 'USDCAD': 7.69,
            'GBPJPY': 9.09, 'EURJPY': 9.09,
            'XAUUSD': 1.0, 'US30': 1.0, 'NAS100': 1.0,
            'BTCUSD': 1.0, 'ETHUSD': 1.0,
        },

        calcPreview() {
            const ps = this.pipSizes[this.asset.toUpperCase()] || 0.0001;
            const pv = this.pipValues[this.asset.toUpperCase()] || 10;
            const lot = parseFloat(this.lot) || 0;
            const entry = parseFloat(this.entry) || 0;
            const sl = parseFloat(this.sl) || 0;
            const tp = parseFloat(this.tp) || 0;
            const exit = parseFloat(this.exit) || 0;

            if (!entry || !lot) { this.preview.hasData = false; return; }

            let risk = 0, reward = 0;
            if (sl) {
                risk = Math.abs((entry - sl) / ps) * pv * lot;
            }
            if (tp) {
                reward = Math.abs((tp - entry) / ps) * pv * lot;
            }

            let pnl = 0;
            if (exit) {
                const priceDiff = this.type === 'buy' ? (exit - entry) : (entry - exit);
                const pips = priceDiff / ps;
                pnl = pips * pv * lot;
            } else if (tp) {
                pnl = reward;
            }

            this.preview.pnl = pnl;
            this.preview.risk = risk;
            this.preview.rr = risk > 0 ? reward / risk : 0;
            this.preview.hasData = true;
        },

        previewImage(event, type) {
            const file = event.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = e => {
                if (type === 'entry') this.preview.entryImg = e.target.result;
                else this.preview.exitImg = e.target.result;
            };
            reader.readAsDataURL(file);
        },

        handleDrop(event, type) {
            event.currentTarget.classList.remove('drag-over');
            const file = event.dataTransfer.files[0];
            if (!file) return;
            const dt = new DataTransfer();
            dt.items.add(file);
            if (type === 'entry') {
                this.$refs.entryFile.files = dt.files;
                this.$refs.entryFile.dispatchEvent(new Event('change'));
            } else {
                this.$refs.exitFile.files = dt.files;
                this.$refs.exitFile.dispatchEvent(new Event('change'));
            }
        },

        submit() { this.$el.submit(); }
    };
}
</script>
@endpush
