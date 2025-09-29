@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="min-height: 100vh; background: #10172a;">
    
    <div class="row p-4 align-items-center">
        <div class="col-md-6">
            <div class="d-flex align-items-center">
                <img src="https://img.icons8.com/ios-filled/48/007BFF/volleyball.png" style="width:38px;" alt="VolleyAI"/>
                <h2 class="ms-3 mb-0 text-white fw-bold">Coach Dashboard</h2>
            </div>
            <p class="text-secondary mt-1 mb-0">Welcome, {{ Auth::user()->name }}! Here’s your team’s performance overview.</p>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('coach.manage.players') }}" class="btn btn-glow px-4 me-2">
                <i class="bi bi-people-fill me-2"></i>Manage Players
            </a>
            <a href="{{ route('coach.drills.index') }}" class="btn btn-glow px-4">
                <i class="bi bi-clipboard-check me-2"></i>Manage Drills
            </a>
        </div>
    </div>

    <div class="container-fluid px-4 pt-3">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card bg-dark border-0 shadow rounded-4">
                    <div class="card-body d-flex flex-column align-items-start">
                        <div class="mb-2">
                            <span class="badge bg-primary"><i class="bi bi-person-check"></i> Managed Players</span>
                        </div>
                        <h3 class="text-white fw-bold mb-1">{{ Auth::user()->managedPlayers->count() ?? 0 }}</h3>
                        <p class="text-secondary mb-0 small">Number of players you manage</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-dark border-0 shadow rounded-4">
                    <div class="card-body d-flex flex-column align-items-start">
                        <div class="mb-2">
                            <span class="badge bg-success"><i class="bi bi-bar-chart-line"></i> Total Sessions</span>
                        </div>
                        <h3 class="text-white fw-bold mb-1">{{ $sessions->count() }}</h3>
                        <p class="text-secondary mb-0 small">Total sessions from your players</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-dark border-0 shadow rounded-4">
                    <div class="card-body d-flex flex-column align-items-start">
                        <div class="mb-2">
                            <span class="badge bg-info text-dark"><i class="bi bi-flag"></i> Flagged Issues</span>
                        </div>
                        <h3 class="text-white fw-bold mb-1">0</h3>
                        <p class="text-secondary mb-0 small">Sessions needing attention</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="card bg-dark border-0 shadow rounded-4">
                    <div class="card-header border-0 bg-transparent pb-0 d-flex justify-content-between align-items-center">
                        <h5 class="text-white fw-bold mb-0"><i class="bi bi-journal-text me-2"></i>Managed Players Sessions</h5>
                        <a href="#" class="btn btn-primary btn-sm">View All</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-dark table-hover mb-0 rounded">
                                <thead>
                                    <tr>
                                        <th>Player</th>
                                        <th>Upload Date</th>
                                        <th>Action Type</th>
                                        <th>Status</th>
                                        <th>Progress</th>
                                        <th>AI Feedback</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($sessions as $session)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                {{ $session->user->name }}
                                                <a href="{{ route('coach.players.sessions', $session->user->id) }}" class="badge bg-primary ms-2">View All</a>
                                            </td>
                                            <td>{{ $session->created_at->format('M d, Y H:i') }}</td>
                                            <td>{{ $session->action_type }}</td>
                                            <td id="session-status-{{ $session->id }}">
                                                @if($session->status === 'completed')
                                                    <span class="badge bg-success">Completed</span>
                                                @elseif($session->status === 'processing')
                                                    <span class="badge bg-info text-dark">Processing</span>
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
                                                <a href="{{ route('volleyball.session.show', $session) }}" class="btn btn-sm btn-outline-light">Details</a>
                                            </td>
                                        </tr>
                                        <div class="modal fade" id="feedbackModal-{{ $session->id }}" tabindex="-1" aria-labelledby="feedbackModalLabel-{{ $session->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content bg-dark text-white">
                                                    <div class="modal-header border-secondary">
                                                        <h5 class="modal-title" id="feedbackModalLabel-{{ $session->id }}">AI Coaching Feedback</h5>
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
                                                            <p class="text-secondary mb-0">Score: <span id="feedbackScore-{{ $session->id }}">N/A</span></p>
                                                        </div>
                                                        <p class="text-secondary mb-0" id="feedbackTone-{{ $session->id }}">N/A</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No volleyball sessions available from your managed players. <a href="{{ route('coach.manage.players') }}" class="text-primary">Manage Players</a> to assign some.</td>
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
</div>

