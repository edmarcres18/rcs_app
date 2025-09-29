<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RCS App - User Guide</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Mobile-first base styles */
        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            overflow-x: hidden;
        }

        /* Custom scrollbar for a cleaner look */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Active state for sidebar navigation */
        .sidebar-link.active {
            background-color: #4f46e5;
            color: white;
            font-weight: 600;
        }
        .sidebar-link.active i {
            color: white;
        }

        /* Mobile-first touch targets */
        .touch-target {
            min-height: 44px;
            min-width: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Sidebar improvements */
        .sidebar-link {
            padding: 0.875rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            touch-action: manipulation;
        }

        .sidebar-link:hover {
            transform: translateX(2px);
        }

        /* Sidebar alignment and accessibility */
        .sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar-link .nav-icon {
            width: 1.5rem; /* 24px */
            min-width: 1.5rem;
            text-align: center;
            color: #94a3b8; /* slate-400 */
        }

        .sidebar-link.active .nav-icon {
            color: #ffffff;
        }

        .sidebar-link .nav-label {
            flex: 1 1 auto;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-link:focus-visible {
            outline: 2px solid #6366f1; /* indigo-500 */
            outline-offset: 2px;
        }

        /* Responsive typography - mobile first */
        .responsive-heading {
            font-size: 1.5rem;
            line-height: 1.3;
        }

        .responsive-subheading {
            font-size: 1.125rem;
            line-height: 1.4;
        }

        .responsive-text {
            font-size: 0.875rem;
            line-height: 1.5;
        }

        /* Card improvements */
        .mobile-card {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }

        /* Table responsiveness */
        .responsive-table {
            font-size: 0.75rem;
            width: 100%;
            border-collapse: collapse;
        }

        .responsive-table th,
        .responsive-table td {
            padding: 0.5rem 0.25rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border-radius: 0.5rem;
            border: 1px solid #e2e8f0;
        }

        /* Form improvements */
        .form-input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: border-color 0.2s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        /* Button improvements */
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            text-align: center;
            transition: all 0.2s ease;
            touch-action: manipulation;
            min-height: 44px;
        }

        .btn:active {
            transform: translateY(1px);
        }

        /* Sticky header improvements */
        .sticky-header {
            position: sticky;
            top: 0;
            z-index: 20;
            background: rgba(248, 250, 252, 0.95);
            backdrop-filter: blur(8px);
            border-bottom: 1px solid #e2e8f0;
        }

        /* Grid improvements */
        .responsive-grid {
            display: grid;
            gap: 1rem;
            grid-template-columns: 1fr;
        }

        /* Tablet and up */
        @media (min-width: 640px) {
            .responsive-heading {
                font-size: 1.875rem;
            }

            .responsive-subheading {
                font-size: 1.25rem;
            }

            .responsive-text {
                font-size: 1rem;
            }

            .mobile-card {
                padding: 1.5rem;
            }

            .responsive-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.5rem;
            }

            .responsive-table {
                font-size: 0.875rem;
            }

            .responsive-table th,
            .responsive-table td {
                padding: 0.75rem 0.5rem;
            }
        }

        /* Desktop and up */
        @media (min-width: 1024px) {
            .responsive-heading {
                font-size: 2.25rem;
            }

            .responsive-subheading {
                font-size: 1.5rem;
            }

            .mobile-card {
                padding: 2rem;
            }

            .responsive-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 2rem;
            }

            .responsive-table {
                font-size: 1rem;
            }

            .responsive-table th,
            .responsive-table td {
                padding: 1rem 0.75rem;
            }
        }

        /* Large desktop */
        @media (min-width: 1280px) {
            .responsive-heading {
                font-size: 3rem;
            }
        }

        /* Prevent horizontal scroll */
        .container {
            max-width: 100%;
            margin: 0 auto;
            padding-left: 1rem;
            padding-right: 1rem;
        }

        @media (min-width: 640px) {
            .container {
                padding-left: 1.5rem;
                padding-right: 1.5rem;
            }
        }

        @media (min-width: 1024px) {
            .container {
                padding-left: 2rem;
                padding-right: 2rem;
            }
        }
    </style>
</head>
<body class="bg-slate-100 font-sans" x-data="guide()" x-on:scroll.window="setActiveSection()">
@php
    $storageAuth = 'storage/app_logo/auth_logo.png';
    $storageDefault = 'storage/app_logo/logo.png';
    $publicAuth = 'images/app_logo/auth_logo.png';
    $publicDefault = 'images/app_logo/logo.png';

    $resolvedAuth = file_exists(public_path($storageAuth)) ? $storageAuth : (file_exists(public_path($publicAuth)) ? $publicAuth : $storageAuth);
    $resolvedDefault = file_exists(public_path($storageDefault)) ? $storageDefault : (file_exists(public_path($publicDefault)) ? $publicDefault : $storageDefault);

    $logoToUse = file_exists(public_path($resolvedAuth)) ? $resolvedAuth : $resolvedDefault;
    $logoPath = versioned_asset($logoToUse);
    $appUrl = config('app.url');
    $visitUrl = str_contains($appUrl, 'localhost') ? url('/') : $appUrl;
