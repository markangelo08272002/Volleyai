@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="min-height: 100vh; background: #10172a;">
    <div class="row mb-4">
        <div class="col">
            <h2 class="text-white fw-bold">Edit Drill</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card bg-dark border-0 shadow rounded-4 p-4">
                <form method="POST" action="{{ route('coach.drills.update', $drill) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="title" class="form-label text-white">Drill Title</label>
                        <input type="text" class="form-control bg-secondary text-white border-0" id="title" name="title" value="{{ old('title', $drill->title) }}" required>
                        @error('title')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label text-white">Description</label>
                        <textarea class="form-control bg-secondary text-white border-0" id="description" name="description" rows="4">{{ old('description', $drill->description) }}</textarea>
                        @error('description')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="action_type" class="form-label text-white">Action Type</label>
                        <select class="form-select bg-secondary text-white border-0" id="action_type" name="action_type" required>
                            <option value="">-- Select Action Type --</option>
                            <option value="spike" {{ old('action_type', $drill->action_type) == 'spike' ? 'selected' : '' }}>Spike</option>
                            <option value="serve" {{ old('action_type', $drill->action_type) == 'serve' ? 'selected' : '' }}>Serve</option>
                            <option value="block" {{ old('action_type', $drill->action_type) == 'block' ? 'selected' : '' }}>Block</option>
                            <option value="pass" {{ old('action_type', $drill->action_type) == 'pass' ? 'selected' : '' }}>Pass</option>
                            <option value="set" {{ old('action_type', $drill->action_type) == 'set' ? 'selected' : '' }}>Set</option>
                        </select>
                        @error('action_type')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-white">Drill Criteria</label>
                        <div id="criteria-container">
                            <!-- Criteria items will be added here by JavaScript -->
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-criterion">Add Criterion</button>
                        <textarea class="form-control bg-secondary text-white border-0 d-none" id="criteria" name="criteria" rows="6">{{ old('criteria', json_encode($drill->criteria ?? [])) }}</textarea>
                        @error('criteria')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-glow px-4">Update Drill</button>
                    <a href="{{ route('coach.drills.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const addCriterionBtn = document.getElementById('add-criterion');
        const criteriaContainer = document.getElementById('criteria-container');
        const criteriaHiddenInput = document.getElementById('criteria');

        function addCriterion(criterion = {}) {
            const div = document.createElement('div');
            div.classList.add('criterion-item', 'p-3', 'mb-3', 'border', 'border-secondary', 'rounded', 'position-relative');
            div.innerHTML = `
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 mt-2 me-2 remove-criterion" aria-label="Close"></button>
                <div class="mb-3">
                    <label class="form-label text-secondary">Criterion Name</label>
                    <input type="text" class="form-control bg-secondary text-white border-0 criterion-name" value="${criterion.name || ''}" placeholder="e.g., Approach Footwork">
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary">Description</label>
                    <textarea class="form-control bg-secondary text-white border-0 criterion-description" rows="2" placeholder="e.g., Evaluate smoothness and power of approach steps.">${criterion.description || ''}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary">Expected Value (Optional, for numerical criteria)</label>
                    <input type="text" class="form-control bg-secondary text-white border-0 criterion-expected-value" value="${criterion.expected_value || ''}" placeholder="e.g., 0.52m (for jump height), 162 (for arm angle)">
                </div>
            `;
            criteriaContainer.appendChild(div);
        }

        function removeCriterion(event) {
            event.target.closest('.criterion-item').remove();
        }

        addCriterionBtn.addEventListener('click', () => addCriterion());

        criteriaContainer.addEventListener('click', function(event) {
            if (event.target.classList.contains('remove-criterion')) {
                removeCriterion(event);
            }
        });

        form.addEventListener('submit', function(event) {
            const criteria = [];
            document.querySelectorAll('.criterion-item').forEach(item => {
                const name = item.querySelector('.criterion-name').value.trim();
                const description = item.querySelector('.criterion-description').value.trim();
                const expected_value = item.querySelector('.criterion-expected-value').value.trim();

                if (name) { // Only add if criterion name is provided
                    const criterion = { name, description };
                    if (expected_value) {
                        criterion.expected_value = expected_value;
                    }
                    criteria.push(criterion);
                }
            });
            criteriaHiddenInput.value = JSON.stringify(criteria);
        });

        // Populate form with existing criteria
        if (criteriaHiddenInput.value) {
            try {
                const existingCriteria = JSON.parse(criteriaHiddenInput.value);
                if (Array.isArray(existingCriteria)) {
                    existingCriteria.forEach(crit => addCriterion(crit));
                } else {
                    console.warn("Existing criteria is not an array:", existingCriteria);
                }
            } catch (e) {
                console.error("Error parsing existing criteria JSON:", e);
            }
        }

        // Add one empty criterion by default if none exist
        if (criteriaContainer.children.length === 0) {
            addCriterion();
        }
    });
</script>
@endpush
@endsection
