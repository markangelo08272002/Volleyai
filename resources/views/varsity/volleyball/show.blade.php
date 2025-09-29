@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" style="background: #10172a; min-height: 100vh;">
    <!-- Header -->
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('varsity.dashboard') }}" class="btn btn-sm btn-outline-light me-3"><i class="bi bi-arrow-left"></i></a>
        <h2 class="text-white fw-bold mb-0">Session Analysis</h2>
    </div>

    <div class="row">
        <!-- Main Content: Video and Feedback -->
        <div class="col-xl-8 mb-4">
            <!-- Video Player Card -->
            <div class="card bg-dark border-0 shadow-lg rounded-4 mb-4">
                <div class="card-body p-0" style="position: relative;">
                    <div id="videoContainer" style="position: relative; background-color: #000; min-height: 480px;">
                        <video id="videoPlayer" src="{{ Storage::url($session->video_path) }}" controls controlsList="nodownload" class="w-100 rounded-top-4" style="display: none;"></video>
                        <canvas id="poseCanvas" style="position: absolute; top: 0; left: 0; pointer-events: none;"></canvas>
                        <div id="loadingIndicator" class="text-white position-absolute top-50 start-50 translate-middle">
                            <div class="spinner-border" role="status"></div>
                            <span class="ms-2">Loading Analysis...</span>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-dark-alt border-0 p-3 d-flex justify-content-between align-items-center">
                    <p class="text-secondary mb-0 small">Uploaded: {{ $session->created_at->format('M d, Y H:i') }}</p>
                    <span id="videoStatus" class="badge bg-secondary">Waiting</span>
                </div>
            </div>

            <!-- AI Feedback Card -->
            <div class="card bg-dark border-0 shadow-lg rounded-4">
                <div class="card-header bg-dark-alt border-0 d-flex justify-content-between align-items-center">
                    <h5 class="text-white fw-bold mb-0"><i class="bi bi-robot me-2"></i>AI Coach Feedback</h5>
                    <div class="d-flex align-items-center">
                        <select id="voice-select" class="form-select form-select-sm bg-dark text-white me-2" style="width: auto;"></select>
                        <button id="speak-button" class="btn btn-sm btn-outline-light me-2"><i class="bi bi-volume-up-fill"></i></button>
                        <button id="stop-button" class="btn btn-sm btn-outline-danger"><i class="bi bi-stop-circle-fill"></i></button>
                    </div>
                </div>
                <div class="card-body">
                    <p id="ai-feedback-text" class="text-white-50" style="white-space: pre-wrap;">{{ $session->ai_feedback }}</p>
                </div>
            </div>
        </div>

        <!-- Side Content: Analysis & Keypoints -->
        <div class="col-xl-4">
            <!-- Uploaded Video Card -->
            <div class="card bg-dark border-0 shadow-lg rounded-4 mb-4">
                <div class="card-header bg-dark-alt border-0">
                    <h5 class="text-white fw-bold mb-0"><i class="bi bi-camera-video-fill me-2"></i>Uploaded Video</h5>
                </div>
                <div class="card-body p-0">
                    @if($session->video_path)
                        <video controls controlsList="nodownload" class="w-100 rounded-bottom-4">
                            @php
                                $extension = pathinfo($session->video_path, PATHINFO_EXTENSION);
                                $mimeType = 'video/mp4';
                                if ($extension === 'mkv') {
                                    $mimeType = 'video/webm'; // Common MIME type for MKV in browsers
                                }
                                $videoUrl = Storage::url($session->video_path);
                            @endphp
                            <source src="{{ $videoUrl }}" type="{{ $mimeType }}">
                            Your browser does not support the video tag.
                        </video>
                        <script>
                            console.log('Video URL for session {{ $session->id }}: {{ $videoUrl }}');
                        </script>
                    @else
                        <p class="text-white-50 p-3">No uploaded video available.</p>
                    @endif
                </div>
            </div>

            <!-- Analysis Metrics Card -->
            <div class="card bg-dark border-0 shadow-lg rounded-4 mb-4">
                <div class="card-header bg-dark-alt border-0">
                    <h5 class="text-white fw-bold mb-0"><i class="bi bi-clipboard-data-fill me-2"></i>Analysis Metrics</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item bg-transparent text-white-50 border-secondary d-flex justify-content-between">
                            <strong>Action Detected:</strong>
                            <span class="text-white">{{ $session->metrics['recognized_action'] ?? 'N/A' }}</span>
                        </li>
                        <li class="list-group-item bg-transparent text-white-50 border-secondary d-flex justify-content-between">
                            <strong>Jump Height:</strong>
                            <span class="text-white">{{ $session->metrics['jump_height'] ?? 'N/A' }}</span>
                        </li>
                        <li class="list-group-item bg-transparent text-white-50 border-secondary d-flex justify-content-between">
                            <strong>Max Arm Angle:</strong>
                            <span class="text-white">{{ isset($session->metrics['max_right_elbow_angle']) ? round($session->metrics['max_right_elbow_angle'], 2) . '°' : 'N/A' }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            

            
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const video = document.getElementById('videoPlayer');
    const canvas = document.getElementById('poseCanvas');
    const ctx = canvas.getContext('2d');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const videoStatus = document.getElementById('videoStatus');

    let keypointsData = [];

    const POSE_CONNECTIONS = [[11, 12], [11, 23], [12, 24], [23, 24], [11, 13], [13, 15], [12, 14], [14, 16], [23, 25], [25, 27], [24, 26], [26, 28]];

    function setStatus(text, type) {
        videoStatus.textContent = text;
        videoStatus.className = `badge bg-${type}`;
    }

    async function initialize() {
        setStatus('Loading Video...', 'secondary');
        try {
            await video.play();
            video.pause();
            video.style.display = 'block';
            loadingIndicator.style.display = 'none';
            setStatus('Loading Keypoints...', 'info');
            await loadKeypoints();
        } catch (err) {
            console.error("Video playback failed:", err);
            setStatus('Error loading video', 'danger');
            loadingIndicator.innerHTML = "Could not load video. Please check the file and try again.";
        }
    }

    async function loadKeypoints() {
        try {
            const response = await fetch("{{ Storage::url($session->keypoints_json_path) }}");
            if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
            keypointsData = await response.json();
            if (!Array.isArray(keypointsData) || keypointsData.length === 0) throw new Error('Keypoints data is empty or invalid.');
            
            setStatus('Ready', 'success');
            setCanvasDimensions();
            video.requestVideoFrameCallback(drawFrame);
        } catch (error) {
            console.error('Error loading keypoints:', error);
            setStatus('Keypoint Error', 'danger');
        }
    }

    function setCanvasDimensions() {
        const rect = video.getBoundingClientRect();
        canvas.width = rect.width;
        canvas.height = rect.height;
    }

    function drawFrame(now, metadata) {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        const frameIndex = Math.floor(metadata.mediaTime * (keypointsData.length / video.duration));

        if (keypointsData[frameIndex]) {
            const frameKeypoints = keypointsData[frameIndex];
            const landmarks = {};
            for(const kp of frameKeypoints) landmarks[kp.id] = kp;

            drawConnections(landmarks);
            drawLandmarks(landmarks);
        }
        video.requestVideoFrameCallback(drawFrame);
    }

    function drawLandmarks(landmarks) {
        ctx.fillStyle = '#32CD32'; // LimeGreen
        for (const id in landmarks) {
            const kp = landmarks[id];
            if (kp.visibility > 0.5) {
                ctx.beginPath();
                ctx.arc(kp.x * canvas.width, kp.y * canvas.height, 5, 0, 2 * Math.PI);
                ctx.fill();
            }
        }
    }

    function drawConnections(landmarks) {
        ctx.strokeStyle = '#FF4500'; // OrangeRed
        ctx.lineWidth = 3;
        for (const conn of POSE_CONNECTIONS) {
            const start = landmarks[conn[0]];
            const end = landmarks[conn[1]];
            if (start && end && start.visibility > 0.5 && end.visibility > 0.5) {
                ctx.beginPath();
                ctx.moveTo(start.x * canvas.width, start.y * canvas.height);
                ctx.lineTo(end.x * canvas.width, end.y * canvas.height);
                ctx.stroke();
            }
        }
    }

    video.onloadeddata = initialize;
    window.addEventListener('resize', setCanvasDimensions);

    // Poll for progress
    const progressInterval = setInterval(() => {
        fetch("{{ route('volleyball.session.progress', $session) }}")
            .then(response => response.json())
            .then(data => {
                if (data.status === 'completed' || data.status === 'failed') {
                    clearInterval(progressInterval);
                }

                
            });
    }, 5000); // Poll every 5 seconds

    // Text-to-speech for AI Feedback
    const speakButton = document.getElementById('speak-button');
    const stopButton = document.getElementById('stop-button');
    const voiceSelect = document.getElementById('voice-select');
    const feedbackText = document.getElementById('ai-feedback-text')?.textContent;

    let voices = [];

    function populateVoiceList() {
        voices = window.speechSynthesis.getVoices();
        voiceSelect.innerHTML = '';

        // Find and add a default, male, and female voice
        const defaultVoice = voices.find(voice => voice.default);
        const maleVoice = voices.find(voice => voice.name.toLowerCase().includes('male')) || voices.find(voice => voice.name.toLowerCase().includes('david')) || voices.find(voice => voice.name.toLowerCase().includes('microsoft mark'));
        const femaleVoice = voices.find(voice => voice.name.toLowerCase().includes('female')) || voices.find(voice => voice.name.toLowerCase().includes('zira')) || voices.find(voice => voice.name.toLowerCase().includes('microsoft hazel'));

        if (defaultVoice) {
            const option = document.createElement('option');
            option.textContent = 'Default';
            option.setAttribute('data-name', defaultVoice.name);
            voiceSelect.appendChild(option);
        }
        if (maleVoice) {
            const option = document.createElement('option');
            option.textContent = 'Male';
            option.setAttribute('data-name', maleVoice.name);
            voiceSelect.appendChild(option);
        }
        if (femaleVoice) {
            const option = document.createElement('option');
            option.textContent = 'Female';
            option.setAttribute('data-name', femaleVoice.name);
            voiceSelect.appendChild(option);
        }
    }


    if (feedbackText && 'speechSynthesis' in window) {
        populateVoiceList();
        if (speechSynthesis.onvoiceschanged !== undefined) {
            speechSynthesis.onvoiceschanged = populateVoiceList;
        }

        speakButton.addEventListener('click', () => {
            const utterance = new SpeechSynthesisUtterance(feedbackText);
            const selectedVoiceName = voiceSelect.selectedOptions[0].getAttribute('data-name');
            const selectedVoice = voices.find(voice => voice.name === selectedVoiceName);
            if (selectedVoice) {
                utterance.voice = selectedVoice;
            }
            
            window.speechSynthesis.cancel(); // Cancel any previous speech
            window.speechSynthesis.speak(utterance);
        });

        stopButton.addEventListener('click', () => {
            window.speechSynthesis.cancel();
        });
    } else {
        // Hide buttons if text-to-speech is not supported or no text
        speakButton.style.display = 'none';
        stopButton.style.display = 'none';
        voiceSelect.style.display = 'none';
    }
});
</script>
@endpush
