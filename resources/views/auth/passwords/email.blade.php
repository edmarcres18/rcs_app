@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')
<div class="container auth-form-container">
    <div class="row justify-content-center w-100">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-lg auth-card">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        @include('auth.partials._logo')
                        <h4 class="mt-3 mb-0">Forgot Your Password?</h4>
                        <p class="text-muted">No problem. Enter your email address and we'll send you a link to reset it.</p>
                    </div>

                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf

                        <div class="form-group mb-3">
                            <label for="email" class="form-label">{{ __('Email Address') }} <span class="text-danger">*</span></label>
                            <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="Enter your email address">
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg" id="email-btn">
                                <span class="btn-text">{{ __('Send Password Reset Link') }}</span>
                                <span class="btn-loading d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    {{ __('Sending...') }}
                                </span>
                            </button>
                        </div>

                        @if (Route::has('login'))
                        <div class="text-center mt-4">
                            <p class="text-muted"><a href="{{ route('login') }}" class="text-decoration-none fw-medium">Back to Login</a></p>
                        </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const emailForm = document.querySelector('form');
    const emailBtn = document.querySelector('#email-btn');
    const btnText = document.querySelector('.btn-text');
    const btnLoading = document.querySelector('.btn-loading');

    // Handle form submission to prevent duplicate submits
    if (emailForm && emailBtn) {
        emailForm.addEventListener('submit', function() {
            // Disable button and show loading state
            emailBtn.disabled = true;
            btnText.classList.add('d-none');
            btnLoading.classList.remove('d-none');
        });
    }
});
</script>
@endpush
