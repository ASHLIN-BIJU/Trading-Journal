<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="TradeJournal — Professional Trading Journal and Analytics Platform">
    <title>{{ isset($title) ? $title.' — ' : '' }}TradeJournal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.46.0/dist/apexcharts.min.js" defer></script>
</head>
<body>

<div class="app-layout" x-data="{ sidebarOpen: false }">

    <aside class="sidebar" :class="{ 'open': sidebarOpen }">
        <div class="sidebar-logo">
            <div class="sidebar-logo-icon">📈</div>
            <span class="sidebar-logo-text">TradeJournal</span>
        </div>

        <nav class="sidebar-nav">
            <span class="sidebar-section-label">Main</span>

            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" id="nav-dashboard">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
                Dashboard
            </a>

            <a href="{{ route('trades.index') }}" class="nav-link {{ request()->routeIs('trades.*') ? 'active' : '' }}" id="nav-trades">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                Trades
            </a>

            <a href="{{ route('journal.index') }}" class="nav-link {{ request()->routeIs('journal.*') ? 'active' : '' }}" id="nav-journal">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                Journal
            </a>

            <span class="sidebar-section-label">Analytics</span>

            <a href="{{ route('analytics.index') }}" class="nav-link {{ request()->routeIs('analytics.*') ? 'active' : '' }}" id="nav-analytics">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                Analysis
            </a>

            <a href="{{ route('tools.index') }}" class="nav-link {{ request()->routeIs('tools.*') ? 'active' : '' }}" id="nav-tools">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93a10 10 0 0 1 0 14.14M4.93 4.93a10 10 0 0 0 0 14.14"/></svg>
                Tools
            </a>

            <span class="sidebar-section-label">Account</span>

            <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" id="nav-profile">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                Profile
            </a>
        </nav>

        <div style="padding:1rem;border-top:1px solid var(--border-dim);">
            <div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:0.75rem;">
                <div style="width:32px;height:32px;border-radius:50%;background:var(--accent-dim);display:flex;align-items:center;justify-content:center;color:var(--accent);font-weight:700;font-size:0.875rem;flex-shrink:0;">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div style="min-width:0;">
                    <div style="font-size:0.8125rem;font-weight:600;color:var(--text-1);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        {{ Auth::user()->name }}
                    </div>
                    <span class="badge badge-{{ Auth::user()->plan }}">{{ ucfirst(Auth::user()->plan) }}</span>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-ghost btn-sm" style="width:100%;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Sign Out
                </button>
            </form>
        </div>
    </aside>

    <div style="min-width:0;display:flex;flex-direction:column;">

        <header class="topnav">
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <h1 class="page-title">{{ $title ?? 'Dashboard' }}</h1>
            </div>
            <div style="display:flex;align-items:center;gap:1.5rem;">
                @if(Auth::user()->accounts->count() > 0)
                <div class="dropdown" x-data="{ open: false }" style="position:relative;">
                    <button @click="open = !open" class="btn btn-ghost btn-sm" style="display:flex;align-items:center;gap:0.5rem;background:var(--bg-surface);border:1px solid var(--border-dim);">
                        <div style="width:8px;height:8px;border-radius:50%;background:{{ Auth::user()->getActiveAccount()->status === 'blown' ? 'var(--negative)' : 'var(--positive)' }};"></div>
                        <span style="font-weight:600;font-size:0.875rem;">{{ Auth::user()->getActiveAccount()->name }}</span>
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    
                    <div x-show="open" @click.away="open = false" style="display:none;position:absolute;top:100%;right:0;margin-top:0.5rem;width:200px;background:var(--bg-surface);border:1px solid var(--border-dim);border-radius:0.5rem;box-shadow:0 10px 15px -3px rgba(0,0,0,0.5);z-index:50;" x-transition>
                        <div style="padding:0.5rem;">
                            <div style="font-size:0.75rem;color:var(--text-3);padding:0.25rem 0.5rem;text-transform:uppercase;font-weight:600;">Switch Account</div>
                            @foreach(Auth::user()->accounts as $acc)
                                <form action="{{ route('accounts.switch', $acc) }}" method="POST" style="margin:0;">
                                    @csrf
                                    <button type="submit" class="dropdown-item" style="width:100%;text-align:left;display:flex;align-items:center;justify-content:space-between;padding:0.5rem;border-radius:0.25rem;background:transparent;border:none;color:var(--text-2);cursor:pointer;font-size:0.875rem;">
                                        <span style="{{ $acc->id === Auth::user()->active_account_id ? 'font-weight:600;color:var(--text-1);' : '' }}">{{ $acc->name }}</span>
                                        @if($acc->id === Auth::user()->active_account_id)
                                            <svg width="14" height="14" fill="none" stroke="var(--accent)" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                        @endif
                                    </button>
                                </form>
                            @endforeach
                            <div style="height:1px;background:var(--border-dim);margin:0.5rem 0;"></div>
                            <a href="{{ route('accounts.index') }}" class="dropdown-item" style="display:flex;align-items:center;gap:0.5rem;padding:0.5rem;border-radius:0.25rem;color:var(--text-1);text-decoration:none;font-size:0.875rem;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                                Manage Accounts
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                <div style="display:flex;align-items:center;gap:0.75rem;">
                    @yield('topnav-actions')
                    <a href="{{ route('trades.create') }}" class="btn btn-primary btn-sm" id="btn-log-trade">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Log Trade
                    </a>
                </div>
            </div>
        </header>

        <div style="padding:1rem 1.5rem 0;">
            @if(session('success'))
                <div class="alert alert-success" x-data x-init="setTimeout(() => $el.style.display='none', 4000)">
                    {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-error">
                    <strong>Please fix the errors below:</strong>
                    <ul style="margin-top:0.25rem;padding-left:1.25rem;">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif
        </div>

        <main class="main-content">
            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
