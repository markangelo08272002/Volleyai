@extends('layouts.auth')

@section('content')
<div class="auth-card" style="max-width: 600px;">
    <div class="text-center mb-5">
        <a href="/" class="d-inline-block">
            <img src="https://img.icons8.com/ios-filled/50/007BFF/volleyball.png" style="width:42px;" class="mb-2" alt="VolleyAI Logo" />
        </a>
        <h3 class="fw-bold text-white mb-1">Create Your Account</h3>
        <div class="text-muted">Join VolleyAI to start your analysis</div>
    </div>

    <!-- Login with Google -->
    <div class="d-grid mb-3">
        <a href="{{ route('google.login') }}" class="btn btn-danger py-2">
            <i class="bi bi-google me-2"></i>Register with Google
        </a>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div class="mb-3">
            <label for="name" class="form-label fw-semibold">Full Name</label>
            <input id="name" type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g., John Doe" value="{{ old('name') }}" required autofocus autocomplete="name">
            @error('name')
                <span class="text-danger small mt-1 d-block" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label fw-semibold">Email Address</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                <input id="email" type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="e.g., john.doe@example.com" value="{{ old('email') }}" required autocomplete="username">
            </div>
            @error('email')
                <span class="text-danger small mt-1 d-block" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>

        <!-- Role -->
        <div class="mb-3">
            <label for="role" class="form-label fw-semibold">Register As</label>
            <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
                <option value="" disabled {{ old('role') ? '' : 'selected' }}>Select your role...</option>
                <option value="varsity" {{ old('role') == 'varsity' ? 'selected' : '' }}>Varsity Player</option>
                <option value="coach" {{ old('role') == 'coach' ? 'selected' : '' }}>Coach</option>
            </select>
            @error('role')
                <span class="text-danger small mt-1 d-block" role="alert"><strong>{{ $message }}</strong></span>
            @enderror
        </div>

        <div class="row">
            <!-- Password -->
            <div class="col-md-6 mb-4">
                <label for="password" class="form-label fw-semibold">Password</label>
                <div class="input-group">
                     <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Create a password" required autocomplete="new-password">
                </div>
                @error('password')
                    <span class="text-danger small mt-1 d-block" role="alert"><strong>{{ $message }}</strong></span>
                @enderror
            </div>
            <!-- Confirm Password -->
            <div class="col-md-6 mb-4">
                <label for="password_confirmation" class="form-label fw-semibold">Confirm Password</label>
                 <div class="input-group">
                     <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" placeholder="Confirm password" required autocomplete="new-password">
                </div>
            </div>
        </div>

        <div class="d-grid mb-4">
            <button type="submit" class="btn btn-glow py-2">{{ __('Register') }}</button>
        </div>

        <div class="text-center">
            <span class="text-muted small">Already have an account?</span>
            <a href="{{ route('login') }}" class="auth-link">Sign In</a>
        </div>
    </form>
</div>
@endsection
