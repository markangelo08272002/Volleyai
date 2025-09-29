@extends('layouts.app')

@section('content')

<div class="container-fluid py-4" style="min-height: 100vh; background: #10172a;">
    <div class="row mb-4 align-items-center">
        <div class="col">
            <div class="d-flex align-items-center">
                <img src="https://img.icons8.com/ios-filled/48/007BFF/volleyball.png" style="width:38px;" alt="VolleyAI"/>
                <h2 class="ms-3 mb-0 text-white fw-bold">Varsity Dashboard</h2>
            </div>
            <p class="text-secondary mt-1 mb-0">Welcome, {{ Auth::user()->name }}! Here’s your performance overview.</p>
        </div>
        <div class="col-auto d-flex gap-2">
            <a href="{{ route('volleyball.upload.form') }}" class="btn btn-glow px-4">
                <i class="bi bi-upload me-2"></i>Upload Video
            </a>
            <a href="{{ route('volleyball.drill.start.form') }}" class="btn btn-glow px-4">
                <i class="bi bi-rocket-takeoff me-2"></i>Start New Drill
            </a>
        </div>
    </div>

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
                    <span class="badge bg-primary mb-2"><i class="bi bi-activity"></i> Activity Score</span>
                    <h3 class="text-white fw-bold mb-1">{{ $performanceData[0]['score'] ?? 'N/A' }}</h3>
                    <p class="text-secondary mb-0 small">Your latest performance rating</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-dark border-0 shadow rounded-4">
                <div class="card-body d-flex flex-column align-items-start">
                    <span class="badge bg-success mb-2"><i class="bi bi-graph-up-arrow"></i> Improvement</span>
                    <h3 class="text-white fw-bold mb-1">
                        @php
                            $improvement = 'N/A';
                            if (count($performanceData) >= 2 && $performanceData[1]['score'] > 0) {
                                $latestScore = $performanceData[0]['score'];
                                $previousScore = $performanceData[1]['score'];
                                $percentageChange = round((($latestScore - $previousScore) / $previousScore) * 100);
                                $improvement = ($percentageChange > 0 ? '+' : '') . $percentageChange . '%';
                            }
                        @endphp
                        {{ $improvement }}
                    </h3>
                    <p class="text-secondary mb-0 small">Vs. previous session</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-dark border-0 shadow rounded-4">
                <div class="card-body d-flex flex-column align-items-start">
                    <span class="badge bg-info text-dark mb-2"><i class="bi bi-clock-history"></i> Total Sessions</span>
                    <h3 class="text-white fw-bold mb-1">{{ count($sessions) }}</h3>
                    <p class="text-secondary mb-0 small">Completed drills</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col">
            <div class="card bg-dark border-0 shadow rounded-4">
                <div class="card-header border-0 bg-transparent pb-0">
                    <h5 class="text-white mb-0 fw-bold"><i class="bi bi-bar-chart-line-fill me-2"></i>Performance Trends</h5>
                </div>
                <div class="card-body">
                    <canvas id="performanceChart" style="height:150px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <div class="card bg-dark border-0 shadow rounded-4">
                <div class="card-header border-0 bg-transparent pb-0 d-flex justify-content-between align-items-center">
                    <h5 class="text-white fw-bold mb-0"><i class="bi bi-journal-text me-2"></i>Session History</h5>
                    <a href="#" class="btn btn-primary btn-sm">View All</a>
                </div>
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
                                        <td colspan="9" class="text-center py-4">No volleyball sessions uploaded yet.</td>
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

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Performance Chart Logic
        const ctx = document.getElementById('performanceChart').getContext('2d');
        const performanceData = @json($performanceData);
        const labels = performanceData.map(data => data.date);
        const scores = performanceData.map(data => data.score);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Performance Score',
                    data: scores,
                    fill: true,
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    borderColor: '#007BFF',
                    tension: 0.4,
                    pointBackgroundColor: '#007BFF',
                    pointRadius: 5
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: '#aaa' }, grid: { display: false } },
                    y: { ticks: { color: '#aaa' }, grid: { color: '#232f44' }, min: 0, max: 100 }
                }
            }
        });

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
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            keypointsContentElement.textContent = JSON.stringify(data, null, 2);
                        })
                        .catch(error => {
                            console.error('Error fetching keypoints:', error);
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