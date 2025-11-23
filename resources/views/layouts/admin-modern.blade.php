<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>@yield('title') - Administration TaPrestation</title>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Custom fonts for this template-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom styles -->
    <style>
        /*
        ========================================
        ADMIN MODERN LAYOUT - CSS ORGANIZATION
        ========================================
        
        Structure:
        1. CSS Variables (Colors, Spacing)
        2. Base Styles (Body, Layout)
        3. Sidebar Navigation
        4. Main Content Area
        5. Reusable Components:
           - Base Card Styles (.card-base)
           - Grid Layouts (.stats-grid)
           - Icons (.icon-base)
           - Text Styles (.text-title, .text-value)
           - Color Variants (.variant-*)
        6. Specific Components (Stats, Charts, Tables)
        7. Buttons & Forms
        8. Responsive Design
        */
        
        :root {
            --primary: #4f46e5;
            --primary-dark: #3730a3;
            --secondary: #6b7280;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #06b6d4;
            --light: #f8fafc;
            --dark: #1f2937;
            --sidebar-bg: #1e293b;
            --sidebar-hover: #334155;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #dbeafe;
            color: #1e3a8a;
            line-height: 1.6;
        }
        
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, var(--sidebar-bg) 0%, #0f172a 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .sidebar-brand {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .sidebar-brand-icon {
            width: 40px;
            height: 40px;
            background: var(--primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }
        
        .sidebar-brand-text {
            font-size: 1.25rem;
            font-weight: 700;
        }
        
        .sidebar-nav {
            padding: 1rem 0;
        }
        
        .nav-section {
            margin-bottom: 2rem;
        }
        
        .nav-section-title {
            padding: 0 1.5rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94a3b8;
        }
        
        .nav-item {
            margin: 0.25rem 1rem;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: #cbd5e1;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s ease;
            position: relative;
        }
        
        .nav-link:hover {
            background-color: var(--sidebar-hover);
            color: white;
            transform: translateX(4px);
        }
        
        .nav-link.active {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }
        
        .nav-link i {
            width: 20px;
            text-align: center;
            font-size: 1rem;
        }
        
        .badge-notification {
            background: var(--danger);
            color: white;
            font-size: 0.7rem;
            padding: 0.2rem 0.5rem;
            border-radius: 10px;
            margin-left: auto;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 280px;
            flex: 1;
            display: flex;
            flex-direction: column;
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            min-height: 100vh;
        }
        
        /* Header */
        .header {
            background: white;
            padding: 1rem 2rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: between;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .header-left h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0;
        }
        
        .header-right {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: #f8fafc;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .user-menu:hover {
            background: #e2e8f0;
        }
        
        .user-avatar {
            width: 32px;
            height: 32px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        
        /* Content Area */
        .content {
            padding: 2rem;
            flex: 1;
            background: transparent;
        }
        
        /* Page Headers */
        .page-header {
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            color: white;
            padding: 2rem;
            margin: -2rem -2rem 2rem -2rem;
            border-radius: 0 0 1rem 1rem;
            text-align: center;
        }
        
        .page-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .page-subtitle {
            font-size: 1.125rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }
        
        /* Base Card Styles */
        .card-base {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
            border: 2px solid #dbeafe;
            transition: all 0.3s ease;
        }
        
        .card-base:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.25);
            border-color: #3b82f6;
        }
        
        /* Grid Layouts */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        /* Stat Cards */
        .stat-card {
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, transparent 100%);
            border-radius: 50%;
            transform: translate(30px, -30px);
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
        }
        
        .stat-card.primary {
            border-left-color: var(--primary);
        }
        
        .stat-card.success {
            border-left-color: var(--success);
        }
        
        .stat-card.warning {
            border-left-color: var(--warning);
        }
        
        .stat-card.info {
            border-left-color: var(--info);
        }
        
        /* Text Styles */
        .text-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .text-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        
        /* Stat Specific */
        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }
        
        /* Icons */
        .icon-base {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }
        
        /* Color Variants */
        .variant-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        }
        
        .variant-success {
            background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
        }
        
        .variant-warning {
            background: linear-gradient(135deg, var(--warning) 0%, #d97706 100%);
        }
        
        .variant-info {
            background: linear-gradient(135deg, var(--info) 0%, #0891b2 100%);
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }
        
        .stat-change {
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .stat-change.positive {
            color: var(--success);
        }
        
        .stat-change.negative {
            color: var(--danger);
        }
        
        /* Chart Cards */
        .chart-card {
            margin-bottom: 2rem;
        }
        
        /* Card Headers */
        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .chart-header {
            margin-bottom: 1.5rem;
        }
        
        .card-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--dark);
        }
        
        .chart-container {
            position: relative;
            height: 300px;
        }
        
        /* Tables */
        .table-card {
            overflow: hidden;
        }
        
        .table-header {
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .modern-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .modern-table th {
            background: #f8fafc;
            padding: 1rem 1.5rem;
            text-align: left;
            font-weight: 600;
            color: var(--secondary);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .modern-table td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        
        .modern-table tbody tr:hover {
            background: #f8fafc;
        }
        
        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.875rem;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }
        
        .btn-success {
            background: var(--success);
            color: white;
        }
        
        .btn-warning {
            background: var(--warning);
            color: white;
        }
        
        .btn-danger {
            background: var(--danger);
            color: white;
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid #e2e8f0;
            color: var(--secondary);
        }
        
        .btn-outline:hover {
            background: #f8fafc;
        }
        
        /* Mobile Toggle Button */
        .mobile-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1001;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.75rem;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
            transition: all 0.3s ease;
        }
        
        .mobile-toggle:hover {
            background: var(--primary-dark);
            transform: scale(1.05);
        }
        
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                width: 260px;
            }
            
            .main-content {
                margin-left: 260px;
            }
            
            .content {
                padding: 1.5rem;
            }
        }
        
        @media (max-width: 768px) {
            .mobile-toggle {
                display: block;
            }
            
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .sidebar-overlay.active {
                display: block;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .header {
                padding: 1rem 1rem 1rem 4rem;
            }
            
            .header-left h1 {
                font-size: 1.25rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .content {
                padding: 1rem;
            }
            
            .page-header {
                padding: 1.5rem 1rem;
                margin: -1rem -1rem 1.5rem -1rem;
            }
            
            .page-title {
                font-size: 1.75rem;
            }
            
            .page-subtitle {
                font-size: 1rem;
            }
            
            .card-base {
                padding: 1rem;
            }
            
            .modern-table th,
            .modern-table td {
                padding: 0.75rem 1rem;
                font-size: 0.875rem;
            }
            
            .user-menu span {
                display: none;
            }
        }
        
        @media (max-width: 480px) {
            .header {
                padding: 0.75rem 0.75rem 0.75rem 3.5rem;
            }
            
            .header-left h1 {
                font-size: 1.125rem;
            }
            
            .content {
                padding: 0.75rem;
            }
            
            .page-header {
                padding: 1rem 0.75rem;
                margin: -0.75rem -0.75rem 1rem -0.75rem;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
            
            .card-base {
                padding: 0.75rem;
                border-radius: 12px;
            }
            
            .stat-card {
                padding: 1rem;
            }
            
            .stat-value {
                font-size: 1.5rem;
            }
            
            .btn {
                padding: 0.5rem 0.75rem;
                font-size: 0.8125rem;
            }
            
            .modern-table {
                font-size: 0.8125rem;
            }
            
            .modern-table th,
            .modern-table td {
                padding: 0.5rem 0.75rem;
            }
        }
        
        /* Progress bars */
        .progress {
            height: 8px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
            margin: 0.5rem 0;
        }
        
        .progress-bar {
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s ease;
        }
        
        .progress-bar.variant-primary {
            background: linear-gradient(90deg, var(--primary) 0%, var(--primary-dark) 100%);
        }
        
        .progress-bar.variant-success {
            background: linear-gradient(90deg, var(--success) 0%, #059669 100%);
        }
        
        .progress-bar.variant-warning {
            background: linear-gradient(90deg, var(--warning) 0%, #d97706 100%);
        }
        
        .progress-bar.variant-info {
            background: linear-gradient(90deg, var(--info) 0%, #0891b2 100%);
        }
    </style>
</head>

<body>
    <div class="admin-wrapper">
        <!-- Mobile Toggle Button -->
        <button class="mobile-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        
        <!-- Sidebar Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        
        <!-- Sidebar -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <div class="sidebar-brand-icon">
                    <i class="fas fa-user-shield"></i>
                </div>
                <div class="sidebar-brand-text">TaPrestation</div>
            </div>
            
            <div class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-item">
                        <a class="nav-link {{ request()->routeIs('administrateur.dashboard') ? 'active' : '' }}" href="{{ route('administrateur.dashboard') }}">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Tableau de bord</span>
                        </a>
                    </div>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Gestion des utilisateurs</div>
                    
                    <div class="nav-item">
                        <a class="nav-link {{ request()->routeIs('administrateur.users.*') ? 'active' : '' }}" href="{{ route('administrateur.users.index') }}">
                            <i class="fas fa-users"></i>
                            <span>Utilisateurs</span>
                        </a>
                    </div>
                    
                    <div class="nav-item">
                        <a class="nav-link {{ request()->routeIs('administrateur.prestataires.*') ? 'active' : '' }}" href="{{ route('administrateur.prestataires.index') }}">
                            <i class="fas fa-user-tie"></i>
                            <span>Prestataires</span>
                            @php
                                $pendingCount = \App\Models\Prestataire::where('is_approved', false)->count();
                            @endphp
                            @if($pendingCount > 0)
                                <span class="badge-notification">{{ $pendingCount }}</span>
                            @endif
                        </a>
                    </div>
                    
                    <div class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.verifications.index') ? 'active' : '' }}" href="{{ route('admin.verifications.index') }}">
                            <i class="bi bi-patch-check-fill"></i>
                            <span>Vérifications</span>
                        </a>
                    </div>
                    
                    <div class="nav-item">
                        <a class="nav-link {{ request()->routeIs('administrateur.clients.*') ? 'active' : '' }}" href="{{ route('administrateur.clients.index') }}">
                            <i class="fas fa-user"></i>
                            <span>Clients</span>
                        </a>
                    </div>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Gestion du contenu</div>
                    
                    <div class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.equipments.*') ? 'active' : '' }}" href="{{ route('admin.equipments.index') }}">
                            <i class="fas fa-wrench"></i>
                            <span>Équipements</span>
                        </a>
                    </div>
                    
                    <div class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.announcements.*') ? 'active' : '' }}" href="{{ route('admin.announcements.index') }}">
                            <i class="fas fa-bullhorn"></i>
                            <span>Annonces</span>
                        </a>
                    </div>
                    
                    <div class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.services.*') ? 'active' : '' }}" href="{{ route('admin.services.index') }}">
                            <i class="fas fa-briefcase"></i>
                            <span>Services</span>
                        </a>
                    </div>
                    
                    <div class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}" href="{{ route('admin.reviews.index') }}">
                            <i class="fas fa-star"></i>
                            <span>Avis</span>
                        </a>
                    </div>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Gestion des activités</div>
                    
                    <div class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}" href="{{ route('admin.bookings.index') }}">
                            <i class="fas fa-calendar-check"></i>
                            <span>Réservations</span>
                            @php
                                $pendingBookingsCount = \App\Models\Booking::where('status', 'pending')->count();
                            @endphp
                            @if($pendingBookingsCount > 0)
                                <span class="badge-notification">{{ $pendingBookingsCount }}</span>
                            @endif
                        </a>
                    </div>
                    
                    
                    

                </div>
                
                
                
                <div class="nav-section">
                    <div class="nav-section-title">Communication</div>
                    
                    <div class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}" href="{{ route('admin.notifications.index') }}">
                            <i class="fas fa-bell"></i>
                            <span>Notifications</span>
                            @php
                                $unreadNotificationsCount = \App\Models\Notification::where('read_at', null)->count();
                            @endphp
                            @if($unreadNotificationsCount > 0)
                                <span class="badge-notification">{{ $unreadNotificationsCount }}</span>
                            @endif
                        </a>
                    </div>
                    
                    <div class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.messages.*') ? 'active' : '' }}" href="{{ route('admin.messages.index') }}">
                            <i class="fas fa-comments"></i>
                            <span>Messages</span>
                            @php
                                $reportedMessagesCount = \App\Models\Message::where('is_reported', true)->where('status', '!=', 'hidden')->count();
                            @endphp
                            @if($reportedMessagesCount > 0)
                                <span class="badge-notification">{{ $reportedMessagesCount }}</span>
                            @endif
                        </a>
                    </div>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Signalements</div>
                    
                    <div class="nav-item">
                        <a class="nav-link {{ request()->routeIs('administrateur.reports.urgent-sales.*') ? 'active' : '' }}" href="{{ route('administrateur.reports.urgent-sales.index') }}">
                            <i class="fas fa-flag"></i>
                            <span>Signalements Ventes</span>
                            @php
                                $pendingUrgentSaleReports = \App\Models\UrgentSaleReport::where('status', 'pending')->count();
                            @endphp
                            @if($pendingUrgentSaleReports > 0)
                                <span class="badge-notification">{{ $pendingUrgentSaleReports }}</span>
                            @endif
                        </a>
                    </div>
                    
                    <div class="nav-item">
                        <a class="nav-link {{ request()->routeIs('administrateur.reports.equipments.*') ? 'active' : '' }}" href="{{ route('administrateur.reports.equipments.index') }}">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Signalements Équipements</span>
                            @php
                                $pendingEquipmentReports = \App\Models\EquipmentReport::where('status', 'pending')->count();
                            @endphp
                            @if($pendingEquipmentReports > 0)
                                <span class="badge-notification">{{ $pendingEquipmentReports }}</span>
                            @endif
                        </a>
                    </div>
                    
                    <div class="nav-item">
                        <a class="nav-link {{ request()->routeIs('administrateur.reports.services.*') ? 'active' : '' }}" href="{{ route('administrateur.reports.services.index') }}">
                            <i class="fas fa-briefcase"></i>
                            <span>Signalements Services</span>
                            @php
                                $pendingServiceReports = \App\Models\ServiceReport::where('status', 'pending')->count();
                            @endphp
                            @if($pendingServiceReports > 0)
                                <span class="badge-notification">{{ $pendingServiceReports }}</span>
                            @endif
                        </a>
                    </div>
                    
                    <div class="nav-item">
                        <a class="nav-link {{ request()->routeIs('administrateur.reports.all.*') ? 'active' : '' }}" href="{{ route('administrateur.reports.all.index') }}">
                            <i class="fas fa-shield-alt"></i>
                            <span>Tous les signalements</span>
                            @php
                                $totalPendingReports = \App\Models\UrgentSaleReport::where('status', 'pending')->count() + \App\Models\EquipmentReport::where('status', 'pending')->count() + \App\Models\ServiceReport::where('status', 'pending')->count();
                            @endphp
                            @if($totalPendingReports > 0)
                                <span class="badge-notification">{{ $totalPendingReports }}</span>
                            @endif
                        </a>
                    </div>
                </div>
                
                <div class="nav-section">
                    <div class="nav-section-title">Analyses & Rapports</div>
                    
                    <div class="nav-item">
                        <a class="nav-link {{ request()->routeIs('administrateur.analytics.*') ? 'active' : '' }}" href="{{ route('administrateur.analytics.dashboard') }}">
                            <i class="fas fa-chart-bar"></i>
                            <span>Rapports</span>
                        </a>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <h1>@yield('page-title', 'Administration')</h1>
                </div>
                <div class="header-right">
                    <div class="user-menu">
                        <div class="user-avatar">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <span>{{ Auth::user()->name }}</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                </div>
            </header>
            
            <!-- Content -->
            <main class="content">
                @yield('content')
            </main>
        </div>
    </div>
    
    <!-- Scripts -->
    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Sidebar toggle for mobile
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const body = document.body;
            
            function toggleSidebar() {
                sidebar.classList.toggle('active');
                sidebarOverlay.classList.toggle('active');
                body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
            }
            
            function closeSidebar() {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
                body.style.overflow = '';
            }
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', toggleSidebar);
            }
            
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', closeSidebar);
            }
            
            // Close sidebar on window resize if screen becomes large
            window.addEventListener('resize', function() {
                if (window.innerWidth > 768) {
                    closeSidebar();
                }
            });
            
            // Close sidebar when clicking on nav links on mobile
            const navLinks = sidebar.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 768) {
                        setTimeout(closeSidebar, 150);
                    }
                });
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>