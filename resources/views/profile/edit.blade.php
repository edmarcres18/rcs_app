@extends('layouts.app')

@section('title', 'Edit Profile')

@push('styles')
<link href="{{ asset('css/avatar-upload.css') }}" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <!-- Form Card -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Edit Profile Information</h5>
                    <a href="{{ route('profile.show') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Profile
                    </a>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Please check the form for errors:</h6>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-12 col-md-4 text-center mb-4">
                                <div class="position-relative mb-3 mx-auto">
                                    <div class="profile-image-container" style="width: 150px; height: 150px; margin: 0 auto;">
                                        <img id="avatar-preview" src="{{ $user->avatar_url }}" alt="Avatar Preview" class="rounded-circle img-fluid" style="width: 150px; height: 150px; object-fit: cover;">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="avatar" class="btn btn-outline-primary btn-upload-avatar">
                                        <i class="fas fa-upload me-1"></i> Change Photo
                                    </label>
                                    <input type="file" name="avatar" id="avatar" class="d-none" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp" data-max-size="10485760">
                                    @error('avatar')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                    <div class="small text-muted mt-1">Max file size: 10MB. Supported formats: JPG, PNG, GIF, WEBP</div>
                                    <div id="upload-progress" class="progress mt-2 d-none" style="height: 4px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                                    </div>
                                    <div id="file-info" class="small text-info mt-1 d-none"></div>
                                </div>
                            </div>

                            <div class="col-12 col-md-8">
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
                                    <button type="submit" class="btn btn-primary" id="save-btn">
                                        <span class="btn-text">
                                            <i class="fas fa-save me-1"></i> Save Changes
                                        </span>
                                        <span class="btn-loading d-none">
                                            <i class="fas fa-spinner fa-spin me-1"></i> Saving...
                                        </span>
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
    // Enhanced image preview functionality and form handling
    document.addEventListener('DOMContentLoaded', function() {
        const avatarInput = document.getElementById('avatar');
        const avatarPreview = document.getElementById('avatar-preview');
        const saveBtn = document.getElementById('save-btn');
        const form = document.querySelector('form');
        const uploadProgress = document.getElementById('upload-progress');
        const fileInfo = document.getElementById('file-info');
        const progressBar = uploadProgress.querySelector('.progress-bar');

        // Enhanced avatar preview functionality
        avatarInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                
                // Reset previous states
                hideFileInfo();
                hideProgress();
                clearAlerts();
                
                // Comprehensive file validation
                const validation = validateFile(file);
                if (!validation.valid) {
                    showAlert(validation.message, 'danger');
                    this.value = '';
                    return;
                }

                // Show file information
                showFileInfo(file);
                
                // Show preview with loading state
                showImagePreview(file);
            }
        });

        // Enhanced form submission with progress
        form.addEventListener('submit', function(e) {
            if (avatarInput.files.length > 0) {
                showProgress();
                simulateUploadProgress();
            }
            
            const btnText = saveBtn.querySelector('.btn-text');
            const btnLoading = saveBtn.querySelector('.btn-loading');
            
            btnText.classList.add('d-none');
            btnLoading.classList.remove('d-none');
            saveBtn.disabled = true;
        });

        // Comprehensive file validation
        function validateFile(file) {
            // Check file size (10MB = 10485760 bytes)
            if (file.size > 10485760) {
                return { valid: false, message: 'File size must not exceed 10MB. Current size: ' + formatFileSize(file.size) };
            }
            
            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                return { valid: false, message: 'Please select a valid image file. Supported formats: JPG, PNG, GIF, WEBP.' };
            }

            // Check for minimum file size (1KB to avoid empty files)
            if (file.size < 1024) {
                return { valid: false, message: 'File is too small. Please select a valid image file.' };
            }

            return { valid: true };
        }

        // Show file information
        function showFileInfo(file) {
            const info = `Selected: ${file.name} (${formatFileSize(file.size)})`;
            fileInfo.textContent = info;
            fileInfo.classList.remove('d-none');
        }

        // Hide file information
        function hideFileInfo() {
            fileInfo.classList.add('d-none');
        }

        // Show image preview
        function showImagePreview(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                avatarPreview.src = e.target.result;
                avatarPreview.style.opacity = '0.7';
                setTimeout(() => {
                    avatarPreview.style.opacity = '1';
                }, 100);
            }
            reader.readAsDataURL(file);
        }

        // Show upload progress
        function showProgress() {
            uploadProgress.classList.remove('d-none');
            progressBar.style.width = '0%';
        }

        // Hide upload progress
        function hideProgress() {
            uploadProgress.classList.add('d-none');
        }

        // Simulate upload progress for better UX
        function simulateUploadProgress() {
            let progress = 0;
            const interval = setInterval(() => {
                progress += Math.random() * 15;
                if (progress > 90) progress = 90;
                progressBar.style.width = progress + '%';
                
                if (progress >= 90) {
                    clearInterval(interval);
                }
            }, 200);
        }

        // Format file size for display
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Clear existing alerts
        function clearAlerts() {
            const alerts = document.querySelectorAll('.alert:not(.alert-danger)');
            alerts.forEach(alert => {
                if (alert.textContent.includes('File size') || alert.textContent.includes('Please select')) {
                    alert.remove();
                }
            });
        }

        // Enhanced alert function
        function showAlert(message, type = 'info') {
            clearAlerts();
            
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                <i class="fas fa-${type === 'danger' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            const container = document.querySelector('.card-body');
            const firstForm = container.querySelector('form');
            container.insertBefore(alertDiv, firstForm);
            
            // Auto-dismiss after 7 seconds for better readability
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 7000);
        }
    });
</script>
@endpush
@endsection
