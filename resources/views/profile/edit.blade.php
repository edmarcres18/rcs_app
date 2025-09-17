@extends('layouts.app')

@section('title', 'Edit Profile')

@push('styles')
<style>
    .profile-image-container {
        position: relative;
        overflow: hidden;
        border-radius: 50%;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    .profile-image-container:hover {
        box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        transform: translateY(-2px);
    }

    .profile-image-container img {
        transition: all 0.3s ease;
    }

    .profile-image-container:hover img {
        transform: scale(1.05);
    }

    .upload-area {
        border: 2px dashed #dee2e6;
        border-radius: 8px;
        padding: 20px;
        text-align: center;
        transition: all 0.3s ease;
        background: #f8f9fa;
    }

    .upload-area:hover {
        border-color: #007bff;
        background: #e3f2fd;
    }

    .file-info {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 8px 12px;
        margin-top: 8px;
        font-size: 0.875rem;
    }

    .progress {
        height: 8px;
        border-radius: 4px;
        background: #e9ecef;
    }

    .progress-bar {
        background: linear-gradient(45deg, #007bff, #0056b3);
        border-radius: 4px;
        transition: width 0.3s ease;
    }

    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .btn-outline-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
    }

    .card {
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        border: none;
        border-radius: 12px;
    }

    .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px 12px 0 0 !important;
        border: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 8px;
        padding: 10px 24px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(102, 126, 234, 0.4);
    }

    .btn-primary:disabled {
        opacity: 0.7;
        transform: none;
        box-shadow: none;
    }

    @media (max-width: 768px) {
        .profile-image-container {
            width: 120px !important;
            height: 120px !important;
        }

        .profile-image-container img {
            width: 120px !important;
            height: 120px !important;
        }
    }
</style>
@endpush

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
                                    <label for="avatar" class="btn btn-outline-primary" title="Click to upload or drag & drop image here">
                                        <i class="fas fa-upload me-1"></i> Change Photo
                                    </label>
                                    <input type="file" name="avatar" id="avatar" class="d-none" accept="image/jpeg,image/png,image/gif,image/webp">
                                    <div class="small text-muted mt-1">
                                        <i class="fas fa-hand-pointer me-1"></i>
                                        Click the button above or drag & drop an image
                                    </div>
                                    @error('avatar')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                    <div class="small text-muted mt-1">
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-info-circle me-2 text-primary"></i>
                                            <strong>Max file size: 10MB</strong>
                                        </div>
                                        <div class="d-flex align-items-center mb-2">
                                            <i class="fas fa-image me-2 text-success"></i>
                                            <span>Supported formats: JPG, PNG, GIF, WebP</span>
                                        </div>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-expand-arrows-alt me-2 text-warning"></i>
                                            <span>Max dimensions: 4000x4000 pixels</span>
                                        </div>
                                        <div id="file-info" class="file-info mt-2"></div>
                                    </div>
                                    <div id="upload-progress" class="progress mt-2" style="display: none;">
                                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
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
    // Enhanced image upload functionality with validation and progress
    document.addEventListener('DOMContentLoaded', function() {
        const avatarInput = document.getElementById('avatar');
        const avatarPreview = document.getElementById('avatar-preview');
        const fileInfo = document.getElementById('file-info');
        const uploadProgress = document.getElementById('upload-progress');
        const progressBar = uploadProgress.querySelector('.progress-bar');
        const submitButton = document.querySelector('button[type="submit"]');
        const form = document.querySelector('form');

        // File validation constants
        const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB in bytes
        const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        const MAX_DIMENSIONS = 4000; // Max width/height in pixels

        avatarInput.addEventListener('change', function() {
            const file = this.files[0];

            if (!file) {
                resetFileInfo();
                return;
            }

            // Validate file size
            if (file.size > MAX_FILE_SIZE) {
                showError('File size exceeds 10MB limit. Please choose a smaller image.');
                this.value = '';
                return;
            }

            // Validate file type
            if (!ALLOWED_TYPES.includes(file.type)) {
                showError('Invalid file type. Please choose JPG, PNG, GIF, or WebP image.');
                this.value = '';
                return;
            }

            // Show file info
            showFileInfo(file);

            // Validate and preview image
            validateAndPreviewImage(file);
        });

        function validateAndPreviewImage(file) {
            const reader = new FileReader();

            reader.onload = function(e) {
                const img = new Image();

                img.onload = function() {
                    // Check dimensions
                    if (this.width > MAX_DIMENSIONS || this.height > MAX_DIMENSIONS) {
                        showError(`Image dimensions too large. Maximum size is ${MAX_DIMENSIONS}x${MAX_DIMENSIONS} pixels.`);
                        avatarInput.value = '';
                        return;
                    }

                    // Show preview
                    avatarPreview.src = e.target.result;
                    showSuccess('Image ready for upload!');
                };

                img.onerror = function() {
                    showError('Invalid image file. Please choose a valid image.');
                    avatarInput.value = '';
                };

                img.src = e.target.result;
            };

            reader.readAsDataURL(file);
        }

        function showFileInfo(file) {
            const sizeInMB = (file.size / (1024 * 1024)).toFixed(2);
            const type = file.type.split('/')[1].toUpperCase();
            fileInfo.innerHTML = `
                <i class="fas fa-file-image me-1"></i>
                ${file.name} (${sizeInMB} MB, ${type})
            `;
        }

        function resetFileInfo() {
            fileInfo.innerHTML = '';
        }

        function showError(message) {
            fileInfo.innerHTML = `<i class="fas fa-exclamation-triangle me-1 text-danger"></i><span class="text-danger">${message}</span>`;
        }

        function showSuccess(message) {
            fileInfo.innerHTML = `<i class="fas fa-check-circle me-1 text-success"></i><span class="text-success">${message}</span>`;
        }

        // Form submission with progress indication
        form.addEventListener('submit', function(e) {
            if (avatarInput.files && avatarInput.files[0]) {
                // Show progress bar
                uploadProgress.style.display = 'block';
                progressBar.style.width = '0%';
                progressBar.textContent = 'Uploading...';

                // Disable submit button
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Uploading...';

                // Simulate progress (since we can't track actual upload progress with standard form submission)
                let progress = 0;
                const progressInterval = setInterval(() => {
                    progress += Math.random() * 30;
                    if (progress > 90) progress = 90;

                    progressBar.style.width = progress + '%';
                    progressBar.textContent = `Uploading... ${Math.round(progress)}%`;
                }, 200);

                // Reset after form submission
                setTimeout(() => {
                    clearInterval(progressInterval);
                    progressBar.style.width = '100%';
                    progressBar.textContent = 'Processing...';
                }, 2000);
            }
        });

        // Drag and drop functionality
        const profileImageContainer = document.querySelector('.profile-image-container');

        profileImageContainer.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('drag-over');
        });

        profileImageContainer.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
        });

        profileImageContainer.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                avatarInput.files = files;
                avatarInput.dispatchEvent(new Event('change'));
            }
        });

        // Add visual feedback for drag and drop
        const style = document.createElement('style');
        style.textContent = `
            .profile-image-container {
                transition: all 0.3s ease;
                border: 2px dashed transparent;
            }
            .profile-image-container.drag-over {
                border-color: #007bff;
                background-color: rgba(0, 123, 255, 0.1);
                transform: scale(1.02);
            }
            .profile-image-container:hover {
                border-color: #007bff;
                cursor: pointer;
            }
        `;
        document.head.appendChild(style);

        // Click to upload
        profileImageContainer.addEventListener('click', function() {
            avatarInput.click();
        });
    });
</script>
@endpush
@endsection
