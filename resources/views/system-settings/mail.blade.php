@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-3 mb-4 mb-lg-0">
            @include('system-settings._settings_menu')
        </div>
        <div class="col-lg-9">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">{{ __('Mail Settings') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.system-settings.mail.update') }}" method="POST" id="mail-settings-form">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="mail_mailer" class="form-label">Mailer</label>
                                <input type="text" name="mail_mailer" id="mail_mailer" class="form-control @error('mail_mailer') is-invalid @enderror" value="{{ config('mail.default') }}">
                                @error('mail_mailer')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="mail_host" class="form-label">Host</label>
                                <input type="text" name="mail_host" id="mail_host" class="form-control @error('mail_host') is-invalid @enderror" value="{{ config('mail.mailers.smtp.host') }}">
                                @error('mail_host')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="mail_port" class="form-label">Port</label>
                                <input type="text" name="mail_port" id="mail_port" class="form-control @error('mail_port') is-invalid @enderror" value="{{ config('mail.mailers.smtp.port') }}">
                                @error('mail_port')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="mail_username" class="form-label">Username</label>
                                <input type="text" name="mail_username" id="mail_username" class="form-control @error('mail_username') is-invalid @enderror" value="{{ config('mail.mailers.smtp.username') }}">
                                @error('mail_username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                             <div class="col-md-6 mb-3">
                                <label for="mail_password" class="form-label">Password</label>
                                <input type="password" name="mail_password" id="mail_password" class="form-control @error('mail_password') is-invalid @enderror" placeholder="Leave blank to keep current password">
                                 @error('mail_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="mail_encryption" class="form-label">Encryption</label>
                                <input type="text" name="mail_encryption" id="mail_encryption" class="form-control @error('mail_encryption') is-invalid @enderror" value="{{ config('mail.mailers.smtp.encryption') }}">
                                @error('mail_encryption')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="mail_from_address" class="form-label">From Address</label>
                            <input type="text" name="mail_from_address" id="mail_from_address" class="form-control @error('mail_from_address') is-invalid @enderror" value="{{ config('mail.from.address') }}">
                             @error('mail_from_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Settings
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
    const form = document.getElementById('mail-settings-form');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const submitButton = this.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;

        submitButton.disabled = true;
        submitButton.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...`;

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
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    showConfirmButton: false,
                    timer: 3500
                });
                const passwordInput = document.getElementById('mail_password');
                if (passwordInput) {
                    passwordInput.value = '';
                }
            }
        })
        .catch(error => {
            if (error.errors) {
                Object.keys(error.errors).forEach(key => {
                    const input = document.getElementById(key);
                    if (input) {
                        input.classList.add('is-invalid');
                        const errorContainer = document.createElement('div');
                        errorContainer.className = 'invalid-feedback';
                        errorContainer.innerHTML = error.errors[key][0];
                        input.parentElement.appendChild(errorContainer);
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
        });
    });
});
</script>
@endpush
