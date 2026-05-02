<form method="post" action="{{ route('password.update') }}" style="display:flex;flex-direction:column;gap:1.25rem;">
    @csrf
    @method('put')

    <div class="form-group">
        <label for="update_password_current_password" class="form-label">Current Password</label>
        <input id="update_password_current_password" name="current_password" type="password" class="form-control" autocomplete="current-password">
        @error('current_password', 'updatePassword')<div class="text-negative" style="font-size:0.875rem;margin-top:0.25rem;">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <label for="update_password_password" class="form-label">New Password</label>
        <input id="update_password_password" name="password" type="password" class="form-control" autocomplete="new-password">
        @error('password', 'updatePassword')<div class="text-negative" style="font-size:0.875rem;margin-top:0.25rem;">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <label for="update_password_password_confirmation" class="form-label">Confirm Password</label>
        <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password">
        @error('password_confirmation', 'updatePassword')<div class="text-negative" style="font-size:0.875rem;margin-top:0.25rem;">{{ $message }}</div>@enderror
    </div>

    <div style="display:flex;align-items:center;gap:1rem;">
        <button type="submit" class="btn btn-primary">Save Password</button>

        @if (session('status') === 'password-updated')
            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" style="font-size:0.875rem;color:var(--text-2);">
                Saved successfully.
            </p>
        @endif
    </div>
</form>