@endphp

    <!-- Mobile header with menu toggle -->
    <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-sm shadow-sm lg:hidden border-b border-slate-200">
        <div class="container mx-auto py-3 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <img src="{{ $logoPath }}" alt="RCS Logo" class="h-8 w-auto flex-shrink-0">
                <span class="font-bold text-lg text-slate-800 truncate">RCS Guide</span>
            </div>
            <button @click="sidebarOpen = !sidebarOpen" class="touch-target text-slate-600 hover:text-indigo-600 rounded-lg hover:bg-slate-100 transition-colors">
                <i class="fas fa-bars text-xl"></i>
            </button>
        </div>
    </header>

    <div class="relative flex min-h-screen">
        <!-- Sidebar Navigation -->
        <aside
            class="fixed inset-y-0 left-0 z-40 w-72 sm:w-80 lg:w-64 bg-white shadow-xl lg:shadow-lg transform lg:transform-none lg:translate-x-0 transition-transform duration-300 ease-in-out"
            :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}">

            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div class="h-16 lg:h-20 flex items-center justify-center border-b border-slate-200 px-4 flex-shrink-0">
                     <div class="flex items-center space-x-2 lg:space-x-3">
                        <img src="{{ $logoPath }}" alt="RCS Logo" class="h-8 lg:h-10 w-auto flex-shrink-0">
                        <span class="font-bold text-lg lg:text-xl text-slate-800 truncate">RCS Guide</span>
                    </div>
                </div>

                <!-- Nav Links -->
                <nav class="sidebar-nav flex-1 px-3 lg:px-4 py-4 lg:py-6 space-y-1 lg:space-y-2 overflow-y-auto overscroll-contain" role="navigation" aria-label="Guide sections">
                    <a href="#introduction" @click="sidebarOpen = false" class="sidebar-link touch-target px-3 lg:px-4 py-3 lg:py-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors text-sm lg:text-base" :class="{ 'active': activeSection === 'introduction' }" :aria-current="activeSection === 'introduction' ? 'page' : false" title="Overview and quick start">
                        <span class="nav-icon"><i class="fas fa-book-open"></i></span>
                        <span class="nav-label">Welcome</span>
                    </a>
                    <a href="#registration" @click="sidebarOpen = false" class="sidebar-link touch-target px-3 lg:px-4 py-3 lg:py-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors text-sm lg:text-base" :class="{ 'active': activeSection === 'registration' }" :aria-current="activeSection === 'registration' ? 'page' : false" title="Create a new account">
                        <span class="nav-icon"><i class="fas fa-user-plus"></i></span>
                        <span class="nav-label">Registration</span>
                    </a>
                    <a href="#email-verification" @click="sidebarOpen = false" class="sidebar-link touch-target px-3 lg:px-4 py-3 lg:py-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors text-sm lg:text-base" :class="{ 'active': activeSection === 'email-verification' }" :aria-current="activeSection === 'email-verification' ? 'page' : false" title="Verify your email with OTP">
                        <span class="nav-icon"><i class="fas fa-envelope-open-text"></i></span>
                        <span class="nav-label">Email Verification</span>
                    </a>
                    <a href="#login" @click="sidebarOpen = false" class="sidebar-link touch-target px-3 lg:px-4 py-3 lg:py-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors text-sm lg:text-base" :class="{ 'active': activeSection === 'login' }" :aria-current="activeSection === 'login' ? 'page' : false" title="Sign in to your account">
                        <span class="nav-icon"><i class="fas fa-sign-in-alt"></i></span>
                        <span class="nav-label">Logging In</span>
                    </a>
                    <a href="#instructions-sending" @click="sidebarOpen = false" class="sidebar-link touch-target px-3 lg:px-4 py-3 lg:py-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors text-sm lg:text-base" :class="{ 'active': activeSection === 'instructions-sending' }" :aria-current="activeSection === 'instructions-sending' ? 'page' : false" title="Compose and send instructions">
                        <span class="nav-icon"><i class="fas fa-paper-plane"></i></span>
                        <span class="nav-label">Sending Instructions</span>
                    </a>
                    <a href="#instructions-reading" @click="sidebarOpen = false" class="sidebar-link touch-target px-3 lg:px-4 py-3 lg:py-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors text-sm lg:text-base" :class="{ 'active': activeSection === 'instructions-reading' }" :aria-current="activeSection === 'instructions-reading' ? 'page' : false" title="Read and reply to instructions">
                        <span class="nav-icon"><i class="fas fa-inbox"></i></span>
                        <span class="nav-label">Reading & Replying</span>
                    </a>
                    <a href="#task-priorities" @click="sidebarOpen = false" class="sidebar-link touch-target px-3 lg:px-4 py-3 lg:py-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors text-sm lg:text-base" :class="{ 'active': activeSection === 'task-priorities' }" :aria-current="activeSection === 'task-priorities' ? 'page' : false" title="Plan and track work with Task Priorities">
                        <span class="nav-icon"><i class="fas fa-list-check"></i></span>
                        <span class="nav-label">Task Priorities</span>
                    </a>
                    <a href="#password-recovery" @click="sidebarOpen = false" class="sidebar-link touch-target px-3 lg:px-4 py-3 lg:py-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors text-sm lg:text-base" :class="{ 'active': activeSection === 'password-recovery' }" :aria-current="activeSection === 'password-recovery' ? 'page' : false" title="Reset a forgotten password">
                        <span class="nav-icon"><i class="fas fa-key"></i></span>
                        <span class="nav-label">Password Recovery</span>
                    </a>
                    <a href="#telegram-notifications" @click="sidebarOpen = false" class="sidebar-link touch-target px-3 lg:px-4 py-3 lg:py-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors text-sm lg:text-base" :class="{ 'active': activeSection === 'telegram-notifications' }" :aria-current="activeSection === 'telegram-notifications' ? 'page' : false" title="Connect the Telegram bot">
                        <span class="nav-icon"><i class="fab fa-telegram"></i></span>
                        <span class="nav-label">Telegram Bot</span>
                    </a>
                    <a href="#security-tips" @click="sidebarOpen = false" class="sidebar-link touch-target px-3 lg:px-4 py-3 lg:py-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors text-sm lg:text-base" :class="{ 'active': activeSection === 'security-tips' }" :aria-current="activeSection === 'security-tips' ? 'page' : false" title="Best practices for safety">
                        <span class="nav-icon"><i class="fas fa-shield-alt"></i></span>
                        <span class="nav-label">Security Tips</span>
                    </a>
                    <a href="#faq" @click="sidebarOpen = false" class="sidebar-link touch-target px-3 lg:px-4 py-3 lg:py-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors text-sm lg:text-base" :class="{ 'active': activeSection === 'faq' }" :aria-current="activeSection === 'faq' ? 'page' : false" title="Answers to common questions">
                        <span class="nav-icon"><i class="fas fa-question-circle"></i></span>
                        <span class="nav-label">FAQ</span>
                    </a>
                    <a href="#support" @click="sidebarOpen = false" class="sidebar-link touch-target px-3 lg:px-4 py-3 lg:py-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors text-sm lg:text-base" :class="{ 'active': activeSection === 'support' }" :aria-current="activeSection === 'support' ? 'page' : false" title="Get help and contact support">
                        <span class="nav-icon"><i class="fas fa-headset"></i></span>
                        <span class="nav-label">Support</span>
                    </a>
                </nav>

                <!-- Back button -->
                <div class="px-4 lg:px-6 py-3 lg:py-4 border-t border-slate-200 flex-shrink-0">
                    <a href="{{ url('/') }}" class="w-full btn flex items-center justify-center px-3 lg:px-4 py-3 lg:py-2 text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg transition-colors font-semibold text-sm lg:text-base">
                        <i class="fas fa-arrow-left mr-2"></i> Go Back
                    </a>
                </div>
            </div>
        </aside>

        <!-- Backdrop for mobile sidebar -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/60 z-30 lg:hidden"></div>

    <!-- Main Content -->
        <main class="flex-1 lg:ml-64 min-h-screen">
            <div class="container mx-auto py-6 lg:py-12">

                <!-- Introduction Section -->
                <section id="introduction" class="mb-16 lg:mb-24 guide-section">
                    <div class="text-center">
                        <h1 class="text-2xl sm:text-3xl lg:text-4xl xl:text-5xl font-extrabold text-slate-900 tracking-tight">RCS App User Guide</h1>
                        <p class="mt-3 lg:mt-4 text-base lg:text-lg text-slate-600 max-w-3xl mx-auto px-4">Welcome! This guide provides comprehensive instructions for all of the app's authentication processes.</p>
                        <a href="{{ $visitUrl }}" target="_blank" class="mt-6 lg:mt-8 inline-flex items-center justify-center px-4 lg:px-6 py-2.5 lg:py-3 border border-transparent text-sm lg:text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">
                            <i class="fas fa-external-link-alt mr-2"></i> Visit RCS App
                        </a>
                    </div>

                    <div class="mt-12 lg:mt-16 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-8">
                        <div class="bg-white p-4 lg:p-6 rounded-xl shadow-sm transition-transform hover:-translate-y-1">
                            <i class="fas fa-shield-alt text-2xl lg:text-3xl text-indigo-500 mb-3 lg:mb-4"></i>
                            <h3 class="font-bold text-slate-800 mb-2 text-sm lg:text-base">Secure Authentication</h3>
                            <p class="text-slate-600 text-xs lg:text-sm">Industry-standard security protocols to protect your account.</p>
                        </div>
                        <div class="bg-white p-4 lg:p-6 rounded-xl shadow-sm transition-transform hover:-translate-y-1">
                            <i class="fas fa-bell text-2xl lg:text-3xl text-green-500 mb-3 lg:mb-4"></i>
                            <h3 class="font-bold text-slate-800 mb-2 text-sm lg:text-base">Real-time Notifications</h3>
                            <p class="text-slate-600 text-xs lg:text-sm">Get instant alerts about your account activity via multiple channels.</p>
                    </div>
                        <div class="bg-white p-4 lg:p-6 rounded-xl shadow-sm transition-transform hover:-translate-y-1 sm:col-span-2 lg:col-span-1">
                            <i class="fas fa-user-check text-2xl lg:text-3xl text-purple-500 mb-3 lg:mb-4"></i>
                            <h3 class="font-bold text-slate-800 mb-2 text-sm lg:text-base">Easy Recovery</h3>
                            <p class="text-slate-600 text-xs lg:text-sm">Simple processes to regain access if you're locked out.</p>
                </div>
            </div>
        </section>



                <!-- Section Heading -->
                <div class="sticky top-0 lg:top-auto bg-slate-100/80 backdrop-blur-sm -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-3 lg:py-4 mb-6 lg:mb-8 z-20 border-b border-slate-200">
                    <h2 class="text-xl lg:text-2xl font-bold text-slate-800 flex items-center">
                <i class="fas fa-user-plus mr-3 lg:mr-4 text-blue-500"></i>
                Account Registration
            </h2>
                        </div>

                <!-- Registration Section -->
                <section id="registration" class="mb-12 lg:mb-16 guide-section">
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="grid grid-cols-1 lg:grid-cols-2">
                            <div class="p-4 lg:p-8">
                                <p class="text-slate-600 mb-6 lg:mb-8 text-sm lg:text-base">Follow these steps to create your new RCS App account. A strong password should be at least 8 characters long and include a mix of uppercase letters, lowercase letters, numbers, and symbols.</p>
                                <div class="space-y-6 lg:space-y-8">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 w-8 h-8 lg:w-10 lg:h-10 bg-blue-500 text-white font-bold rounded-full flex items-center justify-center mr-3 lg:mr-4 text-sm lg:text-base">1</div>
                            <div>
                                            <h3 class="font-bold text-base lg:text-lg text-slate-800">Access Registration Page</h3>
                                            <p class="text-slate-600 text-sm lg:text-base">Click on "Register" on the app homepage or navigate directly to the registration page.</p>
                            </div>
                        </div>
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 w-8 h-8 lg:w-10 lg:h-10 bg-blue-500 text-white font-bold rounded-full flex items-center justify-center mr-3 lg:mr-4 text-sm lg:text-base">2</div>
                            <div>
                                            <h3 class="font-bold text-base lg:text-lg text-slate-800">Fill Form</h3>
                                            <p class="text-slate-600 text-sm lg:text-base">Provide your name, a unique nickname, email, role, and a strong password.</p>
                    </div>
                                    </div>
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 w-8 h-8 lg:w-10 lg:h-10 bg-blue-500 text-white font-bold rounded-full flex items-center justify-center mr-3 lg:mr-4 text-sm lg:text-base">3</div>
                                    <div>
                                            <h3 class="font-bold text-base lg:text-lg text-slate-800">Complete & Verify</h3>
                                            <p class="text-slate-600 text-sm lg:text-base">Submit the form and use the OTP sent to your email to verify your account.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-slate-50 p-4 lg:p-8 flex items-center justify-center lg:order-1">
                                <div class="bg-white p-4 lg:p-6 rounded-lg shadow-md w-full max-w-sm border border-slate-200">
                                    <div class="border-b border-slate-200 pb-3 lg:pb-4 mb-3 lg:mb-4">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="text-xs lg:text-sm text-slate-500">From: {{ config('mail.from.address') }}</span>
                                            <span class="text-xs text-slate-400">Just now</span>
                                        </div>
                                        <h4 class="font-bold text-base lg:text-lg text-slate-800">Your RCS App Verification Code</h4>
                                    </div>
                                    <div class="mb-4 lg:mb-6">
                                        <p class="text-slate-700 mb-3 lg:mb-4 text-sm lg:text-base">Hello [User],</p>
                                        <p class="text-slate-700 mb-3 lg:mb-4 text-sm lg:text-base">Thank you for registering. Here is your One-Time Password (OTP) for email verification:</p>
                                        <div class="text-center my-3 lg:my-4">
                                            <span class="text-2xl lg:text-3xl font-bold tracking-widest bg-slate-100 px-3 lg:px-4 py-2 rounded-lg text-slate-800">123456</span>
                                </div>
                                        <p class="text-slate-500 text-xs lg:text-sm">This code will expire in 15 minutes. If you did not create an account, no further action is required.</p>
                                </div>
                                    <div class="text-xs text-slate-400">
                                        <p>© 2024 RCS App. All rights reserved.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 lg:mt-8 bg-blue-50 border-l-4 border-blue-400 p-4 lg:p-6 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-blue-500 text-lg lg:text-xl mt-1 mr-3 lg:mr-4"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-blue-800 text-sm lg:text-base">Didn't receive the email?</h3>
                                <div class="mt-2 text-xs lg:text-sm text-blue-700 space-y-1">
                                    <p>• Check your spam or junk folder.</p>
                                    <p>• Ensure you entered the correct email address during registration.</p>
                                    <p>• Add <span class="font-mono bg-blue-100 text-blue-800 px-1 py-0.5 rounded">{{ config('mail.from.address') }}</span> to your contacts or safe senders list.</p>
                                    <p>• Wait a few minutes as delivery can sometimes be delayed.</p>
                        </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Section Heading -->
                <div class="sticky top-0 lg:top-auto bg-slate-100/80 backdrop-blur-sm -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-3 lg:py-4 mb-6 lg:mb-8 z-20 border-b border-slate-200">
                    <h2 class="text-xl lg:text-2xl font-bold text-slate-800 flex items-center">
                        <i class="fas fa-envelope-check mr-3 lg:mr-4 text-green-500"></i>
                        Email Verification
                    </h2>
                </div>

                <!-- Email Verification Section -->
                <section id="email-verification" class="mb-12 lg:mb-16 guide-section">
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="grid grid-cols-1 lg:grid-cols-2">
                            <div class="p-4 lg:p-8">
                                <h3 class="font-bold text-lg lg:text-xl text-slate-800 mb-3 lg:mb-4">Confirm Your Account</h3>
                                <p class="text-slate-600 mb-6 lg:mb-8 text-sm lg:text-base">To complete your registration, you must verify that you own the email address you signed up with. This is a crucial security step to protect your account.</p>
                                <div class="space-y-6 lg:space-y-8">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 w-8 h-8 lg:w-10 lg:h-10 bg-green-500 text-white font-bold rounded-full flex items-center justify-center mr-3 lg:mr-4 text-sm lg:text-base">1</div>
                                        <div>
                                            <h3 class="font-bold text-base lg:text-lg text-slate-800">Check Your Inbox</h3>
                                            <p class="text-slate-600 text-sm lg:text-base">Find the email from <span class="font-mono bg-slate-100 text-slate-800 px-1 py-0.5 rounded">{{ config('mail.from.address') }}</span>. It contains your 6-digit One-Time Password (OTP).</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 w-8 h-8 lg:w-10 lg:h-10 bg-green-500 text-white font-bold rounded-full flex items-center justify-center mr-3 lg:mr-4 text-sm lg:text-base">2</div>
                                        <div>
                                            <h3 class="font-bold text-base lg:text-lg text-slate-800">Enter Your Details</h3>
                                            <p class="text-slate-600 text-sm lg:text-base">On the verification page, enter the email address you used for registration and the 6-digit OTP.</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 w-8 h-8 lg:w-10 lg:h-10 bg-green-500 text-white font-bold rounded-full flex items-center justify-center mr-3 lg:mr-4 text-sm lg:text-base">3</div>
                                        <div>
                                            <h3 class="font-bold text-base lg:text-lg text-slate-800">Verify & Complete</h3>
                                            <p class="text-slate-600 text-sm lg:text-base">Click "Verify Account". If the OTP is correct, your account will be activated, and you'll be redirected to a success page.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-slate-50 p-4 lg:p-8 flex items-center justify-center">
                                <div class="bg-white p-4 lg:p-6 rounded-lg shadow-md w-full max-w-sm border border-slate-200">
                                    <h4 class="font-bold text-base lg:text-lg text-center mb-1 text-slate-800">Verify Your Account</h4>
                                    <p class="text-slate-500 text-xs lg:text-sm text-center mb-4 lg:mb-6">Enter the OTP sent to your email.</p>
                                    <div class="space-y-3 lg:space-y-4">
                                        <div>
                                            <label class="block text-slate-700 text-xs lg:text-sm font-medium mb-1">Email Address</label>
                                            <input type="email" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none text-sm" placeholder="your@email.com" value="user@example.com">
                                        </div>
                                        <div>
                                            <label class="block text-slate-700 text-xs lg:text-sm font-medium mb-1">One-Time Password (OTP)</label>
                                            <input type="text" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none text-sm" placeholder="Enter 6-digit OTP">
                                        </div>
                                        <button class="w-full bg-green-600 text-white py-2.5 rounded-lg font-medium hover:bg-green-700 transition duration-300 text-sm">Verify Account</button>
                                        <div class="text-center text-xs lg:text-sm text-slate-500 pt-2">
                                            <p>Didn't receive the code? <a href="#" class="text-indigo-600 hover:underline font-medium">Resend OTP</a></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 lg:mt-8 bg-green-50 border-l-4 border-green-400 p-4 lg:p-6 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-lightbulb text-green-500 text-lg lg:text-xl mt-1 mr-3 lg:mr-4"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-green-800 text-sm lg:text-base">Having trouble with your OTP?</h3>
                                <div class="mt-2 text-xs lg:text-sm text-green-700 space-y-1">
                                    <p>• The OTP code expires after 15 minutes. If it expires, you'll need a new one.</p>
                                    <p>• To get a new code, click the "Resend OTP" link. You may need to enter your email address again.</p>
                                    <p>• Double-check that you entered the email and OTP correctly, without any extra spaces.</p>
                                    <p>• As always, check your spam/junk folder if the email doesn't arrive in your inbox.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Section Heading -->
                <div class="sticky top-0 lg:top-auto bg-slate-100/80 backdrop-blur-sm -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-3 lg:py-4 mb-6 lg:mb-8 z-20 border-b border-slate-200">
                    <h2 class="text-xl lg:text-2xl font-bold text-slate-800 flex items-center">
                        <i class="fas fa-sign-in-alt mr-3 lg:mr-4 text-purple-500"></i>
                        Logging In
                    </h2>
                </div>

                <!-- Login Section -->
                <section id="login" class="mb-12 lg:mb-16 guide-section">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">
                        <div class="bg-white rounded-xl shadow-lg p-4 lg:p-8">
                            <h3 class="font-bold text-lg lg:text-xl text-slate-800 mb-4 lg:mb-6">Standard Login</h3>
                            <div class="space-y-4 lg:space-y-6">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 w-6 h-6 lg:w-8 lg:h-8 border-2 border-purple-500 text-purple-500 font-bold rounded-full flex items-center justify-center mr-3 lg:mr-4 text-xs lg:text-sm">1</div>
                                    <p class="text-slate-600 pt-1 text-sm lg:text-base">Navigate to the login page.</p>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 w-6 h-6 lg:w-8 lg:h-8 border-2 border-purple-500 text-purple-500 font-bold rounded-full flex items-center justify-center mr-3 lg:mr-4 text-xs lg:text-sm">2</div>
                                    <p class="text-slate-600 pt-1 text-sm lg:text-base">Enter your registered email/nickname and password.</p>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 w-6 h-6 lg:w-8 lg:h-8 border-2 border-purple-500 text-purple-500 font-bold rounded-full flex items-center justify-center mr-3 lg:mr-4 text-xs lg:text-sm">3</div>
                                    <p class="text-slate-600 pt-1 text-sm lg:text-base">Click "Login" to access your dashboard.</p>
                                </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl shadow-lg p-4 lg:p-8">
                            <h3 class="font-bold text-lg lg:text-xl text-slate-800 mb-4 lg:mb-6">Two-Factor Authentication (2FA)</h3>
                            <div class="space-y-4 lg:space-y-6">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 w-6 h-6 lg:w-8 lg:h-8 border-2 border-purple-500 text-purple-500 font-bold rounded-full flex items-center justify-center mr-3 lg:mr-4 text-xs lg:text-sm">1</div>
                                    <p class="text-slate-600 pt-1 text-sm lg:text-base">Enable 2FA in your account settings.</p>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 w-6 h-6 lg:w-8 lg:h-8 border-2 border-purple-500 text-purple-500 font-bold rounded-full flex items-center justify-center mr-3 lg:mr-4 text-xs lg:text-sm">2</div>
                                    <p class="text-slate-600 pt-1 text-sm lg:text-base">On login, provide the 6-digit code from your authenticator app.</p>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 w-6 h-6 lg:w-8 lg:h-8 border-2 border-purple-500 text-purple-500 font-bold rounded-full flex items-center justify-center mr-3 lg:mr-4 text-xs lg:text-sm">3</div>
                                    <p class="text-slate-600 pt-1 text-sm lg:text-base">Optionally, trust the device to skip 2FA on future logins.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 lg:mt-8 bg-white rounded-xl shadow-lg p-4 lg:p-8">
                        <h3 class="font-bold text-lg lg:text-xl text-slate-800 mb-3 lg:mb-4">Troubleshooting Login Issues</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:gap-8">
                            <div class="border-l-4 border-purple-300 pl-3 lg:pl-4">
                                <h4 class="font-bold text-slate-800 text-sm lg:text-base">Incorrect Password</h4>
                                <p class="text-slate-600 text-xs lg:text-sm">Use the "Forgot Password" feature to reset it.</p>
                            </div>
                            <div class="border-l-4 border-purple-300 pl-3 lg:pl-4">
                                <h4 class="font-bold text-slate-800 text-sm lg:text-base">Account Locked</h4>
                                <p class="text-slate-600 text-xs lg:text-sm">For security, your account may lock after multiple failed attempts. Please wait before trying again.</p>
                            </div>
                            <div class="border-l-4 border-purple-300 pl-3 lg:pl-4">
                                <h4 class="font-bold text-slate-800 text-sm lg:text-base">Browser Issues</h4>
                                <p class="text-slate-600 text-xs lg:text-sm">Try clearing your browser cache or using a different browser.</p>
                            </div>
                            <div class="border-l-4 border-purple-300 pl-3 lg:pl-4">
                                <h4 class="font-bold text-slate-800 text-sm lg:text-base">Email Not Recognized</h4>
                                <p class="text-slate-600 text-xs lg:text-sm">Ensure you are using the correct registered email or nickname.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Section Heading -->
                <div class="sticky top-0 lg:top-auto bg-slate-100/80 backdrop-blur-sm -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-4 mb-8 z-20 border-b border-slate-200">
                    <h2 class="text-2xl font-bold text-slate-800 flex items-center">
                        <i class="fas fa-paper-plane mr-4 text-indigo-500"></i>
                        Sending Instructions
                    </h2>
                </div>

                <!-- Sending Instructions Section -->
                <section id="instructions-sending" class="mb-16 guide-section">
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="grid grid-cols-1 lg:grid-cols-2">
                            <div class="p-4 lg:p-8">
                                <p class="text-slate-600 mb-6 lg:mb-8 text-sm lg:text-base">Create and send instructions to teammates from the app's Instructions area. You can target specific users, entire roles, or everyone (except System Administrators).</p>
                                <div class="space-y-6 lg:space-y-8">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 w-8 h-8 lg:w-10 lg:h-10 bg-indigo-500 text-white font-bold rounded-full flex items-center justify-center mr-3 lg:mr-4 text-sm lg:text-base">1</div>
                                        <div>
                                            <h3 class="font-bold text-base lg:text-lg text-slate-800">Open New Instruction</h3>
                                            <p class="text-slate-600 text-sm lg:text-base">Go to <span class="font-semibold">My Instructions</span> and click <span class="font-semibold">New Instruction</span>.</p>
                                            <a href="{{ route('instructions.create') }}" class="inline-flex items-center mt-2 lg:mt-3 text-indigo-600 hover:underline text-sm lg:text-base">
                                                Open create page <i class="fas fa-external-link-alt ml-2 text-xs"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 w-8 h-8 lg:w-10 lg:h-10 bg-indigo-500 text-white font-bold rounded-full flex items-center justify-center mr-3 lg:mr-4 text-sm lg:text-base">2</div>
                                        <div>
                                            <h3 class="font-bold text-base lg:text-lg text-slate-800">Enter Details</h3>
                                            <ul class="text-slate-600 list-disc ml-5 space-y-1 text-sm lg:text-base">
                                                <li><span class="font-semibold">Title</span> and <span class="font-semibold">Instruction Body</span> (required)</li>
                                                <li><span class="font-semibold">Target Deadline</span> (optional)</li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 w-8 h-8 lg:w-10 lg:h-10 bg-indigo-500 text-white font-bold rounded-full flex items-center justify-center mr-3 lg:mr-4 text-sm lg:text-base">3</div>
                                        <div>
                                            <h3 class="font-bold text-base lg:text-lg text-slate-800">Choose Recipients</h3>
                                            <ul class="text-slate-600 list-disc ml-5 space-y-1 text-sm lg:text-base">
                                                <li><span class="font-semibold">Specific Users</span>: pick one or more teammates</li>
                                                <li><span class="font-semibold">By Role</span>: send to all with selected roles</li>
                                                <li><span class="font-semibold">All Users</span>: send to everyone (excluding System Administrators)</li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 w-8 h-8 lg:w-10 lg:h-10 bg-indigo-500 text-white font-bold rounded-full flex items-center justify-center mr-3 lg:mr-4 text-sm lg:text-base">4</div>
                                        <div>
                                            <h3 class="font-bold text-base lg:text-lg text-slate-800">Send</h3>
                                            <p class="text-slate-600 text-sm lg:text-base">Click <span class="font-semibold">Send Instruction</span>. Recipients are notified and the instruction appears in your <span class="font-semibold">Sent</span> list.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-slate-50 p-4 lg:p-8 flex items-center justify-center">
                                <div class="bg-white p-4 lg:p-6 rounded-lg shadow-md w-full max-w-md border border-slate-200">
                                    <h4 class="font-bold text-base lg:text-lg text-slate-800 mb-3 lg:mb-4">Create Instruction (Preview)</h4>
                                    <div class="space-y-4 text-sm">
                                        <div>
                                            <label class="block text-slate-700 font-medium mb-1">Title</label>
                                            <input class="w-full px-3 py-2 border border-slate-300 rounded-lg" placeholder="Weekly Project Update Reminder" />
                                        </div>
                                        <div>
                                            <label class="block text-slate-700 font-medium mb-1">Instruction Body</label>
                                            <textarea class="w-full px-3 py-2 border border-slate-300 rounded-lg" rows="4" placeholder="Enter the main content..."></textarea>
                                        </div>
                                        <div>
                                            <label class="block text-slate-700 font-medium mb-1">Target Deadline (optional)</label>
                                            <input type="datetime-local" class="w-full px-3 py-2 border border-slate-300 rounded-lg" />
                                        </div>
                                        <div>
                                            <label class="block text-slate-700 font-medium mb-1">Recipients</label>
                                            <div class="grid grid-cols-3 gap-2">
                                                <button class="px-2 py-1 rounded bg-indigo-50 text-indigo-600 text-xs font-semibold">Specific</button>
                                                <button class="px-2 py-1 rounded bg-slate-100 text-slate-700 text-xs font-semibold">By Role</button>
                                                <button class="px-2 py-1 rounded bg-slate-100 text-slate-700 text-xs font-semibold">All</button>
                                            </div>
                                        </div>
                                        <button class="w-full bg-indigo-600 text-white py-2.5 rounded-lg font-medium hover:bg-indigo-700 transition">Send Instruction</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 lg:mt-8 bg-indigo-50 border-l-4 border-indigo-400 p-4 lg:p-6 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0"><i class="fas fa-info-circle text-indigo-500 text-lg lg:text-xl mt-1 mr-3 lg:mr-4"></i></div>
                            <div class="text-xs lg:text-sm text-indigo-800 space-y-1">
                                <p>• You cannot send to yourself or to System Administrators.</p>
                                <p>• When selecting roles, all users with those roles receive the instruction.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Section Heading -->
                <div class="sticky top-0 lg:top-auto bg-slate-100/80 backdrop-blur-sm -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-4 mb-8 z-20 border-b border-slate-200">
                    <h2 class="text-2xl font-bold text-slate-800 flex items-center">
                        <i class="fas fa-inbox mr-4 text-emerald-500"></i>
                        Reading & Replying to Instructions
                    </h2>
                </div>

                <!-- Reading & Replying Section -->
                <section id="instructions-reading" class="mb-16 guide-section">
                    <div class="bg-white rounded-xl shadow-lg p-4 lg:p-8">
                        <div class="grid lg:grid-cols-2 gap-8 lg:gap-12">
                            <div>
                                <h3 class="font-bold text-lg lg:text-xl text-slate-800 mb-3 lg:mb-4">My Instructions</h3>
                                <p class="text-slate-600 mb-4 lg:mb-6 text-sm lg:text-base">Your instruction center has two tabs:</p>
                                <ul class="space-y-3 text-sm lg:text-base">
                                    <li class="flex items-start"><i class="fas fa-envelope-open text-emerald-500 mt-1 mr-3"></i><span class="text-slate-700"><span class="font-semibold">Received</span>: instructions sent to you. Unread items show a colored dot and accent border.</span></li>
                                    <li class="flex items-start"><i class="fas fa-paper-plane text-indigo-500 mt-1 mr-3"></i><span class="text-slate-700"><span class="font-semibold">Sent</span>: instructions you have sent to others.</span></li>
                                </ul>
                                <div class="mt-5 lg:mt-6">
                                    <a href="{{ route('instructions.index') }}" class="inline-flex items-center text-indigo-600 hover:underline font-medium text-sm lg:text-base">Open My Instructions <i class="fas fa-external-link-alt ml-2 text-xs"></i></a>
                                </div>
                                <div class="mt-8 lg:mt-10">
                                    <h4 class="font-semibold text-slate-800 mb-2">Open an instruction</h4>
                                    <p class="text-slate-600 text-sm lg:text-base">Click a card to view details. It includes sender, recipients, optional deadline, the full body, and your reply box.</p>
                                </div>
                            </div>
                            <div>
                                <h3 class="font-bold text-lg lg:text-xl text-slate-800 mb-3 lg:mb-4">Reply, Forward, and Activity</h3>
                                <div class="space-y-4 text-slate-700 text-sm lg:text-base">
                                    <div class="flex items-start"><i class="fas fa-reply text-emerald-500 mt-1 mr-3"></i><p><span class="font-semibold">Reply</span>: type your message and click <span class="font-semibold">Send Reply</span>. Your reply appears instantly in the activity feed.</p></div>
                                    <div class="flex items-start"><i class="fas fa-share text-sky-500 mt-1 mr-3"></i><p><span class="font-semibold">Forward</span>: click <span class="font-semibold">Forward</span> to select new recipients and optionally add a message.</p></div>
                                    <div class="flex items-start"><i class="fas fa-stream text-purple-500 mt-1 mr-3"></i><p><span class="font-semibold">Activity Feed</span>: shows reads, forwards, and replies in chronological order.</p></div>
                                    <div class="flex items-start"><i class="fas fa-check-circle text-gray-500 mt-1 mr-3"></i><p><span class="font-semibold">Mark as Read</span>: when you open an instruction you received, it is automatically marked as read.</p></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 lg:mt-8 bg-emerald-50 border-l-4 border-emerald-400 p-4 lg:p-6 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0"><i class="fas fa-lightbulb text-emerald-500 text-lg lg:text-xl mt-1 mr-3 lg:mr-4"></i></div>
                            <div class="text-xs lg:text-sm text-emerald-800 space-y-1">
                                <p>• Replies notify the sender and other recipients.</p>
                                <p>• Use <span class="font-semibold">Print</span> for a clean hard copy of the instruction details.</p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 bg-slate-50 border-l-4 border-slate-300 p-4 lg:p-6 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0"><i class="fas fa-info-circle text-slate-500 text-lg lg:text-xl mt-1 mr-3 lg:mr-4"></i></div>
                            <div class="text-xs lg:text-sm text-slate-700 space-y-1">
                                <p>• Opening a received instruction automatically marks it as read and logs the activity.</p>
                                <p>• System Administrators cannot reply to instructions.</p>
                                <p>• When you reply, the sender (if not you) and all other recipients are notified.</p>
                                <p>• The activity feed shows reads, forwards, and replies in time order; replies appear with full content.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Section Heading -->
                <div class="sticky top-0 lg:top-auto bg-slate-100/80 backdrop-blur-sm -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-4 mb-8 z-20 border-b border-slate-200">
                    <h2 class="text-2xl font-bold text-slate-800 flex items-center">
                        <i class="fas fa-list-check mr-4 text-sky-500"></i>
                        Task Priorities
                    </h2>
                </div>

                <!-- Task Priorities Section -->
                <section id="task-priorities" class="mb-16 guide-section">
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="grid grid-cols-1 lg:grid-cols-2">
                            <div class="p-4 lg:p-8">
                                <h3 class="font-bold text-lg lg:text-xl text-slate-800 mb-3 lg:mb-4">What are Task Priorities?</h3>
                                <p class="text-slate-600 text-sm lg:text-base mb-4 lg:mb-6">Task Priorities help you break down an instruction into actionable items you will complete. Items are grouped and tracked together, and the instruction sender is kept up to date automatically.</p>

                                <div class="space-y-6 lg:space-y-8">
                                    <div>
                                        <h4 class="font-semibold text-slate-800 mb-2">Who can create them?</h4>
                                        <p class="text-slate-600 text-sm lg:text-base">Recipients of an instruction can create Task Priorities for that instruction. The instruction sender can view these in their read‑only “Sent” view.</p>
                                    </div>

                                    <div>
                                        <h4 class="font-semibold text-slate-800 mb-2">Statuses</h4>
                                        <div class="flex flex-wrap gap-2 text-xs lg:text-sm">
                                            <span class="px-2 py-1 rounded bg-slate-200 text-slate-800 font-semibold">Not Started</span>
                                            <span class="px-2 py-1 rounded bg-blue-100 text-blue-800 font-semibold">Processing</span>
                                            <span class="px-2 py-1 rounded bg-emerald-100 text-emerald-800 font-semibold">Accomplished</span>
                                        </div>
                                    </div>

                                    <div>
                                        <h4 class="font-semibold text-slate-800 mb-2">Permissions</h4>
                                        <ul class="list-disc ml-5 space-y-1 text-slate-600 text-sm lg:text-base">
                                            <li><span class="font-semibold">Recipients</span>: Create, edit, export, and delete their own Task Priority groups for instructions they received.</li>
                                            <li><span class="font-semibold">Senders</span>: View groups created for their instructions (read‑only).</li>
                                            <li><span class="font-semibold">Delete rule</span>: A group can be deleted only when <span class="font-semibold">all items are Accomplished</span>.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-slate-50 p-4 lg:p-8">
                                <h3 class="font-bold text-lg lg:text-xl text-slate-800 mb-3 lg:mb-4">Flow Overview</h3>
                                <ol class="list-decimal ml-5 space-y-3 text-slate-700 text-sm lg:text-base">
                                    <li>
                                        <span class="font-semibold">Open Create</span> — Go to Task Priorities and click <span class="font-semibold">New</span>. Pick the instruction (must be assigned to you).
                                    </li>
                                    <li>
                                        <span class="font-semibold">Add items</span> — For each item, set a title, level, start date, target deadline, status, and optional notes. Items are saved together as one <span class="font-semibold">group</span>.
                                    </li>
                                    <li>
                                        <span class="font-semibold">Notify sender</span> — When you create or update a group, the instruction sender is notified instantly (in‑app, email, broadcast, and Telegram).
                                    </li>
                                    <li>
                                        <span class="font-semibold">Track & update</span> — Edit the group to keep statuses and deadlines current. The sender’s read‑only view updates automatically.
                                    </li>
                                    <li>
                                        <span class="font-semibold">Finish & clean up</span> — When all items are Accomplished, you can delete the group. Deleted groups move to the <span class="font-semibold">Recycle Bin</span> where you can restore or permanently delete.
                                    </li>
                                </ol>

                                <div class="mt-6 grid sm:grid-cols-2 gap-3">
                                    <a href="{{ route('task-priorities.create') }}" class="w-full btn bg-indigo-600 text-white hover:bg-indigo-700">New Task Priorities</a>
                                    <a href="{{ route('task-priorities.index') }}" class="w-full btn bg-slate-900 text-white hover:bg-slate-800">My Task Priorities</a>
                                    <a href="{{ route('task-priorities.sent') }}" class="w-full btn bg-slate-100 text-slate-800 hover:bg-slate-200">Sent (Read‑only)</a>
                                    <a href="{{ route('task-priorities.recycle-bin') }}" class="w-full btn bg-amber-100 text-amber-800 hover:bg-amber-200">Recycle Bin</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 lg:mt-8 grid lg:grid-cols-2 gap-6">
                        <div class="bg-white rounded-xl shadow p-4 lg:p-6 border border-slate-200">
                            <h4 class="font-semibold text-slate-800 mb-2">Recipient Actions</h4>
                            <ul class="list-disc ml-5 space-y-2 text-sm lg:text-base text-slate-700">
                                <li><span class="font-semibold">Create</span> a group tied to an instruction you received.</li>
                                <li><span class="font-semibold">Edit</span> a group to replace all items with your latest plan.</li>
                                <li><span class="font-semibold">Export</span> a group to a formatted Excel (.xlsx) file.</li>
                                <li><span class="font-semibold">Delete</span> a group once all items are Accomplished.</li>
                                <li><span class="font-semibold">Bulk delete</span> multiple completed groups at once.</li>
                            </ul>
                        </div>
                        <div class="bg-white rounded-xl shadow p-4 lg:p-6 border border-slate-200">
                            <h4 class="font-semibold text-slate-800 mb-2">Sender Visibility</h4>
                            <p class="text-slate-700 text-sm lg:text-base">Instruction senders can open <span class="font-semibold">Sent</span> to see a clean, one‑row‑per‑group overview created by their recipients. Senders cannot modify recipient groups.</p>
                            <div class="mt-3 text-xs lg:text-sm text-slate-500">Tip: Use the search bar to filter by instruction title.</div>
                        </div>
                    </div>

                    <div class="mt-6 bg-sky-50 border-l-4 border-sky-400 p-4 lg:p-6 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0"><i class="fas fa-info-circle text-sky-500 text-lg lg:text-xl mt-1 mr-3 lg:mr-4"></i></div>
                            <div class="text-xs lg:text-sm text-sky-800 space-y-1">
                                <p>• Groups are identified internally by a shared key so all items move together through their lifecycle.</p>
                                <p>• The Recycle Bin retains deleted groups until you permanently remove them.</p>
                                <p>• Date filters and status filters help you focus on what matters now.</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Section Heading -->
                <div class="sticky top-0 lg:top-auto bg-slate-100/80 backdrop-blur-sm -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-4 mb-8 z-20 border-b border-slate-200">
                    <h2 class="text-2xl font-bold text-slate-800 flex items-center">
                        <i class="fas fa-key mr-4 text-red-500"></i>
                        Password Recovery
                    </h2>
                </div>

                <!-- Password Recovery Section -->
                <section id="password-recovery" class="mb-16 guide-section">
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="grid grid-cols-1 lg:grid-cols-2">
                             <div class="p-4 lg:p-8">
                                <p class="text-slate-600 mb-6 lg:mb-8 text-sm lg:text-base">Forgot your password? No problem. Here's how to reset it.</p>
                                <div class="space-y-6 lg:space-y-8">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 w-8 h-8 lg:w-10 lg:h-10 bg-red-500 text-white font-bold rounded-full flex items-center justify-center mr-3 lg:mr-4 text-sm lg:text-base">1</div>
                        <div>
                                            <h3 class="font-bold text-base lg:text-lg text-slate-800">Request Reset</h3>
                                            <p class="text-slate-600 text-sm lg:text-base">On the login page, click "Forgot Password" and enter your email.</p>
                        </div>
                    </div>
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 w-8 h-8 lg:w-10 lg:h-10 bg-red-500 text-white font-bold rounded-full flex items-center justify-center mr-3 lg:mr-4 text-sm lg:text-base">2</div>
                        <div>
                                            <h3 class="font-bold text-base lg:text-lg text-slate-800">Check Your Email</h3>
                                            <p class="text-slate-600 text-sm lg:text-base">You'll receive an email with a secure password reset link. Note that this link is only valid for 24 hours.</p>
                        </div>
                    </div>
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 w-8 h-8 lg:w-10 lg:h-10 bg-red-500 text-white font-bold rounded-full flex items-center justify-center mr-3 lg:mr-4 text-sm lg:text-base">3</div>
                        <div>
                                            <h3 class="font-bold text-base lg:text-lg text-slate-800">Create New Password</h3>
                                            <p class="text-slate-600 text-sm lg:text-base">Click the link and follow the instructions to set a new password.</p>
                        </div>
                    </div>
                </div>
            </div>
                            <div class="bg-slate-50 p-4 lg:p-8 flex items-center justify-center">
                                <div class="bg-white p-4 lg:p-6 rounded-lg shadow-md w-full max-w-md border border-slate-200">
                                    <h4 class="font-bold text-base lg:text-lg text-center mb-4 text-slate-800">Reset Your Password</h4>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-slate-700 text-xs lg:text-sm font-medium mb-1">Email Address</label>
                                            <input type="email" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none" placeholder="your@email.com">
                                        </div>
                                        <button class="w-full bg-red-600 text-white py-2.5 rounded-lg font-medium hover:bg-red-700 transition duration-300 text-sm lg:text-base">Send Reset Link</button>
                                        <div class="text-center text-xs lg:text-sm text-slate-500 pt-2">
                                            <p>Remember your password? <a href="#login" class="text-indigo-600 hover:underline font-medium">Log in</a></p>
                    </div>
                    </div>
                    </div>
                    </div>
                </div>
            </div>
        </section>

                <!-- Section Heading -->
                <div class="sticky top-0 lg:top-auto bg-slate-100/80 backdrop-blur-sm -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-4 mb-8 z-20 border-b border-slate-200">
                    <h2 class="text-2xl font-bold text-slate-800 flex items-center">
                        <i class="fab fa-telegram mr-4 text-sky-500"></i>
                        Telegram Notification Bot
            </h2>
                        </div>

                <!-- Telegram Notifications -->
                <section id="telegram-notifications" class="mb-16 guide-section">
                    <div class="bg-white rounded-xl shadow-lg p-8">
                        <div class="grid lg:grid-cols-2 gap-12 items-start">
                            <div class="lg:order-2">
                                <h3 class="font-bold text-xl text-slate-800 mb-4">How to Set Up</h3>
                                <div class="space-y-6">
                                     <div class="flex items-start">
                                        <div class="flex-shrink-0 w-10 h-10 bg-sky-500 text-white font-bold rounded-full flex items-center justify-center mr-4">1</div>
                            <div>
                                            <h4 class="font-bold text-slate-800">Find the Bot</h4>
                                            <p class="text-slate-600">Search for <a href="https://t.me/mhrcs_notif_bot" target="_blank" class="font-mono bg-slate-200 px-2 py-1 rounded hover:bg-slate-300 transition-colors cursor-pointer text-blue-600 hover:text-blue-800">@mhrcs_notif_bot</a> in Telegram.</p>
                            </div>
                        </div>
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 w-10 h-10 bg-sky-500 text-white font-bold rounded-full flex items-center justify-center mr-4">2</div>
                            <div>
                                            <h4 class="font-bold text-slate-800">Start & Link</h4>
                                            <p class="text-slate-600">Send <span class="font-mono bg-slate-200 px-2 py-1 rounded">/start</span> and use the code provided to link your RCS account in your profile settings.</p>
                            </div>
                        </div>
                                     <div class="flex items-start">
                                        <div class="flex-shrink-0 w-10 h-10 bg-sky-500 text-white font-bold rounded-full flex items-center justify-center mr-4">3</div>
                            <div>
                                            <h4 class="font-bold text-slate-800">Get Notified</h4>
                                            <p class="text-slate-600">Receive real-time alerts for logins, password changes, and other important events.</p>
                        </div>
                    </div>
                                </div>
                            </div>
                            <div class="lg:order-1">
                                <h3 class="font-bold text-xl text-slate-800 mb-4">Get Real-Time Alerts</h3>
                                <p class="text-slate-600 mb-6">Connecting the Telegram bot provides an essential layer of security and awareness for your account. You'll receive instant notifications for:</p>
                        <ul class="space-y-4">
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                        <span class="text-slate-700">New login attempts, including location and device information.</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                        <span class="text-slate-700">Successful password changes or email address updates.</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                        <span class="text-slate-700">Important security announcements from the RCS team.</span>
                            </li>
                            <li class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                                        <span class="text-slate-700">Quick links to secure your account if you detect suspicious activity.</span>
                            </li>
                        </ul>
                            </div>
                        </div>

                        <div class="mt-8 bg-slate-50 border border-slate-200 rounded-lg p-6">
                            <h4 class="font-bold text-slate-800 mb-4 text-center">Sample Notification</h4>
                            <div class="max-w-md mx-auto">
                                <div class="bg-white p-4 rounded-lg shadow-md">
                                    <div class="flex items-center mb-2">
                                        <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center mr-3">
                                            <i class="fab fa-telegram-plane text-white text-xl"></i>
                                        </div>
                            <div>
                                            <span class="font-bold text-slate-800">RCS Notifications</span>
                                            <span class="text-xs text-slate-500 block">via <a href="https://t.me/mhrcs_notif_bot" target="_blank" class="text-blue-600 hover:text-blue-800 hover:underline cursor-pointer">@mhrcs_notif_bot</a></span>
                            </div>
                                        <span class="text-xs text-slate-500 ml-auto">12:34 PM</span>
                        </div>
                                    <p class="text-slate-700 bg-slate-100 p-3 rounded-md mt-2"><b>Security Alert:</b> New login detected from Chrome on Windows near New York, US. If this wasn't you, please secure your account immediately.</p>
                        </div>
                            </div>
                        </div>

                        <!-- Bot Commands & Role Access -->
                        <div class="mt-10">
                            <h3 class="text-xl font-bold text-slate-800 mb-4 flex items-center">
                                <i class="fab fa-telegram text-sky-500 mr-3"></i>
                                Bot Commands & Role Access
                            </h3>
                            <p class="text-slate-600 mb-6">Use these commands in the Telegram chat with the bot. Access is enforced by your role and link status. Make sure your Telegram is linked to your account first using <span class="font-mono bg-slate-100 px-2 py-0.5 rounded">/link your.email@example.com</span>.</p>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-slate-200 responsive-table">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Command</th>
                                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider">Description</th>
                                            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Employee</th>
                                            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Supervisor</th>
                                            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">Admin</th>
                                            <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600 uppercase tracking-wider">System Admin</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 text-sm">
                                        <tr>
                                            <td class="px-4 py-3 font-mono text-slate-800">/start</td>
                                            <td class="px-4 py-3 text-slate-600">Welcome + current link/notification status</td>
                                            <td class="px-4 py-3 text-center">✅</td>
                                            <td class="px-4 py-3 text-center">✅</td>
                                            <td class="px-4 py-3 text-center">✅</td>
                                            <td class="px-4 py-3 text-center">✅</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3 font-mono text-slate-800">/link [email]</td>
                                            <td class="px-4 py-3 text-slate-600">Link your Telegram to account</td>
                                            <td class="px-4 py-3 text-center">✅</td>
                                            <td class="px-4 py-3 text-center">✅</td>
                                            <td class="px-4 py-3 text-center">✅</td>
                                            <td class="px-4 py-3 text-center">✅</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3 font-mono text-slate-800">/unlink</td>
                                            <td class="px-4 py-3 text-slate-600">Unlink Telegram from account</td>
                                            <td class="px-4 py-3 text-center">🚫</td>
                                            <td class="px-4 py-3 text-center">🚫</td>
                                            <td class="px-4 py-3 text-center">🚫</td>
                                            <td class="px-4 py-3 text-center">✅</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3 font-mono text-slate-800">/status</td>
                                            <td class="px-4 py-3 text-slate-600">Show link and notification settings</td>
                                            <td class="px-4 py-3 text-center">✅</td>
                                            <td class="px-4 py-3 text-center">✅</td>
                                            <td class="px-4 py-3 text-center">✅</td>
                                            <td class="px-4 py-3 text-center">✅</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3 font-mono text-slate-800">/enable</td>
                                            <td class="px-4 py-3 text-slate-600">Enable Telegram notifications</td>
                                            <td class="px-4 py-3 text-center">✅</td>
                                            <td class="px-4 py-3 text-center">✅</td>
                                            <td class="px-4 py-3 text-center">✅</td>
                                            <td class="px-4 py-3 text-center">✅</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3 font-mono text-slate-800">/disable</td>
                                            <td class="px-4 py-3 text-slate-600">Disable Telegram notifications</td>
                                            <td class="px-4 py-3 text-center">✅</td>
                                            <td class="px-4 py-3 text-center">✅</td>
                                            <td class="px-4 py-3 text-center">✅</td>
                                            <td class="px-4 py-3 text-center">✅</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3 font-mono text-slate-800">/activity</td>
                                            <td class="px-4 py-3 text-slate-600">Show your recent account activities</td>
                                            <td class="px-4 py-3 text-center">✅</td>
                                            <td class="px-4 py-3 text-center">✅</td>
                                            <td class="px-4 py-3 text-center">✅</td>
                                            <td class="px-4 py-3 text-center">✅</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3 font-mono text-slate-800">/pendings</td>
                                            <td class="px-4 py-3 text-slate-600">List your assigned instructions that are unread or not replied</td>
                                            <td class="px-4 py-3 text-center">✅</td>
                                            <td class="px-4 py-3 text-center">✅</td>
                                            <td class="px-4 py-3 text-center">✅</td>
                                            <td class="px-4 py-3 text-center">✅</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3 font-mono text-slate-800">/reply [id] [message]</td>
                                            <td class="px-4 py-3 text-slate-600">Reply to a specific instruction by ID</td>
                                            <td class="px-4 py-3 text-center">✅</td>
                                            <td class="px-4 py-3 text-center">✅</td>
                                            <td class="px-4 py-3 text-center">✅</td>
                                            <td class="px-4 py-3 text-center">🚫</td>
                                        </tr>
                                        <tr>
                                            <td class="px-4 py-3 font-mono text-slate-800">/help</td>
                                            <td class="px-4 py-3 text-slate-600">Show available commands and usage</td>
                                            <td class="px-4 py-3 text-center">✅</td>
                                            <td class="px-4 py-3 text-center">✅</td>
                                            <td class="px-4 py-3 text-center">✅</td>
                                            <td class="px-4 py-3 text-center">✅</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Usage examples -->
                            <div class="mt-6 grid md:grid-cols-2 gap-6">
                                <div class="bg-slate-50 border border-slate-200 rounded-lg p-5">
                                    <h4 class="font-semibold text-slate-800 mb-2">View your pendings</h4>
                                    <p class="text-slate-600 mb-3">Get a clean list of unread or no-reply instructions assigned to you:</p>
                                    <div class="font-mono text-sm bg-white border border-slate-200 rounded p-3">/pendings</div>
                                </div>
                                <div class="bg-slate-50 border border-slate-200 rounded-lg p-5">
                                    <h4 class="font-semibold text-slate-800 mb-2">Reply from Telegram</h4>
                                    <p class="text-slate-600 mb-3">Use the ID shown in /pendings to reply. Your reply is saved, notifies stakeholders, and appears in real-time:</p>
                                    <div class="font-mono text-sm bg-white border border-slate-200 rounded p-3">/reply 123 Thanks, I will handle this today.</div>
                                </div>
                            </div>

                            <div class="mt-4 text-xs text-slate-500">
                                <p>Notes:</p>
                                <ul class="list-disc ml-5 space-y-1 mt-1">
                                    <li>System Administrators cannot reply to instructions and are the only role authorized to run <span class="font-mono">/unlink</span>.</li>
                                    <li>Replies are linked to the correct instruction ID, saved to the database, and trigger email, in-app, Telegram, and broadcast notifications for real-time updates.</li>
                                    <li>Ensure your Telegram is linked and notifications are enabled to receive alerts: <span class="font-mono">/enable</span>.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Section Heading -->
                <div class="sticky top-0 lg:top-auto bg-slate-100/80 backdrop-blur-sm -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-4 mb-8 z-20 border-b border-slate-200">
                    <h2 class="text-2xl font-bold text-slate-800 flex items-center">
                        <i class="fas fa-shield-alt mr-4 text-slate-500"></i>
                        Account Security Tips
                    </h2>
                </div>

                <!-- Account Security Tips -->
                <section id="security-tips" class="mb-16 guide-section">
                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="bg-white rounded-xl shadow-lg p-6">
                            <i class="fas fa-user-secret text-2xl text-indigo-500 mb-3"></i>
                            <h3 class="font-bold text-lg text-slate-800 mb-2">Strong Passwords</h3>
                            <p class="text-slate-600 text-sm">Create a unique password for RCS App that you don't use anywhere else. A password manager can help you generate and store them securely.</p>
                                </div>
                        <div class="bg-white rounded-xl shadow-lg p-6">
                            <i class="fas fa-mobile-alt text-2xl text-indigo-500 mb-3"></i>
                            <h3 class="font-bold text-lg text-slate-800 mb-2">Enable 2FA</h3>
                            <p class="text-slate-600 text-sm">Two-factor authentication adds a critical second layer of security, requiring a code from your phone to log in.</p>
                            </div>
                        <div class="bg-white rounded-xl shadow-lg p-6">
                            <i class="fas fa-bell text-2xl text-indigo-500 mb-3"></i>
                            <h3 class="font-bold text-lg text-slate-800 mb-2">Monitor Activity</h3>
                            <p class="text-slate-600 text-sm">Periodically review your account's login history and enable Telegram notifications to be alerted to important events.</p>
                        </div>
                        <div class="bg-white rounded-xl shadow-lg p-6">
                            <i class="fas fa-envelope text-2xl text-indigo-500 mb-3"></i>
                            <h3 class="font-bold text-lg text-slate-800 mb-2">Secure Email</h3>
                            <p class="text-slate-600 text-sm">Your account is only as secure as the email linked to it. Protect your email with a strong password and 2FA as well.</p>
                                </div>
                        <div class="bg-white rounded-xl shadow-lg p-6">
                            <i class="fas fa-link text-2xl text-indigo-500 mb-3"></i>
                            <h3 class="font-bold text-lg text-slate-800 mb-2">Beware of Phishing</h3>
                            <p class="text-slate-600 text-sm">We will never ask for your password outside of the official login page. Be wary of suspicious emails or links asking for your credentials.</p>
                            </div>
                        <div class="bg-white rounded-xl shadow-lg p-6">
                            <i class="fas fa-shield-alt text-2xl text-indigo-500 mb-3"></i>
                            <h3 class="font-bold text-lg text-slate-800 mb-2">Regular Updates</h3>
                            <p class="text-slate-600 text-sm">Ensure your web browser and operating system are up-to-date to protect yourself from the latest security vulnerabilities.</p>
                </div>
            </div>
        </section>

                <!-- Section Heading -->
                <div class="sticky top-0 lg:top-auto bg-slate-100/80 backdrop-blur-sm -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-4 mb-8 z-20 border-b border-slate-200">
                    <h2 class="text-2xl font-bold text-slate-800 flex items-center">
                        <i class="fas fa-question-circle mr-4 text-yellow-500"></i>
                        Frequently Asked Questions
            </h2>
                </div>

        <!-- FAQ Section -->
                <section id="faq" class="mb-16 guide-section">
                    <div class="bg-white rounded-xl shadow-lg">
                        <div class="divide-y divide-slate-200">
                            <div x-data="{ open: false }" class="p-6">
                                <button @click="open = !open" class="flex justify-between items-center w-full text-left font-bold text-slate-800">
                            <span>How do I change my registered email address?</span>
                                    <i class="fas fa-chevron-down text-indigo-500 transition-transform duration-300" :class="{'rotate-180': open}"></i>
                        </button>
                                <div x-show="open" x-collapse class="mt-4 text-slate-600">
                                    <p>To change the email address associated with your account, please follow these steps:</p>
                                    <ol class="list-decimal pl-5 space-y-2 mt-3">
                                        <li>Log in to your RCS App account.</li>
                                        <li>Navigate to your Profile or Account Settings page.</li>
                                        <li>Find the option to "Change Email" or "Update Email".</li>
                                        <li>You will be prompted to enter your new email address and your current password for security verification.</li>
                                        <li>After submitting, a verification email will be sent to your new address. Click the link in that email to confirm the change.</li>
                            </ol>
                        </div>
                    </div>
                             <div x-data="{ open: false }" class="p-6">
                                <button @click="open = !open" class="flex justify-between items-center w-full text-left font-bold text-slate-800">
                            <span>What should I do if I suspect unauthorized access?</span>
                                    <i class="fas fa-chevron-down text-indigo-500 transition-transform duration-300" :class="{'rotate-180': open}"></i>
                        </button>
                                <div x-show="open" x-collapse class="mt-4 text-slate-600">
                                    <p>If you suspect unauthorized access, take these steps immediately to secure your account:</p>
                                     <ol class="list-decimal pl-5 space-y-2 mt-3">
                                        <li>Go to "Forgot Password" to reset your password to a new, strong, and unique one.</li>
                                        <li>Review your account's recent login activity for any unfamiliar locations or devices.</li>
                                        <li>If you have any saved payment methods, check them for unauthorized transactions.</li>
                                        <li>Enable Two-Factor Authentication (2FA) if you haven't already. This provides a crucial extra layer of security.</li>
                                        <li>Contact our support team if you need further assistance or see continued suspicious activity.</li>
                            </ol>
                        </div>
                    </div>
                            <div x-data="{ open: false }" class="p-6">
                                <button @click="open = !open" class="flex justify-between items-center w-full text-left font-bold text-slate-800">
                                    <span>Is it safe to reuse my password?</span>
                                    <i class="fas fa-chevron-down text-indigo-500 transition-transform duration-300" :class="{'rotate-180': open}"></i>
                        </button>
                                <div x-show="open" x-collapse class="mt-4 text-slate-600">
                                    <p>No, you should never reuse passwords across different websites or services. If one service experiences a data breach, attackers can use your leaked password to try and access your other accounts, including your RCS App account.</p>
                                    <p class="mt-2">We strongly recommend using a unique and complex password for RCS. A password manager is an excellent tool to help you generate, store, and manage unique passwords for all of your online accounts safely.</p>
                        </div>
                    </div>
                            <div x-data="{ open: false }" class="p-6">
                                <button @click="open = !open" class="flex justify-between items-center w-full text-left font-bold text-slate-800">
                            <span>Why am I not receiving the verification email?</span>
                                    <i class="fas fa-chevron-down text-indigo-500 transition-transform duration-300" :class="{'rotate-180': open}"></i>
                        </button>
                                <div x-show="open" x-collapse class="mt-4 text-slate-600">
                                    <p>Delivery issues can sometimes occur. If you're not receiving verification or password reset emails, please try the following:</p>
                                    <ol class="list-decimal pl-5 space-y-2 mt-3">
                                        <li><b>Check your spam/junk folder:</b> Emails from new senders can sometimes be incorrectly filtered.</li>
                                        <li><b>Verify the email address:</b> Double-check that there are no typos in the email address you provided.</li>
                                        <li><b>Add us to your contacts:</b> Add <span class="font-mono bg-slate-100 text-slate-800 px-1 py-0.5 rounded">{{ config('mail.from.address') }}</span> to your email client's address book or safe sender list.</li>
                                        <li><b>Wait a few minutes:</b> Email networks can occasionally have small delays.</li>
                                        <li>If problems persist, please contact our support team for assistance.</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Support Section -->
                <section id="support" class="guide-section">
                    <div class="bg-gradient-to-r from-indigo-600 to-blue-500 rounded-xl p-6 lg:p-8 text-center">
                        <i class="fas fa-headset text-4xl lg:text-5xl text-white/80 mb-4 lg:mb-6"></i>
                        <h2 class="text-2xl lg:text-3xl font-bold text-white mb-3 lg:mb-4">Need Further Assistance?</h2>
                        <p class="text-indigo-100 max-w-2xl mx-auto mb-6 lg:mb-8 text-sm lg:text-base px-4">Our support team is ready to help you with any questions or issues regarding your RCS App account.</p>
                <div class="flex flex-col sm:flex-row justify-center gap-3 lg:gap-4">
                            <a href="mailto:{{ config('mail.from.address') }}" class="bg-white text-indigo-600 px-4 lg:px-6 py-2.5 lg:py-3 rounded-lg font-semibold hover:bg-indigo-50 transition duration-300 flex items-center justify-center text-sm lg:text-base">
                        <i class="fas fa-envelope mr-2"></i> Email Support
                    </a>
                            <a href="https://t.me/edmarcrescencio" target="_blank" rel="noopener" class="bg-indigo-500/50 text-white px-4 lg:px-6 py-2.5 lg:py-3 rounded-lg font-semibold hover:bg-indigo-500/80 transition duration-300 flex items-center justify-center border border-indigo-400 text-sm lg:text-base">
                                <i class="fab fa-telegram-plane mr-2"></i> Open a Ticket
                    </a>
                </div>
            </div>
        </section>

            </div>

    <!-- Footer -->
            <footer class="bg-white border-t border-slate-200 mt-auto">
                <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
                        <div class="sm:col-span-2 lg:col-span-1">
                             <div class="flex items-center space-x-2 lg:space-x-3">
                                <img src="{{ $logoPath }}" alt="RCS Logo" class="h-6 lg:h-8 w-auto">
                                <span class="font-bold text-base lg:text-lg text-slate-800">RCS App</span>
                            </div>
                            <p class="mt-3 lg:mt-4 text-slate-500 text-xs lg:text-sm">Secure authentication and account management.</p>
                </div>
                <div>
                            <h3 class="font-semibold text-slate-800 mb-3 lg:mb-4 text-sm lg:text-base">Quick Links</h3>
                    <ul class="space-y-1 lg:space-y-2">
                                <li><a href="#login" class="text-xs lg:text-sm text-slate-600 hover:text-indigo-600 transition">Login</a></li>
                                <li><a href="#registration" class="text-xs lg:text-sm text-slate-600 hover:text-indigo-600 transition">Register</a></li>
                                <li><a href="#instructions-sending" class="text-xs lg:text-sm text-slate-600 hover:text-indigo-600 transition">Send Instruction</a></li>
                                <li><a href="#instructions-reading" class="text-xs lg:text-sm text-slate-600 hover:text-indigo-600 transition">My Instructions</a></li>
                                <li><a href="#task-priorities" class="text-xs lg:text-sm text-slate-600 hover:text-indigo-600 transition">Task Priorities</a></li>
                                <li><a href="#password-recovery" class="text-xs lg:text-sm text-slate-600 hover:text-indigo-600 transition">Password Recovery</a></li>
                    </ul>
                </div>
                <div>
                            <h3 class="font-semibold text-slate-800 mb-3 lg:mb-4 text-sm lg:text-base">Resources</h3>
                    <ul class="space-y-1 lg:space-y-2">
                                <li><a href="#faq" class="text-xs lg:text-sm text-slate-600 hover:text-indigo-600 transition">FAQ</a></li>
                                <li><a href="#security-tips" class="text-xs lg:text-sm text-slate-600 hover:text-indigo-600 transition">Security Tips</a></li>
                    </ul>
                </div>
                <div>
                            <h3 class="font-semibold text-slate-800 mb-3 lg:mb-4 text-sm lg:text-base">Connect With Us</h3>
                             <a href="mailto:{{ config('mail.from.address') }}" class="text-xs lg:text-sm text-slate-600 hover:text-indigo-600 transition break-all">{{ config('mail.from.address') }}</a>
                        </div>
                    </div>
                    <div class="border-t border-slate-200 mt-6 lg:mt-8 pt-6 lg:pt-8 text-center text-xs lg:text-sm text-slate-500">
                        <p>&copy; {{ now()->year }} RCS App. All Rights Reserved.</p>
                    </div>
                </div>
            </footer>
        </main>
        </div>

    <script>
        function guide() {
            return {
                sidebarOpen: false,
                activeSection: null,
                sections: [],

                init() {
                    this.sections = Array.from(document.querySelectorAll('.guide-section'));
                    this.setActiveSection();
                },

                setActiveSection() {
                    let currentSection = null;
                    const threshold = window.innerHeight * 0.3;

                    for (const section of this.sections) {
                        const rect = section.getBoundingClientRect();
                        if (rect.top <= threshold && rect.bottom >= threshold) {
                            currentSection = section.id;
                            break;
                }
                    }

                    if (!currentSection) {
                         // If no section is in the sweet spot, check if we're at the top
                        if (window.scrollY < window.innerHeight * 0.5) {
                            currentSection = 'introduction';
                        }
                        // Or if we are at the bottom
                        else if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 100) {
                             currentSection = this.sections[this.sections.length - 1].id;
                        }
                    }

                    this.activeSection = currentSection;
                }
            }
        }
    </script>
</body>
</html>
