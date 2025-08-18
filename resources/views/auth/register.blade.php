@extends('layouts.auth')

@section('title', 'Register')

@section('content')
<div class="container">
    <div class="row justify-content-center w-100">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg auth-card">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        @include('auth.partials._logo')
                        <h4 class="mt-3 mb-0">Create an Account</h4>
                        <p class="text-muted">Get started with your free account.</p>
                    </div>

                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="first_name" class="form-label">{{ __('First Name') }} <span class="text-danger">*</span></label>
                                    <input id="first_name" type="text" class="form-control" name="first_name" value="{{ old('first_name') }}" required autocomplete="first_name" autofocus placeholder="e.g. John">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="last_name" class="form-label">{{ __('Last Name') }} <span class="text-danger">*</span></label>
                                    <input id="last_name" type="text" class="form-control" name="last_name" value="{{ old('last_name') }}" required autocomplete="last_name" placeholder="e.g. Doe">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="middle_name" class="form-label">{{ __('Middle Name') }}</label>
                                    <input id="middle_name" type="text" class="form-control" name="middle_name" value="{{ old('middle_name') }}" autocomplete="middle_name" placeholder="Optional">
                                </div>
                            </div>
                             <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="nickname" class="form-label">{{ __('Nickname') }} <span class="text-danger">*</span></label>
                                    <input id="nickname" type="text" class="form-control" name="nickname" value="{{ old('nickname') }}" required autocomplete="nickname" placeholder="e.g. johndoe">
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="email" class="form-label">{{ __('Email Address') }} <span class="text-danger">*</span></label>
                            <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="e.g. john.doe@example.com">
                        </div>

                        <div class="form-group mb-3">
                            <label for="roles" class="form-label">{{ __('Role') }} <span class="text-danger">*</span></label>
                            <select id="roles" class="form-select" name="roles" required>
                                <option value="" disabled selected>Select your role</option>
                                <option value="EMPLOYEE" {{ old('roles') == 'EMPLOYEE' ? 'selected' : '' }}>Employee</option>
                                <option value="SUPERVISOR" {{ old('roles') == 'SUPERVISOR' ? 'selected' : '' }}>Supervisor</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="password" class="form-label">{{ __('Password') }} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input id="password" type="password" class="form-control" name="password" required autocomplete="new-password" placeholder="Create a password">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fa fa-eye" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="password-confirm" class="form-label">{{ __('Confirm Password') }} <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required autocomplete="new-password" placeholder="Confirm password">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirm">
                                            <i class="fa fa-eye" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary btn-lg" id="register-btn">
                                <span class="btn-text">{{ __('Register') }}</span>
                                <span class="btn-loading d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                                    {{ __('Creating Account...') }}
                                </span>
                            </button>
                        </div>

                        @if (Route::has('login'))
                        <div class="text-center mt-4">
                            <p class="text-muted">Already have an account? <a href="{{ route('login') }}" class="text-decoration-none fw-medium">Sign In</a></p>
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
    const registerForm = document.querySelector('form');
    const registerBtn = document.querySelector('#register-btn');
    const btnText = document.querySelector('.btn-text');
    const btnLoading = document.querySelector('.btn-loading');

    function togglePasswordVisibility(toggleBtnId, passwordInputId) {
        const toggleBtn = document.querySelector(`#${toggleBtnId}`);
        const passwordInput = document.querySelector(`#${passwordInputId}`);

        if (toggleBtn && passwordInput) {
            toggleBtn.addEventListener('click', function () {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                const icon = this.querySelector('i');
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            });
        }
    }

    togglePasswordVisibility('togglePassword', 'password');
    togglePasswordVisibility('togglePasswordConfirm', 'password-confirm');

    // Handle form submission to prevent duplicate submits
    if (registerForm && registerBtn) {
        registerForm.addEventListener('submit', function() {
            // Disable button and show loading state
            registerBtn.disabled = true;
            btnText.classList.add('d-none');
            btnLoading.classList.remove('d-none');
        });
    }
});
</script>
@endpush
