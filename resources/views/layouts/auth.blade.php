<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, shrink-to-fit=no">
    <meta name="theme-color" content="#4070f4">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="MHR RCS">
    <meta name="application-name" content="MHR RCS">
    <meta name="msapplication-TileColor" content="#4070f4">
    <meta name="msapplication-tap-highlight" content="no">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'Laravel'))</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#317EFB"/>
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('{{ asset('serviceworker.js') }}', { scope: '/' });
        }
    </script>
    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .auth-form-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 1.5rem 0;
        }
        .auth-card {
            border-radius: 1rem;
        }
        /* On small screens, make the card take up more space and feel more integrated */
        @media (max-width: 575.98px) {
            .auth-form-container {
                align-items: stretch; /* Stretch card to full height */
                padding: 0;
            }
            .auth-card {
                border: 0;
                border-radius: 0;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }
            .card.auth-card {
                box-shadow: none !important;
            }
        }

        /* Floating help button styles */
        .floating-help-btn {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            display: flex;
            align-items: center;
            background: linear-gradient(45deg, #0d6efd, #0dcaf0); /* Stunning blue gradient */
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2); /* Softer, more pronounced shadow */
            text-decoration: none;
            font-size: 1rem;
            z-index: 1050;
            transition: all 0.3s ease-in-out;
            border: none;
        }

        .floating-help-btn:hover {
            background: linear-gradient(45deg, #0b5ed7, #0a9ebf); /* Darker gradient on hover */
            color: white;
            transform: translateY(-5px) scale(1.05); /* Lift and scale up on hover */
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3); /* Enhanced shadow on hover */
        }

        .floating-help-btn i {
            font-size: 1.5rem;
            transition: transform 0.3s ease;
        }

        .floating-help-btn:hover i {
            transform: rotate(10deg); /* Slightly rotate icon on hover */
        }

        /* Responsive adjustments for smaller screens */
        @media (max-width: 767.98px) {
            .floating-help-btn span {
                display: none;
            }
            .floating-help-btn {
                padding: 0.5rem;
                border-radius: 50%;
                width: 50px;
                height: 50px;
                justify-content: center;
            }
            .floating-help-btn i {
                margin-left: 0 !important; /* Reset margin for icon when text is hidden */
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div id="app">
        <main class="bg-light auth-form-container">
            @yield('content')
        </main>
    </div>

    <a href="{{ url('/help') }}" class="floating-help-btn" title="RCS Guide">
        <span>RCS Guide</span>
        <i class="fas fa-question-circle ms-2"></i>
    </a>

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

            let successMessage = '';
            @if (session('success'))
                successMessage = '{{ session('success') }}';
            @elseif (session('status'))
                successMessage = '{{ session('status') }}';
            @elseif (session('resent'))
                successMessage = '{{ __('A fresh verification link has been sent to your email address.') }}';
            @endif

            if (successMessage) {
                 Toast.fire({
                    icon: 'success',
                    title: successMessage
                });
            }

            @if (session('error'))
                Toast.fire({
                    icon: 'error',
                    title: '{{ session('error') }}'
                });
            @endif
            @if ($errors->any())
                const errorMessages = @json($errors->all());
                let errorText = '';
                errorMessages.forEach(function(error) {
                    errorText += `<p class='text-start mb-0'>${error}</p>`;
                });

                Swal.fire({
                    icon: 'error',
                    title: 'Oops... Something went wrong!',
                    html: errorText,
                });
            @endif
        });
    </script>
    @stack('scripts')
</body>
</html>
