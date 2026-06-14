<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Inventory Control Hadley-Within</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Custom Premium Styles -->
    <style>
        :root {
            --primary-color: #6366f1; /* Vibrant Indigo */
            --primary-hover: #4f46e5;
            --success-color: #10b981; /* Emerald */
            --warning-color: #f59e0b; /* Amber */
            --danger-color: #ef4444; /* Rose */
            --dark-sidebar: #0f172a; /* Slate 900 */
            --sidebar-active: #1e293b; /* Slate 800 */
            --bg-body: #f8fafc; /* Slate 50 */
            --card-shadow: 0 4px 20px -2px rgba(148, 163, 184, 0.12), 0 2px 8px -1px rgba(148, 163, 184, 0.08);
            --transition-speed: 0.3s;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: #334155;
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        .sidebar {
            background-color: var(--dark-sidebar);
            width: 260px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: all var(--transition-speed);
            box-shadow: 4px 0 24px rgba(15, 23, 42, 0.15);
            display: flex;
            flex-direction: column;
        }

        .sidebar-brand {
            padding: 24px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-brand i {
            font-size: 24px;
            color: var(--primary-color);
        }

        .sidebar-brand span {
            color: #fff;
            font-weight: 700;
            font-size: 18px;
            letter-spacing: 0.5px;
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 12px;
            margin: 0;
            flex-grow: 1;
        }

        .sidebar-menu li {
            margin-bottom: 8px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 16px;
            color: #94a3b8;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.2s;
        }

        .sidebar-menu a:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.04);
        }

        .sidebar-menu li.active a {
            color: #fff;
            background-color: var(--primary-color);
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.35);
        }

        .sidebar-footer {
            padding: 20px 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
        }

        /* Main Content Styling */
        .main-content {
            margin-left: 260px;
            min-height: 100vh;
            transition: all var(--transition-speed);
            padding: 30px;
        }

        /* Top Header Styling */
        .top-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e2e8f0;
        }

        .header-title h1 {
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
            margin: 0;
        }

        .header-title p {
            margin: 4px 0 0 0;
            color: #64748b;
            font-size: 14px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            background-color: #fff;
            padding: 8px 16px;
            border-radius: 30px;
            box-shadow: var(--card-shadow);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: #e0e7ff;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        /* Premium Card Styling */
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            background: #fff;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-bottom: 24px;
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid #f1f5f9;
            padding: 20px 24px;
            font-weight: 700;
            font-size: 16px;
            color: #0f172a;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-body {
            padding: 24px;
        }

        /* Stat Widget */
        .stat-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            font-weight: 600;
            border-radius: 10px;
            padding: 10px 20px;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
            transition: all 0.2s;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
            transform: translateY(-1px);
        }

        /* Responsive Layout Grid */
        @media (max-width: 992px) {
            .sidebar {
                left: -260px;
            }
            .sidebar.active {
                left: 0;
            }
            .main-content {
                margin-left: 0;
            }
            .sidebar-toggle {
                display: block !important;
            }
        }

        .sidebar-toggle {
            display: none;
            background: #fff;
            border: 1px solid #e2e8f0;
            padding: 8px 12px;
            border-radius: 8px;
            cursor: pointer;
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Sidebar Navigation -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <i class="bi bi-shield-check"></i>
            <span>Inventory HW</span>
        </div>
        <ul class="sidebar-menu">
            <li class="{{ Route::is('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}">
                    <i class="bi bi-grid-1x2-fill"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="{{ Route::is('products.*') ? 'active' : '' }}">
                <a href="{{ route('products.index') }}">
                    <i class="bi bi-box-seam-fill"></i>
                    <span>Master Produk</span>
                </a>
            </li>
            <li class="{{ Route::is('demands.*') ? 'active' : '' }}">
                <a href="{{ route('demands.index') }}">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span>Data Permintaan</span>
                </a>
            </li>
            <li class="{{ Route::is('calculations.create') ? 'active' : '' }}">
                <a href="{{ route('calculations.create') }}">
                    <i class="bi bi-calculator-fill"></i>
                    <span>Kalkulator (R,s,S)</span>
                </a>
            </li>
            <li class="{{ Route::is('calculations.index') || (Route::is('calculations.show') && !Route::is('calculations.create')) ? 'active' : '' }}">
                <a href="{{ route('calculations.index') }}">
                    <i class="bi bi-clock-history"></i>
                    <span>Riwayat Perhitungan</span>
                </a>
            </li>
        </ul>
        <div class="sidebar-footer">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-danger w-100 d-flex align-items-center justify-content-center gap-2" style="border-radius: 10px;">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Main Content wrapper -->
    <div class="main-content">
        <!-- Header -->
        <div class="top-header">
            <div class="d-flex align-items-center gap-3">
                <button class="sidebar-toggle" onclick="toggleSidebar()">
                    <i class="bi bi-list"></i>
                </button>
                <div class="header-title">
                    <h1>@yield('header_title', 'Sistem Pengendalian Persediaan')</h1>
                    <p>@yield('header_subtitle', 'Metode Hadley-Within Periodic Review (R,s,S)')</p>
                </div>
            </div>
            
            <div class="user-profile">
                <div class="user-avatar">
                    {{ substr(Auth::user()->name ?? 'A', 0, 1) }}
                </div>
                <div class="d-none d-md-block">
                    <div class="fw-semibold text-dark">{{ Auth::user()->name ?? 'Admin' }}</div>
                    <div class="text-muted" style="font-size: 11px;">Administrator</div>
                </div>
            </div>
        </div>

        <!-- Notification Alerts -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: 12px; background-color: #ecfdf5; color: #065f46;">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle-fill"></i>
                    <div>{{ session('success') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: 12px; background-color: #fff1f2; color: #9f1239;">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>{{ session('error') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any() && !Route::is('login'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: 12px; background-color: #fff1f2; color: #9f1239;">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <div>
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Core Page Content -->
        @yield('content')
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Layout JS -->
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }
    </script>
    @yield('scripts')
</body>
</html>
