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
                            <img src="{{ $user->avatar }}" alt="Profile Picture" class="rounded-circle img-fluid" style="width: 150px; height: 150px; object-fit: cover;">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ $user->first_name }}+{{ $user->last_name }}&background=4070f4&color=fff&size=150" alt="Profile Picture" class="rounded-circle img-fluid">
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
@endsection
