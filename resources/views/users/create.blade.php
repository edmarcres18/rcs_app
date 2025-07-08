@extends('layouts.app')

@section('title', 'Create New User')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="fw-bold">Create New User</h2>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Users
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('users.store') }}" class="needs-validation" enctype="multipart/form-data" novalidate>
                        @csrf

                        <div class="row g-3">
                            <!-- Basic Information -->
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">Basic Information</h5>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="avatar" class="form-label">Profile Photo</label>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-preview me-3">
                                        <img id="avatar-preview" src="https://ui-avatars.com/api/?background=4070f4&color=fff&size=100"
                                            class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                                    </div>
                                    <div class="flex-grow-1">
                                        <input type="file" class="form-control @error('avatar') is-invalid @enderror"
                                            id="avatar" name="avatar" accept="image/*"
                                            style="background-color: var(--bg-input); color: var(--text-color);">
                                        <div class="form-text">Upload a profile photo (JPEG, PNG, GIF, max 2MB)</div>
                                        @error('avatar')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                       id="first_name" name="first_name" value="{{ old('first_name') }}"
                                       required style="background-color: var(--bg-input); color: var(--text-color);">
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="middle_name" class="form-label">Middle Name</label>
                                <input type="text" class="form-control @error('middle_name') is-invalid @enderror"
                                       id="middle_name" name="middle_name" value="{{ old('middle_name') }}"
                                       style="background-color: var(--bg-input); color: var(--text-color);">
                                @error('middle_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                       id="last_name" name="last_name" value="{{ old('last_name') }}"
                                       required style="background-color: var(--bg-input); color: var(--text-color);">
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="nickname" class="form-label">Nickname</label>
                                <input type="text" class="form-control @error('nickname') is-invalid @enderror"
                                       id="nickname" name="nickname" value="{{ old('nickname') }}"
                                       style="background-color: var(--bg-input); color: var(--text-color);">
                                @error('nickname')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Account Information -->
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3 mt-2">Account Information</h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email" value="{{ old('email') }}"
                                       required style="background-color: var(--bg-input); color: var(--text-color);">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="roles" class="form-label">Role <span class="text-danger">*</span></label>
                                <select class="form-select @error('roles') is-invalid @enderror"
                                        id="roles" name="roles" required
                                        style="background-color: var(--bg-input); color: var(--text-color);">
                                    <option value="" selected disabled>Select role...</option>
                                    @foreach (\App\Enums\UserRole::cases() as $role)
                                        <option value="{{ $role->value }}" {{ old('roles') == $role->value ? 'selected' : '' }}>
                                            {{ $role->value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('roles')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                           id="password" name="password" required
                                           style="background-color: var(--bg-input); color: var(--text-color);">
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text">Password must be at least 8 characters long</div>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control"
                                           id="password_confirmation" name="password_confirmation" required
                                           style="background-color: var(--bg-input); color: var(--text-color);">
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password_confirmation">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <div class="d-flex justify-content-end gap-2">
                                    <button type="reset" class="btn btn-secondary">
                                        <i class="fas fa-undo me-2"></i>Reset
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Create User
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Dark mode compatibility fixes */
    [data-theme="dark"] .form-control,
    [data-theme="dark"] .form-select {
        background-color: var(--bg-input);
        color: var(--text-color);
        border-color: var(--border-color);
    }

    [data-theme="dark"] .form-control:focus,
    [data-theme="dark"] .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.25rem rgba(var(--primary-color-rgb), 0.25);
    }

    [data-theme="dark"] .form-text {
        color: var(--text-light);
    }

    [data-theme="dark"] .input-group-text {
        background-color: var(--bg-card);
        color: var(--text-color);
        border-color: var(--border-color);
    }

    /* Responsive fixes for mobile */
    @media (max-width: 768px) {
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .avatar-preview {
            margin-bottom: 1rem;
        }

        .d-flex.align-items-center {
            flex-direction: column;
            align-items: flex-start !important;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Avatar preview
        const avatarInput = document.getElementById('avatar');
        const avatarPreview = document.getElementById('avatar-preview');

        avatarInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    avatarPreview.src = e.target.result;
                }

                reader.readAsDataURL(this.files[0]);
            }
        });

        // Password visibility toggle
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
                const icon = this.querySelector('i');

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });

        // Form validation
        const forms = document.querySelectorAll('.needs-validation');

        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }

                form.classList.add('was-validated');
            }, false);
        });
    });
</script>
@endsection
