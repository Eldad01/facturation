<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Application de gestion de facturation">
    <title>@yield('title', 'Facturation')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap5.min.css" rel="stylesheet" />

    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --info-color: #0dcaf0;
            --light-bg: #f8f9fa;
            --dark-text: #212529;
            --card-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            --card-shadow-hover: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        * {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        body {
            background-color: var(--light-bg);
            color: var(--dark-text);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            padding-bottom: 60px;
        }

        /* Navbar Styles - Horizontal Desktop Menu */
        .main-navbar {
            background: #ffffff;
            box-shadow: var(--card-shadow);
            padding: 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
        }

        .navbar-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 0.5rem;
            height: 50px;
            width: 100%;
        }

        @media (max-width: 991.98px) {
            .navbar-container {
                padding: 0 0.75rem;
            }
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1rem;
            color: var(--primary-color) !important;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            padding: 0 1rem;
            height: 50px;
            margin: 0;
        }

        .navbar-brand:hover {
            color: #0a58ca !important;
        }

        .navbar-brand i {
            font-size: 1.5rem;
        }

        /* Horizontal Menu */
        .navbar-menu {
            display: flex;
            align-items: stretch;
            justify-content: flex-start;
            gap: 0;
            height: 50px;
            margin: 0;
            padding: 0;
        }

        .nav-item {
            list-style: none;
            display: flex;
            align-items: stretch;
        }

        .nav-link {
            font-weight: 500;
            font-size: 0.85rem;
            color: var(--secondary-color) !important;
            padding: 0 0.75rem !important;
            border-radius: 0;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            text-decoration: none;
            white-space: nowrap;
            height: 50px;
            border-bottom: 2px solid transparent;
        }
        
        .nav-link i {
            font-size: 1rem;
            line-height: 1;
        }
        
        .nav-link span {
            line-height: 1;
        }

        .nav-link:hover {
            background-color: rgba(13, 110, 253, 0.1);
            color: var(--primary-color) !important;
        }

        .nav-link.active {
            background-color: var(--primary-color);
            color: #ffffff !important;
        }

        /* User Section */
        .navbar-user {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.375rem 0.75rem;
            border-radius: 50rem;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .user-badge.admin {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
        }

        .user-badge.employe {
            background-color: rgba(13, 202, 240, 0.1);
            color: var(--info-color);
        }

        /* Mobile Toggle */
        .navbar-toggler {
            border: none;
            padding: 0.5rem;
            font-size: 1.5rem;
            background: transparent;
            color: var(--secondary-color);
            cursor: pointer;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 0.375rem;
        }

        .navbar-toggler:hover {
            background-color: rgba(0, 0, 0, 0.05);
            color: var(--primary-color);
        }

        .navbar-toggler:focus {
            box-shadow: none;
            outline: none;
        }

        /* Cards */
        .modern-card {
            background: #ffffff;
            border: none;
            border-radius: 0.75rem;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
        }

        .modern-card:hover {
            box-shadow: var(--card-shadow-hover);
        }

        .modern-card .card-header {
            background: transparent;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 1rem 1.25rem;
            font-weight: 600;
        }

        .modern-card .card-body {
            padding: 1.25rem;
        }

        /* Stat Cards */
        .stat-card {
            background: #ffffff;
            border-radius: 0.75rem;
            padding: 1.25rem;
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-color);
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--card-shadow-hover);
        }

        .stat-card.success { border-left-color: var(--success-color); }
        .stat-card.warning { border-left-color: var(--warning-color); }
        .stat-card.danger { border-left-color: var(--danger-color); }
        .stat-card.info { border-left-color: var(--info-color); }

        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-card .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .stat-card .stat-label {
            font-size: 0.875rem;
            color: var(--secondary-color);
            margin-top: 0.25rem;
        }

        /* Buttons */
        .btn {
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-icon {
            width: 38px;
            height: 38px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.5rem;
        }

        /* Tables */
        .table {
            margin-bottom: 0;
        }

        .table thead th {
            font-weight: 600;
            font-size: 0.8125rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--secondary-color);
            border-bottom: 2px solid rgba(0, 0, 0, 0.05);
            padding: 0.75rem 1rem;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 0.875rem 1rem;
            vertical-align: middle;
            border-color: rgba(0, 0, 0, 0.05);
        }

        .table tbody tr {
            transition: background-color 0.2s ease;
        }

        .table tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.02);
        }

        /* Badges */
        .badge {
            font-weight: 500;
            padding: 0.35em 0.65em;
            border-radius: 0.375rem;
        }

        /* Form Controls */
        .form-control, .form-select {
            border-radius: 0.5rem;
            padding: 0.625rem 0.875rem;
            border: 1px solid #dee2e6;
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
        }

        .form-label {
            font-weight: 500;
            font-size: 0.875rem;
            margin-bottom: 0.375rem;
            color: var(--dark-text);
        }

        /* Page Headers */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            color: var(--dark-text);
        }

        .page-subtitle {
            font-size: 0.875rem;
            color: var(--secondary-color);
            margin-top: 0.25rem;
        }

        /* Alert Messages */
        .alert {
            border-radius: 0.5rem;
            border: none;
            padding: 1rem 1.25rem;
        }

        .alert-success {
            background-color: rgba(25, 135, 84, 0.1);
            color: var(--success-color);
        }

        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
        }

        .alert-warning {
            background-color: rgba(255, 193, 7, 0.15);
            color: #997404;
        }

        .alert-info {
            background-color: rgba(13, 202, 240, 0.1);
            color: #055160;
        }

        /* Empty States */
        .empty-state {
            text-align: center;
            padding: 3rem 1.5rem;
            color: var(--secondary-color);
        }

        .empty-state i {
            font-size: 4rem;
            opacity: 0.3;
            margin-bottom: 1rem;
        }

        .empty-state h5 {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 0.25rem;
            flex-wrap: wrap;
        }

        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.8125rem;
        }

        /* Pagination */
        .pagination {
            margin: 0;
        }

        .page-link {
            border-radius: 0.375rem;
            margin: 0 0.125rem;
            font-weight: 500;
        }

        /* Select2 Overrides */
        .select2-container--bootstrap5 .select2-selection {
            border-radius: 0.5rem;
            min-height: 42px;
        }

        .select2-container--bootstrap5 .select2-selection--single .select2-selection__rendered {
            padding: 0.5rem 0.75rem;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }

        /* Mobile Overlay */
        .mobile-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1040;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s, visibility 0.3s;
        }
        
        .mobile-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        /* Mobile Menu Header */
        .navbar-menu-header {
            display: none;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            border-bottom: 1px solid #e0e0e0;
            background: #fff;
        }

        .navbar-menu-title {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
        }

        .navbar-menu-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
            padding: 0.25rem 0.5rem;
            line-height: 1;
        }

        .navbar-menu-close:hover {
            color: #333;
        }

        /* Mobile User Section */
        .mobile-user-section {
            display: none;
            padding: 1rem;
            background: #f8f9fa;
            border-top: 1px solid #e0e0e0;
            margin-top: auto;
        }

        .mobile-user-section .user-info {
            margin-bottom: 1rem;
        }

        /* Prevent body scroll when menu is open */
        body.menu-open {
            overflow: hidden;
        }

        /* Mobile Responsive - Slide-in Sidebar Menu */
        @media (max-width: 991.98px) {
            /* Overlay background */
            .mobile-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 998;
                opacity: 0;
                transition: opacity 0.3s ease;
            }

            .mobile-overlay.show {
                display: block;
                opacity: 1;
            }

            /* Sidebar menu - slides from right */
            .navbar-menu {
                display: block;
                position: fixed;
                top: 0;
                right: -280px;
                width: 280px;
                height: 100vh;
                background: #ffffff;
                padding: 1.5rem 1rem;
                flex-direction: column;
                z-index: 1000;
                transition: right 0.3s ease;
                overflow-y: auto;
                box-shadow: -4px 0 20px rgba(0, 0, 0, 0.15);
            }

            .navbar-menu.show {
                right: 0;
            }

            /* Sidebar header with close button */
            .navbar-menu-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 1.5rem;
                padding-bottom: 1rem;
                border-bottom: 1px solid #e9ecef;
            }

            .navbar-menu-title {
                font-size: 1.1rem;
                font-weight: 700;
                color: #212529;
                margin: 0;
            }

            .navbar-menu-close {
                width: 36px;
                height: 36px;
                border: none;
                background: #f8f9fa;
                border-radius: 50%;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1.25rem;
                color: #6c757d;
                transition: all 0.2s ease;
            }

            .navbar-menu-close:hover {
                background: #e9ecef;
                color: #212529;
            }

            .navbar-menu .nav-item {
                display: block;
                width: 100%;
                margin-bottom: 0.5rem;
            }

            /* Menu links - full width cards */
            .navbar-menu .nav-link {
                display: flex !important;
                align-items: center;
                gap: 0.875rem;
                padding: 0.875rem 1rem !important;
                border-radius: 10px;
                width: 100%;
                text-align: left;
                justify-content: flex-start;
                font-size: 0.95rem;
                height: auto;
                background: #f8f9fa;
                color: #495057 !important;
                border: none;
                transition: all 0.2s ease;
            }

            .navbar-menu .nav-link:hover {
                background: #e7f1ff;
                color: #0d6efd !important;
            }

            .navbar-menu .nav-link i {
                font-size: 1.2rem;
                width: 24px;
                text-align: center;
                color: #6c757d;
                transition: color 0.2s ease;
            }

            .navbar-menu .nav-link:hover i {
                color: #0d6efd;
            }

            /* Active menu item */
            .navbar-menu .nav-link.active {
                background: #0d6efd !important;
                color: #ffffff !important;
                font-weight: 600;
            }

            .navbar-menu .nav-link.active i {
                color: #ffffff !important;
            }

            /* Grouped "Administration" submenu - render inline instead of floating */
            .navbar-menu .dropdown-menu {
                position: static !important;
                inset: auto !important;
                transform: none !important;
                float: none;
                width: 100%;
                margin-top: 0.25rem;
                margin-bottom: 0.5rem;
                background: transparent;
                border: none;
                box-shadow: none;
                padding: 0 0 0 1.5rem;
                display: none;
            }

            .navbar-menu .dropdown-menu.show {
                display: block;
            }

            .navbar-menu .dropdown-item {
                display: flex;
                align-items: center;
                padding: 0.65rem 1rem;
                border-radius: 10px;
                font-size: 0.9rem;
                color: #495057;
                margin-bottom: 0.25rem;
            }

            .navbar-menu .dropdown-item:hover {
                background: #e7f1ff;
                color: #0d6efd;
            }

            .navbar-menu .dropdown-item.active {
                background: #0d6efd !important;
                color: #ffffff !important;
                font-weight: 600;
            }

            .navbar-menu .dropdown-toggle::after {
                margin-left: auto;
            }

            /* User section at bottom of sidebar */
            .navbar-user {
                display: none;
            }

            .navbar-user.show {
                display: flex;
                position: absolute;
                bottom: 0;
                left: 0;
                right: 0;
                background: #ffffff;
                padding: 1rem;
                border-top: 1px solid #e9ecef;
                flex-direction: column;
                gap: 0.75rem;
            }

            .mobile-menu-btn {
                display: flex !important;
            }

            /* Main content when menu is open */
            body.menu-open {
                overflow: hidden;
            }

            /* Show close button on sidebar */
            .navbar-menu .close-btn {
                display: flex;
            }

            footer {
                display: none !important;
            }

            /* Page header mobile */
            .page-header {
                flex-direction: column;
                align-items: stretch;
                gap: 1rem;
            }

            .page-header .btn {
                width: 100%;
                justify-content: center;
            }

            /* Stats cards mobile */
            .stat-card {
                padding: 0.875rem;
            }

            .stat-card .stat-value {
                font-size: 1.25rem;
            }

            .stat-card .stat-icon {
                width: 36px;
                height: 36px;
                font-size: 1rem;
            }

            /* Table mobile improvements */
            .table {
                font-size: 0.8125rem;
            }

            .table thead th,
            .table tbody td {
                padding: 0.5rem 0.375rem;
            }

            /* Form mobile */
            .form-label {
                font-size: 0.8125rem;
            }

            .form-control, .form-select {
                padding: 0.5rem 0.75rem;
                font-size: 0.875rem;
            }

            /* Action buttons mobile */
            .action-buttons {
                gap: 0.125rem;
            }

            .action-buttons .btn {
                padding: 0.25rem 0.375rem;
                font-size: 0.75rem;
            }

            /* Card mobile */
            .modern-card .card-body {
                padding: 0.875rem;
            }

            /* Footer mobile */
            footer {
                font-size: 0.75rem;
                padding: 0.5rem 0;
            }
        }

        /* Extra small screens */
        @media (max-width: 575.98px) {
            .page-title {
                font-size: 1.25rem;
            }

            .btn {
                padding: 0.375rem 0.75rem;
                font-size: 0.875rem;
            }

            .h4, h4 {
                font-size: 1rem;
            }

            .h5, h5 {
                font-size: 0.875rem;
            }
        }

        @media (min-width: 992px) {
            .mobile-menu-btn {
                display: none;
            }

            .navbar-menu,
            .navbar-user {
                display: flex !important;
            }
        }

        @media (max-width: 575.98px) {
            .stat-card {
                padding: 1rem;
            }

            .stat-card .stat-value {
                font-size: 1.5rem;
            }

            .stat-card .stat-icon {
                width: 40px;
                height: 40px;
                font-size: 1.25rem;
            }

            .table-responsive {
                font-size: 0.875rem;
            }
        }

        /* Bootstrap 5.3 Subtle Backgrounds Fix */
        .bg-primary-subtle { background-color: rgba(13, 110, 253, 0.1) !important; }
        .bg-success-subtle { background-color: rgba(25, 135, 84, 0.1) !important; }
        .bg-warning-subtle { background-color: rgba(255, 193, 7, 0.15) !important; }
        .bg-danger-subtle { background-color: rgba(220, 53, 69, 0.1) !important; }
        .bg-info-subtle { background-color: rgba(13, 202, 240, 0.1) !important; }
        .bg-secondary-subtle { background-color: rgba(108, 117, 125, 0.1) !important; }
        
        .text-primary { color: var(--primary-color) !important; }
        .text-success { color: var(--success-color) !important; }
        .text-warning { color: #997404 !important; }
        .text-danger { color: var(--danger-color) !important; }
        .text-info { color: #055160 !important; }
        
        .border-warning { border-color: var(--warning-color) !important; }
        
        /* Dropdown menu improvements */
        .dropdown-menu {
            border: 1px solid rgba(0,0,0,0.1);
            box-shadow: var(--card-shadow-hover);
        }

        /* Admin "Administration" grouped nav dropdown (desktop) */
        .navbar-menu .nav-item.dropdown .dropdown-menu {
            min-width: 200px;
        }

        .navbar-menu .dropdown-item.active {
            background-color: var(--primary-color);
            color: #ffffff !important;
        }
        
        /* Card footer fix */
        .card-footer {
            background-color: #ffffff;
            border-top: 1px solid rgba(0,0,0,0.05);
        }
        
        /* Form check improvements */
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        /* Button group */
        .btn-group .btn {
            border-radius: 0.375rem;
        }
        
        .btn-check:checked + .btn {
            background-color: var(--primary-color);
            color: #fff;
        }

        /* Badge Colors */
        .bg-primary { background-color: #0d6efd !important; color: #fff; }
        .bg-secondary { background-color: #6c757d !important; color: #fff; }
        .bg-success { background-color: #198754 !important; color: #fff; }
        .bg-danger { background-color: #dc3545 !important; color: #fff; }
        .bg-warning { background-color: #ffc107 !important; color: #000; }
        .bg-info { background-color: #0dcaf0 !important; color: #000; }
        .bg-light { background-color: #f8f9fa !important; color: #000; }
        .bg-dark { background-color: #212529 !important; color: #fff; }

        /* Nav Tabs */
        .nav-tabs {
            border-bottom: 2px solid rgba(0,0,0,0.05);
        }
        
        .nav-tabs .nav-link {
            color: #495057;
            border: none;
            border-bottom: 2px solid transparent;
            padding: 0.75rem 1rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .nav-tabs .nav-link:hover {
            border-color: transparent;
            color: var(--primary-color);
        }
        
        .nav-tabs .nav-link.active {
            background: transparent;
            border: none;
            border-bottom: 2px solid var(--primary-color);
            color: var(--primary-color) !important;
            font-weight: 600;
        }

        /* Modal Improvements */
        .modal-header {
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 1rem 1.25rem;
        }
        
        .modal-footer {
            border-top: 1px solid rgba(0,0,0,0.05);
            padding: 1rem 1.25rem;
        }
        
        .modal-content {
            border: none;
            border-radius: 0.75rem;
            box-shadow: var(--card-shadow-hover);
        }

        /* Navbar Logo */
        .navbar-logo-img {
            max-height: 28px;
            width: auto;
        }

        /* Dropdown items */
        .dropdown-item:hover {
            background-color: rgba(13, 110, 253, 0.1);
        }

        /* Form switch */
        .form-switch .form-check-input {
            width: 2.5em;
            margin-left: -2.5em;
        }
    </style>

    @stack('head')
</head>
<body>

    {{-- Navbar --}}
    <nav class="main-navbar">
        <div class="navbar-container">
            {{-- Logo & Brand --}}
            <a class="navbar-brand" href="{{ auth()->check() ? (auth()->user()->isAdmin() ? route('dashboard') : route('dashboard.employe')) : url('/') }}">
                @if($app_settings?->logo)
                    <img src="{{ asset('logos/'.$app_settings->logo) }}" class="navbar-logo-img me-2" alt="Logo">
                @else
                    <i class="bi bi-receipt"></i>
                @endif
                <span>{{ $app_settings?->company_name ?? 'Facturation' }}</span>
            </a>

            {{-- Mobile Toggle Button --}}
            <button class="navbar-toggler mobile-menu-btn" type="button" onclick="toggleMobileMenu()">
                <i class="bi bi-list"></i>
            </button>

            {{-- Horizontal Navigation Menu --}}
            @auth
            <!-- Mobile Overlay -->
            <div class="mobile-overlay" id="mobileOverlay" onclick="closeMobileMenu()"></div>
            
            <!-- Sidebar Menu -->
            <ul class="navbar-menu" id="mainMenu">
                <div class="navbar-menu-header">
                    <h5 class="navbar-menu-title">Menu</h5>
                    <button class="navbar-menu-close" onclick="closeMobileMenu()">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard*') ? 'active' : '' }}" 
                       href="{{ auth()->user()->isAdmin() ? route('dashboard') : route('dashboard.employe') }}">
                        <i class="bi bi-grid-1x2"></i>
                        <span>Tableau de bord</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('clients*') ? 'active' : '' }}" 
                       href="{{ route('clients.index') }}">
                        <i class="bi bi-people"></i>
                        <span>Clients</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('factures*') || request()->routeIs('devis*') ? 'active' : '' }}"
                       href="{{ route('factures.index') }}">
                        <i class="bi bi-file-text"></i>
                        <span>Factures</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('produits*') ? 'active' : '' }}"
                       href="{{ route('produits.index') }}">
                        <i class="bi bi-box-seam"></i>
                        <span>Produits</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('inventaires*') ? 'active' : '' }}"
                       href="{{ route('inventaires.index') }}">
                        <i class="bi bi-clipboard-check"></i>
                        <span>Inventaire</span>
                    </a>
                </li>

                @if(auth()->user()->isAdmin())
                @php
                    $adminSectionActive = request()->routeIs('historique*') || request()->routeIs('rapports*') || request()->routeIs('settings*');
                @endphp
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ $adminSectionActive ? 'active' : '' }}"
                       href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-gear-wide-connected"></i>
                        <span>Administration</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('historique*') ? 'active' : '' }}"
                               href="{{ route('historique.index') }}">
                                <i class="bi bi-clock-history me-2"></i>Historique
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('rapports*') ? 'active' : '' }}"
                               href="{{ route('rapports.index') }}">
                                <i class="bi bi-bar-chart me-2"></i>Rapports
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('settings*') ? 'active' : '' }}"
                               href="{{ route('settings.edit') }}">
                                <i class="bi bi-gear me-2"></i>Parametres
                            </a>
                        </li>
                    </ul>
                </li>
                @endif

                <!-- Mobile User Section (inside sidebar) -->
                <div class="mobile-user-section">
                    <div class="user-info">
                        <span class="user-badge {{ auth()->user()->isAdmin() ? 'admin' : 'employe' }}">
                            <i class="bi bi-person-circle"></i>
                            {{ auth()->user()->name }}
                            @if(auth()->user()->isAdmin())
                                <i class="bi bi-shield-check"></i>
                            @endif
                        </span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="bi bi-box-arrow-right me-2"></i>
                            Deconnexion
                        </button>
                    </form>
                </div>
            </ul>

            {{-- User Menu (shown on desktop) --}}
            <div class="navbar-user" id="userMenu">
                <span class="user-badge {{ auth()->user()->isAdmin() ? 'admin' : 'employe' }}">
                    <i class="bi bi-person-circle"></i>
                    {{ auth()->user()->name }}
                    @if(auth()->user()->isAdmin())
                        <i class="bi bi-shield-check"></i>
                    @endif
                </span>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-icon" title="Deconnexion">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
            @endauth
        </div>
    </nav>

    {{-- Mobile Menu Script --}}
    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mainMenu');
            const overlay = document.getElementById('mobileOverlay');
            menu.classList.toggle('show');
            overlay.classList.toggle('show');
            document.body.classList.toggle('menu-open');
        }

        function closeMobileMenu() {
            const menu = document.getElementById('mainMenu');
            const overlay = document.getElementById('mobileOverlay');
            menu.classList.remove('show');
            overlay.classList.remove('show');
            document.body.classList.remove('menu-open');
        }

        // Close menu on resize to desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth > 991.98) {
                closeMobileMenu();
            }
        });
    </script>

    {{-- Main Content --}}
    <main class="py-4" style="margin-top: 50px;">
        <div class="container">
            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="alert alert-success d-flex align-items-center" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                    <div>{{ session('warning') }}</div>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info d-flex align-items-center" role="alert">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <div>{{ session('info') }}</div>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    {{-- Footer --}}
    <footer class="fixed-bottom bg-white py-2 border-top">
        <div class="container">
            <div class="text-center text-muted small mb-0">
                {{ $app_settings?->company_name ?? 'Wels' }} &copy; {{ date('Y') }}
            </div>
        </div>
    </footer>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/fr.js"></script>

    <!-- Auto-hide Flash Messages -->
    <script>
        $(document).ready(function() {
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 5000);

            // Initialize Select2
            $('.select2').select2({
                theme: 'bootstrap-5',
                language: 'fr',
                placeholder: 'Selectionnez...'
            });
        });
    </script>

    @stack('scripts')

</body>
</html>
