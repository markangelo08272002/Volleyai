<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>VolleyAI - Modern Volleyball Analysis</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #111827; /* Fallback for when video doesn't load */
            color: #e5e7eb;
        }

        #bg-video {
            position: fixed;
            top: 50%;
            left: 50%;
            min-width: 100%;
            min-height: 100%;
            width: auto;
            height: auto;
            z-index: -2;
            transform: translateX(-50%) translateY(-50%);
            object-fit: cover;
            opacity: 0.75; /* Adjusted for dark theme */
        }
        
        .main-header {
            background: rgba(17, 24, 39, 0.7); /* Transparent dark header */
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .main-content-panel {
           background: rgba(255,255,255,0.45);  /* <- Set your transparency here */
            box-shadow: 0 4px 24px 0 rgba(34, 60, 80, 0.09);
            border-radius: 18px;
            padding: 2.5rem 1.5rem;
        }
        
        .logo-text { font-weight: 800; font-size: 1.5rem; color: #fff; }
        .section-title { font-weight: 800; color: #fff; }
        .section-subtitle { font-size: 1.25rem; color: #9ca3af; max-width: 700px; margin: auto; }

        .feature-card, .role-card {
            background: rgba(31, 41, 55, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 1rem;
            padding: 2rem;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }

        .feature-card:hover, .role-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 30px rgba(0, 123, 255, 0.2);
            border-color: rgba(0, 123, 255, 0.5);
        }

        .feature-card .bi { font-size: 2.5rem; color: #007BFF; margin-bottom: 1rem; }
        .role-card .bi { font-size: 1.75rem; color: #007BFF; margin-bottom: 0.75rem; }
        .card-title { font-weight: 700; color: #fff; }
        .card-text, .role-list { color: #d1d5db; }

        .btn-glow {
            background: #007BFF;
            color: #fff;
            font-weight: 600;
            border-radius: 0.5rem;
            padding: 0.75rem 2rem;
            border: none;
            transition: box-shadow 0.3s ease, background-color 0.3s ease;
        }

        .btn-glow:hover {
            box-shadow: 0 0 20px rgba(0, 123, 255, 0.5);
            background-color: #0069d9;
        }
    </style>
</head>
<body>
    <video autoplay loop muted playsinline id="bg-video">
        <source src="{{ asset('videos/volleyball-bg.mp4') }}" type="video/mp4">
    </video>

    <nav class="main-header p-3">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <img src="https://img.icons8.com/ios-filled/50/007BFF/volleyball.png" style="width:32px;"/>
                <span class="logo-text ms-2">VolleyAI</span>
            </div>
            <div>
                <span class="badge bg-primary text-white">AI Powered</span>
            </div>
        </div>
    </nav>

    <main class="container py-5">
        <div class="main-content-panel">
            @yield('content')
        </div>
    </main>

    <footer class="text-center py-4 mt-4">
        <p class="text-white-50 small">© {{ date('Y') }} VolleyAI. All rights reserved.</p>
    </footer>
</body>
</html>