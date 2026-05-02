<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

<form method="post" action="{{ route('profile.update') }}" style="display:flex;flex-direction:column;gap:1.25rem;">
    @csrf
    @method('patch')

    <div class="form-group">
        <label for="name" class="form-label">Name</label>
        <input id="name" name="name" type="text" class="form-control" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
        @error('name')<div class="text-negative" style="font-size:0.875rem;margin-top:0.25rem;">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <label for="email" class="form-label">Email</label>
        <input id="email" name="email" type="email" class="form-control" value="{{ old('email', $user->email) }}" required autocomplete="username">
        @error('email')<div class="text-negative" style="font-size:0.875rem;margin-top:0.25rem;">{{ $message }}</div>@enderror

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div style="margin-top:0.75rem;">
                <p style="font-size:0.875rem;color:var(--text-2);">
                    Your email address is unverified.
                    <button form="send-verification" style="background:none;border:none;padding:0;color:var(--accent);text-decoration:underline;cursor:pointer;">
                        Click here to re-send the verification email.
                    </button>
                </p>

                @if (session('status') === 'verification-link-sent')
                    <p class="text-positive" style="font-size:0.875rem;margin-top:0.5rem;">
                        A new verification link has been sent to your email address.
                    </p>
                @endif
            </div>
        @endif
    </div>

    <div style="display:flex;align-items:center;gap:1rem;">
        <button type="submit" class="btn btn-primary">Save Changes</button>

        @if (session('status') === 'profile-updated')
            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 3000)" class="text-positive" style="font-size:0.875rem;font-weight:600;display:flex;align-items:center;gap:0.25rem;">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                Saved successfully.
            </p>
        @endif
    </div>
</form>
