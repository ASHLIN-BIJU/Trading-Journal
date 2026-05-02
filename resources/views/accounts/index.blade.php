@extends('layouts.app', ['title' => 'Trading Accounts'])

@section('topnav-actions')
<a href="{{ route('accounts.create') }}" class="btn btn-secondary btn-sm">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    New Account
</a>
@endsection

@section('content')
<div class="card" style="padding:1.5rem;">
    <h2 style="font-size:1.125rem;font-weight:600;color:var(--text-1);margin-bottom:1.5rem;">Your Trading Accounts</h2>

    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(300px, 1fr));gap:1.5rem;">
        @foreach($accounts as $account)
            <div style="border:1px solid var(--border-dim);border-radius:0.75rem;padding:1.5rem;background:var(--bg-base);position:relative;{{ $account->id === Auth::user()->active_account_id ? 'border-color:var(--accent);box-shadow:0 0 0 1px var(--accent);' : '' }}">
                
                @if($account->id === Auth::user()->active_account_id)
                <div style="position:absolute;top:1rem;right:1rem;font-size:0.75rem;font-weight:600;color:var(--accent);background:rgba(59,130,246,0.1);padding:0.25rem 0.5rem;border-radius:1rem;">Active</div>
                @endif

                <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1rem;">
                    <div style="width:10px;height:10px;border-radius:50%;background:{{ $account->status === 'blown' ? 'var(--negative)' : ($account->status === 'passed' ? 'var(--positive)' : 'var(--warning)') }}"></div>
                    <h3 style="font-weight:600;font-size:1rem;color:var(--text-1);">{{ $account->name }}</h3>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem;">
                    <div>
                        <div style="font-size:0.75rem;color:var(--text-3);text-transform:uppercase;font-weight:600;margin-bottom:0.25rem;">Initial Capital</div>
                        <div style="font-size:1rem;font-weight:600;color:var(--text-1);">${{ number_format($account->initial_capital, 2) }}</div>
                    </div>
                    <div>
                        <div style="font-size:0.75rem;color:var(--text-3);text-transform:uppercase;font-weight:600;margin-bottom:0.25rem;">Current Balance</div>
                        <div style="font-size:1rem;font-weight:600;color:{{ $account->balance >= $account->initial_capital ? 'var(--positive)' : 'var(--negative)' }};">${{ number_format($account->balance, 2) }}</div>
                    </div>
                </div>

                <div style="display:flex;align-items:center;gap:0.5rem;border-top:1px solid var(--border-dim);padding-top:1rem;">
                    @if($account->id !== Auth::user()->active_account_id)
                        <form action="{{ route('accounts.switch', $account) }}" method="POST" style="flex:1;">
                            @csrf
                            <button type="submit" class="btn btn-secondary btn-sm" style="width:100%;justify-content:center;">Switch to</button>
                        </form>
                    @endif
                    <a href="{{ route('accounts.edit', $account) }}" class="btn btn-ghost btn-sm" style="{{ $account->id === Auth::user()->active_account_id ? 'flex:1;justify-content:center;' : '' }}">Edit</a>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
