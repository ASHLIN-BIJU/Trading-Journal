@extends('layouts.app')
@php $title = 'Profile'; @endphp

@section('content')
<div style="max-width:900px;margin:0 auto;display:flex;flex-direction:column;gap:1.5rem;">
    <div class="card">
        <div class="card-header">
            <div>
                <h2 style="font-size:1.125rem;font-weight:600;color:var(--text-1);">Profile Information</h2>
                <p style="font-size:0.875rem;color:var(--text-2);margin-top:0.25rem;">Update your account's profile information and email address.</p>
            </div>
        </div>
        <div class="card-body">
            @include('profile.partials.update-profile-information-form')
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <h2 style="font-size:1.125rem;font-weight:600;color:var(--text-1);">Update Password</h2>
                <p style="font-size:0.875rem;color:var(--text-2);margin-top:0.25rem;">Ensure your account is using a long, random password to stay secure.</p>
            </div>
        </div>
        <div class="card-body">
            @include('profile.partials.update-password-form')
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <h2 style="font-size:1.125rem;font-weight:600;color:var(--loss-text);">Delete Account</h2>
                <p style="font-size:0.875rem;color:var(--text-2);margin-top:0.25rem;">Once your account is deleted, all of its resources and data will be permanently deleted.</p>
            </div>
        </div>
        <div class="card-body">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</div>
@endsection
