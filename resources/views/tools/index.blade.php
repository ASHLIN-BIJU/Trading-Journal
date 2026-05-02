@extends('layouts.app')
@php $title = 'Trading Tools'; @endphp

@section('content')
<div x-data="tradingTools({{ $balance }})" style="display:flex;flex-direction:column;gap:2rem;">

    <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:1.5rem;align-items:start;">
        
        {{-- ── Position Size Calculator ────────────────────────────────────────── --}}
        <div class="card">
            <div class="card-header">
                <div style="display:flex;justify-content:space-between;align-items:center;width:100%;">
                    <div>
                        <div class="card-title">Position Size Calculator</div>
                        <div class="card-subtitle">Calculate optimal lot size</div>
                    </div>
                    <div style="display:flex;background:var(--bg-base);padding:2px;border-radius:6px;border:1px solid var(--border-dim);">
                        <button @click="ps.mode='pips'; calcPositionSize()" :class="ps.mode==='pips' ? 'btn-primary' : 'btn-ghost'" class="btn btn-sm" style="padding:2px 8px;font-size:10px;">PIPS</button>
                        <button @click="ps.mode='price'; calcPositionSize()" :class="ps.mode==='price' ? 'btn-primary' : 'btn-ghost'" class="btn btn-sm" style="padding:2px 8px;font-size:10px;">PRICE</button>
                    </div>
                </div>
            </div>

            <div style="display:flex;flex-direction:column;gap:1rem;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Account Size ($)</label>
                        <input type="number" class="form-control form-control-mono" x-model="ps.account"
                               @input="calcPositionSize()" id="ps-account">
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Risk %</label>
                        <input type="number" class="form-control form-control-mono" x-model="ps.riskPct"
                               @input="calcPositionSize()" step="0.1" id="ps-risk-pct">
                    </div>

                    <template x-if="ps.mode === 'pips'">
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Stop Loss (pips)</label>
                            <input type="number" class="form-control form-control-mono" x-model="ps.slPips"
                                   @input="calcPositionSize()" placeholder="20" id="ps-sl-pips">
                        </div>
                    </template>
                    <template x-if="ps.mode === 'price'">
                        <div style="grid-column: span 2; display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                            <div class="form-group" style="margin:0;">
                                <label class="form-label">Entry Price</label>
                                <input type="number" class="form-control form-control-mono" x-model="ps.entry"
                                       @input="calcPositionSize()" step="0.00001" placeholder="2350.00">
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label class="form-label">Stop Loss Price</label>
                                <input type="number" class="form-control form-control-mono" x-model="ps.slPrice"
                                       @input="calcPositionSize()" step="0.00001" placeholder="2340.00">
                            </div>
                        </div>
                    </template>

                    <div class="form-group" style="margin:0; grid-column: span 2;">
                        <label class="form-label">Asset</label>
                        <select class="form-control" x-model="ps.asset" @change="calcPositionSize()" id="ps-asset">
                            <option>XAUUSD</option>
                            <option>EURUSD</option><option>GBPUSD</option><option>AUDUSD</option>
                            <option>USDJPY</option><option>BTCUSD</option>
                            <option>US30</option><option>NAS100</option>
                        </select>
                    </div>
                </div>

                <div class="result-box" x-show="ps.result">
                    <div class="result-item">
                        <span class="result-label">Risk Amount</span>
                        <span class="result-value glow-accent" x-text="'$' + ps.result.risk_amount?.toFixed(2)"></span>
                    </div>
                    <div class="result-item">
                        <span class="result-label">Recommended Lot Size</span>
                        <span class="result-value" style="font-size:1.5rem;" x-text="ps.result.lot_size?.toFixed(2) + ' lots'"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── R:R Calculator ──────────────────────────────────────────────────── --}}
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Risk:Reward Calculator</div>
                    <div class="card-subtitle">Calculate R:R ratio from entry, SL and TP</div>
                </div>
            </div>

            <div style="display:flex;flex-direction:column;gap:1rem;">
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:1rem;">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Direction</label>
                        <select class="form-control" x-model="rr.type" @change="calcRR()" id="rr-type">
                            <option value="buy">BUY</option>
                            <option value="sell">SELL</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Entry Price</label>
                        <input type="number" class="form-control form-control-mono" x-model="rr.entry"
                               @input="calcRR()" placeholder="2350.00" step="0.00001" id="rr-entry">
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Stop Loss</label>
                        <input type="number" class="form-control form-control-mono" x-model="rr.sl"
                               @input="calcRR()" placeholder="2330.00" step="0.00001" id="rr-sl">
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Take Profit</label>
                        <input type="number" class="form-control form-control-mono" x-model="rr.tp"
                               @input="calcRR()" placeholder="2410.00" step="0.00001" id="rr-tp">
                    </div>
                </div>

                <div class="result-box" x-show="rr.result">
                    <div class="result-item">
                        <span class="result-label">R:R Ratio</span>
                        <span class="result-value" style="font-size:1.5rem;color:var(--accent);" x-text="'1 : ' + rr.result.rr?.toFixed(2)"></span>
                    </div>
                    <div style="margin-top:0.75rem;padding:0.75rem;border-radius:8px;font-size:0.8125rem;"
                         :style="rr.result.rr >= 2 ? 'background:var(--accent-dim);color:var(--accent)' : (rr.result.rr >= 1 ? 'background:var(--warn-dim);color:var(--warn)' : 'background:var(--danger-dim);color:var(--danger)')">
                        <span x-text="rr.result.rr >= 2 ? '✅ Excellent R:R' : (rr.result.rr >= 1 ? '⚠️ Acceptable R:R' : '❌ Poor R:R')"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Profit Calculator ───────────────────────────────────────────────── --}}
        <div class="card">
            <div class="card-header">
                <div style="display:flex;justify-content:space-between;align-items:center;width:100%;">
                    <div>
                        <div class="card-title">Profit Calculator</div>
                        <div class="card-subtitle">Calculate profit or loss</div>
                    </div>
                    <div style="display:flex;background:var(--bg-base);padding:2px;border-radius:6px;border:1px solid var(--border-dim);">
                        <button @click="pc.mode='pips'; calcProfit()" :class="pc.mode==='pips' ? 'btn-primary' : 'btn-ghost'" class="btn btn-sm" style="padding:2px 8px;font-size:10px;">PIPS</button>
                        <button @click="pc.mode='price'; calcProfit()" :class="pc.mode==='price' ? 'btn-primary' : 'btn-ghost'" class="btn btn-sm" style="padding:2px 8px;font-size:10px;">PRICE</button>
                    </div>
                </div>
            </div>

            <div style="display:flex;flex-direction:column;gap:1rem;">
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:1rem;">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Lot Size</label>
                        <input type="number" class="form-control form-control-mono" x-model="pc.lot"
                               @input="calcProfit()" placeholder="0.10" step="0.001" id="pc-lot">
                    </div>

                    <template x-if="pc.mode === 'pips'">
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Pips (+ for profit)</label>
                            <input type="number" class="form-control form-control-mono" x-model="pc.pips"
                                   @input="calcProfit()" placeholder="20" id="pc-pips">
                        </div>
                    </template>
                    <template x-if="pc.mode === 'price'">
                        <div style="grid-column: span 2; display:grid; grid-template-columns: 1fr 1fr; gap:1rem;">
                            <div class="form-group" style="margin:0;">
                                <label class="form-label">Entry Price</label>
                                <input type="number" class="form-control form-control-mono" x-model="pc.entry"
                                       @input="calcProfit()" step="0.00001" placeholder="2350.00">
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label class="form-label">Exit Price</label>
                                <input type="number" class="form-control form-control-mono" x-model="pc.exit"
                                       @input="calcProfit()" step="0.00001" placeholder="2370.00">
                            </div>
                        </div>
                    </template>

                    <div class="form-group" style="margin:0; grid-column: span 2;">
                        <label class="form-label">Asset</label>
                        <select class="form-control" x-model="pc.asset" @change="calcProfit()" id="pc-asset">
                            <option>XAUUSD</option>
                            <option>EURUSD</option><option>GBPUSD</option><option>AUDUSD</option>
                            <option>USDJPY</option><option>BTCUSD</option>
                            <option>US30</option><option>NAS100</option>
                        </select>
                    </div>
                </div>

                <div class="result-box" x-show="pc.result !== null">
                    <div class="result-item">
                        <span class="result-label">Profit / Loss</span>
                        <span class="result-value" :class="pc.result >= 0 ? 'glow-accent' : 'glow-danger'"
                              :style="pc.result >= 0 ? 'color:var(--accent)' : 'color:var(--danger)'"
                              x-text="(pc.result >= 0 ? '+' : '') + '$' + pc.result?.toFixed(2)"></span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ── Drawdown Calculator (Bottom Row) ────────────────────────────────── --}}
    <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:1.5rem;">
        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title">Drawdown Recovery Calculator</div>
                    <div class="card-subtitle">See recovery gain needed after a loss</div>
                </div>
            </div>

            <div style="display:flex;flex-direction:column;gap:1rem;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Starting Balance ($)</label>
                        <input type="number" class="form-control form-control-mono" x-model="dd.balance"
                               @input="calcDrawdown()" placeholder="10000" id="dd-balance">
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Drawdown (%)</label>
                        <input type="number" class="form-control form-control-mono" x-model="dd.pct"
                               @input="calcDrawdown()" placeholder="20" step="0.1" max="99" id="dd-pct">
                    </div>
                </div>

                <div class="result-box" x-show="dd.result">
                    <div class="result-item">
                        <span class="result-label">Loss Amount</span>
                        <span class="result-value glow-danger" x-text="'-$' + dd.result?.loss?.toFixed(2)"></span>
                    </div>
                    <div class="result-item">
                        <span class="result-label">Recovery Needed</span>
                        <span class="result-value" style="color:var(--warn);" x-text="dd.result?.recovery_needed?.toFixed(2) + '% gain needed'"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function tradingTools(balance) {
    return {
        tab: 'position',

        ps: { mode: 'pips', account: balance, riskPct: 1, slPips: 20, asset: 'XAUUSD', entry: '', slPrice: '', result: null },
        rr: { type: 'buy', entry: '', sl: '', tp: '', result: null },
        pc: { mode: 'pips', lot: 0.1, pips: 20, asset: 'XAUUSD', entry: '', exit: '', result: null },
        dd: { balance: balance, pct: 20, result: null },

        pipValues: {
            'EURUSD': 10, 'GBPUSD': 10, 'AUDUSD': 10, 'NZDUSD': 10,
            'USDCAD': 10, 'USDCHF': 10, 'USDJPY': 10, 'XAUUSD': 10,
            'BTCUSD': 1, 'ETHUSD': 1, 'US30': 1, 'NAS100': 1, 'GER40': 1
        },

        calcPositionSize() {
            let pips = parseFloat(this.ps.slPips) || 0;
            
            if (this.ps.mode === 'price' && this.ps.entry && this.ps.slPrice) {
                let diff = Math.abs(parseFloat(this.ps.entry) - parseFloat(this.ps.slPrice));
                if (this.ps.asset === 'XAUUSD' || this.ps.asset === 'USDJPY') {
                    pips = diff * 10;
                } else if (this.ps.asset === 'BTCUSD' || this.ps.asset.includes('30') || this.ps.asset.includes('100')) {
                    pips = diff;
                } else {
                    pips = diff * 10000;
                }
            }

            if (!this.ps.account || !this.ps.riskPct || pips <= 0) {
                this.ps.result = null;
                return;
            }

            const riskAmount = this.ps.account * (this.ps.riskPct / 100);
            const pipValue = this.pipValues[this.ps.asset] || 10;
            const lotSize = riskAmount / (pips * pipValue);

            this.ps.result = {
                risk_amount: riskAmount,
                lot_size: lotSize
            };
        },

        calcRR() {
            if (!this.rr.entry || !this.rr.sl || !this.rr.tp) {
                this.rr.result = null;
                return;
            }

            const entry = parseFloat(this.rr.entry);
            const sl = parseFloat(this.rr.sl);
            const tp = parseFloat(this.rr.tp);

            let risk, reward;
            if (this.rr.type === 'buy') {
                risk = entry - sl;
                reward = tp - entry;
            } else {
                risk = sl - entry;
                reward = entry - tp;
            }

            if (risk <= 0 || reward <= 0) {
                this.rr.result = null;
                return;
            }

            this.rr.result = {
                risk: risk,
                reward: reward,
                rr: reward / risk
            };
        },

        calcProfit() {
            let pips = parseFloat(this.pc.pips);

            if (this.pc.mode === 'price' && this.pc.entry && this.pc.exit) {
                let diff = parseFloat(this.pc.exit) - parseFloat(this.pc.entry);
                if (this.pc.asset === 'XAUUSD' || this.pc.asset === 'USDJPY') {
                    pips = diff * 10;
                } else if (this.pc.asset === 'BTCUSD' || this.pc.asset.includes('30') || this.pc.asset.includes('100')) {
                    pips = diff;
                } else {
                    pips = diff * 10000;
                }
            }

            if (!this.pc.lot || isNaN(pips)) {
                this.pc.result = null;
                return;
            }

            const pipValue = this.pipValues[this.pc.asset] || 10;
            this.pc.result = this.pc.lot * pips * pipValue;
        },

        calcDrawdown() {
            const bal  = parseFloat(this.dd.balance) || 0;
            const pct  = parseFloat(this.dd.pct) || 0;
            const loss = bal * (pct / 100);
            const rem  = bal - loss;
            const rec  = rem > 0 ? parseFloat((loss / rem * 100).toFixed(2)) : null;
            this.dd.result = { loss: parseFloat(loss.toFixed(2)), remaining: parseFloat(rem.toFixed(2)), recovery_needed: rec };
        },

        init() {
            this.calcPositionSize();
            this.calcDrawdown();
        }
    };
}
</script>
@endpush
