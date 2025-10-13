@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <div class="position-relative mb-4 mx-auto" style="width: 150px; height: 150px;">
                        @if ($user->avatar)
                            <img src="{{ $user->avatar }}" alt="Profile Picture" class="rounded-circle img-fluid avatar-clickable" style="width: 150px; height: 150px; object-fit: cover; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#avatarModal">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ $user->first_name }}+{{ $user->last_name }}&background=4070f4&color=fff&size=150" alt="Profile Picture" class="rounded-circle img-fluid avatar-clickable" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#avatarModal">
                        @endif
                    </div>

                    <h5 class="my-3">{{ $user->getFullNameAttribute() }}</h5>
                    <p class="text-muted mb-1">{{ ucfirst(strtolower(str_replace('_', ' ', $user->roles->name))) }}</p>
                    <p class="text-muted mb-4">{{ $user->email }}</p>

                    <div class="d-flex justify-content-center mb-2">
                        <a href="{{ route('profile.edit') }}" class="btn btn-primary me-2">Edit Profile</a>
                        <a href="{{ route('profile.change-password') }}" class="btn btn-outline-primary ms-1">Change Password</a>
                    </div>
                </div>
            </div>

            <!-- Quick Stats Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Account Info</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                            <span><i class="fas fa-envelope text-primary me-2"></i> Email</span>
                            <span class="text-muted">{{ $user->email }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                            <span><i class="fas fa-user-tag text-primary me-2"></i> Role</span>
                            <span class="badge bg-primary">{{ ucfirst(strtolower(str_replace('_', ' ', $user->roles->name))) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                            <span><i class="fas fa-calendar-alt text-primary me-2"></i> Joined</span>
                            <span class="text-muted">{{ $user->created_at->format('M d, Y') }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Telegram Details Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Telegram Details</h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @if($user->telegram_chat_id || $user->telegram_username)
                            <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                                <span><i class="fab fa-telegram-plane text-primary me-2"></i> Telegram Username</span>
                                <span class="text-muted">{{ $user->telegram_username ?? 'Not set' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center p-3">
                                <span><i class="fas fa-bell text-primary me-2"></i> Notifications</span>
                                @if($user->telegram_notifications_enabled)
                                    <span class="badge bg-success">Enabled</span>
                                @else
                                    <span class="badge bg-secondary">Disabled</span>
                                @endif
                            </li>
                        @else
                            <li class="list-group-item text-center p-3">
                                <p class="mb-0 text-muted">You have not linked your Telegram account.</p>
                                <a href="{{ route('profile.edit') }}" class="btn btn-link">Link now</a>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <!-- Personal Information Card -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Personal Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <p class="mb-0 text-muted">Full Name</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="mb-0">{{ $user->first_name }} {{ $user->middle_name ? $user->middle_name . ' ' : '' }}{{ $user->last_name }}</p>
                        </div>
                    </div>
                    <hr>
                    @if($user->nickname)
                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <p class="mb-0 text-muted">Nickname</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="mb-0">{{ $user->nickname }}</p>
                        </div>
                    </div>
                    <hr>
                    @endif
                    @if($user->position)
                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <p class="mb-0 text-muted">Position</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="mb-0">{{ $user->position }}</p>
                        </div>
                    </div>
                    <hr>
                    @endif
                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <p class="mb-0 text-muted">Email</p>
                        </div>
                        <div class="col-sm-9">
                            <p class="mb-0">{{ $user->email }}</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-sm-12">
                            <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary">Edit Information</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Card -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Activity</h5>
                    @if(count($activities) > 0)
                    <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
                    @endif
                </div>
                <div class="card-body p-0">
                    @if(count($activities) > 0)
                    <ul class="list-group list-group-flush">
                        @foreach($activities as $activity)
                        <li class="list-group-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">{{ $activity->activity_description }}</h6>
                                <small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small>
                            </div>
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i> {{ $activity->created_at->format('M d, Y h:i A') }}
                                @if($activity->ip_address)
                                <span class="ms-3"><i class="fas fa-globe me-1"></i> {{ $activity->ip_address }}</span>
                                @endif
                                @if($activity->device)
                                <span class="ms-3"><i class="fas fa-laptop me-1"></i> {{ $activity->device }}</span>
                                @endif
                            </small>
                        </li>
                        @endforeach
                    </ul>
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-history fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No recent activities found.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Avatar Full Screen Modal -->
<div class="modal fade" id="avatarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content bg-black border-0">
            <div class="modal-body d-flex align-items-center justify-content-center p-0 position-relative">
                <!-- Close button -->
                <button type="button" class="btn-close btn-close-white position-absolute" style="top: 20px; right: 20px; z-index: 10;" data-bs-dismiss="modal" aria-label="Close"></button>

                <!-- Avatar image -->
                <div class="text-center">
                    @if ($user->avatar)
                        <img id="fullScreenAvatar" src="{{ $user->avatar }}" alt="Profile Picture" class="img-fluid" style="max-width: 90vw; max-height: 90vh; object-fit: contain;">
                    @else
                        <img id="fullScreenAvatar" src="https://ui-avatars.com/api/?name={{ $user->first_name }}+{{ $user->last_name }}&background=4070f4&color=fff&size=800" alt="Profile Picture" class="img-fluid" style="max-width: 90vw; max-height: 90vh; object-fit: contain;">
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add hover effect to avatar
    const avatarImages = document.querySelectorAll('.avatar-clickable');

    avatarImages.forEach(function(img) {
        img.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.05)';
            this.style.transition = 'transform 0.2s ease-in-out';
        });

        img.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });

    // Handle modal show event to update the full-screen image
    const avatarModal = document.getElementById('avatarModal');
    const fullScreenAvatar = document.getElementById('fullScreenAvatar');

    avatarModal.addEventListener('show.bs.modal', function() {
        // Get the source from the clicked avatar
        const clickedAvatar = document.querySelector('.avatar-clickable');
        if (clickedAvatar) {
            fullScreenAvatar.src = clickedAvatar.src;
        }
    });

    // Close modal when clicking on the image
    fullScreenAvatar.addEventListener('click', function() {
        const modal = bootstrap.Modal.getInstance(avatarModal);
        if (modal) {
            modal.hide();
        }
    });
});
</script>
@endpush
@endsection
