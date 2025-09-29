@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="background: #10172a; min-height: 100vh;">
    <!-- Header -->
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('coach.dashboard') }}" class="btn btn-sm btn-outline-light me-3"><i class="bi bi-arrow-left"></i></a>
        <h2 class="text-white fw-bold mb-0">{{ $player->name }}'s Sessions</h2>
    </div>

    <!-- Session History Table -->
    <div class="row">
        <div class="col">
            <div class="card bg-dark border-0 shadow rounded-4">
                <div class="card-header border-0 bg-transparent pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="text-white fw-bold mb-0"><i class="bi bi-journal-text me-2"></i>All Sessions</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover mb-0 rounded">
                            <thead>
                                <tr>
                                    <th>Upload Date</th>
                                    <th>Action Type</th>
                                    <th>Status</th>
                                    <th>Progress</th>
                                    <th>AI Feedback</th>
                                    <th>Manual Feedback</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sessions as $session)
                                    <tr>
                                        <td>{{ $session->created_at->format('M d, Y H:i') }}</td>
                                        <td>{{ $session->action_type }}</td>
                                        <td id="session-status-{{ $session->id }}">
                                            @if($session->status === 'completed')
                                                <span class="badge bg-success">Completed</span>
                                            @elseif($session->status === 'processing')
                                                <span class="badge bg-info text-dark">Processing</span>
                                            @elseif($session->status === 'failed')
                                                <span class="badge bg-danger">Failed</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($session->status) }}</span>
                                            @endif
                                        </td>
                                        <td id="session-progress-{{ $session->id }}">
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $session->progress }}%;" aria-valuenow="{{ $session->progress }}" aria-valuemin="0" aria-valuemax="100">{{ $session->progress }}%</div>
                                            </div>
                                            @if($session->status === 'processing')
                                                <small class="text-secondary mt-1 d-block">Processing...</small>
                                            @endif
                                        </td>
                                        <td data-full-feedback="{{ addslashes($session->ai_feedback) }}" data-grade="{{ $session->grade ?? 'N/A' }}" data-bs-toggle="modal" data-bs-target="#feedbackModal-{{ $session->id }}" style="cursor: pointer;">
                                            @if($session->ai_feedback)
                                                {{ Str::limit($session->ai_feedback, 70) }}
                                            @else
                                                <span class="text-secondary">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($session->manual_feedback)
                                                {{ Str::limit($session->manual_feedback, 70) }}
                                            @else
                                                <span class="text-secondary">No manual feedback yet.</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('volleyball.session.show', $session) }}" class="btn btn-sm btn-outline-light">Details</a>
                                        </td>
                                    </tr>

                                    <!-- Feedback Modal (AI) -->
                                    <div class="modal fade" id="feedbackModal-{{ $session->id }}" tabindex="-1" aria-labelledby="feedbackModalLabel-{{ $session->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content bg-dark text-white">
                                                <div class="modal-header border-secondary">
                                                    <h5 class="modal-title" id="feedbackModalLabel-{{ $session->id }}">AI Coaching Feedback</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div id="feedbackContent-{{ $session->id }}">
                                                        <!-- Feedback will be loaded here by JS -->
                                                        <p class="text-secondary">Loading feedback...</p>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-secondary d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="text-white mb-0">Grade: <span id="feedbackGrade-{{ $session->id }}" class="badge bg-primary">N/A</span></h6>
                                                        <p class="text-secondary mb-0">Score: <span id="feedbackScore-{{ $session->id }}">N/A</span></p>
                                                    </div>
                                                    <p class="text-secondary mb-0" id="feedbackTone-{{ $session->id }}">N/A</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No sessions available for this player.</td>
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var feedbackModals = document.querySelectorAll('.modal[id^="feedbackModal-"]');
        feedbackModals.forEach(function (modal) {
            modal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget; // Button that triggered the modal
                var sessionId = modal.id.replace('feedbackModal-', '');
                var feedbackContentElement = document.getElementById('feedbackContent-' + sessionId);
                var feedbackGradeElement = document.getElementById('feedbackGrade-' + sessionId);
                var feedbackScoreElement = document.getElementById('feedbackScore-' + sessionId);
                var feedbackToneElement = document.getElementById('feedbackTone-' + sessionId);

                var rawFeedback = button.closest('tr').querySelector('td[data-full-feedback]').dataset.fullFeedback;
                var grade = button.dataset.grade;

                feedbackContentElement.innerHTML = '<p class="text-secondary">Loading feedback...</p>';
                feedbackGradeElement.textContent = 'N/A';
                feedbackScoreElement.textContent = 'N/A';
                feedbackToneElement.textContent = 'N/A';

                if (rawFeedback) {
                    const strengthsMatch = rawFeedback.match(/Strengths:\n([\s\S]*?)(?=Areas for Improvement:|Actionable Recommendations:|Grade:|Percentage score:|Supportive and motivating tone:|$)/);
                    const areasMatch = rawFeedback.match(/Areas for Improvement:\n([\s\S]*?)(?=Actionable Recommendations:|Grade:|Percentage score:|Supportive and motivating tone:|$)/);
                    const recommendationsMatch = rawFeedback.match(/Actionable Recommendations:\n([\s\S]*?)(?=Grade:|Percentage score:|Supportive and motivating tone:|$)/);
                    const toneMatch = rawFeedback.match(/Supportive and motivating tone:\s*([\s\S]*)/);

                    let formattedHtml = '';

                    if (strengthsMatch && strengthsMatch[1].trim()) {
                        formattedHtml += '<h6 class="text-white mt-3">Strengths:</h6><p>' + strengthsMatch[1].trim().replace(/\n/g, '<br>') + '</p>';
                    }
                    if (areasMatch && areasMatch[1].trim()) {
                        formattedHtml += '<h6 class="text-white mt-3">Areas for Improvement:</h6><p>' + areasMatch[1].trim().replace(/\n/g, '<br>') + '</p>';
                    }
                    if (recommendationsMatch && recommendationsMatch[1].trim()) {
                        formattedHtml += '<h6 class="text-white mt-3">Actionable Recommendations/Drills:</h6><p>' + recommendationsMatch[1].trim().replace(/\n/g, '<br>') + '</p>';
                    }

                    feedbackContentElement.innerHTML = formattedHtml || '<p class="text-secondary">No detailed feedback available.</p>';

                    feedbackGradeElement.textContent = grade;
                    feedbackScoreElement.textContent = 'N/A'; 

                    if (toneMatch) {
                        feedbackToneElement.textContent = toneMatch[1].trim();
                    }
                } else {
                    feedbackContentElement.innerHTML = '<p class="text-secondary">No feedback available.</p>';
                }
            });
        });
    });
</script>
@endpush
