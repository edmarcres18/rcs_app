@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-3 mb-4 mb-lg-0">
            @include('system-settings._settings_menu')
        </div>
        <div class="col-lg-9">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('General Settings') }}</h5>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.system-settings.store') }}" enctype="multipart/form-data" id="system-settings-form">
                        @csrf

                        <div class="mb-3">
                            <label for="app_name" class="form-label">{{ __('Application Name') }}</label>
                            <input id="app_name" type="text" class="form-control @error('app_name') is-invalid @enderror" name="app_name" value="{{ config('app.name') }}" required autocomplete="app_name" autofocus>
                            @error('app_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="app_logo" class="form-label">{{ __('Application Logo') }}</label>
                                <div class="card">
                                    <div class="card-body text-center">
                                        @php
                                            $storageLogo = 'storage/app_logo/logo.png';
                                            $publicLogo = 'images/app_logo/logo.png';
                                            $currentAppLogo = file_exists(public_path($storageLogo))
                                                ? versioned_asset($storageLogo)
                                                : versioned_asset($publicLogo);
                                        @endphp
                                        <img src="{{ $currentAppLogo }}" alt="Current App Logo" class="img-fluid rounded mb-3" style="max-height: 100px;" id="app-logo-preview">
                                        <input id="app_logo" type="file" class="form-control @error('app_logo') is-invalid @enderror" name="app_logo">
                                        <small class="form-text text-muted mt-2">Upload a new PNG or JPG to update the logo.</small>
                                        @error('app_logo')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="auth_logo" class="form-label">{{ __('Authentication Page Logo') }}</label>
                                <div class="card">
                                    <div class="card-body text-center">
                                        @php
                                            $storageAuthLogo = 'storage/app_logo/auth_logo.png';
                                            $publicAuthLogo = 'images/app_logo/auth_logo.png';
                                            $currentAuthLogo = file_exists(public_path($storageAuthLogo))
                                                ? versioned_asset($storageAuthLogo)
                                                : versioned_asset($publicAuthLogo);
                                        @endphp
                                        <img src="{{ $currentAuthLogo }}" alt="Current Auth Logo" class="img-fluid rounded mb-3" style="max-height: 100px;" onerror="this.style.display='none'; this.onerror=null;" id="auth-logo-preview">
                                        <input id="auth_logo" type="file" class="form-control @error('auth_logo') is-invalid @enderror" name="auth_logo">
                                        <small class="form-text text-muted mt-2">Upload a new PNG or JPG for the auth pages.</small>
                                        @error('auth_logo')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input type="hidden" name="wrapped_enabled" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" id="wrapped_enabled" name="wrapped_enabled" value="1" {{ ($settings['wrapped_enabled'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="wrapped_enabled">Enable RCS Wrapped</label>
                            <div class="form-text">Controls visibility and access to RCS Wrapped (public share included).</div>
                        </div>

                        <div class="mt-3 text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>{{ __('Update Settings') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('system-settings-form');
    const appLogoInput = document.getElementById('app_logo');
    const authLogoInput = document.getElementById('auth_logo');
    const appLogoPreview = document.getElementById('app-logo-preview');
    const authLogoPreview = document.getElementById('auth-logo-preview');

    const handleFilePreview = (input, preview) => {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = (e) => {
                preview.src = e.target.result;
                preview.style.display = 'block';
                preview.onerror = null;
            };
            reader.readAsDataURL(input.files[0]);
        }
    };

    if (appLogoInput) {
        appLogoInput.addEventListener('change', () => handleFilePreview(appLogoInput, appLogoPreview));
    }

    if (authLogoInput) {
        authLogoInput.addEventListener('change', () => handleFilePreview(authLogoInput, authLogoPreview));
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const submitButton = this.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;

        submitButton.disabled = true;
        submitButton.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Updating...`;

        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());

        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => Promise.reject(err));
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });

                if (data.new_app_name) {
                    document.title = data.new_app_name;
                    const logoName = document.querySelector('.logo-name');
                    if (logoName) {
                        logoName.textContent = data.new_app_name;
                    }
                }
                if (data.app_logo_url) {
                    const logoImage = document.querySelector('.logo-image');
                    if (logoImage) {
                        logoImage.src = data.app_logo_url;
                    }
                    appLogoPreview.src = data.app_logo_url;
                }
                if (data.auth_logo_url) {
                    authLogoPreview.src = data.auth_logo_url;
                    authLogoPreview.style.display = 'block';
                }
            }
        })
        .catch(error => {
            if (error.errors) { // Handle validation errors
                Object.keys(error.errors).forEach(key => {
                    const input = document.getElementById(key);
                    if (input) {
                        input.classList.add('is-invalid');
                        const errorContainer = document.createElement('div');
                        errorContainer.className = 'invalid-feedback';
                        errorContainer.innerHTML = error.errors[key][0];
                        const parent = input.closest('.card-body') || input.parentElement;
                        parent.appendChild(errorContainer);
                    }
                });

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please check the form for errors.',
                    showConfirmButton: false,
                    timer: 3500
                });

            } else {
                let errorMsg = 'An unexpected error occurred. Please try again later.';
                if (error.message) {
                    errorMsg = error.message;
                }
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'error',
                    title: 'Error!',
                    text: errorMsg,
                    showConfirmButton: false,
                    timer: 3500
                });
            }
        })
        .finally(() => {
            submitButton.disabled = false;
            submitButton.innerHTML = originalButtonText;
            appLogoInput.value = '';
            authLogoInput.value = '';
        });
    });
});
</script>
@endpush
