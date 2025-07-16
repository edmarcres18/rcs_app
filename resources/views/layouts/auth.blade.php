<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

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
    </style>
</head>
<body>
    <div id="app">
        <main class="bg-light auth-form-container">
            @yield('content')
        </main>
    </div>

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
