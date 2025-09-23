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

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Moment.js for time formatting -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

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

        .notification-item.unread {
            background-color: var(--bg-hover);
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

        .notification-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 15px;
            flex-shrink: 0;
            background-color: var(--bg-input);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .notification-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .notification-avatar .notification-icon {
            color: var(--primary-color);
            font-size: 20px;
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

        /* System Notification Sidebar Styles */
        .system-notification-sidebar {
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

        .system-notification-sidebar.active {
            right: 0;
        }

        .system-notification-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: white;
        }

        .system-notification-header h4 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: white;
        }

        .system-notification-close {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            cursor: pointer;
            color: white;
            transition: all 0.2s;
        }

        .system-notification-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        .system-notification-list {
            padding: 0;
            margin: 0;
            list-style: none;
        }

        .system-notification-item {
            padding: 15px 20px;
            border-bottom: 1px solid var(--border-color);
            transition: background-color 0.2s;
            cursor: pointer;
            position: relative;
        }

        .system-notification-item:hover {
            background-color: var(--bg-hover);
        }

        .system-notification-item.unread {
            background-color: rgba(255, 107, 53, 0.05);
            border-left: 4px solid #ff6b35;
        }

        .system-notification-item.unread::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 3px;
            background: #ff6b35;
        }

        .system-notification-content {
            display: flex;
            align-items: flex-start;
        }

        .system-notification-icon {
            width: 40px;
            height: 40px;
            min-width: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 18px;
        }

        .system-notification-text {
            flex: 1;
        }

        .system-notification-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-color);
            margin: 0 0 5px;
        }

        .system-notification-desc {
            font-size: 13px;
            color: var(--text-muted);
            margin: 0;
            line-height: 1.4;
        }

        .system-notification-time {
            font-size: 11px;
            color: var(--text-light);
            margin-top: 5px;
            display: block;
        }

        .system-notification-priority {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            margin-top: 5px;
        }

        .system-notification-priority.urgent {
            background: #ff3e3e;
            color: white;
        }

        .system-notification-priority.high {
            background: #ff6b35;
            color: white;
        }

        .system-notification-priority.medium {
            background: #f7931e;
            color: white;
        }

        .system-notification-priority.low {
            background: #28a745;
            color: white;
        }

        .system-notification-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            display: none;
        }

        .system-notification-backdrop.active {
            display: block;
        }

        .system-notification-badge {
            position: absolute;
            top: 5px;
            right: 5px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #ff6b35;
            border: 2px solid var(--bg-navbar);
        }

        /* System notification toggle styling */
        .system-notification-toggle {
            position: relative;
        }

        .system-notification-toggle i {
            color: #ff6b35;
        }

        .system-notification-toggle:hover i {
            color: #f7931e;
        }

        /* Mobile responsive adjustments for system notifications */
        @media (max-width: 768px) {
            .system-notification-sidebar {
                width: 85%;
                max-width: 320px;
            }

            .system-notification-item {
                padding: 12px 15px;
            }

            .system-notification-icon {
                width: 36px;
                height: 36px;
                min-width: 36px;
                font-size: 16px;
            }
        }

        @media (max-width: 575px) {
            .system-notification-sidebar {
                width: 100%;
                max-width: 100%;
                right: -100%;
            }
        }

        /* Rate Us Button Styles - Professional & Responsive */
        .rate-us-container {
            position: fixed;
            bottom: clamp(16px, 4vw, 24px);
            right: clamp(16px, 4vw, 24px);
            z-index: 1050;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .rate-us-btn {
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 50%, #1e3a8a 100%);
            border: none;
            border-radius: clamp(24px, 6vw, 28px);
            color: white;
            cursor: pointer;
            font-family: 'Nunito', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            font-weight: 600;
            letter-spacing: 0.025em;
            outline: none;
            overflow: hidden;
            position: relative;
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            box-shadow:
                0 4px 14px rgba(30, 64, 175, 0.25),
                0 2px 6px rgba(30, 64, 175, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            width: clamp(48px, 12vw, 56px);
            height: clamp(48px, 12vw, 56px);
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            opacity: 0.75;
        }

        .rate-us-btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .rate-us-btn i {
            font-size: clamp(16px, 4vw, 20px);
            color: #fbbf24;
            transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
            filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.2));
            z-index: 2;
            position: relative;
        }

        .rate-us-text {
            position: absolute;
            right: 100%;
            top: 50%;
            transform: translateY(-50%);
            background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            color: white;
            padding: clamp(8px, 2vw, 12px) clamp(12px, 3vw, 16px);
            border-radius: clamp(6px, 1.5vw, 8px);
            font-size: clamp(11px, 2.5vw, 13px);
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-50%) translateX(-10px);
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            box-shadow:
                0 4px 12px rgba(30, 64, 175, 0.25),
                0 2px 6px rgba(30, 64, 175, 0.15);
            margin-right: clamp(8px, 2vw, 12px);
            z-index: 1;
        }

        .rate-us-text::after {
            content: '';
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            width: 0;
            height: 0;
            border-left: 6px solid #1e40af;
            border-top: 6px solid transparent;
            border-bottom: 6px solid transparent;
        }

        /* Advanced Hover & Interaction States */
        .rate-us-btn:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow:
                0 12px 32px rgba(30, 64, 175, 0.35),
                0 6px 16px rgba(30, 64, 175, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.15);
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 50%, #1e40af 100%);
            border-radius: clamp(6px, 1.5vw, 8px);
            width: auto;
            min-width: clamp(110px, 25vw, 140px);
            padding: 0 clamp(12px, 3vw, 16px);
            justify-content: space-between;
            opacity: 1;
        }

        .rate-us-btn:hover::after {
            opacity: 1;
        }

        .rate-us-btn:hover i {
            color: #fcd34d;
            transform: rotate(72deg) scale(1.1);
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
            margin-left: clamp(8px, 2vw, 12px);
        }

        .rate-us-btn:hover .rate-us-text {
            opacity: 1;
            visibility: visible;
            transform: translateY(-50%) translateX(0);
            position: relative;
            right: auto;
            top: auto;
            transform: none;
            background: transparent;
            box-shadow: none;
            margin-right: 0;
            padding: 0;
        }

        .rate-us-btn:hover .rate-us-text::after {
            display: none;
        }

        .rate-us-btn:active {
            transform: translateY(-1px) scale(0.98);
            box-shadow:
                0 6px 20px rgba(30, 64, 175, 0.3),
                0 3px 8px rgba(30, 64, 175, 0.15),
                inset 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.15s cubic-bezier(0.4, 0, 0.6, 1);
        }

        .rate-us-btn:focus {
            box-shadow:
                0 4px 14px rgba(30, 64, 175, 0.25),
                0 2px 6px rgba(30, 64, 175, 0.15),
                0 0 0 3px rgba(59, 130, 246, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            outline: none;
        }

        .rate-us-btn:focus:not(:focus-visible) {
            box-shadow:
                0 4px 14px rgba(30, 64, 175, 0.25),
                0 2px 6px rgba(30, 64, 175, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }

        /* Enhanced Ripple Effect */
        .rate-us-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.4) 0%, rgba(255, 255, 255, 0.1) 70%, transparent 100%);
            transform: translate(-50%, -50%);
            transition: all 0.6s cubic-bezier(0.25, 0.8, 0.25, 1);
            z-index: 1;
            pointer-events: none;
        }

        .rate-us-btn:active::before {
            width: 200%;
            height: 200%;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        /* Responsive Design System */

        /* Large Desktop (1200px+) */
        @media (min-width: 1200px) {
            .rate-us-container {
                bottom: 32px;
                right: 32px;
            }

            .rate-us-btn:hover {
                transform: translateY(-4px) scale(1.03);
            }
        }

        /* Desktop (992px - 1199px) */
        @media (min-width: 992px) and (max-width: 1199px) {
            .rate-us-container {
                bottom: 28px;
                right: 28px;
            }
        }

        /* Tablet Landscape (768px - 991px) */
        @media (min-width: 768px) and (max-width: 991px) {
            .rate-us-container {
                bottom: 20px;
                right: 20px;
            }

            .rate-us-btn:hover {
                transform: translateY(-2px) scale(1.01);
                box-shadow:
                    0 8px 24px rgba(30, 64, 175, 0.3),
                    0 4px 12px rgba(30, 64, 175, 0.15);
            }
        }

        /* Mobile & Tablet Portrait (≤767px) */
        @media (max-width: 767px) {
            .rate-us-container {
                bottom: clamp(12px, 3vw, 18px);
                right: clamp(12px, 3vw, 18px);
                transition: all 0.3s ease;
            }

            .rate-us-btn {
                box-shadow:
                    0 3px 12px rgba(30, 64, 175, 0.25),
                    0 1px 4px rgba(30, 64, 175, 0.15);
                backdrop-filter: blur(8px);
                -webkit-backdrop-filter: blur(8px);
            }

            .rate-us-btn:hover {
                transform: translateY(-1px) scale(1.01);
                box-shadow:
                    0 6px 20px rgba(30, 64, 175, 0.3),
                    0 3px 8px rgba(30, 64, 175, 0.2);
            }

            .rate-us-btn:active {
                transform: translateY(0) scale(0.99);
            }

            /* Smart sidebar interaction */
            .sidebar.active ~ .main-content .rate-us-container,
            .notification-sidebar.active ~ .rate-us-container {
                opacity: 0.6;
                transform: scale(0.9);
                pointer-events: none;
            }

            /* Touch-friendly adjustments */
            .rate-us-btn {
                -webkit-tap-highlight-color: transparent;
                touch-action: manipulation;
            }
        }

        /* Small Mobile (≤480px) */
        @media (max-width: 480px) {
            .rate-us-container {
                bottom: clamp(10px, 2.5vw, 16px);
                right: clamp(10px, 2.5vw, 16px);
            }

            .rate-us-btn {
                border-radius: clamp(5px, 1.2vw, 8px);
            }
        }

        /* Landscape orientation on mobile */
        @media (max-width: 767px) and (orientation: landscape) {
            .rate-us-container {
                bottom: clamp(8px, 2vw, 12px);
                right: clamp(12px, 3vw, 18px);
            }
        }

        /* High DPI displays */
        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .rate-us-btn {
                box-shadow:
                    0 4px 14px rgba(30, 64, 175, 0.25),
                    0 2px 6px rgba(30, 64, 175, 0.15),
                    inset 0 0.5px 0 rgba(255, 255, 255, 0.1);
            }

            .rate-us-icon i {
                filter: drop-shadow(0 0.5px 1px rgba(0, 0, 0, 0.2));
            }
        }

        /* Advanced Accessibility & Performance */

        /* High contrast mode support */
        @media (prefers-contrast: high) {
            .rate-us-btn {
                border: 2px solid #1e40af;
                background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
            }

            .rate-us-icon {
                border: 1px solid rgba(255, 255, 255, 0.3);
            }
        }

        /* Reduced motion support */
        @media (prefers-reduced-motion: reduce) {
            .rate-us-btn,
            .rate-us-icon,
            .rate-us-icon i,
            .rate-us-btn::before,
            .rate-us-btn::after,
            .rate-us-container {
                transition: none !important;
                animation: none !important;
            }

            .rate-us-btn:hover {
                transform: none;
                box-shadow: 0 4px 14px rgba(30, 64, 175, 0.4);
            }

            .rate-us-btn:hover .rate-us-icon {
                transform: none;
            }

            .rate-us-btn:hover .rate-us-icon i {
                transform: none;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .rate-us-btn {
                background: linear-gradient(135deg, #3b82f6 0%, #2563eb 50%, #1d4ed8 100%);
                box-shadow:
                    0 4px 14px rgba(59, 130, 246, 0.3),
                    0 2px 6px rgba(59, 130, 246, 0.2),
                    inset 0 1px 0 rgba(255, 255, 255, 0.15);
            }

            .rate-us-btn:hover {
                background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 50%, #2563eb 100%);
                box-shadow:
                    0 12px 32px rgba(59, 130, 246, 0.4),
                    0 6px 16px rgba(59, 130, 246, 0.25);
            }
        }

        /* Enhanced pulse animation */
        @keyframes pulse-rate {
            0% {
                box-shadow:
                    0 4px 14px rgba(30, 64, 175, 0.25),
                    0 2px 6px rgba(30, 64, 175, 0.15);
                transform: scale(1);
            }
            50% {
                box-shadow:
                    0 8px 24px rgba(30, 64, 175, 0.4),
                    0 4px 12px rgba(30, 64, 175, 0.25),
                    0 0 0 4px rgba(59, 130, 246, 0.1);
                transform: scale(1.02);
            }
            100% {
                box-shadow:
                    0 4px 14px rgba(30, 64, 175, 0.25),
                    0 2px 6px rgba(30, 64, 175, 0.15);
                transform: scale(1);
            }
        }

        @keyframes star-twinkle {
            0%, 100% {
                color: #fbbf24;
                filter: drop-shadow(0 1px 1px rgba(0, 0, 0, 0.2));
            }
            50% {
                color: #fcd34d;
                filter: drop-shadow(0 2px 4px rgba(251, 191, 36, 0.4));
            }
        }

        .rate-us-btn.pulse {
            animation: pulse-rate 2.5s ease-in-out infinite;
        }

        .rate-us-btn.pulse .rate-us-icon i {
            animation: star-twinkle 1.5s ease-in-out infinite;
        }

        /* Performance optimizations */
        .rate-us-btn {
            will-change: transform, box-shadow;
            contain: layout style paint;
        }

        .rate-us-icon {
            will-change: transform, background;
        }

        .rate-us-icon i {
            will-change: transform, color;
        }

        /* Print styles */
        @media print {
            .rate-us-container {
                display: none !important;
            }
        }

        /* Screen reader support */
        .rate-us-btn .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        /* Rating Modal Styles */
        .star-rating {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin: 20px 0;
        }

        .star-rating i {
            font-size: 2rem;
            color: #e5e7eb;
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 5px;
        }

        .star-rating i:hover,
        .star-rating i.active {
            color: #fbbf24;
            transform: scale(1.1);
        }

        .star-rating i:hover ~ i {
            color: #e5e7eb;
        }

        .rating-text {
            min-height: 24px;
            font-weight: 500;
        }

        #ratingModal .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
        }

        #ratingModal .modal-header {
            border-bottom: 1px solid #f1f5f9;
            padding: 1.5rem;
        }

        #ratingModal .modal-body {
            padding: 1.5rem;
        }

        #ratingModal .modal-footer {
            border-top: 1px solid #f1f5f9;
            padding: 1.5rem;
        }

        #reviewComment {
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        #reviewComment:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
    </style>
