@extends('layouts.auth')

@section('title', 'Verify OTP')

@section('content')
<div class="container">
    <div class="row justify-content-center w-100">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg auth-card">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        @include('auth.partials._logo')
                        <h4 class="mt-3 mb-0">Verify Your Account</h4>
                        <p class="text-muted">An OTP has been sent to your email. Please enter it below.</p>
                    </div>

                    <form method="POST" action="{{ route('verification.verify') }}" id="verify-form">
                        @csrf
                        <div class="form-group mb-3">
                            <label for="email" class="form-label">{{ __('Email Address') }} <span class="text-danger">*</span></label>
                            <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="Enter your email address">
                        </div>

                        <div class="form-group mb-3">
                            <label for="otp" class="form-label">{{ __('One-Time Password (OTP)') }} <span class="text-danger">*</span></label>
                            <input id="otp" type="text" class="form-control" name="otp" required placeholder="Enter 6-digit OTP">
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg" id="verify-btn">
                                <span class="btn-text">{{ __('Verify Account') }}</span>
                                <span class="btn-loading d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    {{ __('Verifying...') }}
                                </span>
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                         <p class="text-muted">Didn't receive the code?</p>
                         <button id="resend-btn" class="btn btn-link text-decoration-none fw-medium p-0">Resend OTP</button>
                    </div>

                    <form method="POST" action="{{ route('verification.resend') }}" id="resend-form" class="d-none">
                        @csrf
                        <input id="resend-email" type="hidden" name="email">
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
    const resendBtn = document.getElementById('resend-btn');
    const emailInput = document.getElementById('email');
    const resendEmailInput = document.getElementById('resend-email');
    const resendForm = document.getElementById('resend-form');
    const verifyForm = document.getElementById('verify-form');
    const verifyBtn = document.getElementById('verify-btn');
    const btnText = document.querySelector('.btn-text');
    const btnLoading = document.querySelector('.btn-loading');

    if(resendBtn) {
        resendBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const emailValue = emailInput.value;
            if (!emailValue) {
                Swal.fire({
                    icon: 'error',
                    title: 'Email required',
                    text: 'Please enter your email address to resend the OTP.',
                });
                return;
            }
            resendEmailInput.value = emailValue;
            resendForm.submit();
        });
    }

    // Handle form submission to prevent duplicate submits
    if (verifyForm && verifyBtn) {
        verifyForm.addEventListener('submit', function() {
            // Disable button and show loading state
            verifyBtn.disabled = true;
            btnText.classList.add('d-none');
            btnLoading.classList.remove('d-none');
        });
    }
});
</script>
@endpush
