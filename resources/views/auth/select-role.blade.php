@extends('layouts.auth')

@section('content')
<div class="auth-card">
    <div class="text-center mb-5">
        <a href="/" class="d-inline-block">
            <img src="https://img.icons8.com/ios-filled/50/007BFF/volleyball.png" style="width:42px;" class="mb-2" alt="VolleyAI Logo" />
        </a>
        <h3 class="fw-bold text-white mb-1">Welcome!</h3>
        <div class="text-muted">Select your role to continue to VolleyAI</div>
    </div>

    <form method="POST" action="{{ route('oauth.role.select') }}">
        @csrf
        <input type="hidden" name="user_id" value="{{ $user_id }}">

        <div class="mb-4">
            <label for="role" class="form-label fw-semibold">Select Your Role</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                <select id="role" name="role" required class="form-control @error('role') is-invalid @enderror">
                    <option value="">-- Choose --</option>
                    <option value="varsity">Varsity Player</option>
                    <option value="coach">Coach</option>
                </select>
            </div>
            @error('role')
                <span class="text-danger small mt-1 d-block" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        
        <div class="d-grid">
            <button type="submit" class="btn btn-glow py-2">{{ __('Continue') }}</button>
        </div>
    </form>
</div>
@endsection