</head>
<body>
    <div id="app">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    @php
                        $storageLogo = 'storage/app_logo/logo.png';
                        $publicLogo = 'images/app_logo/logo.png';
                        $sidebarLogoPath = file_exists(public_path($storageLogo))
                            ? versioned_asset($storageLogo)
                            : versioned_asset($publicLogo);
                    @endphp
                    <img src="{{ $sidebarLogoPath }}" alt="{{ config('app.name') }} Logo" class="logo-image">
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
            <ul class="notification-list" id="notification-list">
                <div id="notification-loader" class="text-center p-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </ul>
        </div>
        <div class="notification-backdrop"></div>

        <!-- System Notification Sidebar -->
        <div class="system-notification-sidebar">
            <div class="system-notification-header">
                <h4>System Notifications</h4>
                <div class="system-notification-close" id="system-notification-close">
                    <i class="fas fa-times"></i>
                </div>
            </div>
            <ul class="system-notification-list" id="system-notification-list">
                <div id="system-notification-loader" class="text-center p-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </ul>
        </div>
        <div class="system-notification-backdrop"></div>

        <!-- System Notification Detail Modal -->
        <div class="modal fade" id="systemNotificationModal" tabindex="-1" aria-labelledby="systemNotificationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header" style="border-bottom: none;">
                        <div class="d-flex align-items-center">
                            <div class="system-notification-icon me-2" id="sysModalIcon"><i class="fa-solid fa-bullhorn"></i></div>
                            <div>
                                <h5 class="modal-title mb-1" id="systemNotificationModalLabel">System Notification</h5>
                                <div class="small text-muted" id="sysModalTime">—</div>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body pt-0">
                        <div class="d-flex align-items-center flex-wrap gap-2 mb-3">
                            <span id="sysModalTypeBadge" class="badge rounded-pill" style="background:#ff6b35;color:#fff;">TYPE</span>
                            <span id="sysModalStatusBadge" class="badge rounded-pill bg-secondary">STATUS</span>
                        </div>

                        <h4 class="fw-bold mb-2" id="sysModalTitle">Title</h4>
                        <div class="text-muted mb-3" id="sysModalSubtitle" style="display:none;"></div>

                        <div id="sysModalMessage" class="mb-2" style="white-space: pre-line; line-height: 1.7; font-size: 1.02rem;">Message</div>

                        <hr class="mt-3 mb-2" />
                        <div class="small text-muted">This announcement is provided by your system administrators.</div>
                    </div>
                    <div class="modal-footer" style="border-top: none;">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i> Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Collapse Button -->
        <div class="sidebar-collapse-btn" id="sidebar-collapse-btn">
            <i class="fas fa-chevron-left"></i>
        </div>

        <!-- Rate Us Button (visible to EMPLOYEE, SUPERVISOR, ADMIN only) -->
        @auth
            @php($role = Auth::user()->roles)
            @if(in_array($role, [\App\Enums\UserRole::EMPLOYEE, \App\Enums\UserRole::SUPERVISOR, \App\Enums\UserRole::ADMIN], true))
                <div class="rate-us-container">
                    <button type="button" class="rate-us-btn" id="rate-us-button" aria-label="Rate our service - Click to provide feedback">
                        <i class="fas fa-star" aria-hidden="true"></i>
                        <span class="rate-us-text">RATE US</span>
                        <span class="sr-only">Click to rate our service and provide feedback</span>
                    </button>
                </div>
            @endif
        @endauth

        <!-- Rating Modal -->
        <div class="modal fade" id="ratingModal" tabindex="-1" aria-labelledby="ratingModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ratingModalLabel">
                            <i class="fas fa-star text-warning me-2"></i>
                            Rate Our Service
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="ratingForm">
                            @csrf
                            <div class="text-center mb-4">
                                <p class="text-muted mb-3">How would you rate your experience with us?</p>
                                <div class="star-rating" id="starRating">
                                    <i class="fas fa-star" data-rating="1"></i>
                                    <i class="fas fa-star" data-rating="2"></i>
                                    <i class="fas fa-star" data-rating="3"></i>
                                    <i class="fas fa-star" data-rating="4"></i>
                                    <i class="fas fa-star" data-rating="5"></i>
                                </div>
                                <div class="rating-text mt-2">
                                    <span id="ratingText" class="text-muted">Click a star to rate</span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="reviewComment" class="form-label">
                                    <i class="fas fa-comment me-1"></i>
                                    Tell us about your experience (optional)
                                </label>
                                <textarea class="form-control" id="reviewComment" name="comment" rows="4"
                                    placeholder="Share your thoughts about our service..."></textarea>
                            </div>

                            <input type="hidden" id="ratingValue" name="rating" value="0">
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>
                            Cancel
                        </button>
                        <button type="button" class="btn btn-primary" id="submitRating" disabled>
                            <i class="fas fa-paper-plane me-1"></i>
                            Submit Rating
                        </button>
                    </div>
                </div>
            </div>
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

            // System notification sidebar functionality
            const systemNotificationToggle = document.getElementById('system-notification-toggle');
            const systemNotificationClose = document.getElementById('system-notification-close');
            const systemNotificationSidebar = document.querySelector('.system-notification-sidebar');
            const systemNotificationBackdrop = document.querySelector('.system-notification-backdrop');

            if (systemNotificationToggle) {
                systemNotificationToggle.addEventListener('click', () => {
                    systemNotificationSidebar.classList.toggle('active');
                    systemNotificationBackdrop.classList.toggle('active');
                    preventScroll(systemNotificationSidebar.classList.contains('active'));
                });
            }

            if (systemNotificationClose) {
                systemNotificationClose.addEventListener('click', () => {
                    systemNotificationSidebar.classList.remove('active');
                    systemNotificationBackdrop.classList.remove('active');
                    preventScroll(false);
                });
            }

            if (systemNotificationBackdrop) {
                systemNotificationBackdrop.addEventListener('click', () => {
                    systemNotificationSidebar.classList.remove('active');
                    systemNotificationBackdrop.classList.remove('active');
                    preventScroll(false);
                });
            }

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

            // Make system notification items clickable
            document.querySelectorAll('.system-notification-item').forEach(item => {
                item.addEventListener('click', function() {
                    this.classList.remove('unread');
                    // Here you would typically make an AJAX call to mark the system notification as read
                });
            });

            // Add fastclick to eliminate 300ms delay on mobile browsers
            if (isTouchDevice()) {
                document.querySelectorAll('a, button, .sidebar-nav-link, .navbar-menu-item, .dropdown-toggle, .notification-item, .system-notification-item')
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

                // For system notification sidebar (swipe right to close)
                const systemNotificationSidebar = document.querySelector('.system-notification-sidebar');
                if (systemNotificationSidebar) {
                    systemNotificationSidebar.addEventListener('touchstart', e => {
                        touchStartX = e.changedTouches[0].screenX;
                    }, passiveIfSupported);

                    systemNotificationSidebar.addEventListener('touchend', e => {
                        touchEndX = e.changedTouches[0].screenX;
                        if (touchEndX - touchStartX > 50) { // Swipe right threshold
                            systemNotificationSidebar.classList.remove('active');
                            document.querySelector('.system-notification-backdrop').classList.remove('active');
                            preventScroll(false);
                        }
                    }, passiveIfSupported);
                }
            }
        });
    </script>

    @auth
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const userId = {{ auth()->id() }};
            const notificationList = document.getElementById('notification-list');
            const notificationToggle = document.getElementById('notification-toggle');
            const notificationToast = new bootstrap.Toast(document.getElementById('notification-toast'));
            const notificationLoader = document.getElementById('notification-loader');

            const updateBadge = (count) => {
                let badge = notificationToggle.querySelector('.notification-badge-count');
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'notification-badge-count badge rounded-pill bg-danger';
                    notificationToggle.appendChild(badge);
                    notificationToggle.classList.add('has-badge');
                }
                badge.textContent = count > 9 ? '9+' : count;
                if (count === 0) {
                    badge.style.display = 'none';
                } else {
                    badge.style.display = 'block';
                }
            };

            const getUnreadCount = () => {
                fetch('{{ route('api.notifications.unread.count') }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            updateBadge(data.count);
                        }
                    });
            };

            const createNotificationIcon = (type) => {
                const icons = {
                    'info': 'fa-solid fa-circle-info',
                    'success': 'fa-solid fa-check-circle',
                    'warning': 'fa-solid fa-triangle-exclamation',
                    'error': 'fa-solid fa-times-circle',
                    'test': 'fa-solid fa-vial',
                    'default': 'fa-solid fa-bell'
                };
                const iconClass = icons[type] || icons['default'];
                return `<div class="notification-icon"><i class="${iconClass}"></i></div>`;
            };

            const renderNotification = (notification, unread = false) => {
                const newNotification = document.createElement('li');
                newNotification.className = `notification-item ${unread ? 'unread' : ''}`;
                newNotification.dataset.id = notification.id;

                // Handle two different data structures: one from the API (nested) and one from broadcast (flat)
                const isApiResource = !!notification.data;
                const data = isApiResource ? notification.data : notification;
                const time = isApiResource ? notification.created_at : data.time;

                newNotification.innerHTML = `
                    <a href="${data.url || data.link || '#'}" class="text-decoration-none text-reset" data-notification-id="${notification.id}">
                        <div class="notification-content">
                            ${createNotificationIcon(data.type)}
                            <div class="notification-text">
                                <h5 class="notification-title">${data.title || data.message}</h5>
                                <p class="notification-desc">${data.body || ''}</p>
                                <span class="notification-time">${moment(time).fromNow()}</span>
                            </div>
                        </div>
                    </a>
                `;
                return newNotification;
            };

            const loadNotifications = () => {
                const url = '{{ route('api.notifications.index') }}';
                fetch(url)
                    .then(response => {
                        if (!response.ok) {
                            console.error(`Error fetching notifications. Status: ${response.status} ${response.statusText}. URL: ${response.url}`);
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        notificationList.innerHTML = ''; // Clear loader/old notifications
                        if (data.data && data.data.length > 0) {
                            data.data.forEach(notification => {
                                const unread = notification.read_at === null;
                                const notificationElement = renderNotification(notification, unread);
                                notificationList.appendChild(notificationElement);
                            });
                        } else {
                            notificationList.innerHTML = `<li class="text-center text-muted p-4">You have no notifications.</li>`;
                        }
                    })
                    .catch(error => {
                        console.error('Full error object while fetching notifications:', error);
                        notificationList.innerHTML = `<li class="text-center text-danger p-4">Could not load notifications.</li>`;
                    });
            };

            notificationList.addEventListener('click', function(event) {
                const targetLink = event.target.closest('a[data-notification-id]');
                if (!targetLink) return;

                const notificationId = targetLink.dataset.notificationId;
                const notificationItem = targetLink.closest('.notification-item');

                if (notificationItem && notificationItem.classList.contains('unread')) {
                    fetch(`/api/notifications/${notificationId}/mark-as-read`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            notificationItem.classList.remove('unread');
                            getUnreadCount(); // Update the badge
                        }
                    })
                    .catch(error => console.error('Error marking notification as read:', error));
                }
            });

            // For debugging connection state
            console.log('Echo: Initializing...');
            window.Echo.connector.pusher.connection.bind('state_change', function(states) {
                console.log("Echo: Pusher connection state changed from", states.previous, "to", states.current);
            });
            window.Echo.connector.pusher.connection.bind('error', function(err) {
                console.error('Echo: Pusher connection error:', err);
            });


            window.Echo.private('App.Models.User.' + userId)
                .on('pusher:subscription_succeeded', function() {
                    console.log('Echo: Successfully subscribed to the private channel!');
                })
                .on('pusher:subscription_error', function(status) {
                    console.error('Echo: Failed to subscribe to private channel with status:', status);
                })
                .notification((notification) => {
                    console.log('Notification received:', notification);

                    // Remove placeholder if it exists
                    const placeholder = notificationList.querySelector('li[class*="text-center"]');
                    if (placeholder) {
                        placeholder.remove();
                    }

                    // Update toast
                    document.getElementById('toast-title').innerText = notification.title || notification.message || 'New Notification';
                    document.getElementById('toast-body').innerText = notification.body || 'You have a new notification.';
                    document.getElementById('toast-time').innerText = 'just now';
                    notificationToast.show();

                    // Prepend to sidebar list
                    const notificationElement = renderNotification(notification, true);
                    notificationList.prepend(notificationElement);


                    // Update badge
                    getUnreadCount();
                });

            // Initial fetch for user notifications
            getUnreadCount();
            loadNotifications();

            // ==========================
            // System Notifications (Right Sidebar)
            // ==========================
            const sysToggle = document.getElementById('system-notification-toggle');
            const sysList = document.getElementById('system-notification-list');
            const sysLoader = document.getElementById('system-notification-loader');
            const sysSidebar = document.querySelector('.system-notification-sidebar');

            const createSystemIcon = (type) => {
                const iconMap = {
                    info: 'fa-circle-info',
                    update: 'fa-arrows-rotate',
                    maintenance: 'fa-screwdriver-wrench',
                    alert: 'fa-triangle-exclamation',
                    default: 'fa-bullhorn'
                };
                const icon = iconMap[type] || iconMap.default;
                return `<div class="system-notification-icon"><i class="fa-solid ${icon}"></i></div>`;
            };

            const renderSystemNotification = (n) => {
                const li = document.createElement('li');
                li.className = 'system-notification-item';
                const createdAt = n.created_at || n.date_start || n.date_end || new Date().toISOString();

                // Persist details for modal in dataset (simple and safe)
                li.dataset.title = (n.title || '').toString();
                li.dataset.message = (n.message || '').toString();
                li.dataset.type = (n.type || 'info').toString();
                li.dataset.status = (n.status || '').toString();
                li.dataset.createdAt = createdAt;
                // schedule fields removed from UX; do not store

                li.innerHTML = `
                    <div class="system-notification-content">
                        ${createSystemIcon(n.type)}
                        <div class="system-notification-text">
                            <h5 class="system-notification-title">${(n.title || '').toString().trim()}</h5>
                            <p class="system-notification-desc">${(n.message || '').toString().trim()}</p>
                            <span class="system-notification-time">${moment(createdAt).fromNow()}</span>
                        </div>
                    </div>
                `;
                return li;
            };

            let sysFirstLoadDone = false;
            async function loadSystemNotifications(initial = false) {
                try {
                    if (sysLoader && initial) {
                        sysLoader.style.display = 'block';
                    }
                    const res = await fetch('/api/system-notifications', {
                        method: 'GET',
                        credentials: 'same-origin',
                        headers: { 'Accept': 'application/json' }
                    });
                    if (!res.ok) throw new Error(`HTTP ${res.status}`);
                    const data = await res.json();
                    if (!sysList) return;
                    sysList.innerHTML = '';
                    const items = Array.isArray(data.data) ? data.data : [];
                    if (items.length === 0) {
                        sysList.innerHTML = `<li class="text-center text-muted p-4">No Available System Notifications</li>`;
                    } else {
                        items.forEach(n => sysList.appendChild(renderSystemNotification(n)));
                    }
                } catch (e) {
                    // Avoid noisy console errors; provide graceful UI feedback
                    if (sysList) {
                        sysList.innerHTML = `<li class="text-center text-danger p-4">Failed to load system notifications</li>`;
                    }
                } finally {
                    if (sysLoader) sysLoader.style.display = 'none';
                    sysFirstLoadDone = true;
                }
            }

            if (sysToggle) {
                // Lazy-load on first open for responsiveness
                sysToggle.addEventListener('click', () => {
                    if (!sysFirstLoadDone) {
                        loadSystemNotifications(true);
                    }
                });
            }

            // Click -> open modal with full details (event delegation)
            if (sysList) {
                sysList.addEventListener('click', (e) => {
                    const item = e.target.closest('.system-notification-item');
                    if (!item) return;

                    try {
                        const modalEl = document.getElementById('systemNotificationModal');
                        if (!modalEl) return;
                        const modal = new bootstrap.Modal(modalEl);

                        const type = item.dataset.type || 'info';
                        const status = (item.dataset.status || 'active').toLowerCase();
                        const title = item.dataset.title || 'System Notification';
                        const message = item.dataset.message || '';
                        const createdAt = item.dataset.createdAt || new Date().toISOString();

                        // Icon
                        const iconMap = {
                            info: 'fa-circle-info',
                            update: 'fa-arrows-rotate',
                            maintenance: 'fa-screwdriver-wrench',
                            alert: 'fa-triangle-exclamation',
                            default: 'fa-bullhorn'
                        };
                        const icon = iconMap[type] || iconMap.default;
                        const iconWrap = document.getElementById('sysModalIcon');
                        if (iconWrap) iconWrap.innerHTML = `<i class="fa-solid ${icon}"></i>`;

                        // Title & Message
                        const elTitle = document.getElementById('sysModalTitle');
                        const elMsg = document.getElementById('sysModalMessage');
                        if (elTitle) elTitle.textContent = title;
                        if (elMsg) elMsg.textContent = message;

                        // Time and badges
                        const elTime = document.getElementById('sysModalTime');
                        const elType = document.getElementById('sysModalTypeBadge');
                        const elStatus = document.getElementById('sysModalStatusBadge');
                        if (elTime) elTime.textContent = `Posted ${moment(createdAt).fromNow()}`;
                        if (elType) {
                            elType.textContent = (type || 'info').toString().toUpperCase();
                            elType.style.background = '#ff6b35';
                            elType.style.color = '#fff';
                        }
                        if (elStatus) {
                            elStatus.textContent = (status || 'active').toString().toUpperCase();
                            elStatus.className = 'badge rounded-pill ms-2 ' + (status === 'active' ? 'bg-success' : (status === 'inactive' ? 'bg-secondary' : 'bg-dark'));
                        }

                        modal.show();
                    } catch (_) { /* swallow errors to keep console clean */ }
                });
            }

            // Realtime subscription (if Echo available), else polling fallback
            (function initSystemNotificationsRealtime() {
                try {
                    if (window.Echo && typeof window.Echo.channel === 'function') {
                        const channel = window.Echo.channel('system-notifications');
                        channel.listen('.SystemNotificationCreated', (payload) => {
                            if (!sysList) return;
                            const n = payload?.notification || payload;
                            const el = renderSystemNotification(n);
                            sysList.prepend(el);
                        });
                        // Also listen for updates if emitted by backend (optional)
                        channel.listen('.SystemNotificationUpdated', () => {
                            // Refresh the list silently
                            if (sysFirstLoadDone) loadSystemNotifications(false);
                        });
                        return; // Using Echo; no polling needed
                    }
                } catch(_) { /* swallow errors to avoid console issues */ }

                // Fallback polling every 30s (only after first view)
                setInterval(() => {
                    if (sysFirstLoadDone) loadSystemNotifications(false);
                }, 30000);
            })();
        });
    </script>
    @endauth

    <!-- Rate Us Button Handler -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const rateUsButton = document.getElementById('rate-us-button');
            const ratingModal = new bootstrap.Modal(document.getElementById('ratingModal'));
            const starRating = document.getElementById('starRating');
            const ratingText = document.getElementById('ratingText');
            const ratingValue = document.getElementById('ratingValue');
            const submitButton = document.getElementById('submitRating');
            const reviewComment = document.getElementById('reviewComment');

            let selectedRating = 0;

            // Rating text messages
            const ratingMessages = {
                1: 'Poor - We\'re sorry to hear that',
                2: 'Fair - We can do better',
                3: 'Good - Thanks for your feedback',
                4: 'Very Good - We\'re glad you liked it',
                5: 'Excellent - Thank you so much!'
            };

            if (rateUsButton) {
                // Rate Us button click handler
                rateUsButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    ratingModal.show();
                });

                // Star rating functionality
                const stars = starRating.querySelectorAll('i');

                stars.forEach((star, index) => {
                    star.addEventListener('mouseenter', function() {
                        highlightStars(index + 1);
                    });

                    star.addEventListener('click', function() {
                        selectedRating = index + 1;
                        ratingValue.value = selectedRating;
                        ratingText.textContent = ratingMessages[selectedRating];
                        ratingText.className = 'text-primary fw-bold';
                        submitButton.disabled = false;

                        // Keep stars highlighted
                        highlightStars(selectedRating);
                    });
                });

                starRating.addEventListener('mouseleave', function() {
                    if (selectedRating > 0) {
                        highlightStars(selectedRating);
                    } else {
                        highlightStars(0);
                    }
                });

                function highlightStars(rating) {
                    stars.forEach((star, index) => {
                        if (index < rating) {
                            star.classList.add('active');
                            star.style.color = '#fbbf24';
                        } else {
                            star.classList.remove('active');
                            star.style.color = '#e5e7eb';
                        }
                    });
                }

                // Submit rating
                submitButton.addEventListener('click', function() {
                    const formData = new FormData();
                    formData.append('rating', selectedRating);
                    formData.append('comment', reviewComment.value);
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

                    // Show loading state
                    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Submitting...';
                    submitButton.disabled = true;

                    // Submit to your rating endpoint (session-auth with cookies)
                    fetch('/api/ratings', {
                        method: 'POST',
                        credentials: 'same-origin', // ensure session cookie is sent
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: formData
                    })
                    .then(async response => {
                        let data = null;
                        try { data = await response.json(); } catch (e) {}

                        if (response.status === 401) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Please sign in',
                                text: 'You need to be logged in to submit a rating.',
                                confirmButtonColor: '#2563eb'
                            });
                            throw new Error('Unauthenticated');
                        }

                        if (response.status === 419) { // CSRF token mismatch / expired
                            Swal.fire({
                                icon: 'warning',
                                title: 'Session expired',
                                text: 'Please refresh the page and try again.',
                                confirmButtonColor: '#2563eb'
                            });
                            throw new Error('CSRF token mismatch');
                        }

                        if (response.status === 422) { // validation error
                            const msg = data?.message || 'Invalid input.';
                            const errors = data?.errors ? Object.values(data.errors).flat().join(' ') : '';
                            Swal.fire({
                                icon: 'error',
                                title: 'Validation error',
                                text: (msg + (errors ? ' ' + errors : '')).trim(),
                                confirmButtonColor: '#2563eb'
                            });
                            throw new Error('Validation error');
                        }

                        if (response.status === 429) { // rate limited
                            const msg = data?.message || 'Too many requests. Please try again later.';
                            Swal.fire({
                                icon: 'info',
                                title: 'Please wait',
                                text: msg,
                                confirmButtonColor: '#2563eb'
                            });
                            throw new Error('Rate limited');
                        }

                        if (!response.ok || !(data && data.success)) {
                            const msg = (data && data.message) ? data.message : 'You already submitted a rating for this service. Thanks for your feedback!';
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: msg,
                                confirmButtonColor: '#2563eb'
                            });
                            throw new Error(msg);
                        }

                        return data;
                    })
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Thank You!',
                                text: 'Your rating has been submitted successfully.',
                                confirmButtonColor: '#2563eb'
                            });

                            // Reset form
                            resetRatingForm();
                            ratingModal.hide();
                        } else {
                            throw new Error(data.message || 'You already submitted a rating for this service. Thanks for your feedback!');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'You already submitted a rating for this service. Thanks for your feedback!',
                            confirmButtonColor: '#2563eb'
                        });
                    })
                    .finally(() => {
                        // Reset button state
                        submitButton.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Submit Rating';
                        submitButton.disabled = selectedRating === 0;
                    });
                });

                // Reset form when modal is hidden
                document.getElementById('ratingModal').addEventListener('hidden.bs.modal', function() {
                    resetRatingForm();
                });

                function resetRatingForm() {
                    selectedRating = 0;
                    ratingValue.value = 0;
                    reviewComment.value = '';
                    ratingText.textContent = 'Click a star to rate';
                    ratingText.className = 'text-muted';
                    submitButton.disabled = true;
                    highlightStars(0);
                }

                // Auto-show pulse animation after user interaction
                let interactionCount = 0;
                document.addEventListener('click', function() {
                    interactionCount++;
                    if (interactionCount === 10) {
                        rateUsButton.classList.add('pulse');
                        setTimeout(() => {
                            rateUsButton.classList.remove('pulse');
                        }, 4000);
                    }
                });
            }
        });
    </script>

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

    <!-- Service Worker Management -->
    <script>
        // Check if service workers are supported
        if ('serviceWorker' in navigator) {
            // First, unregister any existing service workers
            navigator.serviceWorker.getRegistrations().then(function(registrations) {
                for (let registration of registrations) {
                    registration.unregister().then(function(success) {
                        if (success) {
                            console.log('Successfully unregistered old service worker');
                        }
                    });
                }

                // After unregistering, register the new service worker
                setTimeout(() => {
                    navigator.serviceWorker.register('{{ asset('serviceworker.js') }}?v=3', { scope: '/' })
                        .then(function(registration) {
                            console.log('Service Worker registered with scope:', registration.scope);
                            initializePushNotifications(registration);
                        })
                        .catch(function(error) {
                            console.log('Service Worker registration failed:', error);
                        });
                }, 1000);
            });
        }

        // Initialize push notifications
        function initializePushNotifications(registration) {
            // Check if push messaging is supported
            if (!('PushManager' in window)) {
                console.log('Push messaging is not supported');
                return;
            }

            // Check if notification permissions are granted
            if (Notification.permission === 'granted') {
                subscribeToPushNotifications(registration);
            } else if (Notification.permission !== 'denied') {
                // We need to ask for permission
                const notificationToggle = document.getElementById('notification-toggle');
                if (notificationToggle) {
                    notificationToggle.addEventListener('click', function() {
                        requestNotificationPermission(registration);
                    }, { once: true });
                }
            }
        }

        // Request notification permission
        function requestNotificationPermission(registration) {
            Notification.requestPermission().then(function(permission) {
                if (permission === 'granted') {
                    console.log('Notification permission granted');
                    subscribeToPushNotifications(registration);
                } else {
                    console.log('Notification permission denied');
                }
            });
        }

        // Subscribe to push notifications
        function subscribeToPushNotifications(registration) {
            // Get the server's public key
            fetch('{{ route('api.webpush.vapid-public-key') }}')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to fetch VAPID public key');
                    }
                    return response.json();
                })
                .then(data => {
                    const options = {
                        userVisibleOnly: true,
                        applicationServerKey: urlBase64ToUint8Array(data.vapidPublicKey)
                    };

                    // Subscribe the user
                    return registration.pushManager.subscribe(options);
                })
                .then(subscription => {
                    // Send the subscription to the server
                    return fetch('{{ route('api.push-subscriptions.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(subscription)
                    });
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to store subscription on server');
                    }
                    console.log('Push notification subscription successful');
                })
                .catch(error => {
                    console.error('Error subscribing to push notifications:', error);
                });
        }

        // Convert base64 string to Uint8Array for applicationServerKey
        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding)
                .replace(/\-/g, '+')
                .replace(/_/g, '/');

            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);

            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }
    </script>
</body>
</html>
