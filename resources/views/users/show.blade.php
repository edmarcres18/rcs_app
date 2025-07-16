@extends('layouts.app')

@section('title', 'User Details')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="fw-bold">User Details</h2>
        </div>
        <div class="col-md-6 text-md-end">
            <div class="btn-group">
                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit me-2"></i>Edit User
                </a>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Users
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 col-md-5 mb-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="my-3">
                        @if($user->avatar)
                            <img src="{{ asset($user->avatar) }}" alt="{{ $user->first_name }}'s Avatar" class="rounded-circle img-fluid" style="width: 150px; height: 150px; object-fit: cover;">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->first_name . ' ' . $user->last_name) }}&background=4070f4&color=fff&size=150"
                                 alt="{{ $user->first_name }}'s Avatar" class="rounded-circle img-fluid" style="width: 150px; height: 150px; object-fit: cover;">
                        @endif
                    </div>
                    <h4 class="mb-1">{{ $user->first_name }} {{ $user->last_name }}</h4>
                    @if($user->nickname)
                        <p class="text-muted mb-2">({{ $user->nickname }})</p>
                    @endif
                    <p class="mb-2">
                        <span class="badge rounded-pill
                            @switch($user->roles->value)
                                @case('SYSTEM_ADMIN')
                                    bg-danger
                                    @break
                                @case('ADMIN')
                                    bg-warning
                                    @break
                                @case('SUPERVISOR')
                                    bg-info
                                    @break
                                @default
                                    bg-success
                            @endswitch
                        ">
                            {{ $user->roles->value }}
                        </span>
                    </p>
                    <div class="d-flex justify-content-center mt-4 mb-3">
                        <a href="mailto:{{ $user->email }}" class="btn btn-outline-primary rounded-circle mx-1" title="Email">
                            <i class="fas fa-envelope"></i>
                        </a>
                        <button class="btn btn-outline-info rounded-circle mx-1" title="Reset Password"
                                onclick="if(confirm('Are you sure you want to send a password reset link to this user?')){document.getElementById('reset-form').submit();}">
                            <i class="fas fa-key"></i>
                        </button>
                        <button class="btn btn-outline-danger rounded-circle mx-1" title="Delete User"
                                onclick="if(confirm('Are you sure you want to delete this user?')){document.getElementById('delete-form').submit();}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>

                    <form id="reset-form" action="{{ route('password.email') }}" method="POST" class="d-none">
                        @csrf
                        <input type="hidden" name="email" value="{{ $user->email }}">
                    </form>

                    <form id="delete-form" action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-none">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>

            <!-- Account Status Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Account Status</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: var(--bg-card); color: var(--text-color);">
                            Email Verification
                            @if($user->email_verified_at)
                                <span class="badge bg-success rounded-pill">Verified</span>
                            @else
                                <span class="badge bg-warning rounded-pill">Pending</span>
                            @endif
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: var(--bg-card); color: var(--text-color);">
                            Created
                            <span>{{ $user->created_at->format('M d, Y') }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: var(--bg-card); color: var(--text-color);">
                            Last Updated
                            <span>{{ $user->updated_at->format('M d, Y') }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Telegram Details Card -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Telegram Details</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        @if($user->telegram_chat_id || $user->telegram_username)
                            <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: var(--bg-card); color: var(--text-color);">
                                <span><i class="fab fa-telegram-plane me-2"></i> Username</span>
                                <span class="text-secondary">{{ $user->telegram_username ?? 'Not set' }}</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center" style="background-color: var(--bg-card); color: var(--text-color);">
                                <span><i class="fas fa-bell me-2"></i> Notifications</span>
                                @if($user->telegram_notifications_enabled)
                                    <span class="badge bg-success rounded-pill">Enabled</span>
                                @else
                                    <span class="badge bg-secondary rounded-pill">Disabled</span>
                                @endif
                            </li>
                        @else
                            <li class="list-group-item text-center" style="background-color: var(--bg-card); color: var(--text-color);">
                                <p class="mb-0 text-muted">Telegram account not linked.</p>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-8 col-md-7">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Personal Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <h6 class="mb-0">Full Name</h6>
                        </div>
                        <div class="col-sm-9 text-secondary">
                            {{ $user->first_name }} {{ $user->middle_name }} {{ $user->last_name }}
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <h6 class="mb-0">Email</h6>
                        </div>
                        <div class="col-sm-9 text-secondary">
                            <a href="mailto:{{ $user->email }}">{{ $user->email }}</a>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <h6 class="mb-0">Role</h6>
                        </div>
                        <div class="col-sm-9 text-secondary">
                            {{ $user->roles->value }}
                        </div>
                    </div>
                    @if($user->nickname)
                    <div class="row mb-3">
                        <div class="col-sm-3">
                            <h6 class="mb-0">Nickname</h6>
                        </div>
                        <div class="col-sm-9 text-secondary">
                            {{ $user->nickname }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Recent Activity Card -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Activity</h5>
                    <a href="{{ route('users.activities', $user->id) }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="timeline-wrapper">
                        @forelse($activities as $activity)
                            <div class="timeline-item">
                                <div class="timeline-item-marker">
                                    <div class="timeline-item-marker-text">{{ $activity->created_at->diffForHumans() }}</div>
                                    <div class="timeline-item-marker-indicator bg-{{ getActivityColor($activity->activity_type) }}"></div>
                                </div>
                                <div class="timeline-item-content">
                                    <div class="card shadow-sm border-0">
                                        <div class="card-body p-3">
                                            <p class="mb-1 fw-bold">
                                                <span class="badge bg-{{ getActivityColor($activity->activity_type) }} me-2">
                                                    {{ ucfirst($activity->activity_type) }}
                                                </span>
                                                {{ $activity->activity_description }}
                                            </p>
                                            <div class="d-flex align-items-center text-muted small">
                                                <i class="far fa-clock me-1"></i>
                                                <span>{{ $activity->created_at->format('M d, Y h:i A') }}</span>
                                                <span class="mx-2">•</span>
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                <span>{{ $activity->ip_address }}</span>
                                                <span class="mx-2">•</span>
                                                <i class="fas fa-desktop me-1"></i>
                                                <span>{{ $activity->browser }} ({{ $activity->platform }})</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <div class="mb-3">
                                    <i class="far fa-calendar-times fa-3x text-muted"></i>
                                </div>
                                <p class="text-muted">No recent activity found</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Dark mode compatibility fixes */
    [data-theme="dark"] .list-group-item {
        background-color: var(--bg-card);
        color: var(--text-color);
        border-color: var(--border-color);
    }

    [data-theme="dark"] .timeline-item-marker-text {
        color: var(--text-muted);
    }

    [data-theme="dark"] .timeline-item-content .card {
        background-color: var(--bg-card);
    }

    /* Timeline Styling */
    .timeline-wrapper {
        position: relative;
        padding-left: 1rem;
        margin-left: 1rem;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 2rem;
    }

    .timeline-item:last-child {
        padding-bottom: 0;
    }

    .timeline-item::after {
        content: '';
        position: absolute;
        left: -0.25rem;
        top: 1.75rem;
        bottom: 0;
        width: 2px;
        background-color: rgba(0,0,0,0.1);
        z-index: 1;
    }

    .timeline-item:last-child::after {
        display: none;
    }

    .timeline-item-marker {
        position: absolute;
        left: -1.5rem;
        width: 1.5rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        z-index: 2;
    }

    .timeline-item-marker-text {
        color: var(--text-muted);
        font-size: 0.75rem;
        font-weight: 500;
        white-space: nowrap;
        margin-bottom: 0.25rem;
    }

    .timeline-item-marker-indicator {
        height: 14px;
        width: 14px;
        border-radius: 50%;
        border: 3px solid #fff;
        box-shadow: 0 0 0 3px rgba(0,0,0,0.05);
    }

    [data-theme="dark"] .timeline-item-marker-indicator {
        border-color: var(--bg-card);
    }

    .timeline-item-content {
        padding-left: 1rem;
        padding-top: 0.25rem;
        width: 100%;
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

        .timeline-wrapper {
            margin-left: 0.5rem;
        }

        .timeline-item-marker-text {
            font-size: 0.7rem;
            width: 4rem;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    }

    @media (max-width: 576px) {
        .timeline-item-content .card-body {
            padding: 0.75rem;
        }

        .timeline-item-content .d-flex {
            flex-direction: column;
            align-items: flex-start !important;
        }

        .timeline-item-content .d-flex .mx-2 {
            display: none;
        }

        .timeline-item-content .d-flex span {
            margin-bottom: 0.25rem;
        }
    }
</style>
@endsection
