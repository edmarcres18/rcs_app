@extends('layouts.auth')

@section('title', 'Email Verified')

@section('content')
<div class="container">
    <div class="row justify-content-center align-items-center vh-100">
        <div class="col-md-8 col-lg-6 text-center">
            <div class="card shadow-lg auth-card border-0">
                <div class="card-body p-5">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success fa-5x"></i>
                    </div>
                    <h2 class="h4 fw-bold mb-3">Email Verified Successfully!</h2>
                    <p class="text-muted mb-4">
                        Thank you for verifying your email address. You will be redirected to the login page shortly.
                    </p>
                    <div class="d-flex justify-content-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <p class="mt-3 text-muted">
                        If you are not redirected automatically, <a href="{{ route('login') }}" class="text-decoration-none fw-medium">click here</a>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .auth-card {
        transition: all 0.3s ease;
    }
    .auth-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important;
    }
    .fa-check-circle {
        animation: scale-in 0.5s cubic-bezier(0.250, 0.460, 0.450, 0.940) both;
    }
    @keyframes scale-in {
        0% {
            transform: scale(0);
            opacity: 0;
        }
        100% {
            transform: scale(1);
            opacity: 1;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'success',
            title: 'Email verified successfully!',
            showConfirmButton: false,
            timer: 5000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        setTimeout(function () {
            window.location.href = "{{ route('login') }}";
        }, 5000); // 5-second delay
    });
</script>
@endpush
