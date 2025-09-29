<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Start New Drill - VolleyAI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: #10172a;
            color: #fff;
        }
        .card {
            background: #1a202c;
            border: none;
        }
        .btn-glow {
            background-color: #007BFF;
            border-color: #007BFF;
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
            color: white;
        }
        .btn-glow:hover {
            background-color: #0056b3;
            border-color: #0056b3;
            box-shadow: 0 0 15px rgba(0, 123, 255, 0.8);
        }
        video {
            width: 100%;
            max-width: 640px;
            height: auto;
            background: #000;
            border-radius: 8px;
        }
        /* Loading Overlay Styles */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            flex-direction: column;
        }
        .spinner-border {
            width: 3rem;
            height: 3rem;
            color: #007BFF;
        }
        .loading-text {
            color: #fff;
            margin-top: 15px;
            font-size: 1.2rem;
        }
        .error-details {
            background: #dc3545;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
            font-family: monospace;
            font-size: 0.9rem;
            max-height: 200px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-flex align-items-center mb-4">
            <img src="https://img.icons8.com/ios-filled/48/007BFF/volleyball.png" style="width:38px;" alt="VolleyAI"/>
            <h2 class="ms-3 mb-0 text-white fw-bold">Start New Drill</h2>
        </div>

        <div class="card p-4 mb-4">
            <h5 class="text-white mb-3">Choose Drill Action</h5>
            <form id="drillForm">
                <div class="mb-3">
                    <label for="actionSelect" class="form-label text-secondary">Select the action you will perform:</label>
                    <select class="form-select bg-dark text-white border-secondary" id="actionSelect" name="action_type" required>
                        <option value="">-- Select Action --</option>
                        <option value="volleyball_spike">Spike</option>
                        <option value="block">Block</option>
                        <option value="serve">Serve</option>
                        <option value="setter">Setter</option>
                        <option value="dive">Dive</option>
                    </select>
                </div>
                
                <button type="button" class="btn btn-glow" id="startCameraBtn"><i class="bi bi-camera-video me-2"></i>Start Camera</button>
                <button type="button" class="btn btn-secondary mt-2" id="flipCameraBtn" style="display: none;"><i class="bi bi-arrow-repeat me-2"></i>Flip Camera</button>

                
            </form>
        </div>

        <div class="card p-4" id="cameraSection" style="display: none;">
            <h5 class="text-white mb-3">Record Your Drill</h5>
            <div class="row">
                <div class="col-md-8">
                    <video id="videoFeed" autoplay playsinline></video>
                </div>
                <div class="col-md-4 d-flex flex-column justify-content-center align-items-center">
                    <button class="btn btn-danger btn-lg mb-3" id="recordBtn"><i class="bi bi-record-circle me-2"></i>Record</button>
                    <button type="button" class="btn btn-warning btn-lg mb-3" id="stopBtn" style="display: none;"><i class="bi bi-stop-circle me-2"></i>Stop</button>
                    <button type="button" class="btn btn-success btn-lg" id="uploadBtn" style="display: none;"><i class="bi bi-upload me-2"></i>Upload & Analyze</button>
                    <button type="button" class="btn btn-secondary btn-lg" id="retakeBtn" style="display: none; margin-top: 10px;"><i class="bi bi-camera-reels me-2"></i>Retake</button>
                    <button type="button" class="btn btn-secondary mt-2" id="flipCameraBtn" style="display: none;"><i class="bi bi-arrow-repeat me-2"></i>Flip Camera</button>
                    <p id="recordingStatus" class="text-secondary mt-2"></p>
                    <p id="recordingTime" class="text-secondary mt-2" style="display: none;">00:00</p>
                </div>
            </div>
        </div>

        <div id="errorSection" class="card p-4 mt-4" style="display: none;">
            <h5 class="text-danger mb-3">Error Details</h5>
            <div id="errorDetails" class="error-details"></div>
            <button type="button" class="btn btn-secondary mt-3" id="retryBtn">Retry Upload</button>
        </div>
    </div>

    <div id="loadingOverlay" class="loading-overlay" style="display: none;">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="loading-text">Uploading and analyzing your drill...</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const startCameraBtn = document.getElementById('startCameraBtn');
        const flipCameraBtn = document.getElementById('flipCameraBtn');
        const cameraSection = document.getElementById('cameraSection');
        const videoFeed = document.getElementById('videoFeed');
        const recordBtn = document.getElementById('recordBtn');
        const stopBtn = document.getElementById('stopBtn');
        const uploadBtn = document.getElementById('uploadBtn');
        const retakeBtn = document.getElementById('retakeBtn');
        const recordingStatus = document.getElementById('recordingStatus');
        const recordingTime = document.getElementById('recordingTime');
        const actionSelect = document.getElementById('actionSelect');
        const loadingOverlay = document.getElementById('loadingOverlay');
        const errorSection = document.getElementById('errorSection');
        const errorDetails = document.getElementById('errorDetails');
        const retryBtn = document.getElementById('retryBtn');
        

        let mediaRecorder;
        let recordedChunks = [];
        let stream;
        let currentDeviceId = null;
        let videoDevices = [];
        let recordingStartTime;
        let recordingInterval;
        let keepAliveInterval;

        function startKeepAlive() {
            console.log('Starting keep-alive pings...');
            keepAliveInterval = setInterval(() => {
                console.log('Sending keep-alive ping...');
                fetch('{{ route('keep-alive') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                }).then(response => {
                    if (!response.ok) {
                        console.error('Keep-alive ping failed:', response.status, response.statusText);
                    }
                }).catch(error => {
                    console.error('Keep-alive fetch error:', error);
                });
            }, 60000); // Every 60 seconds
        }

        function stopKeepAlive() {
            clearInterval(keepAliveInterval);
        }

        function showError(message, details = null) {
            recordingStatus.textContent = message;
            if (details) {
                errorDetails.innerHTML = `<strong>Error Details:</strong><br>${details}`;
                errorSection.style.display = 'block';
            }
            uploadBtn.disabled = false;
            loadingOverlay.style.display = 'none';
        }

        function hideError() {
            errorSection.style.display = 'none';
        }

        async function getConnectedDevices(type) {
            const devices = await navigator.mediaDevices.enumerateDevices();
            return devices.filter(device => device.kind === type);
        }

        async function startCamera(deviceId) {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }

            const constraints = {
                video: deviceId ? { deviceId: { exact: deviceId } } : { facingMode: 'environment' },
                audio: false
            };

            try {
                stream = await navigator.mediaDevices.getUserMedia(constraints);
                videoFeed.srcObject = stream;
                videoFeed.play();
                cameraSection.style.display = 'block';
                startCameraBtn.style.display = 'none';
                recordBtn.style.display = 'block';
                flipCameraBtn.style.display = 'block';
                currentDeviceId = deviceId || stream.getVideoTracks()[0].getSettings().deviceId;
            } catch (err) {
                console.error('Error accessing camera:', err);
                alert(`Could not access camera. Please ensure you have a camera connected and grant permission. Error: ${err.name}: ${err.message}`);
            }
        }

        startCameraBtn.addEventListener('click', async () => {
            if (actionSelect.value === "") {
                alert("Please select an action first.");
                return;
            }
            videoDevices = await getConnectedDevices('videoinput');
            if (videoDevices.length === 0) {
                alert("No camera found.");
                return;
            }
            await startCamera(videoDevices[0].deviceId);
        });

        flipCameraBtn.addEventListener('click', async () => {
            if (videoDevices.length < 2) {
                alert("Only one camera detected. Cannot flip.");
                return;
            }

            const currentIndex = videoDevices.findIndex(device => device.deviceId === currentDeviceId);
            const nextIndex = (currentIndex + 1) % videoDevices.length;
            const nextDeviceId = videoDevices[nextIndex].deviceId;

            await startCamera(nextDeviceId);
        });

        recordBtn.addEventListener('click', () => {
            recordedChunks = [];
            mediaRecorder = new MediaRecorder(stream, { mimeType: 'video/webm' });

            mediaRecorder.ondataavailable = (event) => {
                if (event.data.size > 0) {
                    recordedChunks.push(event.data);
                }
            };

            mediaRecorder.onstop = () => {
                stopKeepAlive();
                const blob = new Blob(recordedChunks, { type: 'video/webm' });
                videoFeed.srcObject = null;
                videoFeed.src = URL.createObjectURL(blob);
                videoFeed.controls = true;
                uploadBtn.style.display = 'block';
                retakeBtn.style.display = 'block';
                recordingStatus.textContent = 'Recording stopped. Review and Upload.';
                stream.getTracks().forEach(track => track.stop());
                flipCameraBtn.style.display = 'none';
                clearInterval(recordingInterval);
                recordingTime.style.display = 'none';
            };

            mediaRecorder.start();
            startKeepAlive();
            recordingStatus.textContent = 'Recording...';
            recordBtn.style.display = 'none';
            stopBtn.style.display = 'block';
            uploadBtn.style.display = 'none';
            retakeBtn.style.display = 'none';
            recordingTime.style.display = 'block';
            recordingStartTime = Date.now();
            recordingInterval = setInterval(() => {
                const elapsedTime = Date.now() - recordingStartTime;
                const minutes = Math.floor(elapsedTime / 60000);
                const seconds = Math.floor((elapsedTime % 60000) / 1000);
                recordingTime.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
            }, 1000);
        });

        stopBtn.addEventListener('click', () => {
            mediaRecorder.stop();
            stream.getTracks().forEach(track => track.stop());
            stopBtn.style.display = 'none';
            flipCameraBtn.style.display = 'none';
            clearInterval(recordingInterval);
            recordingTime.style.display = 'none';
        });

        retakeBtn.addEventListener('click', async () => {
            // Hide upload and retake buttons
            uploadBtn.style.display = 'none';
            retakeBtn.style.display = 'none';

            // Show record button
            recordBtn.style.display = 'block';

            // Clear recorded chunks
            recordedChunks = [];

            // Reset video element
            videoFeed.src = null;
            videoFeed.srcObject = null;
            videoFeed.controls = false;

            // Restart the camera
            await startCamera(currentDeviceId);

            recordingStatus.textContent = 'Ready to record again.';
        });

        async function uploadVideo() {
            hideError();
            recordingStatus.textContent = 'Uploading and analyzing...';
            uploadBtn.disabled = true;
            loadingOverlay.style.display = 'flex';

            const blob = new Blob(recordedChunks, { type: 'video/webm' });
            console.log('Video blob type:', blob.type);
            const formData = new FormData();
            formData.append('video', blob, 'recorded_drill.webm');
            formData.append('action_type', actionSelect.value);

            try {
                console.log('Uploading to:', '/volleyball/upload-drill');
                console.log('Action type:', actionSelect.value);
                console.log('Video blob size:', blob.size);

                const response = await fetch('{{ route('volleyball.drill.upload') }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });

                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);

                // Get the response text first to see what we're dealing with
                const responseText = await response.text();
                console.log('Response text:', responseText);

                // Check if the response is HTML (error page) or JSON
                if (responseText.trim().startsWith('<!DOCTYPE') || responseText.trim().startsWith('<html')) {
                    alert('Your session may have expired. Please log in again.');
                    window.location.reload();
                    throw new Error('Server returned HTML instead of JSON. This usually indicates a server error or routing issue.');
                }

                // Try to parse as JSON
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (jsonError) {
                    throw new Error(`Invalid JSON response: ${responseText.substring(0, 200)}...`);
                }

                if (response.ok) {
                    recordingStatus.textContent = 'Upload successful! Redirecting for analysis...';
                    // Use a fallback redirect if the route doesn't exist
                    window.location.href = result.redirect_url || '/dashboard' || '/';
                } else {
                    showError(`Upload failed (${response.status}): ${result.message || response.statusText}`, 
                             `Status: ${response.status}\nResponse: ${JSON.stringify(result, null, 2)}`);
                }
            } catch (error) {
                console.error('Error during upload:', error);
                let errorMessage = 'Upload failed: ';
                let errorDetailsText = '';

                if (error.name === 'TypeError' && error.message.includes('Failed to fetch')) {
                    errorMessage += 'Network error. Please check your connection.';
                    errorDetailsText = `Network Error: ${error.message}\n\nPossible causes:\n- Server is down\n- Network connectivity issues\n- CORS policy blocking the request`;
                } else if (error.message.includes('HTML instead of JSON')) {
                    errorMessage += 'Server configuration error.';
                    errorDetailsText = `${error.message}\n\nPossible causes:\n- Route '/volleyball/upload-drill' doesn't exist\n- Server returned an error page\n- Authentication issue`;
                } else {
                    errorMessage += error.message;
                    errorDetailsText = `Error: ${error.message}\nStack: ${error.stack}`;
                }

                showError(errorMessage, errorDetailsText);
            }
        }

        uploadBtn.addEventListener('click', uploadVideo);
        retryBtn.addEventListener('click', uploadVideo);
    </script>
</body>
</html>