<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @php
        use Illuminate\Support\Str;
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, shrink-to-fit=no">
    <meta name="theme-color" content="#4070f4">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#317EFB"/>
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('{{ asset('serviceworker.js') }}', { scope: '/' });
        }
    </script>


    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    @stack('styles')

    <style>
        :root {
            /* Base colors - Light mode */
            --primary-color: #4070f4;
            --text-color: #333;
            --text-muted: #666;
            --text-light: #888;
            --bg-color: #f0f5ff;
            --bg-card: #fff;
            --bg-sidebar: linear-gradient(180deg, #fff 0%, #fafbff 100%);
            --bg-navbar: #fff;
            --bg-input: #f5f5f5;
            --bg-hover: rgba(64, 112, 244, 0.08);
            --bg-active: rgba(64, 112, 244, 0.12);
            --border-color: rgba(0, 0, 0, 0.05);
            --shadow-color: rgba(0, 0, 0, 0.1);
            --shadow-color-darker: rgba(0, 0, 0, 0.2);

            /* Layout */
            --sidebar-width: 260px;
            --sidebar-collapsed-width: 80px;
            --header-height: 60px;
            --body-font: 'Nunito', sans-serif;
            --transition-speed: 0.3s;

            /* Touch-friendly variables */
            --min-touch-target: 44px;
            --content-padding-mobile: 15px;
            --content-padding-desktop: 20px;
        }



        body {
            font-family: var(--body-font);
            background-color: var(--bg-color);
            overflow-x: hidden;
            color: var(--text-color);
            transition: color 0.3s ease;
        }

        #app {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100%;
            width: var(--sidebar-width);
            background: var(--bg-sidebar);
            z-index: 100;
            transition: all var(--transition-speed) ease;
            box-shadow: 0 0 10px var(--shadow-color);
            display: flex;
            flex-direction: column;
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        .sidebar-header {
            height: var(--header-height);
            display: flex;
            align-items: center;
            padding: 0 15px;
            border-bottom: 1px solid var(--border-color);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .sidebar-content {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: thin;
            scrollbar-color: var(--border-color) transparent;
            padding-bottom: 60px; /* Space for collapse button */
        }

        .sidebar-content::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-content::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-content::-webkit-scrollbar-thumb {
            background-color: var(--border-color);
            border-radius: 4px;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
        }

        .logo-image {
            height: 40px;
            width: auto;
            object-fit: contain;
            margin-right: 10px;
            transition: height var(--transition-speed) ease;
        }

        .sidebar.collapsed .sidebar-logo {
            justify-content: center;
        }

        .sidebar.collapsed .logo-image {
            height: 35px;
            margin-right: 0;
        }

        .sidebar-logo .logo-name {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary-color);
            white-space: nowrap;
            letter-spacing: -0.5px;
        }

        .sidebar.collapsed .logo-name {
            display: none;
        }

        .sidebar-section {
            margin-top: 20px;
            padding: 0 15px;
        }

        .sidebar-section-title {
            color: var(--text-light);
            font-size: 12px;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 10px;
            padding-left: 10px;
            white-space: nowrap;
            letter-spacing: 1px;
        }

        .sidebar.collapsed .sidebar-section-title {
            display: none;
        }

        .sidebar-nav {
            padding: 0;
            margin: 0;
        }

        .sidebar-nav-item {
            list-style: none;
            position: relative;
        }

        .sidebar-nav-link {
            display: flex;
            align-items: center;
            color: var(--text-muted);
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 5px;
            transition: all 0.3s;
            text-decoration: none;
            position: relative;
            overflow: hidden;
            min-height: var(--min-touch-target); /* Ensure proper touch target size */
        }

        .sidebar-nav-link:hover,
        .sidebar-nav-link.active {
            background: var(--bg-hover);
            color: var(--primary-color);
        }

        .sidebar-nav-link.active {
            background: var(--bg-active);
            font-weight: 600;
        }

        .sidebar-nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: var(--primary-color);
            border-radius: 0 2px 2px 0;
        }

        .sidebar-nav-link i {
            font-size: 20px;
            min-width: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s ease;
        }

        .sidebar-nav-link:hover i {
            transform: translateX(3px);
        }

        .sidebar-nav-link span {
            white-space: nowrap;
            opacity: 1;
            transition: opacity 0.3s ease;
            font-size: 15px; /* Slightly reduced for cleaner look */
            letter-spacing: 0.2px; /* Better readability */
        }

        .sidebar.collapsed .sidebar-nav-link span {
            display: none;
        }

        .sidebar.collapsed .sidebar-nav-link i {
            min-width: 60px;
        }

        .sidebar.collapsed .sidebar-nav-link.active::before {
            width: 3px;
        }

        .sidebar-nav-link .badge {
            margin-left: auto;
            padding: 0.25em 0.65em;
            font-size: 11px;
            font-weight: 600;
            border-radius: 10px;
        }

        .sidebar-nav-link .badge-primary {
            background: var(--primary-color);
            color: white;
        }

        .sidebar-collapse-btn {
            position: fixed;
            bottom: 20px;
            left: calc(var(--sidebar-width) - 25px);
            height: 40px;
            width: 40px;
            background: var(--bg-card);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            box-shadow: 0 0 10px var(--shadow-color);
            cursor: pointer;
            transition: all var(--transition-speed) ease;
            z-index: 101;
        }

        .sidebar.collapsed .sidebar-collapse-btn {
            left: calc(var(--sidebar-collapsed-width) - 20px);
        }

        .sidebar-collapse-btn i {
            transition: all 0.3s ease;
            color: var(--primary-color);
        }

        .sidebar.collapsed .sidebar-collapse-btn i {
            transform: rotate(180deg);
        }

        /* Tooltip for collapsed sidebar */
        .sidebar.collapsed .sidebar-nav-link {
            position: relative;
        }

        .sidebar.collapsed .sidebar-nav-link:hover:after {
            content: attr(data-title);
            position: absolute;
            left: 70px;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.7);
            color: #fff;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 99;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            transition: all var(--transition-speed) ease;
        }

        .sidebar.collapsed ~ .main-content {
            margin-left: var(--sidebar-collapsed-width);
        }

        /* Navbar Styles */
        .main-navbar {
            height: var(--header-height);
            background: var(--bg-navbar);
            display: flex;
            align-items: center;
            padding: 0 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            z-index: 99;
            transition: all 0.3s ease;
        }

        .page-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-color);
            margin: 0 20px;
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            transition: color 0.3s ease;
        }

        .navbar-search {
            flex: 1;
            max-width: 400px;
            margin: 0 20px;
            position: relative;
        }

        .navbar-search input {
            width: 100%;
            height: 40px;
            padding: 0 15px;
            padding-left: 45px;
            font-size: 14px;
            border: none;
            border-radius: 20px;
            background: var(--bg-input);
            outline: none;
            color: var(--text-color);
            transition: all 0.3s ease;
        }

        .navbar-search i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
        }

        .navbar-menu {
            display: flex;
            align-items: center;
            margin-left: auto;
        }

        .navbar-menu-item {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            margin-left: 10px;
            border-radius: 50%;
            cursor: pointer;
            color: var(--text-muted);
            transition: all 0.3s;
        }

        .navbar-menu-item:hover {
            background: var(--bg-input);
            color: var(--primary-color);
        }

        .navbar-menu-item i {
            font-size: 20px;
        }

        .navbar-user {
            margin-left: 15px;
            display: flex;
            align-items: center;
        }

        .navbar-user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            transition: border-color 0.3s ease;
        }

        .navbar-user-avatar:hover {
            border-color: var(--primary-color);
        }

        .navbar-user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .navbar-toggler {
            display: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: transparent;
            border: none;
            cursor: pointer;
            color: var(--text-muted);
        }

        /* Mobile Improvements */
        /* Tablet and Mobile Responsive Styles */
        @media (max-width: 991px) {
            .page-content {
                padding: 15px;
            }

            .main-navbar {
                padding: 0 15px;
            }

            .page-title {
                font-size: 16px;
                margin: 0 10px;
            }

            a, button, .sidebar-nav-link, .navbar-menu-item, .notification-item,
            .dropdown-item, .navbar-toggler, .notification-close {
                -webkit-tap-highlight-color: transparent;
                touch-action: manipulation;
            }

            /* Remove hover effects that might cause delay */
            .sidebar-nav-link:hover i {
                transform: none;
            }

            /* Adjust padding for better finger touch */
            .dropdown-item {
                padding: 10px 20px;
            }
        }

        /* Mobile Specific Styles */
        @media (max-width: 768px) {
            .sidebar {
                left: -100%;
                box-shadow: none;
                width: 85%;
                max-width: 320px;
                z-index: 1001; /* Ensure sidebar is above other elements */
            }

            .sidebar.active {
                left: 0;
                box-shadow: 0 0 20px var(--shadow-color-darker);
            }

            .sidebar-backdrop {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1000;
                backdrop-filter: blur(3px);
                -webkit-backdrop-filter: blur(3px);
            }

            .sidebar-backdrop.active {
                display: block;
            }

            .main-content {
                margin-left: 0 !important;
                width: 100%;
            }

            .navbar-toggler {
                display: flex;
                align-items: center;
                justify-content: center;
                margin-right: 15px;
                min-width: 40px;
                min-height: 40px; /* Ensure adequate touch target size */
            }

            .navbar-menu-item {
                min-width: 40px;
                min-height: 40px; /* Ensure adequate touch target size */
                margin-left: 5px; /* Reduce margin to fit better on small screens */
            }

            .navbar-search {
                display: none;
            }

            .navbar-search.active {
                display: block;
                position: absolute;
                top: 70px;
                left: 15px;
                right: 15px;
                max-width: none;
                z-index: 98;
            }

            .sidebar-collapse-btn {
                display: none;
            }

            /* Optimize navbar for smaller screens */
            .navbar-user-avatar {
                width: 32px;
                height: 32px;
            }

            /* Ensure dropdown menus fit within viewport */
            .dropdown-menu {
                position: fixed;
                right: 10px;
                left: auto;
                top: var(--header-height);
                max-width: calc(100vw - 20px);
            }
        }

        /* Extra Small Devices */
        @media (max-width: 575px) {
            :root {
                --content-padding-mobile: 10px;
            }

            .page-content {
                padding: var(--content-padding-mobile);
            }

            .main-navbar {
                height: 50px;
                padding: 0 10px;
            }

            .page-title {
                font-size: 15px;
                max-width: 150px;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            /* Further optimize for very small screens */
            .notification-sidebar {
                width: 100%;
                max-width: 100%;
                right: -100%;
            }

            .navbar-user-avatar {
                width: 30px;
                height: 30px;
            }

            /* Stack items in card headers on mobile */
            .card-header {
                flex-direction: column;
                align-items: flex-start !important;
            }

            .card-header .btn-group,
            .card-header .card-tools {
                margin-top: 10px;
                width: 100%;
                display: flex;
                justify-content: flex-start;
            }

            /* Make form elements more touch-friendly */
            input, select, textarea, .form-control, .btn {
                font-size: 16px !important; /* Prevents iOS zoom on focus */
                min-height: 44px;
            }

            /* Improve table display on very small screens */
            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }

            .table-responsive {
                border: 0;
                margin-bottom: 0;
            }
        }

        /* Page Content Styles */
        .page-content {
            padding: var(--content-padding-desktop);
            transition: padding 0.3s ease;
        }

        /* Cards in Dark Mode */
        .card {
            background-color: var(--bg-card);
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px var(--shadow-color);
            transition: box-shadow 0.3s ease;
        }

        .card-header {
            background-color: var(--bg-card);
            border-bottom: 1px solid var(--border-color);
            transition: border-color 0.3s ease;
        }

        /* Dropdown Styles */
        .dropdown-menu {
            border: none;
            box-shadow: 0 5px 15px var(--shadow-color);
            border-radius: 8px;
            padding: 10px 0;
            background-color: var(--bg-card);
            transition: all 0.3s ease;
        }

        .dropdown-item {
            padding: 8px 20px;
            color: var(--text-muted);
            transition: all 0.2s;
        }

        .dropdown-item:hover {
            background-color: var(--bg-hover);
            color: var(--primary-color);
        }

        /* Dropdown Dividers */
        .dropdown-divider {
            border-top: 1px solid var(--border-color);
            transition: border-color 0.3s ease;
        }

        /* Fix for Bootstrap dropdown */
        .dropdown-toggle::after {
            margin-left: 8px;
        }

        /* Alert styles with dark mode compatibility */
        .alert {
            border: none;
            box-shadow: 0 2px 10px var(--shadow-color);
            border-radius: 8px;
        }



        /* Add notification sidebar styles */
        .notification-sidebar {
            position: fixed;
            top: 0;
            right: -320px;
            width: 320px;
            height: 100%;
            background: var(--bg-card);
            z-index: 1000;
            transition: all var(--transition-speed) ease;
            box-shadow: -5px 0 15px var(--shadow-color);
            overflow-y: auto;
        }

        .notification-sidebar.active {
            right: 0;
        }

        .notification-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .notification-header h4 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: var(--text-color);
        }

        .notification-close {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: var(--bg-hover);
            cursor: pointer;
            color: var(--text-muted);
            transition: all 0.2s;
        }

        .notification-close:hover {
            background: var(--bg-active);
            color: var(--primary-color);
        }

        .notification-list {
            padding: 0;
            margin: 0;
            list-style: none;
        }

        .notification-item {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            transition: background-color 0.2s;
            cursor: pointer;
        }

        .notification-item:hover {
            background-color: var(--bg-hover);
        }

        .notification-item.unread {
            position: relative;
        }

        .notification-item.unread::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 3px;
            background: var(--primary-color);
        }

        .notification-content {
            display: flex;
            align-items: flex-start;
        }

        .notification-icon {
            width: 40px;
            height: 40px;
            min-width: 40px;
            border-radius: 50%;
            background: var(--bg-hover);
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }

        .notification-text {
            flex: 1;
        }

        .notification-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-color);
            margin: 0 0 5px;
        }

        .notification-desc {
            font-size: 13px;
            color: var(--text-muted);
            margin: 0;
        }

        .notification-time {
            font-size: 11px;
            color: var(--text-light);
            margin-top: 5px;
            display: block;
        }

        .notification-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
        }

        .notification-backdrop.active {
            display: block;
        }

        .notification-badge {
            position: absolute;
            top: 5px;
            right: 5px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #ff3e3e;
            border: 2px solid var(--bg-navbar);
        }

        /* Timeline Styles */
        .timeline {
            position: relative;
        }

        .timeline-item {
            display: flex;
            position: relative;
        }

        .timeline-item:not(:last-child)::after {
            content: '';
            position: absolute;
            left: 14px;
            top: 30px;
            height: calc(100% - 10px);
            width: 2px;
            background-color: #e9ecef;
        }

        .timeline-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        /* Reply item styles */
        .reply-item {
            padding: 15px;
            border-radius: 8px;
            background-color: var(--bg-input);
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .reply-item:hover {
            box-shadow: 0 2px 8px var(--shadow-color);
        }

        .reply-content {
            margin-top: 8px;
            white-space: pre-line;
        }

        .reply-attachment {
            margin-top: 10px;
        }

        /* Enhanced sidebar responsiveness */
        .sidebar {
            scrollbar-width: thin;
            scrollbar-color: var(--border-color) transparent;
        }

        .sidebar::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background-color: var(--border-color);
            border-radius: 4px;
        }

        /* Hide collapse elements on desktop */
        .sidebar-collapse-btn,
        #collapse-btn {
            display: none;
        }

        /* Mobile-specific sidebar styles */
        @media (min-width: 769px) {
            .main-content {
                margin-left: var(--sidebar-width) !important;
                transition: none;
            }

            .sidebar {
                width: var(--sidebar-width) !important;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                left: -100%;
                box-shadow: none;
                width: 85%;
                max-width: 320px;
                z-index: 1001;
            }

            .sidebar.active {
                left: 0;
                box-shadow: 0 0 20px var(--shadow-color-darker);
            }

            .navbar-search {
                max-width: none;
                margin: 0 10px;
            }

            .notification-sidebar {
                width: 85%;
                max-width: 320px;
            }

            .main-content {
                margin-left: 0 !important;
                transition: none;
                width: 100%;
            }

            .navbar-menu-item {
                width: 40px;
                height: 40px;
                margin-left: 5px;
            }

            /* Show mobile-specific collapse button */
            #collapse-btn {
                display: flex;
            }

            /* Improve notification display on mobile */
            .notification-item {
                padding: 12px 15px;
            }

            .notification-icon {
                width: 36px;
                height: 36px;
                min-width: 36px;
            }

            /* Ensure alerts fit mobile screens */
            .alert {
                margin-bottom: 15px;
                padding: 10px 15px;
            }

            /* Improve card display on mobile */
            .card {
                margin-bottom: 15px;
            }

            .card-header {
                padding: 12px 15px;
            }

            .card-body {
                padding: 15px;
            }
        }

        /* Toggle button animation */
        .navbar-menu-item.has-badge {
            position: relative;
        }

        .notification-toggle {
            position: relative;
        }
    </style>
