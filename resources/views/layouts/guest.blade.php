<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'TradeJournal') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body style="background-color: var(--bg-base); color: var(--text-1); min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; font-family: 'Inter', sans-serif;">
        <div style="width: 100%; max-w-md: 400px; padding: 2rem; display: flex; flex-direction: column; gap: 2rem;">
            <div style="text-align: center;">
                <a href="/" style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; text-decoration: none;">
                    <div style="font-size: 2rem;">📈</div>
                    <span style="font-size: 1.5rem; font-weight: 700; color: var(--text-1); letter-spacing: -0.025em;">TradeJournal</span>
                </a>
            </div>

            <div class="card" style="padding: 2rem;">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
