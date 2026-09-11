@extends('layouts.auth')

@section('title', 'Admin Login')

@section('content')
<div class="auth-card">
    <div class="auth-card-header">
        <a href="{{ url('/') }}" class="auth-logo">
            <i class="fa-solid fa-shapes"></i> Onedash
        </a>
        <p class="auth-subtitle">Sign in to your admin dashboard</p>
    </div>

    <div class="auth-card-body">
        @if (session('status'))
            <div style="background: #ecfdf5; color: #065f46; padding: 10px; border-radius: 6px; font-size: 0.85rem; margin-bottom: 16px;">
                {{ session('status') }}
            </div>
        @endif

        @if (session('info'))
            <div style="background: #eff6ff; color: #1e40af; padding: 10px; border-radius: 6px; font-size: 0.85rem; margin-bottom: 16px;">
                {{ session('info') }}
            </div>
        @endif

        @if ($errors->any())
            <div style="background: #fef2f2; color: #991b1b; padding: 10px; border-radius: 6px; font-size: 0.85rem; margin-bottom: 16px;">
                <ul style="padding-left: 18px; margin: 0;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}" id="loginForm">
            @csrf

            <div class="form-group">
                <label class="form-label" for="email">Email Address or Account ID</label>
                <div class="input-group">
                    <i class="fa-regular fa-envelope input-icon"></i>
                    <input 
                        type="text" 
                        id="email" 
                        name="email" 
                        class="form-control" 
                        value="{{ old('email') }}" 
                        required 
                        autofocus
                        placeholder="Enter email or account ID"
                        autocomplete="username"
                    >
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="input-group">
                    <i class="fa-solid fa-lock input-icon"></i>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        class="form-control" 
                        value="" 
                        required
                        placeholder="Enter password"
                        autocomplete="current-password"
                    >
                </div>
            </div>

            <div class="auth-meta">
                <label class="checkbox-label">
                    <input type="checkbox" name="remember" id="remember">
                    <span>Remember me</span>
                </label>
                <a href="javascript:void(0)" style="color: var(--primary); text-decoration: none; font-size: 0.82rem;">Forgot password?</a>
            </div>

            <button type="submit" class="btn-primary-auth">
                <i class="fa-solid fa-right-to-bracket" style="margin-right: 6px;"></i> Sign In
            </button>
        </form>
    </div>
</div>
@endsection
