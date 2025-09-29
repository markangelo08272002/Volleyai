<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>VolleyAI - Authentication</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #111827; /* Fallback color */
            color: #e5e7eb;
            /* New layout structure to accommodate header */
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        /* Header styles (copied from main layout) */
        .main-header {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .logo-text { font-weight: 800; font-size: 1.5rem; color: #fff; }

        /* This new container will now handle the centering */
        main.auth-container {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-grow: 1; /* Allows it to take up the remaining vertical space */
            padding: 1rem;
        }

        .auth-card {
            width: 100%;
            max-width: 450px;
            background: rgba(17, 24, 39, 0.80);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1.25rem;
            padding: 2.5rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.4);
        }

        /* Custom Dark Form Styles */
        .form-control {
            background-color: rgba(31, 41, 55, 0.7);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 0.75rem 1rem;
        }
        .form-control:focus {
            background-color: rgba(31, 41, 55, 0.9);
            color: #fff;
            border-color: #007BFF;
            box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
        }
        .form-control::placeholder { color: #9ca3af; }

        .input-group-text {
            background-color: rgba(31, 41, 55, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #007BFF;
        }

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
        
        .auth-link {
            color: #60a5fa;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .auth-link:hover {
            color: #fff;
            text-decoration: underline;
        }
    </style>
</head>
<body>

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

    <main class="auth-container">
        @yield('content')
    </main>

</body>
</html>