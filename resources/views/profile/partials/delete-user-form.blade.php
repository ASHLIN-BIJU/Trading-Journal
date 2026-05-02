<div x-data="{ confirmingUserDeletion: false }">
    <button type="button" class="btn btn-danger" @click="confirmingUserDeletion = true">Delete Account</button>

    <div x-show="confirmingUserDeletion" style="display:none;margin-top:1.5rem;padding-top:1.5rem;border-top:1px solid var(--border-dim);" x-transition>
        <form method="post" action="{{ route('profile.destroy') }}" style="display:flex;flex-direction:column;gap:1.25rem;">
            @csrf
            @method('delete')

            <div>
                <h3 style="font-size:1rem;font-weight:600;color:var(--text-1);">Are you sure you want to delete your account?</h3>
                <p style="font-size:0.875rem;color:var(--text-2);margin-top:0.25rem;">
                    Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.
                </p>
            </div>

            <div class="form-group">
                <label for="password" class="form-label sr-only">Password</label>
                <input id="password" name="password" type="password" class="form-control" placeholder="Password" required>
                @error('password', 'userDeletion')<div class="text-negative" style="font-size:0.875rem;margin-top:0.25rem;">{{ $message }}</div>@enderror
            </div>

            <div style="display:flex;align-items:center;justify-content:flex-end;gap:1rem;">
                <button type="button" class="btn btn-secondary" @click="confirmingUserDeletion = false">Cancel</button>
                <button type="submit" class="btn btn-danger">Confirm Delete Account</button>
            </div>
        </form>
    </div>
</div>