</head>
<body>
    <div id="app">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <img src="{{ asset('images/app_logo/logo.png') }}" alt="{{ config('app.name') }} Logo" class="logo-image">
                    <span class="logo-name">{{ config('app.name', 'Laravel') }}</span>
                </div>
            </div>
            <!-- Sidebar -->
            @include('layouts.partials.sidebar')
        </div>

        <!-- Sidebar Backdrop for mobile -->
        <div class="sidebar-backdrop"></div>

        <!-- Navbar -->
        @include('layouts.partials.navbar')

        <!-- Toast Container for Notifications -->
        <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
            <div id="notification-toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header">
                    <i class="fas fa-bell me-2 text-primary"></i>
                    <strong class="me-auto" id="toast-title">New Notification</strong>
                    <small id="toast-time">just now</small>
                    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body" id="toast-body">
                    You have a new notification.
                </div>
            </div>
        </div>

        <!-- Notification Sidebar -->
        <div class="notification-sidebar">
            <div class="notification-header">
                <h4>Notifications</h4>
                <div class="notification-close" id="notification-close">
                    <i class="fas fa-times"></i>
                </div>
            </div>
            <div class="d-flex justify-content-end p-2 border-bottom">
                <a href="#" id="mark-all-as-read" class="btn btn-sm btn-link">Mark all as read</a>
            </div>
            <ul class="notification-list" id="notification-list">
                <!-- Notifications will be dynamically inserted here -->
                <li class="notification-item-placeholder p-4 text-center text-muted">
                    No notifications yet.
                </li>
            </ul>
        </div>
        <div class="notification-backdrop"></div>

        <!-- Sidebar Collapse Button -->
        <div class="sidebar-collapse-btn" id="sidebar-collapse-btn">
            <i class="fas fa-chevron-left"></i>
        </div>
    </div>

    <!-- Add Application JS -->
    <script>
        // Prevent scrolling when mobile navigation is open
        function preventScroll(prevent) {
            document.body.style.overflow = prevent ? 'hidden' : '';
        }

        // Helper function to detect touch-enabled devices
        function isTouchDevice() {
            return ('ontouchstart' in window) || (navigator.maxTouchPoints > 0) || (navigator.msMaxTouchPoints > 0);
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Add touch class to body if on touch device
            if (isTouchDevice()) {
                document.body.classList.add('touch-device');
            }

            // Sidebar functionality
            const sidebar = document.querySelector('.sidebar');
            const sidebarCollapseBtn = document.getElementById('sidebar-collapse-btn');
            const collapseBtn = document.getElementById('collapse-btn');
            const sidebarBackdrop = document.querySelector('.sidebar-backdrop');

            // Add passive event listeners for better scroll performance
            const passiveIfSupported = {
                passive: true,
                capture: false
            };

            // Debounce function for performance optimization
            function debounce(func, wait) {
                let timeout;
                return function() {
                    const context = this;
                    const args = arguments;
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(context, args), wait);
                };
            }

            // Optimize resize event with debounce
            const handleResize = debounce(() => {
                if (!isMobile() && sidebar.classList.contains('active')) {
                    sidebar.classList.remove('active');
                    sidebarBackdrop.classList.remove('active');
                    preventScroll(false);
                }

                // Remove collapsed class on desktop
                if (!isMobile()) {
                    sidebar.classList.remove('collapsed');
                }
            }, 150);

            // Mobile-only: Check if device is mobile
            const isMobile = () => window.innerWidth <= 768;

            // Prevent sidebar collapse on desktop
            if (sidebarCollapseBtn) {
                sidebarCollapseBtn.addEventListener('click', () => {
                    if (isMobile()) {
                        sidebar.classList.toggle('active');
                        sidebarBackdrop.classList.toggle('active');
                        preventScroll(sidebar.classList.contains('active'));
                    }
                });
            }

            if (collapseBtn) {
                collapseBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (isMobile()) {
                        sidebar.classList.remove('active');
                        sidebarBackdrop.classList.remove('active');
                        preventScroll(false);
                    }
                });
            }

            // Mobile sidebar toggle
            const sidebarToggle = document.getElementById('sidebar-toggle');

            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('active');
                sidebarBackdrop.classList.toggle('active');
                preventScroll(sidebar.classList.contains('active'));
            });

            // Close sidebar when clicking outside on mobile
            sidebarBackdrop.addEventListener('click', () => {
                sidebar.classList.remove('active');
                sidebarBackdrop.classList.remove('active');
                preventScroll(false);
            });

            // Handle window resize
            window.addEventListener('resize', handleResize, passiveIfSupported);

            // Check initial state on page load
            if (!isMobile()) {
                sidebar.classList.remove('collapsed');
            }

            // Notification sidebar functionality
            const notificationToggle = document.getElementById('notification-toggle');
            const notificationClose = document.getElementById('notification-close');
            const notificationSidebar = document.querySelector('.notification-sidebar');
            const notificationBackdrop = document.querySelector('.notification-backdrop');

            notificationToggle.addEventListener('click', () => {
                notificationSidebar.classList.toggle('active');
                notificationBackdrop.classList.toggle('active');
                preventScroll(notificationSidebar.classList.contains('active'));
            });

            notificationClose.addEventListener('click', () => {
                notificationSidebar.classList.remove('active');
                notificationBackdrop.classList.remove('active');
                preventScroll(false);
            });

            notificationBackdrop.addEventListener('click', () => {
                notificationSidebar.classList.remove('active');
                notificationBackdrop.classList.remove('active');
                preventScroll(false);
            });



            // Active link highlighting
            const currentPath = window.location.pathname;
            document.querySelectorAll('.sidebar-nav-link').forEach(link => {
                const href = link.getAttribute('href');
                if (href && href !== '#' && currentPath.includes(href)) {
                    link.classList.add('active');
                }
            });

            // Make notification items clickable
            document.querySelectorAll('.notification-item').forEach(item => {
                item.addEventListener('click', function() {
                    this.classList.remove('unread');
                    // Here you would typically make an AJAX call to mark the notification as read
                });
            });

            // Add fastclick to eliminate 300ms delay on mobile browsers
            if (isTouchDevice()) {
                document.querySelectorAll('a, button, .sidebar-nav-link, .navbar-menu-item, .dropdown-toggle, .notification-item')
                    .forEach(el => {
                        el.addEventListener('touchstart', function() {}, passiveIfSupported);
                    });
            }

            // Add support for swipe to close sidebars on mobile
            if (isTouchDevice()) {
                let touchStartX = 0;
                let touchEndX = 0;

                // For main sidebar (swipe left to close)
                sidebar.addEventListener('touchstart', e => {
                    touchStartX = e.changedTouches[0].screenX;
                }, passiveIfSupported);

                sidebar.addEventListener('touchend', e => {
                    touchEndX = e.changedTouches[0].screenX;
                    if (touchStartX - touchEndX > 50) { // Swipe left threshold
                        sidebar.classList.remove('active');
                        sidebarBackdrop.classList.remove('active');
                        preventScroll(false);
                    }
                }, passiveIfSupported);

                // For notification sidebar (swipe right to close)
                const notificationSidebar = document.querySelector('.notification-sidebar');
                notificationSidebar.addEventListener('touchstart', e => {
                    touchStartX = e.changedTouches[0].screenX;
                }, passiveIfSupported);

                notificationSidebar.addEventListener('touchend', e => {
                    touchEndX = e.changedTouches[0].screenX;
                    if (touchEndX - touchStartX > 50) { // Swipe right threshold
                        notificationSidebar.classList.remove('active');
                        document.querySelector('.notification-backdrop').classList.remove('active');
                        preventScroll(false);
                    }
                }, passiveIfSupported);
            }
        });
    </script>

    @auth
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const userId = {{ Auth::id() }};
        const notificationList = document.getElementById('notification-list');
        const notificationToggle = document.getElementById('notification-toggle');
        const notificationBadge = notificationToggle.querySelector('.notification-badge');
        const markAllAsReadBtn = document.getElementById('mark-all-as-read');
        const placeholder = document.querySelector('.notification-item-placeholder');

        /**
         * Formats a date string into a human-readable "time ago" format.
         * @param {string} dateString - The ISO date string to format.
         * @returns {string} The formatted time ago string.
         */
        function formatTimeAgo(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString);
            const now = new Date();
            const seconds = Math.floor((now - date) / 1000);
            if (seconds < 10) return "just now";
            let interval = seconds / 31536000;
            if (interval > 1) return Math.floor(interval) + " years ago";
            interval = seconds / 2592000;
            if (interval > 1) return Math.floor(interval) + " months ago";
            interval = seconds / 86400;
            if (interval > 1) return Math.floor(interval) + " days ago";
            interval = seconds / 3600;
            if (interval > 1) return Math.floor(interval) + " hours ago";
            interval = seconds / 60;
            if (interval > 1) return Math.floor(interval) + " minutes ago";
            return Math.floor(seconds) + " seconds ago";
        }

        /**
         * Creates a notification list item from a notification object.
         * Handles missing data gracefully to prevent "undefined" in the UI.
         * @param {object} notification - The notification object from the API or Echo.
         * @returns {HTMLElement} The created list item element.
         */
        function createNotificationItem(notification) {
            const item = document.createElement('li');
            item.classList.add('notification-item');
            if (!notification.read_at) {
                item.classList.add('unread');
            }
            item.dataset.id = notification.id;

            // Safely access notification data with fallbacks
            const data = notification.data || {};
            const url = data.url || '#';
            const title = data.title || 'Notification';
            const body = data.body || 'You have a new notification.';
            const type = data.type || 'default';

            // Define icons for each notification type
            const iconMap = {
                instruction_assigned: 'fa-file-alt',
                instruction_replied: 'fa-reply',
                instruction_forwarded: 'fa-share',
                instruction_deadline_reminder: 'fa-clock',
                instruction_forwarded_to_sender: 'fa-paper-plane',
                default: 'fa-bell'
            };
            const icon = iconMap[type] || 'fa-bell';

            item.innerHTML = `
                <div class="notification-content">
                    <div class="notification-icon"><i class="fas ${icon}"></i></div>
                    <div class="notification-text">
                        <h5 class="notification-title">${title}</h5>
                        <p class="notification-desc">${body}</p>
                        <span class="notification-time">${formatTimeAgo(notification.created_at)}</span>
                    </div>
                </div>
            `;

            // Make the item clickable only if a valid URL is provided
            if (url && url !== '#') {
                item.dataset.url = url;
                item.style.cursor = 'pointer';
                item.addEventListener('click', () => markAsRead(notification.id, url));
            }

            return item;
        }

        /**
         * Adds a new notification to the top of the list.
         * @param {object} notification - The new notification to add.
         */
        function prependNotification(notification) {
            if (placeholder) {
                placeholder.style.display = 'none';
            }
            const newItem = createNotificationItem(notification);
            notificationList.prepend(newItem);
            updateUnreadCountBadge(true); // Increment unread count
        }

        /**
         * Fetches all notifications from the API and populates the list.
         */
        function fetchNotifications() {
            axios.get('{{ route("api.notifications.index") }}')
                .then(response => {
                    const notifications = response.data.data;
                    notificationList.innerHTML = ''; // Clear current list
                    if (notifications && notifications.length > 0) {
                        if (placeholder) placeholder.style.display = 'none';
                        notifications.forEach(notification => {
                            notificationList.appendChild(createNotificationItem(notification));
                        });
                    } else {
                        if (placeholder) placeholder.style.display = 'block';
                    }
                    fetchUnreadCount();
                })
                .catch(error => {
                    console.error('Error fetching notifications:', {
                        message: error.message,
                        status: error.response ? error.response.status : 'N/A',
                        url: error.config ? error.config.url : 'N/A',
                        response: error.response ? error.response.data : 'N/A'
                    });
                    if (placeholder) {
                        placeholder.textContent = 'Could not load notifications.';
                        placeholder.style.display = 'block';
                    }
                });
        }

        /**
         * Fetches the count of unread notifications and updates the badge.
         */
        function fetchUnreadCount() {
            axios.get('{{ route("api.notifications.unread") }}')
                .then(response => {
                    updateUnreadCountBadge(false, response.data.count);
                })
                .catch(error => console.error('Error fetching unread count:', error));
        }

        /**
         * Updates the visibility of the unread notification badge.
         * @param {boolean} isNew - True if this is a new notification, false otherwise.
         * @param {number} [count=0] - The total number of unread notifications.
         */
        function updateUnreadCountBadge(isNew, count = 0) {
            let showBadge = false;
            if (isNew) {
                showBadge = true;
            } else {
                showBadge = count > 0;
            }
            notificationBadge.style.display = showBadge ? 'block' : 'none';
        }

        /**
         * Marks a notification as read via API call.
         * If a redirect URL is provided, it navigates to that URL.
         * @param {string} notificationId - The ID of the notification to mark as read.
         * @param {string} redirectUrl - The URL to redirect to after marking as read.
         */
        function markAsRead(notificationId, redirectUrl) {
            axios.post(`/api/notifications/${notificationId}/mark-as-read`)
                .then(() => {
                    if (redirectUrl && redirectUrl !== '#') {
                        window.location.href = redirectUrl;
                    } else {
                        // If no URL, just update the UI locally
                        const item = notificationList.querySelector(`li[data-id="${notificationId}"]`);
                        if (item) item.classList.remove('unread');
                        fetchUnreadCount();
                    }
                })
                .catch(error => console.error('Error marking notification as read:', error));
        }

        /**
         * Attaches a click listener to the "Mark all as read" button.
         */
        markAllAsReadBtn.addEventListener('click', (e) => {
            e.preventDefault();
            axios.post('{{ route("api.notifications.read.all") }}')
                .then(() => {
                    document.querySelectorAll('.notification-item.unread').forEach(item => {
                        item.classList.remove('unread');
                    });
                    updateUnreadCountBadge(false, 0); // All are read
                })
                .catch(error => console.error('Error marking all as read:', error));
        });

        // --- Initialization ---

        // Initial fetch of notifications when the page loads
        fetchNotifications();

        // Listen for real-time notifications via Laravel Echo
        if (window.Echo) {
            const toastElement = document.getElementById('notification-toast');
            const toast = new bootstrap.Toast(toastElement);

            window.Echo.private(`App.Models.User.${userId}`)
                .notification((notification) => {
                    console.log('New notification received via Pusher:', notification);

                    // Prepend the new notification to the sidebar list
                    prependNotification({
                        id: notification.id,
                        data: notification.data || {},
                        read_at: null, // New notifications are always unread
                        created_at: new Date().toISOString()
                    });

                    // --- Show a toast for the new notification ---
                    const toastTitle = document.getElementById('toast-title');
                    const toastBody = document.getElementById('toast-body');

                    // Safely access data
                    const data = notification.data || {};
                    toastTitle.textContent = data.title || 'New Notification';
                    toastBody.textContent = data.body || 'You have a new notification.';

                    // Make the toast clickable if there is a URL
                    toastBody.style.cursor = 'default';
                    toastElement.onclick = null; // Clear previous listener
                    if (data.url && data.url !== '#') {
                        toastBody.style.cursor = 'pointer';
                        toastElement.onclick = () => {
                            markAsRead(notification.id, data.url);
                        };
                    }

                    toast.show();
                });

            window.Echo.connector.pusher.connection.bind('error', function(err) {
                console.error('Pusher connection error:', err);
                if (err.error && err.error.data && err.error.data.code === 4004) {
                    console.error('Pusher connection failed. App key may be misconfigured or the app is over its message quota.');
                }
            });
        }
    });
    </script>
    @endauth
    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3500,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                },
                showClass: {
                    popup: 'animate__animated animate__fadeInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp'
                }
            });

            @if (session('success'))
                Toast.fire({
                    icon: 'success',
                    title: '{{ session('success') }}'
                });
            @endif
            @if (session('error'))
                Toast.fire({
                    icon: 'error',
                    title: '{{ session('error') }}'
                });
            @endif
        });
    </script>
</body>
</html>
