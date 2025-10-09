@extends('layouts.app')

@section('title', 'Instruction Activities')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0 text-gray-800">Instruction Activities</h1>
            <p class="text-muted">
                <small>
                    {{ $instruction->title }} |
                    Sent by {{ $instruction->sender->full_name }} on {{ $instruction->created_at->format('M d, Y g:i A') }}
                </small>
            </p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="{{ route('instructions.monitor') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
            <a href="{{ route('instructions.show', $instruction) }}" class="btn btn-info ms-2">
                <i class="fas fa-eye me-1"></i> View Instruction
            </a>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter me-1"></i> Filters
            </h6>
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('instructions.monitor.activities', $instruction) }}">
                <i class="fas fa-redo-alt me-1"></i> Reset
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('instructions.monitor.activities', $instruction) }}" method="GET">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="action" class="form-label">Activity Type</label>
                        <select class="form-select" id="action" name="action">
                            <option value="">All Activities</option>
                            <option value="sent" {{ request('action') == 'sent' ? 'selected' : '' }}>Sent</option>
                            <option value="read" {{ request('action') == 'read' ? 'selected' : '' }}>Read</option>
                            <option value="replied" {{ request('action') == 'replied' ? 'selected' : '' }}>Replied</option>
                            <option value="forwarded" {{ request('action') == 'forwarded' ? 'selected' : '' }}>Forwarded</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="user_id" class="form-label">User</label>
                        <select class="form-select" id="user_id" name="user_id">
                            <option value="">All Users</option>
                            @foreach(\App\Models\User::orderBy('first_name')->get() as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> Apply Filters
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Activities Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-history me-1"></i> Activity Timeline
            </h6>
            <span class="badge bg-primary">{{ $activities->total() }}</span>
        </div>
        <div class="card-body">
            @if($activities->count() > 0)
                <div class="timeline">
                    @foreach($activities as $activity)
                        <div class="timeline-item">
                            <div class="timeline-marker
                                @if($activity->action == 'sent') bg-primary
                                @elseif($activity->action == 'read') bg-success
                                @elseif($activity->action == 'replied') bg-info
                                @elseif($activity->action == 'forwarded') bg-warning
                                @endif
                            ">
                                @if($activity->action == 'sent')
                                    <i class="fas fa-paper-plane text-white"></i>
                                @elseif($activity->action == 'read')
                                    <i class="fas fa-check text-white"></i>
                                @elseif($activity->action == 'replied')
                                    <i class="fas fa-reply text-white"></i>
                                @elseif($activity->action == 'forwarded')
                                    <i class="fas fa-share text-white"></i>
                                @endif
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <span class="badge
                                        @if($activity->action == 'sent') bg-primary
                                        @elseif($activity->action == 'read') bg-success
                                        @elseif($activity->action == 'replied') bg-info
                                        @elseif($activity->action == 'forwarded') bg-warning
                                        @endif
                                    ">
                                        {{ ucfirst($activity->action) }}
                                    </span>
                                    <span class="timeline-date ms-2">{{ $activity->created_at->format('M d, Y g:i A') }}</span>
                                </div>
                                <p class="mb-0">
                                    <strong>{{ $activity->user->full_name }}</strong>
                                    @if($activity->action == 'sent')
                                        sent this instruction.
                                    @elseif($activity->action == 'read')
                                        read this instruction.
                                    @elseif($activity->action == 'replied')
                                        replied to this instruction.
                                    @elseif($activity->action == 'forwarded')
                                        forwarded this instruction
                                        @if($activity->target_user_id)
                                            to <strong>{{ $activity->targetUser->full_name }}</strong>.
                                        @else
                                            to other users.
                                        @endif
                                    @endif
                                </p>

                                @if($activity->content)
                                    <div class="timeline-details mt-2 p-3 bg-light rounded">
                                        @if($activity->action == 'replied')
                                            <strong>Reply:</strong><br>
                                            {!! nl2br(e($activity->content)) !!}
                                        @elseif($activity->action == 'forwarded')
                                            <strong>Forward Message:</strong><br>
                                            {!! nl2br(e($activity->content)) !!}
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4">
                    {{ $activities->withQueryString()->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-history fa-3x mb-3 text-muted"></i>
                    <p class="mb-0">No activities found.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Recipients Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-users me-1"></i> Recipients
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Recipient</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Forwarded By</th>
                            <th>Assigned Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($instruction->recipients as $recipient)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $recipient->avatar }}"
                                             alt="{{ $recipient->full_name }}" 
                                             class="rounded-circle border border-1 me-2" 
                                             width="28" 
                                             height="28"
                                             style="object-fit: cover;"
                                             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($recipient->full_name) }}&color=7F9CF5&background=EBF4FF';">
                                        {{ $recipient->full_name }}
                                    </div>
                                </td>
                                <td>{{ $recipient->email }}</td>
                                <td>
                                    @if(isset($recipient->pivot) && $recipient->pivot->is_read)
                                        <span class="badge bg-success">Read</span>
                                    @else
                                        <span class="badge bg-danger">Unread</span>
                                    @endif
                                    @if(isset($recipient->pivot) && $recipient->pivot->forwarded_by_id)
                                        <span class="badge bg-info">Forwarded</span>
                                        @php
                                            $forwarder = \App\Models\User::find($recipient->pivot->forwarded_by_id);
                                        @endphp
                                    @endif
                                </td>
                                <td>
                                    @if($recipient->pivot->forwarded_by_id)
                                        @php
                                            $forwarder = \App\Models\User::find($recipient->pivot->forwarded_by_id);
                                        @endphp
                                        {{ $forwarder ? $forwarder->full_name : 'Unknown' }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $recipient->pivot->created_at->format('M d, Y g:i A') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .timeline {
        position: relative;
        list-style: none;
        padding: 20px 0;
        margin: 0;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 20px;
        top: 0;
        height: 100%;
        width: 2px;
        background-color: #e9ecef;
    }

    .timeline-item {
        position: relative;
        display: flex;
        margin-bottom: 30px;
    }

    .timeline-marker {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1;
        margin-right: 20px;
        flex-shrink: 0;
    }

    .timeline-content {
        background-color: #fff;
        border-radius: 0.25rem;
        padding: 15px 20px;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        flex-grow: 1;
    }

    .timeline-header {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }

    .timeline-date {
        color: #6c757d;
        font-size: 0.875rem;
    }

    .timeline-details {
        font-size: 0.9rem;
        border-left: 2px solid #dee2e6;
    }
</style>
@endsection
