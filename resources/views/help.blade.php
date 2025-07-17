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
        /* Custom scrollbar for a cleaner look */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
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
    </style>
</head>
<body class="bg-slate-100 font-sans" x-data="guide()" x-on:scroll.window="setActiveSection()">
@php
    $authLogo = 'images/app_logo/auth_logo.png';
    $defaultLogo = 'images/app_logo/logo.png';
    $logoPath = file_exists(public_path($authLogo)) ? asset($authLogo) : asset($defaultLogo);
    $appUrl = config('app.url');
    $visitUrl = str_contains($appUrl, 'localhost') ? url('/') : $appUrl;
@endphp

    <!-- Mobile header with menu toggle -->
    <header class="sticky top-0 z-30 bg-white/80 backdrop-blur-sm shadow-sm lg:hidden">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <img src="{{ $logoPath }}" alt="RCS Logo" class="h-8 w-auto">
                <span class="font-bold text-lg text-slate-800">RCS Guide</span>
            </div>
            <button @click="sidebarOpen = !sidebarOpen" class="text-slate-600 hover:text-indigo-600">
                <i class="fas fa-bars text-xl"></i>
            </button>
        </div>
    </header>

    <div class="relative flex min-h-screen">
        <!-- Sidebar Navigation -->
        <aside
            class="fixed inset-y-0 left-0 z-40 w-64 bg-white shadow-lg transform lg:transform-none lg:translate-x-0 transition-transform duration-300 ease-in-out"
            :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}">

            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div class="h-20 flex items-center justify-center border-b border-slate-200">
                     <div class="flex items-center space-x-3">
                        <img src="{{ $logoPath }}" alt="RCS Logo" class="h-10 w-auto">
                        <span class="font-bold text-xl text-slate-800">RCS Guide</span>
                    </div>
                </div>

                <!-- Nav Links -->
                <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
                    <a href="#introduction" @click="sidebarOpen = false" class="sidebar-link flex items-center px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors" :class="{ 'active': activeSection === 'introduction' }">
                        <i class="fas fa-book-open w-6 text-center text-slate-400 mr-3"></i> Welcome
                    </a>
                    <a href="#registration" @click="sidebarOpen = false" class="sidebar-link flex items-center px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors" :class="{ 'active': activeSection === 'registration' }">
                        <i class="fas fa-user-plus w-6 text-center text-slate-400 mr-3"></i> Registration
                    </a>
                    <a href="#email-verification" @click="sidebarOpen = false" class="sidebar-link flex items-center px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors" :class="{ 'active': activeSection === 'email-verification' }">
                        <i class="fas fa-envelope-open-text w-6 text-center text-slate-400 mr-3"></i> Email Verification
                    </a>
                    <a href="#login" @click="sidebarOpen = false" class="sidebar-link flex items-center px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors" :class="{ 'active': activeSection === 'login' }">
                        <i class="fas fa-sign-in-alt w-6 text-center text-slate-400 mr-3"></i> Logging In
                    </a>
                    <a href="#password-recovery" @click="sidebarOpen = false" class="sidebar-link flex items-center px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors" :class="{ 'active': activeSection === 'password-recovery' }">
                        <i class="fas fa-key w-6 text-center text-slate-400 mr-3"></i> Password Recovery
                    </a>
                    <a href="#telegram-notifications" @click="sidebarOpen = false" class="sidebar-link flex items-center px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors" :class="{ 'active': activeSection === 'telegram-notifications' }">
                        <i class="fab fa-telegram w-6 text-center text-slate-400 mr-3"></i> Telegram Bot
                    </a>
                    <a href="#security-tips" @click="sidebarOpen = false" class="sidebar-link flex items-center px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors" :class="{ 'active': activeSection === 'security-tips' }">
                        <i class="fas fa-shield-alt w-6 text-center text-slate-400 mr-3"></i> Security Tips
                    </a>
                    <a href="#faq" @click="sidebarOpen = false" class="sidebar-link flex items-center px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors" :class="{ 'active': activeSection === 'faq' }">
                        <i class="fas fa-question-circle w-6 text-center text-slate-400 mr-3"></i> FAQ
                    </a>
                    <a href="#support" @click="sidebarOpen = false" class="sidebar-link flex items-center px-4 py-2 text-slate-600 hover:bg-slate-100 rounded-lg transition-colors" :class="{ 'active': activeSection === 'support' }">
                        <i class="fas fa-headset w-6 text-center text-slate-400 mr-3"></i> Support
                    </a>
                </nav>

                <!-- Back button -->
                <div class="px-6 py-4 border-t border-slate-200">
                    <a href="{{ url('/') }}" class="w-full flex items-center justify-center px-4 py-2 text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 rounded-lg transition-colors font-semibold">
                        <i class="fas fa-arrow-left mr-2"></i> Go Back
                    </a>
                </div>
            </div>
        </aside>

        <!-- Backdrop for mobile sidebar -->
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/50 z-30 lg:hidden"></div>

    <!-- Main Content -->
        <main class="flex-1 lg:ml-64">
            <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">

                <!-- Introduction Section -->
                <section id="introduction" class="mb-24 guide-section">
                    <div class="text-center">
                        <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 tracking-tight">RCS App User Guide</h1>
                        <p class="mt-4 text-lg text-slate-600 max-w-3xl mx-auto">Welcome! This guide provides comprehensive instructions for all of the app's authentication processes.</p>
                        <a href="{{ $visitUrl }}" target="_blank" class="mt-8 inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            <i class="fas fa-external-link-alt mr-2"></i> Visit RCS App
                        </a>
                    </div>

                    <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8">
                        <div class="bg-white p-6 rounded-xl shadow-sm transition-transform hover:-translate-y-1">
                            <i class="fas fa-shield-alt text-3xl text-indigo-500 mb-4"></i>
                            <h3 class="font-bold text-slate-800 mb-2">Secure Authentication</h3>
                            <p class="text-slate-600 text-sm">Industry-standard security protocols to protect your account.</p>
                        </div>
                        <div class="bg-white p-6 rounded-xl shadow-sm transition-transform hover:-translate-y-1">
                            <i class="fas fa-bell text-3xl text-green-500 mb-4"></i>
                            <h3 class="font-bold text-slate-800 mb-2">Real-time Notifications</h3>
                            <p class="text-slate-600 text-sm">Get instant alerts about your account activity via multiple channels.</p>
                    </div>
                        <div class="bg-white p-6 rounded-xl shadow-sm transition-transform hover:-translate-y-1">
                            <i class="fas fa-user-check text-3xl text-purple-500 mb-4"></i>
                            <h3 class="font-bold text-slate-800 mb-2">Easy Recovery</h3>
                            <p class="text-slate-600 text-sm">Simple processes to regain access if you're locked out.</p>
                </div>
            </div>
        </section>

                <!-- Section Heading -->
                <div class="sticky top-0 lg:top-auto bg-slate-100/80 backdrop-blur-sm -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-4 mb-8 z-20 border-b border-slate-200">
                    <h2 class="text-2xl font-bold text-slate-800 flex items-center">
                <i class="fas fa-user-plus mr-4 text-blue-500"></i>
                Account Registration
            </h2>
                        </div>

                <!-- Registration Section -->
                <section id="registration" class="mb-16 guide-section">
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="grid md:grid-cols-2">
                            <div class="p-8">
                                <p class="text-slate-600 mb-8">Follow these steps to create your new RCS App account. A strong password should be at least 8 characters long and include a mix of uppercase letters, lowercase letters, numbers, and symbols.</p>
                                <div class="space-y-8">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 w-10 h-10 bg-blue-500 text-white font-bold rounded-full flex items-center justify-center mr-4">1</div>
                            <div>
                                            <h3 class="font-bold text-lg text-slate-800">Access Registration Page</h3>
                                            <p class="text-slate-600">Click on "Register" on the app homepage or navigate directly to the registration page.</p>
                            </div>
                        </div>
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 w-10 h-10 bg-blue-500 text-white font-bold rounded-full flex items-center justify-center mr-4">2</div>
                            <div>
                                            <h3 class="font-bold text-lg text-slate-800">Fill Form</h3>
                                            <p class="text-slate-600">Provide your name, a unique nickname, email, role, and a strong password.</p>
                    </div>
                                    </div>
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 w-10 h-10 bg-blue-500 text-white font-bold rounded-full flex items-center justify-center mr-4">3</div>
                                    <div>
                                            <h3 class="font-bold text-lg text-slate-800">Complete & Verify</h3>
                                            <p class="text-slate-600">Submit the form and use the OTP sent to your email to verify your account.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-slate-50 p-8 flex items-center justify-center md:order-1">
                                <div class="bg-white p-6 rounded-lg shadow-md w-full max-w-sm border border-slate-200">
                                    <div class="border-b border-slate-200 pb-4 mb-4">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="text-sm text-slate-500">From: {{ config('mail.from.address') }}</span>
                                            <span class="text-xs text-slate-400">Just now</span>
                                        </div>
                                        <h4 class="font-bold text-lg text-slate-800">Your RCS App Verification Code</h4>
                                    </div>
                                    <div class="mb-6">
                                        <p class="text-slate-700 mb-4">Hello [User],</p>
                                        <p class="text-slate-700 mb-4">Thank you for registering. Here is your One-Time Password (OTP) for email verification:</p>
                                        <div class="text-center my-4">
                                            <span class="text-3xl font-bold tracking-widest bg-slate-100 px-4 py-2 rounded-lg text-slate-800">123456</span>
                                </div>
                                        <p class="text-slate-500 text-sm">This code will expire in 15 minutes. If you did not create an account, no further action is required.</p>
                                </div>
                                    <div class="text-xs text-slate-400">
                                        <p>© 2024 RCS App. All rights reserved.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 bg-blue-50 border-l-4 border-blue-400 p-6 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-blue-500 text-xl mt-1 mr-4"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-blue-800">Didn't receive the email?</h3>
                                <div class="mt-2 text-sm text-blue-700 space-y-1">
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
                <div class="sticky top-0 lg:top-auto bg-slate-100/80 backdrop-blur-sm -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-4 mb-8 z-20 border-b border-slate-200">
                    <h2 class="text-2xl font-bold text-slate-800 flex items-center">
                        <i class="fas fa-envelope-check mr-4 text-green-500"></i>
                        Email Verification
                    </h2>
                </div>

                <!-- Email Verification Section -->
                <section id="email-verification" class="mb-16 guide-section">
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <div class="grid md:grid-cols-2">
                            <div class="p-8">
                                <h3 class="font-bold text-xl text-slate-800 mb-4">Confirm Your Account</h3>
                                <p class="text-slate-600 mb-8">To complete your registration, you must verify that you own the email address you signed up with. This is a crucial security step to protect your account.</p>
                                <div class="space-y-8">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 w-10 h-10 bg-green-500 text-white font-bold rounded-full flex items-center justify-center mr-4">1</div>
                                        <div>
                                            <h3 class="font-bold text-lg text-slate-800">Check Your Inbox</h3>
                                            <p class="text-slate-600">Find the email from <span class="font-mono bg-slate-100 text-slate-800 px-1 py-0.5 rounded">{{ config('mail.from.address') }}</span>. It contains your 6-digit One-Time Password (OTP).</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 w-10 h-10 bg-green-500 text-white font-bold rounded-full flex items-center justify-center mr-4">2</div>
                                        <div>
                                            <h3 class="font-bold text-lg text-slate-800">Enter Your Details</h3>
                                            <p class="text-slate-600">On the verification page, enter the email address you used for registration and the 6-digit OTP.</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 w-10 h-10 bg-green-500 text-white font-bold rounded-full flex items-center justify-center mr-4">3</div>
                                        <div>
                                            <h3 class="font-bold text-lg text-slate-800">Verify & Complete</h3>
                                            <p class="text-slate-600">Click "Verify Account". If the OTP is correct, your account will be activated, and you'll be redirected to a success page.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-slate-50 p-8 flex items-center justify-center">
                                <div class="bg-white p-6 rounded-lg shadow-md w-full max-w-sm border border-slate-200">
                                    <h4 class="font-bold text-lg text-center mb-1 text-slate-800">Verify Your Account</h4>
                                    <p class="text-slate-500 text-sm text-center mb-6">Enter the OTP sent to your email.</p>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-slate-700 text-sm font-medium mb-1">Email Address</label>
                                            <input type="email" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none" placeholder="your@email.com" value="user@example.com">
                                        </div>
                                        <div>
                                            <label class="block text-slate-700 text-sm font-medium mb-1">One-Time Password (OTP)</label>
                                            <input type="text" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 outline-none" placeholder="Enter 6-digit OTP">
                                        </div>
                                        <button class="w-full bg-green-600 text-white py-2.5 rounded-lg font-medium hover:bg-green-700 transition duration-300">Verify Account</button>
                                        <div class="text-center text-sm text-slate-500 pt-2">
                                            <p>Didn't receive the code? <a href="#" class="text-indigo-600 hover:underline font-medium">Resend OTP</a></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 bg-green-50 border-l-4 border-green-400 p-6 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-lightbulb text-green-500 text-xl mt-1 mr-4"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-green-800">Having trouble with your OTP?</h3>
                                <div class="mt-2 text-sm text-green-700 space-y-1">
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
                <div class="sticky top-0 lg:top-auto bg-slate-100/80 backdrop-blur-sm -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-4 mb-8 z-20 border-b border-slate-200">
                    <h2 class="text-2xl font-bold text-slate-800 flex items-center">
                        <i class="fas fa-sign-in-alt mr-4 text-purple-500"></i>
                        Logging In
                    </h2>
                </div>

                <!-- Login Section -->
                <section id="login" class="mb-16 guide-section">
                    <div class="grid md:grid-cols-2 gap-8">
                        <div class="bg-white rounded-xl shadow-lg p-8">
                            <h3 class="font-bold text-xl text-slate-800 mb-6">Standard Login</h3>
                            <div class="space-y-6">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 w-8 h-8 border-2 border-purple-500 text-purple-500 font-bold rounded-full flex items-center justify-center mr-4">1</div>
                                    <p class="text-slate-600 pt-1">Navigate to the login page.</p>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 w-8 h-8 border-2 border-purple-500 text-purple-500 font-bold rounded-full flex items-center justify-center mr-4">2</div>
                                    <p class="text-slate-600 pt-1">Enter your registered email/nickname and password.</p>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 w-8 h-8 border-2 border-purple-500 text-purple-500 font-bold rounded-full flex items-center justify-center mr-4">3</div>
                                    <p class="text-slate-600 pt-1">Click "Login" to access your dashboard.</p>
                            </div>
                            </div>
                        </div>
                        <div class="bg-white rounded-xl shadow-lg p-8">
                            <h3 class="font-bold text-xl text-slate-800 mb-6">Two-Factor Authentication (2FA)</h3>
                            <div class="space-y-6">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 w-8 h-8 border-2 border-purple-500 text-purple-500 font-bold rounded-full flex items-center justify-center mr-4">1</div>
                                    <p class="text-slate-600 pt-1">Enable 2FA in your account settings.</p>
                    </div>
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 w-8 h-8 border-2 border-purple-500 text-purple-500 font-bold rounded-full flex items-center justify-center mr-4">2</div>
                                    <p class="text-slate-600 pt-1">On login, provide the 6-digit code from your authenticator app.</p>
                </div>
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 w-8 h-8 border-2 border-purple-500 text-purple-500 font-bold rounded-full flex items-center justify-center mr-4">3</div>
                                    <p class="text-slate-600 pt-1">Optionally, trust the device to skip 2FA on future logins.</p>
                </div>
            </div>
                        </div>
                    </div>

                    <div class="mt-8 bg-white rounded-xl shadow-lg p-8">
                        <h3 class="font-bold text-xl text-slate-800 mb-4">Troubleshooting Login Issues</h3>
                        <div class="grid md:grid-cols-2 gap-x-8 gap-y-4">
                            <div class="border-l-4 border-purple-300 pl-4">
                                <h4 class="font-bold text-slate-800">Incorrect Password</h4>
                                <p class="text-slate-600 text-sm">Use the "Forgot Password" feature to reset it.</p>
                            </div>
                            <div class="border-l-4 border-purple-300 pl-4">
                                <h4 class="font-bold text-slate-800">Account Locked</h4>
                                <p class="text-slate-600 text-sm">For security, your account may lock after multiple failed attempts. Please wait before trying again.</p>
                            </div>
                            <div class="border-l-4 border-purple-300 pl-4">
                                <h4 class="font-bold text-slate-800">Browser Issues</h4>
                                <p class="text-slate-600 text-sm">Try clearing your browser cache or using a different browser.</p>
                        </div>
                            <div class="border-l-4 border-purple-300 pl-4">
                                <h4 class="font-bold text-slate-800">Email Not Recognized</h4>
                                <p class="text-slate-600 text-sm">Ensure you are using the correct registered email or nickname.</p>
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
                        <div class="grid md:grid-cols-2">
                             <div class="p-8">
                                <p class="text-slate-600 mb-8">Forgot your password? No problem. Here's how to reset it.</p>
                                <div class="space-y-8">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 w-10 h-10 bg-red-500 text-white font-bold rounded-full flex items-center justify-center mr-4">1</div>
                        <div>
                                            <h3 class="font-bold text-lg text-slate-800">Request Reset</h3>
                                            <p class="text-slate-600">On the login page, click "Forgot Password" and enter your email.</p>
                        </div>
                    </div>
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 w-10 h-10 bg-red-500 text-white font-bold rounded-full flex items-center justify-center mr-4">2</div>
                        <div>
                                            <h3 class="font-bold text-lg text-slate-800">Check Your Email</h3>
                                            <p class="text-slate-600">You'll receive an email with a secure password reset link. Note that this link is only valid for 24 hours.</p>
                        </div>
                    </div>
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 w-10 h-10 bg-red-500 text-white font-bold rounded-full flex items-center justify-center mr-4">3</div>
                        <div>
                                            <h3 class="font-bold text-lg text-slate-800">Create New Password</h3>
                                            <p class="text-slate-600">Click the link and follow the instructions to set a new password.</p>
                        </div>
                    </div>
                </div>
            </div>
                            <div class="bg-slate-50 p-8 flex items-center justify-center">
                                <div class="bg-white p-6 rounded-lg shadow-md w-full max-w-md border border-slate-200">
                                    <h4 class="font-bold text-lg text-center mb-6 text-slate-800">Reset Your Password</h4>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-slate-700 text-sm font-medium mb-1">Email Address</label>
                                            <input type="email" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 outline-none" placeholder="your@email.com">
                                        </div>
                                        <button class="w-full bg-red-600 text-white py-2.5 rounded-lg font-medium hover:bg-red-700 transition duration-300">Send Reset Link</button>
                                        <div class="text-center text-sm text-slate-500 pt-2">
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
                                            <p class="text-slate-600">Search for <span class="font-mono bg-slate-200 px-2 py-1 rounded">@mhr_rcs_bot</span> in Telegram.</p>
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
                                            <span class="text-xs text-slate-500 block">via @mhr_rcs_bot</span>
                            </div>
                                        <span class="text-xs text-slate-500 ml-auto">12:34 PM</span>
                        </div>
                                    <p class="text-slate-700 bg-slate-100 p-3 rounded-md mt-2"><b>Security Alert:</b> New login detected from Chrome on Windows near New York, US. If this wasn't you, please secure your account immediately.</p>
                        </div>
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
                    <div class="bg-gradient-to-r from-indigo-600 to-blue-500 rounded-xl p-8 text-center">
                        <i class="fas fa-headset text-5xl text-white/80 mb-6"></i>
                        <h2 class="text-3xl font-bold text-white mb-4">Need Further Assistance?</h2>
                        <p class="text-indigo-100 max-w-2xl mx-auto mb-8">Our support team is ready to help you with any questions or issues regarding your RCS App account.</p>
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                            <a href="mailto:{{ config('mail.from.address') }}" class="bg-white text-indigo-600 px-6 py-3 rounded-lg font-semibold hover:bg-indigo-50 transition duration-300 flex items-center justify-center">
                        <i class="fas fa-envelope mr-2"></i> Email Support
                    </a>
                            <a href="mailto:{{ config('mail.from.address') }}?subject=Support%20Ticket%20Request" class="bg-indigo-500/50 text-white px-6 py-3 rounded-lg font-semibold hover:bg-indigo-500/80 transition duration-300 flex items-center justify-center border border-indigo-400">
                                <i class="fas fa-life-ring mr-2"></i> Open a Ticket
                    </a>
                </div>
            </div>
        </section>

            </div>

    <!-- Footer -->
            <footer class="bg-white border-t border-slate-200 mt-auto">
                <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12">
                    <div class="grid md:grid-cols-4 gap-8">
                        <div class="md:col-span-1">
                             <div class="flex items-center space-x-3">
                                <img src="{{ $logoPath }}" alt="RCS Logo" class="h-8 w-auto">
                                <span class="font-bold text-lg text-slate-800">RCS App</span>
                            </div>
                            <p class="mt-4 text-slate-500 text-sm">Secure authentication and account management.</p>
                </div>
                <div>
                            <h3 class="font-semibold text-slate-800 mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                                <li><a href="#login" class="text-sm text-slate-600 hover:text-indigo-600 transition">Login</a></li>
                                <li><a href="#registration" class="text-sm text-slate-600 hover:text-indigo-600 transition">Register</a></li>
                                <li><a href="#password-recovery" class="text-sm text-slate-600 hover:text-indigo-600 transition">Password Recovery</a></li>
                    </ul>
                </div>
                <div>
                            <h3 class="font-semibold text-slate-800 mb-4">Resources</h3>
                    <ul class="space-y-2">
                                <li><a href="#faq" class="text-sm text-slate-600 hover:text-indigo-600 transition">FAQ</a></li>
                                <li><a href="#security-tips" class="text-sm text-slate-600 hover:text-indigo-600 transition">Security Tips</a></li>
                    </ul>
                </div>
                <div>
                            <h3 class="font-semibold text-slate-800 mb-4">Connect With Us</h3>
                             <a href="mailto:{{ config('mail.from.address') }}" class="text-sm text-slate-600 hover:text-indigo-600 transition">{{ config('mail.from.address') }}</a>
                        </div>
                    </div>
                    <div class="border-t border-slate-200 mt-8 pt-8 text-center text-sm text-slate-500">
                        <p>&copy; 2024 RCS App. All Rights Reserved.</p>
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
