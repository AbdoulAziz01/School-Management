<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Tableau de bord - Administration')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #1e40af;
            --primary-dark: #1e3a8a;
            --sidebar-width: 250px;
        }
        
        body {
            background-color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            min-height: 100vh;
            padding-top: 0;
        }
        
        .wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            min-height: 100vh;
            height: 100vh;
            background: linear-gradient(180deg, var(--primary-dark) 0%, var(--primary-color) 100%);
            color: white;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(33, 40, 50, 0.15);
            z-index: 1050;
            position: fixed;
            left: 0;
            top: 0;
            border-right: none;
            padding: 15px 0;
            width: var(--sidebar-width);
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.85);
            padding: 12px 20px;
            margin: 2px 10px;
            border-radius: 6px;
            transition: all 0.2s ease;
            font-size: 0.925rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            border-left: 3px solid transparent;
        }
        
        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.15);
            color: white !important;
            font-weight: 600;
            border-left-color: white;
        }
        
        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            text-decoration: none;
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            width: 24px;
            text-align: center;
            font-size: 1.1rem;
            transition: transform 0.2s;
        }
        
        .sidebar .nav-link:hover i {
            transform: scale(1.1);
        }
        
        /* Mobile Header */
        .mobile-header {
            display: none;
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary-color) 100%);
            color: white;
            padding: 15px 20px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1040;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .mobile-header .menu-toggle {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            padding: 0;
            cursor: pointer;
        }
        
        .mobile-header h5 {
            margin: 0;
            font-size: 1.1rem;
        }
        
        /* Sidebar Overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1045;
        }
        
        .sidebar-overlay.active {
            display: block;
        }
        
        .main-content {
            padding: 25px;
            padding-bottom: 50px;
            flex: 1;
            background-color: #f1f5f9;
            min-height: 100vh;
            width: calc(100% - var(--sidebar-width));
            overflow-x: auto;
            overflow-y: visible;
            margin-left: var(--sidebar-width);
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #e5e7eb;
            border-radius: 10px 10px 0 0 !important;
            padding: 1rem 1.25rem;
        }
        
        .user-dropdown .dropdown-menu {
            left: auto;
            right: 0;
        }
        
        .alert {
            border-radius: 8px;
        }
        
        /* Pagination Styles */
        .pagination {
            margin-bottom: 0;
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .pagination .page-link {
            color: var(--primary-color);
            border-radius: 6px;
            margin: 2px;
            padding: 0.5rem 0.75rem;
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .pagination .page-link svg {
            width: 1rem;
            height: 1rem;
        }
        
        /* Responsive Styles */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .mobile-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 15px;
                padding-top: 80px;
                padding-bottom: 50px;
            }
        }
        
        @media (max-width: 576px) {
            .main-content {
                padding: 10px;
                padding-top: 75px;
                padding-bottom: 40px;
            }
            
            .card-body {
                padding: 15px;
            }
            
            /* Table responsive on mobile */
            .table-responsive {
                margin: 0 -15px;
                padding: 0 15px;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .btn-group .btn {
                border-radius: 4px !important;
                margin-bottom: 2px;
            }
            
            .table-responsive {
                font-size: 0.875rem;
            }
            
            h1, .h1 { font-size: 1.5rem; }
            h2, .h2 { font-size: 1.3rem; }
            h3, .h3 { font-size: 1.15rem; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Mobile Header -->
    <div class="mobile-header">
        <button class="menu-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <h5><i class="fas fa-school me-2"></i>Administration</h5>
        <div style="width: 24px;"></div>
    </div>
    
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <div class="wrapper d-flex">
        <!-- Sidebar -->
        @include('admin.components.sidebar')

        <!-- Main content -->
        <main class="main-content">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i> {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Sidebar Toggle
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('sidebarOverlay').classList.toggle('active');
        });
        
        document.getElementById('sidebarOverlay').addEventListener('click', function() {
            document.getElementById('sidebar').classList.remove('active');
            this.classList.remove('active');
        });
        
        // Close sidebar when clicking a link on mobile
        document.querySelectorAll('.sidebar .nav-link').forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.innerWidth < 992) {
                    document.getElementById('sidebar').classList.remove('active');
                    document.getElementById('sidebarOverlay').classList.remove('active');
                }
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>
