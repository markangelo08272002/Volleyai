@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="min-height: 100vh; background: #10172a;">
    
    <div class="row mb-4">
        <div class="col">
            <h2 class="text-white fw-bold">{{ __('Manage Players') }}</h2>
            <p class="text-secondary">Select the varsity players you want to manage. Only sessions from these players will appear on your dashboard.</p>
        </div>
    </div>
    ---
    
    <div class="card bg-dark border-0 shadow rounded-4 p-4">
        <h3 class="text-white fw-bold mb-3">Assign Varsity Players</h3>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        <form method="POST" action="{{ route('coach.assign.players') }}">
            @csrf

            <ul class="list-group list-group-flush rounded-3 mb-4">
                @forelse ($varsityPlayers as $player)
                    <li class="list-group-item bg-dark border-secondary">
                        <div class="d-flex align-items-center">
                            <input type="checkbox" name="player_ids[]" value="{{ $player->id }}"
                                {{ in_array($player->id, $managedPlayers) ? 'checked' : '' }}
                                class="form-check-input me-3" id="player_{{ $player->id }}">
                            <label class="form-check-label text-white" for="player_{{ $player->id }}">
                                {{ $player->name }} <span class="text-secondary small">({{ $player->email }})</span>
                            </label>
                        </div>
                    </li>
                @empty
                    <li class="list-group-item bg-dark border-secondary">
                        <p class="text-secondary mb-0">No varsity players found to manage.</p>
                    </li>
                @endforelse
            </ul>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary btn-glow">{{ __('Save Assignments') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection