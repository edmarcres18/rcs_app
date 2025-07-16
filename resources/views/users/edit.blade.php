@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="fw-bold">Edit User</h2>
            <p class="text-muted">Editing {{ $user->first_name }} {{ $user->last_name }}</p>
        </div>
        <div class="col-md-6 text-md-end">
            <div class="btn-group">
                <a href="{{ route('users.show', $user->id) }}" class="btn btn-info">
                    <i class="fas fa-eye me-2"></i>View Profile
                </a>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Users
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('users.update', $user->id) }}" class="needs-validation" enctype="multipart/form-data" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <!-- Basic Information -->
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3">Basic Information</h5>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="avatar" class="form-label">Profile Photo</label>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-preview me-3">
                                        <img id="avatar-preview" src="{{ $user->avatar ? asset($user->avatar) : 'https://ui-avatars.com/api/?name='. urlencode($user->first_name . ' ' . $user->last_name) .'&background=4070f4&color=fff&size=100' }}"
                                            class="rounded-circle" style="width: 100px; height: 100px; object-fit: cover;">
                                    </div>
                                    <div class="flex-grow-1">
                                        <input type="file" class="form-control @error('avatar') is-invalid @enderror"
                                            id="avatar" name="avatar" accept="image/*"
                                            style="background-color: var(--bg-input); color: var(--text-color);">
                                        <div class="form-text">Upload a new profile photo (JPEG, PNG, GIF, max 2MB)</div>
                                        @error('avatar')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('first_name') is-invalid @enderror"
                                       id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}"
                                       required style="background-color: var(--bg-input); color: var(--text-color);">
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="middle_name" class="form-label">Middle Name</label>
                                <input type="text" class="form-control @error('middle_name') is-invalid @enderror"
                                       id="middle_name" name="middle_name" value="{{ old('middle_name', $user->middle_name) }}"
                                       style="background-color: var(--bg-input); color: var(--text-color);">
                                @error('middle_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('last_name') is-invalid @enderror"
                                       id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}"
                                       required style="background-color: var(--bg-input); color: var(--text-color);">
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="nickname" class="form-label">Nickname</label>
                                <input type="text" class="form-control @error('nickname') is-invalid @enderror"
                                       id="nickname" name="nickname" value="{{ old('nickname', $user->nickname) }}"
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
                                       id="email" name="email" value="{{ old('email', $user->email) }}"
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
                                    <option value="" disabled>Select role...</option>
                                    @foreach (\App\Enums\UserRole::cases() as $role)
                                        <option value="{{ $role->value }}" {{ (old('roles', $user->roles->value) == $role->value) ? 'selected' : '' }}>
                                            {{ $role->value }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('roles')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Telegram Configuration -->
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3 mt-2">Telegram Configuration</h5>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="telegram_username" class="form-label">Telegram Username</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fab fa-telegram-plane"></i></span>
                                    <input type="text" class="form-control @error('telegram_username') is-invalid @enderror"
                                        id="telegram_username" name="telegram_username" value="{{ old('telegram_username', $user->telegram_username) }}"
                                        placeholder="e.g. john_doe" style="background-color: var(--bg-input); color: var(--text-color);">
                                </div>
                                @error('telegram_username')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3 d-flex align-items-center">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="telegram_notifications_enabled"
                                        name="telegram_notifications_enabled" value="1" {{ old('telegram_notifications_enabled', $user->telegram_notifications_enabled) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="telegram_notifications_enabled">
                                        Enable Telegram Notifications
                                    </label>
                                    <div class="form-text">
                                        User must have a linked Telegram account.
                                    </div>
                                </div>
                            </div>

                            <!-- Password Change (Optional) -->
                            <div class="col-12">
                                <h5 class="border-bottom pb-2 mb-3 mt-2">Change Password (Optional)</h5>
                                <p class="text-muted mb-3">Leave blank to keep current password</p>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                           id="password" name="password"
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
                                <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control"
                                           id="password_confirmation" name="password_confirmation"
                                           style="background-color: var(--bg-input); color: var(--text-color);">
                                    <button class="btn btn-outline-secondary toggle-password" type="button" data-target="password_confirmation">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <div class="d-flex justify-content-between flex-wrap">
                                    <div>
                                        <span class="text-muted">
                                            Account created: {{ $user->created_at->format('M d, Y') }}
                                            @if($user->email_verified_at)
                                                | Email verified: {{ $user->email_verified_at->format('M d, Y') }}
                                            @endif
                                        </span>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="reset" class="btn btn-secondary">
                                            <i class="fas fa-undo me-2"></i>Reset
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Update User
                                        </button>
                                    </div>
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
        .btn-group {
            flex-direction: column;
            width: 100%;
        }

        .btn-group .btn {
            width: 100%;
            margin-bottom: 5px;
            border-radius: 0.25rem !important;
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
