@extends('layouts.auth')

@section('content')
<div class="auth-card">
    <div class="text-center mb-5">
        <a href="/" class="d-inline-block">
            <img src="https://img.icons8.com/ios-filled/50/007BFF/volleyball.png" style="width:42px;" class="mb-2" alt="VolleyAI Logo" />
        </a>
        <h3 class="fw-bold text-white mb-1">Welcome Back!</h3>
        <div class="text-muted"><span style="color:white";>Sign in to continue to VolleyAI</span></div>
    </div>

    <!-- Session Status -->
    @if (session('status'))
        <div class="alert alert-success mb-4" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label fw-semibold">Email</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="e.g., your.email@example.com" value="{{ old('email') }}" required autofocus autocomplete="username">
            </div>
            @error('email')
                <span class="text-danger small mt-1 d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label for="password" class="form-label fw-semibold">Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Enter your password" required autocomplete="current-password">
            </div>
            @error('password')
                <span class="text-danger small mt-1 d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
                <label class="form-check-label small" for="remember_me">
                    {{ __('Remember me') }}
                </label>
            </div>
            @if (Route::has('password.request'))
                <a class="auth-link small" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif
        </div>

        <!-- Submit Button -->
        <div class="d-grid mb-3">
            <button type="submit" class="btn btn-glow py-2">{{ __('Log in') }}</button>
        </div>

        <!-- Login with Google Button -->
        <div class="d-grid mb-3">
            <a href="{{ route('google.login') }}" class="btn btn-danger py-2">
                <i class="bi bi-google me-2"></i>Login with Google
            </a>
        </div>

        <div class="text-center mt-2">
            <span class="text-muted small"><span style="color:white;">Don't have an account?</span></span>
            <a href="{{ route('register') }}" class="auth-link">Create one</a>
        </div>
    </form>
</div>
@endsection
