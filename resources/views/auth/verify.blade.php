@extends('layouts.auth')

@section('title', 'Verify Email')

@section('content')
<div class="container">
    <div class="row justify-content-center w-100">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg auth-card">
                <div class="card-body p-4 p-md-5 text-center">
                    @include('auth.partials._logo')
                    <h4 class="mt-3 mb-2">{{ __('Verify Your Email Address') }}</h4>

                    <p class="text-muted mb-4">
                        {{ __('Before proceeding, please check your email for a verification link.') }}
                    </p>

                    <p class="text-muted">
                        {{ __('If you did not receive the email') }},
                    </p>
                    <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">{{ __('Resend Verification Email') }}</button>
                    </form>

                    <div class="mt-4">
                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-decoration-none fw-medium">
                           <i class="fas fa-sign-out-alt"></i> {{ __('Logout') }}
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
