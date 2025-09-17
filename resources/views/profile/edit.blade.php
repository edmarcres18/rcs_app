@extends('layouts.app')

@section('title', 'Edit Profile')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <!-- Form Card -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Edit Profile Information</h5>
                    <a href="{{ route('profile.show') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Profile
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-4 text-center mb-4">
                                <div class="position-relative mb-3 mx-auto">
                                    <div class="profile-image-container" style="width: 150px; height: 150px; margin: 0 auto;">
                                        @if ($user->avatar)
                                            <img id="avatar-preview" src="{{ $user->avatar }}" alt="Avatar Preview" class="rounded-circle img-fluid" style="width: 150px; height: 150px; object-fit: cover;">
                                        @else
                                            <img id="avatar-preview" src="https://ui-avatars.com/api/?name={{ $user->first_name }}+{{ $user->last_name }}&background=4070f4&color=fff&size=150" alt="Avatar Preview" class="rounded-circle img-fluid">
                                        @endif
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="avatar" class="btn btn-outline-primary">
                                        <i class="fas fa-upload me-1"></i> Change Photo
                                    </label>
                                    <input type="file" name="avatar" id="avatar" class="d-none" accept="image/*">
                                    @error('avatar')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                    <div class="small text-muted mt-1">
                                        Max file size: 10MB. Supported formats: JPG, PNG, GIF, WebP<br>
                                        <i class="fas fa-info-circle me-1"></i>
                                        <small>Images will be automatically optimized and resized to 500x500px for best performance.</small>
                                    </div>

                                    <!-- Upload Progress Bar -->
                                    <div id="upload-progress" class="mt-2" style="display: none;">
                                        <div class="progress" style="height: 6px;">
                                            <div class="progress-bar progress-bar-striped progress-bar-animated"
                                                 role="progressbar" style="width: 0%"></div>
                                        </div>
                                        <small class="text-muted">Uploading image...</small>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="first_name" class="form-label">First Name</label>
                                        <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
                                        @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="last_name" class="form-label">Last Name</label>
                                        <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
                                        @error('last_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="middle_name" class="form-label">Middle Name (optional)</label>
                                    <input type="text" class="form-control @error('middle_name') is-invalid @enderror" id="middle_name" name="middle_name" value="{{ old('middle_name', $user->middle_name) }}">
                                    @error('middle_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="nickname" class="form-label">Nickname (optional)</label>
                                    <input type="text" class="form-control @error('nickname') is-invalid @enderror" id="nickname" name="nickname" value="{{ old('nickname', $user->nickname) }}">
                                    @error('nickname')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <hr>
                                <h6 class="text-muted mb-3">Telegram Settings</h6>

                                <div class="mb-3">
                                    <label for="telegram_username" class="form-label">Telegram Username (optional)</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fab fa-telegram-plane"></i></span>
                                        <input type="text" class="form-control @error('telegram_username') is-invalid @enderror" id="telegram_username" name="telegram_username" value="{{ old('telegram_username', $user->telegram_username) }}" placeholder="e.g. john_doe">
                                    </div>
                                    @error('telegram_username')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3 form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="telegram_notifications_enabled" name="telegram_notifications_enabled" value="1" {{ old('telegram_notifications_enabled', $user->telegram_notifications_enabled) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="telegram_notifications_enabled">Enable Telegram Notifications</label>
                                    <small class="form-text text-muted d-block">
                                        Receive real-time notifications on Telegram. You must first link your account via the bot.
                                    </small>
                                </div>


                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Save Changes
                                    </button>
                                    <a href="{{ route('profile.show') }}" class="btn btn-secondary">
                                        Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Enhanced image preview and validation functionality
    document.addEventListener('DOMContentLoaded', function() {
        const avatarInput = document.getElementById('avatar');
        const avatarPreview = document.getElementById('avatar-preview');
        const fileSizeInfo = document.createElement('div');
        fileSizeInfo.className = 'small text-muted mt-1';
        avatarInput.parentNode.appendChild(fileSizeInfo);

        // File size validation (10MB = 10 * 1024 * 1024 bytes)
        const maxFileSize = 10 * 1024 * 1024;
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];

        avatarInput.addEventListener('change', function() {
            const file = this.files[0];

            if (file) {
                // Clear previous error messages
                const existingError = avatarInput.parentNode.querySelector('.text-danger');
                if (existingError) {
                    existingError.remove();
                }

                // Validate file size
                if (file.size > maxFileSize) {
                    showError('File size exceeds 10MB limit. Please choose a smaller image.');
                    return;
                }

                // Validate file type
                if (!allowedTypes.includes(file.type)) {
                    showError('Invalid file type. Please choose JPG, PNG, GIF, or WebP image.');
                    return;
                }

                // Validate image dimensions (max 4000x4000)
                const img = new Image();
                img.onload = function() {
                    if (this.width > 4000 || this.height > 4000) {
                        showError('Image dimensions too large. Maximum size is 4000x4000 pixels.');
                        return;
                    }

                    // Show file info
                    showFileInfo(file);

                    // Update preview
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        avatarPreview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                };
                img.src = URL.createObjectURL(file);
            }
        });

        function showError(message) {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'text-danger mt-1';
            errorDiv.textContent = message;
            avatarInput.parentNode.appendChild(errorDiv);

            // Clear file input
            avatarInput.value = '';

            // Hide file info
            fileSizeInfo.style.display = 'none';
        }

        function showFileInfo(file) {
            const fileSizeMB = (file.size / (1024 * 1024)).toFixed(2);
            const dimensions = file.type.startsWith('image/') ? ' (checking dimensions...)' : '';

            fileSizeInfo.innerHTML = `
                <i class="fas fa-info-circle me-1"></i>
                Selected: ${file.name} (${fileSizeMB} MB)${dimensions}
            `;
            fileSizeInfo.style.display = 'block';
            fileSizeInfo.className = 'small text-info mt-1';
        }

        // Show loading state and progress during upload
        const form = document.querySelector('form');
        const submitBtn = form.querySelector('button[type="submit"]');
        const uploadProgress = document.getElementById('upload-progress');
        const progressBar = uploadProgress.querySelector('.progress-bar');

        form.addEventListener('submit', function() {
            if (avatarInput.files.length > 0) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Uploading...';
                submitBtn.disabled = true;

                // Show progress bar
                uploadProgress.style.display = 'block';
                progressBar.style.width = '0%';

                // Simulate progress (since we can't track actual upload progress with standard form submission)
                let progress = 0;
                const progressInterval = setInterval(() => {
                    progress += Math.random() * 15;
                    if (progress > 90) progress = 90;
                    progressBar.style.width = progress + '%';
                }, 200);

                // Clear interval after form submission
                setTimeout(() => {
                    clearInterval(progressInterval);
                    progressBar.style.width = '100%';
                }, 2000);
            }
        });
    });
</script>
@endpush
@endsection
