@extends('layouts.app', ['title' => 'Edit Account'])

@section('content')
<div class="card" style="max-width:600px;margin:0 auto;padding:2rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;">
        <h2 style="font-size:1.25rem;font-weight:600;color:var(--text-1);">Edit Trading Account</h2>
        @if(Auth::user()->accounts()->count() > 1)
        <form action="{{ route('accounts.destroy', $account) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this account and all its trades? This cannot be undone.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--negative);">Delete Account</button>
        </form>
        @endif
    </div>

    <form method="POST" action="{{ route('accounts.update', $account) }}" style="display:flex;flex-direction:column;gap:1.5rem;">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label for="name" class="form-label">Account Name <span class="text-negative">*</span></label>
            <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $account->name) }}" required>
            @error('name')<div style="color:var(--negative);font-size:0.875rem;margin-top:0.25rem;">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="initial_capital" class="form-label">Initial Capital ($) <span class="text-negative">*</span></label>
            <input type="number" id="initial_capital" name="initial_capital" class="form-control" value="{{ old('initial_capital', $account->initial_capital) }}" min="0" step="0.01" required>
            @error('initial_capital')<div style="color:var(--negative);font-size:0.875rem;margin-top:0.25rem;">{{ $message }}</div>@enderror
        </div>

        <div class="form-group">
            <label for="status" class="form-label">Status <span class="text-negative">*</span></label>
            <select id="status" name="status" class="form-control" required>
                <option value="active" {{ old('status', $account->status) === 'active' ? 'selected' : '' }}>Active</option>
                <option value="passed" {{ old('status', $account->status) === 'passed' ? 'selected' : '' }}>Passed</option>
                <option value="blown" {{ old('status', $account->status) === 'blown' ? 'selected' : '' }}>Blown</option>
            </select>
            @error('status')<div style="color:var(--negative);font-size:0.875rem;margin-top:0.25rem;">{{ $message }}</div>@enderror
        </div>

        <div style="display:flex;gap:1rem;margin-top:1rem;">
            <a href="{{ route('accounts.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center;">Save Changes</button>
        </div>
    </form>
</div>
@endsection
