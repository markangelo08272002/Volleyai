@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="min-height: 100vh; background: #10172a;">
    <div class="row mb-4">
        <div class="col">
            <h2 class="text-white fw-bold">Drill Management</h2>
        </div>
        <div class="col-auto">
            <a href="{{ route('coach.drills.create') }}" class="btn btn-glow px-4">
                <i class="bi bi-plus-circle me-2"></i>Create New Drill
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col">
            <div class="card bg-dark border-0 shadow rounded-4">
                <div class="card-header border-0 bg-transparent pb-0">
                    <h5 class="text-white mb-0 fw-bold">Your Drills</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0 rounded">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Action Type</th>
                                    <th>Description</th>
                                    <th>Criteria</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($drills as $drill)
                                    <tr>
                                        <td>{{ $drill->title }}</td>
                                        <td>{{ ucfirst($drill->action_type) }}</td>
                                        <td>{{ Str::limit($drill->description, 50) }}</td>
                                        <td>
                                            @if($drill->criteria)
                                                @foreach($drill->criteria as $criterion)
                                                    <span class="badge bg-info text-dark mb-1">{{ $criterion['name'] ?? 'N/A' }}</span>
                                                @endforeach
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('coach.drills.edit', $drill) }}" class="btn btn-sm btn-outline-light me-2">Edit</a>
                                            <form action="{{ route('coach.drills.destroy', $drill) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this drill?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No drills created yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
