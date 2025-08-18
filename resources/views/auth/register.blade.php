@extends('layouts.auth')

@section('title', 'Register')

@push('styles')
<style>
.password-requirements .requirement {
    margin-bottom: 0.25rem;
    transition: all 0.3s ease;
}

.password-requirements .requirement i {
    transition: all 0.3s ease;
}

.password-match {
    transition: all 0.3s ease;
}

.password-match i {
    transition: all 0.3s ease;
}

.requirement small, .password-match small {
    transition: color 0.3s ease;
}
</style>
@endpush

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
                                    <div class="password-requirements mt-2">
                                        <small class="form-text text-muted mb-2 d-block">Password requirements:</small>
                                        <div class="requirement" data-requirement="length">
                                            <i class="fa fa-circle text-muted me-2"></i>
                                            <small>At least 8 characters</small>
                                        </div>
                                        <div class="requirement" data-requirement="uppercase">
                                            <i class="fa fa-circle text-muted me-2"></i>
                                            <small>1 capital letter</small>
                                        </div>
                                        <div class="requirement" data-requirement="number">
                                            <i class="fa fa-circle text-muted me-2"></i>
                                            <small>1 number</small>
                                        </div>
                                        <div class="requirement" data-requirement="symbol">
                                            <i class="fa fa-circle text-muted me-2"></i>
                                            <small>1 symbol</small>
                                        </div>
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
                                    <div class="password-match mt-2">
                                        <small class="form-text text-muted">
                                            <i class="fa fa-circle text-muted me-2"></i>
                                            Passwords must match
                                        </small>
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
    const passwordInput = document.querySelector('#password');
    const confirmPasswordInput = document.querySelector('#password-confirm');

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

    function validatePassword(password) {
        const requirements = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            number: /[0-9]/.test(password),
            symbol: /[^A-Za-z0-9]/.test(password)
        };

        // Update requirement indicators
        Object.keys(requirements).forEach(requirement => {
            const requirementElement = document.querySelector(`[data-requirement="${requirement}"]`);
            if (requirementElement) {
                const icon = requirementElement.querySelector('i');
                const text = requirementElement.querySelector('small');

                if (requirements[requirement]) {
                    icon.className = 'fa fa-check-circle text-success me-2';
                    text.className = 'text-success';
                } else {
                    icon.className = 'fa fa-circle text-muted me-2';
                    text.className = 'text-muted';
                }
            }
        });

        return Object.values(requirements).every(Boolean);
    }

    function validatePasswordMatch() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        const matchElement = document.querySelector('.password-match small');
        const matchIcon = document.querySelector('.password-match i');

        if (confirmPassword === '') {
            matchIcon.className = 'fa fa-circle text-muted me-2';
            matchElement.className = 'text-muted';
            return false;
        }

        if (password === confirmPassword) {
            matchIcon.className = 'fa fa-check-circle text-success me-2';
            matchElement.className = 'text-success';
            return true;
        } else {
            matchIcon.className = 'fa fa-times-circle text-danger me-2';
            matchElement.className = 'text-danger';
            return false;
        }
    }

    function updateRegisterButton() {
        const isPasswordValid = validatePassword(passwordInput.value);
        const isPasswordMatch = validatePasswordMatch();
        const isFormValid = isPasswordValid && isPasswordMatch;

        registerBtn.disabled = !isFormValid;
    }

    // Password validation on input
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            validatePassword(this.value);
            validatePasswordMatch();
            updateRegisterButton();
        });
    }

    // Confirm password validation on input
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', function() {
            validatePasswordMatch();
            updateRegisterButton();
        });
    }

    togglePasswordVisibility('togglePassword', 'password');
    togglePasswordVisibility('togglePasswordConfirm', 'password-confirm');

    // Handle form submission to prevent duplicate submits
    if (registerForm && registerBtn) {
        registerForm.addEventListener('submit', function(e) {
            const isPasswordValid = validatePassword(passwordInput.value);
            const isPasswordMatch = validatePasswordMatch();

            if (!isPasswordValid || !isPasswordMatch) {
                e.preventDefault();
                return false;
            }

            // Disable button and show loading state
            registerBtn.disabled = true;
            btnText.classList.add('d-none');
            btnLoading.classList.remove('d-none');
        });
    }

    // Initial validation
    updateRegisterButton();
});
</script>
@endpush
