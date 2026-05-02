<x-guest-layout>
    <div style="text-align: center; margin-bottom: 2rem;">
        <h1 style="font-size: 1.5rem; font-weight: 600; color: var(--text-1);">Create an account</h1>
        <p style="font-size: 0.875rem; color: var(--text-2); margin-top: 0.25rem;">Start journaling your trades today</p>
    </div>

    <form method="POST" action="{{ route('register') }}" style="display: flex; flex-direction: column; gap: 1.25rem;">
        @csrf

        <div class="form-group">
            <label for="name" class="form-label">Full Name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" class="form-control" placeholder="John Doe">
            @error('name')<div class="text-negative" style="font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="email" class="form-label">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" class="form-control" placeholder="name@example.com">
            @error('email')<div class="text-negative" style="font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password" class="form-control" placeholder="••••••••">
            @error('password')<div class="text-negative" style="font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="form-control" placeholder="••••••••">
            @error('password_confirmation')<div class="text-negative" style="font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; margin-top: 0.5rem;">Sign Up</button>
    </form>

    <div style="margin: 1.5rem 0; display: flex; align-items: center; gap: 1rem;">
        <div style="flex: 1; height: 1px; background: var(--border-dim);"></div>
        <span style="font-size: 0.75rem; color: var(--text-3); text-transform: uppercase; font-weight: 600;">Or register with</span>
        <div style="flex: 1; height: 1px; background: var(--border-dim);"></div>
    </div>

    <a href="{{ route('google.redirect') }}" class="btn btn-secondary" style="width: 100%; justify-content: center; gap: 0.5rem;">
        <svg viewBox="0 0 24 24" width="18" height="18" xmlns="http://www.w3.org/2000/svg"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/><path d="M1 1h22v22H1z" fill="none"/></svg>
        Sign up with Google
    </a>

    <p style="margin-top: 2rem; text-align: center; font-size: 0.875rem; color: var(--text-2);">
        Already have an account? <a href="{{ route('login') }}" style="color: var(--text-1); font-weight: 600; text-decoration: none;">Log in</a>
    </p>
</x-guest-layout>
