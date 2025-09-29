<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>VolleyAI - Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap & Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #111827;
            color: #e5e7eb;
            min-height: 100vh;
        }
        .main-header {
            background: rgba(17, 24, 39, 0.85);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .logo-text {
            font-weight: 800;
            font-size: 1.5rem;
            color: #fff;
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
        .auth-link, .nav-link {
            color: #60a5fa !important;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .auth-link:hover, .nav-link:hover {
            color: #fff !important;
            text-decoration: underline;
        }
        .dashboard-container {
            flex-grow: 1;
            padding: 2rem 0;
        }
        .profile-dropdown {
            background: rgba(31, 41, 55, 0.9);
            border: 1px solid rgba(255,255,255,0.1);
            color: #fff;
            border-radius: 1rem;
        }
    </style>
    @stack('styles')
</head>
<body class="d-flex flex-column" style="min-height:100vh;">

    <!-- Main Header -->
    <nav class="main-header p-3">
        <div class="container d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <img src="https://img.icons8.com/ios-filled/50/007BFF/volleyball.png" style="width:32px;"/>
                <span class="logo-text ms-2">VolleyAI</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <!-- Add more nav links as needed -->
                <a href="{{ route('dashboard') }}" class="nav-link fw-semibold">Dashboard</a>
                <a href="{{ route('profile.edit') }}" class="nav-link fw-semibold">Profile</a>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button class="btn btn-sm btn-outline-light ms-2" type="submit"><i class="bi bi-box-arrow-right me-1"></i>Logout</button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="dashboard-container container-fluid flex-grow-1">
        @yield('content')
    </main>

    <!-- Bootstrap JS (Optional, for dropdowns) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
