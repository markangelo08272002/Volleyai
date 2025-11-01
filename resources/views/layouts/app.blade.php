<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <title>VolleyAI</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* --- Custom CSS Variables for a professional theme --- */
        :root {
            --sidebar-bg: #111827; /* Darker sidebar */
            --main-bg: #1f2937;    /* Slightly lighter content background */
            --card-bg: #374151;    /* Card and panel background */
            --border-color: #4b5563;
            --primary-color: #3b82f6;
            --primary-hover-color: #2563eb;
            --text-primary: #f9fafb;
            --text-secondary: #9ca3af;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--main-bg);
            color: var(--text-primary);
            display: flex;
            min-height: 100vh;
        }

        /* --- Sidebar Navigation --- */
        .sidebar {
            width: 260px;
            background-color: var(--sidebar-bg);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            border-right: 1px solid var(--border-color);
            transition: transform 0.3s ease-in-out; 
        }
        .sidebar-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2rem;
        }
        .sidebar-logo-text {
            font-weight: 800;
            font-size: 1.75rem;
            color: #fff;
        }
        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1rem;
            font-weight: 500;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            color: var(--text-secondary);
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        .sidebar-nav .nav-link:hover, .sidebar-nav .nav-link.active {
            background-color: var(--main-bg);
            color: var(--text-primary);
        }
        .sidebar-nav .nav-link .bi {
            font-size: 1.2rem;
        }

        /* --- Main Content Area --- */
        .main-content {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-width: 0; 
        }
        .main-header {
            padding: 1.5rem 2rem;
            background-color: var(--sidebar-bg);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0;
        }

        /* --- Main content inside the scrollable area --- */
        .content-body {
            padding: 2rem;
            overflow-y: auto;
        }
        
        /* --- UI Component Styling --- */
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            font-weight: 600;
            transition: background-color 0.2s ease, border-color 0.2s ease;
        }
        .btn-primary:hover {
            background-color: var(--primary-hover-color);
            border-color: var(--primary-hover-color);
        }
        /* This is the btn-glow from your dashboard file, adapted to the theme */
        .btn-glow {
             background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: #fff;
            font-weight: 600;
            transition: background-color 0.2s ease, border-color 0.2s ease, box-shadow 0.3s ease;
        }
        .btn-glow:hover {
            background-color: var(--primary-hover-color);
            border-color: var(--primary-hover-color);
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.5);
        }

        .card {
            background-color: var(--card-bg);
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: transparent;
            border-bottom: 1px solid var(--border-color);
        }
        .table {
            --bs-table-bg: var(--card-bg);
            --bs-table-border-color: var(--border-color);
            --bs-table-hover-bg: #4b5563;
        }
        .table th {
            font-weight: 600;
            color: var(--text-secondary);
        }
        .progress-bar {
            background-color: var(--primary-color);
        }
        .modal-content {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
        }

        /* --- CSS for Mobile Responsiveness --- */
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1040; 
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0s 0.3s linear;
        }
        .sidebar-overlay.is-visible {
            opacity: 1;
            visibility: visible;
            transition: opacity 0.3s ease;
        }

        @media (max-width: 991.98px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                z-index: 1050; 
                transform: translateX(-100%); 
            }
            .sidebar.is-visible {
                transform: translateX(0); 
            }
            .main-header {
                padding: 1rem 1.5rem;
            }
            .content-body {
                padding: 1.5rem;
            }
            .page-title {
                font-size: 1.25rem;
            }
        }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="https://img.icons8.com/ios-filled/50/3b82f6/volleyball.png" style="width:36px;" alt="VolleyAI Logo"/>
            <span class="sidebar-logo-text">VolleyAI</span>
        </div>
        <nav class="sidebar-nav flex-grow-1">
            <ul class="nav flex-column gap-2">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('varsity.dashboard') ? 'active' : '' }}" href="{{ route('varsity.dashboard') }}">
                        <i class="bi bi-grid-1x2-fill"></i>
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('varsity.sessions') ? 'active' : '' }}" href="{{ route('varsity.sessions') }}">
                        <i class="bi bi-journal-text"></i>
                        Session History
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="bi bi-person-fill"></i>
                        Profile
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('volleyball.upload.form') }}">
                        <i class="bi bi-upload"></i>
                        Upload Video
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('volleyball.drill.start.form') }}">
                        <i class="bi bi-rocket-takeoff-fill"></i>
                        Start New Drill
                    </a>
                </li>
            </ul>
        </nav>
        <div>
             <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-outline-secondary w-100" type="submit">
                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                </button>
            </form>
        </div>
    </aside>

    <div class="sidebar-overlay d-lg-none" id="sidebarOverlay"></div>

    <div class="main-content">
        <header class="main-header">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-link p-0 d-lg-none" type="button" id="sidebarToggle">
                    <i class="bi bi-list text-white fs-2"></i>
                </button>
                <h1 class="page-title d-none d-lg-block">Varsity Dashboard</h1>
            </div>
        
            <div class="d-flex align-items-center gap-3">
                <div class="d-none d-md-flex gap-2">
                    <a href="{{ route('volleyball.upload.form') }}" class="btn btn-glow btn-sm px-3">
                        <i class="bi bi-upload me-2"></i>Upload Video
                    </a>
                    <a href="{{ route('volleyball.drill.start.form') }}" class="btn btn-glow btn-sm px-3">
                        <i class="bi bi-rocket-takeoff me-2"></i>Start New Drill
                    </a>
                </div>
                
                <div class="d-flex align-items-center">
                    <div class="text-end d-none d-sm-block">
                        <div class="fw-bold">{{ Auth::user()->name }}</div>
                        <small class="text-secondary">{{ Auth::user()->email }}</small>
                    </div>
                    <img src="https://i.pravatar.cc/40?u={{ Auth::user()->id }}" class="rounded-circle ms-3" alt="User Avatar">
                </div>
            </div>
        </header>

        <main class="content-body">
            
            @yield('content')

        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {

        /* --- JavaScript for Mobile Sidebar Toggle --- */
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const sidebar = document.querySelector('.sidebar');

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('is-visible');
                sidebarOverlay.classList.toggle('is-visible');
            });
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('is-visible');
                sidebarOverlay.classList.remove('is-visible');
            });
        }
    });
    </script>

    @stack('scripts')
</body>
</html>