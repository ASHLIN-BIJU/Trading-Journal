@extends('layouts.app', ['title' => 'New Trading Account'])

@section('content')
<div class="card" style="max-width:600px;margin:0 auto;padding:2rem;">
    <h2 style="font-size:1.25rem;font-weight:600;color:var(--text-1);margin-bottom:1.5rem;">Create a New Trading Account</h2>

    <form method="POST" action="{{ route('accounts.store') }}" style="display:flex;flex-direction:column;gap:1.5rem;">
        @csrf
        <div class="form-group">
            <label for="name" class="form-label">Account Name <span class="text-negative">*</span></label>
            <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. 100k Challenge, Personal Live, Demo" required>
            @error('name')<div style="color:var(--negative);font-size:0.875rem;margin-top:0.25rem;">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="initial_capital" class="form-label">Initial Capital ($) <span class="text-negative">*</span></label>
            <input type="number" id="initial_capital" name="initial_capital" class="form-control" value="{{ old('initial_capital') }}" min="0" step="0.01" placeholder="10000.00" required>
            <div style="font-size:0.75rem;color:var(--text-3);margin-top:0.25rem;">The starting balance of this account. Drawdown and PnL% will be calculated based on this value.</div>
            @error('initial_capital')<div style="color:var(--negative);font-size:0.875rem;margin-top:0.25rem;">{{ $message }}</div>@enderror
        </div>

        <div style="display:flex;gap:1rem;margin-top:1rem;">
            <a href="{{ route('accounts.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center;">Create Account</button>
        </div>
    </form>
</div>
@endsection
