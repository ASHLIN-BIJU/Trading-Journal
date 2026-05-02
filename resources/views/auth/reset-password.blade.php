<x-guest-layout>
    <div style="text-align: center; margin-bottom: 2rem;">
        <h1 style="font-size: 1.5rem; font-weight: 600; color: var(--text-1);">Choose a new password</h1>
        <p style="font-size: 0.875rem; color: var(--text-2); margin-top: 0.5rem;">Enter your new password below to reset your account access.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" style="display: flex; flex-direction: column; gap: 1.25rem;">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div class="form-group">
            <label for="email" class="form-label">Email address</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" class="form-control">
            @error('email')<div class="text-negative" style="font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>@enderror
        </div>

        <!-- Password -->
        <div class="form-group">
            <label for="password" class="form-label">New Password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password" class="form-control" placeholder="••••••••">
            @error('password')<div class="text-negative" style="font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>@enderror
        </div>

        <!-- Confirm Password -->
        <div class="form-group">
            <label for="password_confirmation" class="form-label">Confirm New Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="form-control" placeholder="••••••••">
            @error('password_confirmation')<div class="text-negative" style="font-size: 0.875rem; margin-top: 0.25rem;">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; margin-top: 0.5rem;">Reset Password</button>
    </form>
</x-guest-layout>