@if(!session('rules_accepted'))
<div class="modal fade" id="rulesModal" tabindex="-1" aria-labelledby="rulesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header">
                <h5 class="modal-title" id="rulesModalLabel">Rules and Regulations of Volleyball</h5>
            </div>
            <div class="modal-body">
                <div id="rules-content">
                    <h6>1. The Court and Equipment</h6>
                    <ul>
                        <li>Court size: 18m long × 9m wide, divided by a net.</li>
                        <li>Net height:
                            <ul>
                                <li>Men – 2.43m</li>
                                <li>Women – 2.24m</li>
                            </ul>
                        </li>
                        <li>Ball: Round, leather or synthetic, circumference 65–67 cm, weight 260–280 g.</li>
                    </ul>

                    <h6>2. The Teams</h6>
                    <ul>
                        <li>Players: 6 players on court (3 front row, 3 back row).</li>
                        <li>Minimum: A team must have at least 6 players to start a set.</li>
                        <li>Substitutions: Up to 6 per set.</li>
                    </ul>

                    <h6>3. Scoring System</h6>
                    <ul>
                        <li>Rally Point System: Every rally scores a point.</li>
                        <li>Winning a Set: First to 25 points, must lead by 2 points.</li>
                        <li>Winning a Match: Best of 5 sets. If tied 2–2, the 5th set is played to 15 points (must lead by 2).</li>
                    </ul>

                    <h6>4. Starting and Playing the Game</h6>
                    <ul>
                        <li>Coin Toss: Decides serve or side.</li>
                        <li>Rotation: After gaining serve, players rotate clockwise.</li>
                        <li>Serving:
                            <ul>
                                <li>Must be done from behind the end line.</li>
                                <li>Ball must be hit within 8 seconds after referee’s whistle.</li>
                                <li>Only one toss attempt allowed.</li>
                            </ul>
                        </li>
                    </ul>

                    <h6>5. Playing the Ball</h6>
                    <ul>
                        <li>A team may hit the ball up to 3 times before sending it over the net.</li>
                        <li>A player cannot hit the ball twice in succession (except after a block).</li>
                        <li>Allowed hits: Bump (forearm pass), set, spike, block, dig.</li>
                        <li>Illegal hits: Lift, carry, double contact.</li>
                    </ul>

                    <h6>6. Ball In and Out</h6>
                    <ul>
                        <li>Ball in: When it lands on or inside boundary lines.</li>
                        <li>Ball out: When it touches floor outside the lines, antennas, net posts, or ceiling.</li>
                    </ul>

                    <h6>7. Net Rules</h6>
                    <ul>
                        <li>Players may not touch the net while playing the ball.</li>
                        <li>The ball may touch the net during play and on serve (let serve is allowed).</li>
                        <li>Crossing the center line is a fault if it interferes with opponent’s play.</li>
                    </ul>

                    <h6>8. Attacking and Blocking</h6>
                    <ul>
                        <li>Front-row players: Can attack and block anywhere.</li>
                        <li>Back-row players: Must jump from behind the attack line (3m line) when spiking.</li>
                        <li>Block: Does not count as one of the three team hits.</li>
                    </ul>

                    <h6>9. Player Conduct</h6>
                    <ul>
                        <li>Respect referees and opponents.</li>
                        <li>No unsportsmanlike conduct (shouting at opponents, delaying game, etc.).</li>
                        <li>Yellow card = warning; Red card = penalty point; Red + Yellow = expulsion.</li>
                    </ul>

                    <h6>10. Officials</h6>
                    <ul>
                        <li>First Referee: Stands on referee stand, controls the game.</li>
                        <li>Second Referee: Assists, monitors net faults, substitutions.</li>
                        <li>Line Judges: Signal if ball lands in/out.</li>
                        <li>Scorer: Records points, rotations, substitutions.</li>
                    </ul>
                </div>
                <div id="voice-explanation" class="mt-4 text-center">
                    <p>You can listen to the full rules and regulations.</p>
                    <button id="play-voice" class="btn btn-primary mt-2">Play Full Rules</button>
                    <button id="stop-voice" class="btn btn-danger mt-2">Stop Voice</button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="close-rules">Close</button>
            </div>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script>
    // Feedback Modal Logic (similar to varsity dashboard)
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
                    
                    // Score is not directly available in this context, set to N/A or remove if not needed
                    feedbackScoreElement.textContent = 'N/A';
                    
                    if (toneMatch) {
                        feedbackToneElement.textContent = toneMatch[1].trim();
                    }
                } else {
                    feedbackContentElement.innerHTML = '<p class="text-secondary">No feedback available.</p>';
                }
            });
        });

        @if(!session('rules_accepted'))
        var rulesModal = new bootstrap.Modal(document.getElementById('rulesModal'), {
            keyboard: false,
            backdrop: 'static'
        });
        rulesModal.show();

        document.getElementById('close-rules').addEventListener('click', function() {
            fetch('/api/rules/accept', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if(data.status === 'success') {
                    rulesModal.hide();
                }
            });
        });

        document.getElementById('play-voice').addEventListener('click', () => {
            const rulesText = document.getElementById('rules-content').innerText;
            const utterance = new SpeechSynthesisUtterance(rulesText);
            speechSynthesis.speak(utterance);
        });

        document.getElementById('stop-voice').addEventListener('click', () => {
            speechSynthesis.cancel();
        });
        @endif
    });
</script>
@endpush
@endsection
