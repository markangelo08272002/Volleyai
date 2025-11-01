@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="min-height: 100vh; background: #10172a;">
    <div class="row mb-4 align-items-center">
        <div class="col">
            <div class="d-flex align-items-center">
                <img src="https://img.icons8.com/ios-filled/48/007BFF/volleyball.png" style="width:38px;" alt="VolleyAI"/>
                <h2 class="ms-3 mb-0 text-white fw-bold">Session History</h2>
            </div>
            <p class="text-secondary mt-1 mb-0">Here is a complete list of all your past sessions.</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('varsity.dashboard') }}" class="btn btn-secondary px-4">
                <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <div class="card bg-dark border-0 shadow rounded-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0 rounded">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Video</th>
                                    <th>Status</th>
                                    <th>Progress</th>
                                    <th>Action</th>
                                    <th>Keypoints</th>
                                    <th>Grade</th>
                                    <th>AI Feedback</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sessions as $session)
                                    <tr>
                                        <td>{{ $session->created_at->format('M d, Y H:i') }}</td>
                                        <td>
                                            <video width="160" height="90" controls>
                                                <source src="{{ Storage::url($session->video_path) }}" type="video/mp4">
                                                Your browser does not support the video tag.
                                            </video>
                                        </td>
                                        <td>
                                            @php
                                                $statusColor = [
                                                    'completed' => 'bg-success',
                                                    'processing' => 'bg-info text-dark',
                                                    'failed' => 'bg-danger',
                                                ][$session->status] ?? 'bg-secondary';
                                            @endphp
                                            <span class="badge {{ $statusColor }}">{{ ucfirst($session->status) }}</span>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $session->progress }}%;" aria-valuenow="{{ $session->progress }}">{{ $session->progress }}%</div>
                                            </div>
                                            @if($session->status === 'processing')
                                                <small class="text-secondary mt-1 d-block">Processing your video...</small>
                                            @endif
                                        </td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $session->metrics['recognized_action'] ?? 'N/A')) }}</td>
                                        <td>
                                            @if($session->keypoints_json_path)
                                                <a href="#" class="text-primary" data-bs-toggle="modal" data-bs-target="#keypointsModal-{{ $session->id }}" data-keypoints-path="{{ Storage::url($session->keypoints_json_path) }}">View Keypoints</a>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $session->grade ?? 'N/A' }}</span>
                                        </td>
                                        <td data-full-feedback="{{ addslashes($session->ai_feedback) }}" data-grade="{{ $session->grade ?? 'N/A' }}" data-bs-toggle="modal" data-bs-target="#feedbackModal-{{ $session->id }}" style="cursor: pointer;">
                                            @if($session->ai_feedback)
                                                {{ Str::limit($session->ai_feedback, 70) }}
                                            @else
                                                <span class="text-secondary">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('volleyball.session.show', $session) }}" class="btn btn-sm btn-outline-light">Details</a>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="feedbackModal-{{ $session->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content bg-dark text-white">
                                                <div class="modal-header border-secondary">
                                                    <h5 class="modal-title">AI Coaching Feedback</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div id="feedbackContent-{{ $session->id }}">
                                                        <p class="text-secondary">Loading feedback...</p>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-secondary d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="text-white mb-0">Grade: <span id="feedbackGrade-{{ $session->id }}" class="badge bg-primary">N/A</span></h6>
                                                    </div>
                                                    <p class="text-secondary mb-0" id="feedbackTone-{{ $session->id }}">N/A</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal fade" id="keypointsModal-{{ $session->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-xl">
                                            <div class="modal-content bg-dark text-white">
                                                <div class="modal-header border-secondary">
                                                    <h5 class="modal-title">Keypoints Data</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <pre id="keypointsContent-{{ $session->id }}" class="text-success-emphasis bg-dark-subtle p-3 rounded">Loading keypoints...</pre>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4">No volleyball sessions found.</td>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Keypoints Modal Logic
    document.querySelectorAll('.modal[id^="keypointsModal-"]').forEach(modal => {
        modal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const keypointsPath = button.getAttribute('data-keypoints-path');
            const sessionId = this.id.replace('keypointsModal-', '');
            const keypointsContentElement = document.getElementById('keypointsContent-' + sessionId);

            if (keypointsPath) {
                keypointsContentElement.textContent = 'Loading keypoints...';
                fetch(keypointsPath)
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        keypointsContentElement.textContent = JSON.stringify(data, null, 2);
                    })
                    .catch(error => {
                        keypointsContentElement.textContent = `Failed to load keypoints: ${error.message}`;
                    });
            } else {
                keypointsContentElement.textContent = 'No keypoints path available.';
            }
        });
    });

    // Feedback Modal Logic
    document.querySelectorAll('.modal[id^="feedbackModal-"]').forEach(modal => {
        modal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const sessionId = this.id.replace('feedbackModal-', '');
            const feedbackContentElement = document.getElementById('feedbackContent-' + sessionId);
            const feedbackGradeElement = document.getElementById('feedbackGrade-' + sessionId);
            const feedbackToneElement = document.getElementById('feedbackTone-' + sessionId);

            const rawFeedback = button.closest('tr').querySelector('td[data-full-feedback]').dataset.fullFeedback;
            const grade = button.dataset.grade;
            
            feedbackContentElement.innerHTML = '<p class="text-secondary">No detailed feedback available.</p>';
            feedbackGradeElement.textContent = grade;
            feedbackToneElement.textContent = 'N/A';

            if (rawFeedback) {
                const strengthsMatch = rawFeedback.match(/Strengths:\n([\s\S]*?)(?=Areas for Improvement:|Actionable Recommendations:|Grade:|Percentage score:|Supportive and motivating tone:|$)/);
                const areasMatch = rawFeedback.match(/Areas for Improvement:\n([\s\S]*?)(?=Actionable Recommendations:|Grade:|Percentage score:|Supportive and motivating tone:|$)/);
                const recommendationsMatch = rawFeedback.match(/Actionable Recommendations:\n([\s\S]*?)(?=Grade:|Percentage score:|Supportive and motivating tone:|$)/);
                const toneMatch = rawFeedback.match(/Supportive and motivating tone:\s*([\s\S]*)/);

                let formattedHtml = '';
                if (strengthsMatch) formattedHtml += `<h6 class="text-white mt-3">Strengths:</h6><p>${strengthsMatch[1].trim().replace(/\n/g, '<br>')}</p>`;
                if (areasMatch) formattedHtml += `<h6 class="text-white mt-3">Areas for Improvement:</h6><p>${areasMatch[1].trim().replace(/\n/g, '<br>')}</p>`;
                if (recommendationsMatch) formattedHtml += `<h6 class="text-white mt-3">Actionable Recommendations/Drills:</h6><p>${recommendationsMatch[1].trim().replace(/\n/g, '<br>')}</p>`;
                
                if (formattedHtml) {
                    feedbackContentElement.innerHTML = formattedHtml;
                }

                if (toneMatch) {
                    feedbackToneElement.textContent = toneMatch[1].trim();
                }
            }
        });
    });
});
</script>
@endpush
