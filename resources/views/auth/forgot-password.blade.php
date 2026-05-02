<x-guest-layout>
    <div style="text-align: center; margin-bottom: 2rem;">
        <h1 style="font-size: 1.5rem; font-weight: 600; color: var(--text-1);">Reset password</h1>
        <p style="font-size: 0.875rem; color: var(--text-2); margin-top: 0.5rem; line-height: 1.5;">Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" style="display: flex; flex-direction: column; gap: 1.25rem;">
        @csrf

        <!-- Email Address -->
        <div class="form-group">
            <label for="email" class="form-label">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="form-control" placeholder="name@example.com">
            @error('email')<div class="text-negative" style="font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">Email Password Reset Link</button>
    </form>

    <div style="margin-top: 2rem; text-align: center;">
        <a href="{{ route('login') }}" style="font-size: 0.875rem; color: var(--text-2); text-decoration: none;">&larr; Back to login</a>
    </div>
</x-guest-layout>
