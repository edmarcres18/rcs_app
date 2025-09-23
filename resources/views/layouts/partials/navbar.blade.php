<!-- Main Content -->
<div class="main-content">
    <div class="main-navbar">
        <button class="navbar-toggler" id="sidebar-toggle">
            <i class="fas fa-bars"></i>
        </button>

        <h1 class="page-title">@yield('title', config('app.name'))</h1>

        <div class="navbar-menu">

            <div class="navbar-menu-item has-badge notification-toggle" id="notification-toggle">
                <i class="fas fa-bell"></i>
                <span class="notification-badge"></span>
            </div>

            @auth
                @php($role = Auth::user()->roles)
                @if(!in_array($role, [\App\Enums\UserRole::SYSTEM_ADMIN], true))
                    <div class="navbar-menu-item has-badge system-notification-toggle" id="system-notification-toggle">
                        <i class="fas fa-bullhorn"></i>
                        <span class="system-notification-badge"></span>
                    </div>
                @endif
            @endauth

            @guest
                <div class="navbar-menu">
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="navbar-menu-item">
                            <i class="fas fa-sign-in-alt"></i>
                        </a>
                    @endif

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="navbar-menu-item">
                            <i class="fas fa-user-plus"></i>
                        </a>
                    @endif
                </div>
            @else
                <div class="dropdown navbar-user">
                    <div class="navbar-user-avatar dropdown-toggle" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    @if (Auth::user()->avatar)
                        <img src="{{ Auth::user()->avatar }}" alt="Profile Picture" class="rounded-circle img-fluid" style="width: 36px; height: 36px; object-fit: cover;">
                    @else
                        <img src="https://ui-avatars.com/api/?name={{ Auth::user()->full_name }}&background=4070f4&color=fff&size=150" alt="Profile Picture" class="rounded-circle img-fluid" style="width: 36px; height: 36px;">
                    @endif
                    </div>
                </div>
            @endguest
        </div>
    </div>

    <div class="page-content">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>Please check the form for errors.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        @yield('content')
    </div>
</div>
