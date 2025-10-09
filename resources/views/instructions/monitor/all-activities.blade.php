@extends('layouts.app')

@section('title', 'All Instruction Activities')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0 text-gray-800">All Instruction Activities</h1>
            <p class="text-muted">
                <small>Comprehensive activity logs for all instructions in the system</small>
            </p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="{{ route('instructions.monitor') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter me-1"></i> Filters
            </h6>
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('instructions.monitor.all-activities') }}">
                <i class="fas fa-redo-alt me-1"></i> Reset
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('instructions.monitor.all-activities') }}" method="GET" id="activities-filters-form">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="action" class="form-label">Activity Type</label>
                        <select class="form-select" id="action" name="action">
                            <option value="">All Activities</option>
                            <option value="sent" {{ request('action') == 'sent' ? 'selected' : '' }}>Sent</option>
                            <option value="read" {{ request('action') == 'read' ? 'selected' : '' }}>Read</option>
                            <option value="replied" {{ request('action') == 'replied' ? 'selected' : '' }}>Replied</option>
                            <option value="forwarded" {{ request('action') == 'forwarded' ? 'selected' : '' }}>Forwarded</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
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
                    <div class="col-md-3 mb-3">
                        <label for="instruction_id" class="form-label">Instruction</label>
                        <select class="form-select" id="instruction_id" name="instruction_id">
                            <option value="">All Instructions</option>
                            @foreach(\App\Models\Instruction::orderBy('created_at', 'desc')->get() as $instruction)
                                <option value="{{ $instruction->id }}" {{ request('instruction_id') == $instruction->id ? 'selected' : '' }}>
                                    {{ Str::limit($instruction->title, 50) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        @php
                            $pp = (int) request('per_page', 10);
                            if ($pp < 10) { $pp = 10; }
                            if ($pp > 100) { $pp = 100; }
                        @endphp
                        <label for="per_page" class="form-label">Per Page</label>
                        <select class="form-select" id="per_page" name="per_page">
                            <option value="10" {{ $pp === 10 ? 'selected' : '' }}>10 / page</option>
                            <option value="20" {{ $pp === 20 ? 'selected' : '' }}>20 / page</option>
                            <option value="50" {{ $pp === 50 ? 'selected' : '' }}>50 / page</option>
                            <option value="100" {{ $pp === 100 ? 'selected' : '' }}>100 / page</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3 d-flex align-items-end">
                        <input type="hidden" name="page" value="1">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> Apply Filters
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="from_date" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="from_date" name="from_date" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="to_date" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="to_date" name="to_date" value="{{ request('to_date') }}">
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Activities Table Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-history me-1"></i> Activity Logs
            </h6>
            <span class="badge bg-primary">{{ $activities->total() }} Records</span>
        </div>
        <div class="card-body">
            @if($activities->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-striped" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Type</th>
                                <th>Instruction</th>
                                <th>User</th>
                                <th>Details</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activities as $activity)
                                <tr>
                                    <td>{{ $activity->created_at->format('M d, Y g:i A') }}</td>
                                    <td>
                                        <span class="badge
                                            @if($activity->action == 'sent') bg-primary
                                            @elseif($activity->action == 'read') bg-success
                                            @elseif($activity->action == 'replied') bg-info
                                            @elseif($activity->action == 'forwarded') bg-warning
                                            @endif">
                                            <i class="fas
                                                @if($activity->action == 'sent') fa-paper-plane
                                                @elseif($activity->action == 'read') fa-check
                                                @elseif($activity->action == 'replied') fa-reply
                                                @elseif($activity->action == 'forwarded') fa-share
                                                @endif me-1"></i>
                                            {{ ucfirst($activity->action) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('instructions.show', $activity->instruction) }}" class="text-decoration-none">
                                            {{ Str::limit($activity->instruction->title, 30) }}
                                        </a>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $activity->user->avatar_url }}"
                                                 alt="{{ $activity->user->full_name }}" 
                                                 class="rounded-circle border border-1 me-2" 
                                                 width="28" 
                                                 height="28"
                                                 style="object-fit: cover;"
                                                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($activity->user->full_name) }}&color=7F9CF5&background=EBF4FF';">
                                            {{ $activity->user->full_name }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($activity->action == 'sent')
                                            Instruction sent
                                        @elseif($activity->action == 'read')
                                            Instruction viewed
                                        @elseif($activity->action == 'replied')
                                            Reply submitted
                                        @elseif($activity->action == 'forwarded')
                                            @if($activity->target_user_id)
                                                Forwarded to <strong>{{ $activity->targetUser->full_name }}</strong>
                                            @else
                                                Forwarded to multiple users
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-info"
                                            data-bs-toggle="modal" data-bs-target="#activityModal{{ $activity->id }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="{{ route('instructions.monitor.activities', $activity->instruction) }}"
                                            class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-list"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                    <div class="text-muted small">
                        @if($activities->total() > 0)
                            Showing <strong>{{ $activities->firstItem() }}</strong> to <strong>{{ $activities->lastItem() }}</strong> of <strong>{{ $activities->total() }}</strong> entries
                        @else
                            Showing 0 entries
                        @endif
                    </div>
                    <nav class="ms-md-auto" aria-label="Activities pagination">
                        {{ $activities->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </nav>
                </div>

                <!-- Activity Detail Modals -->
                @foreach($activities as $activity)
                    <div class="modal fade" id="activityModal{{ $activity->id }}" tabindex="-1"
                        aria-labelledby="activityModalLabel{{ $activity->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="activityModalLabel{{ $activity->id }}">
                                        {{ ucfirst($activity->action) }} Activity Details
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Instruction:</strong></p>
                                            <p>{{ $activity->instruction->title }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Date & Time:</strong></p>
                                            <p>{{ $activity->created_at->format('M d, Y g:i A') }}</p>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>User:</strong></p>
                                            <p>{{ $activity->user->full_name }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Action:</strong></p>
                                            <p>
                                                <span class="badge
                                                    @if($activity->action == 'sent') bg-primary
                                                    @elseif($activity->action == 'read') bg-success
                                                    @elseif($activity->action == 'replied') bg-info
                                                    @elseif($activity->action == 'forwarded') bg-warning
                                                    @endif">
                                                    {{ ucfirst($activity->action) }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>

                                    @if($activity->target_user_id)
                                    <div class="row mb-3">
                                        <div class="col-md-12">
                                            <p class="mb-1"><strong>Target User:</strong></p>
                                            <p>{{ $activity->targetUser->full_name }}</p>
                                        </div>
                                    </div>
                                    @endif

                                    @if($activity->content)
                                    <div class="row">
                                        <div class="col-md-12">
                                            <p class="mb-1"><strong>
                                                @if($activity->action == 'replied')
                                                    Reply Content:
                                                @elseif($activity->action == 'forwarded')
                                                    Forward Message:
                                                @else
                                                    Content:
                                                @endif
                                            </strong></p>
                                            <div class="p-3 bg-light rounded">
                                                {!! nl2br(e($activity->content)) !!}
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <a href="{{ route('instructions.show', $activity->instruction) }}"
                                        class="btn btn-primary">View Instruction</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-center py-5">
                    <i class="fas fa-history fa-3x mb-3 text-muted"></i>
                    <p class="mb-0">No activities found.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
    .table th {
        background-color: #f8f9fc;
    }

    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }

    .badge {
        font-weight: 500;
        padding: 0.35em 0.65em;
    }

    .modal-body p {
        margin-bottom: 0.5rem;
    }

    /* Normalize pagination across this page (match Instructions index) */
    .pagination {
        justify-content: center;
        margin-bottom: 0;
    }
    .pagination .page-link {
        padding: 6px 10px;
        font-size: 0.875rem;
        line-height: 1.2;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function(){
        var form = document.getElementById('activities-filters-form');
        var perPage = document.getElementById('per_page');
        if (form && perPage) {
            perPage.addEventListener('change', function(){
                var pageInput = form.querySelector('input[name="page"]');
                if (pageInput) { pageInput.value = '1'; }
                form.submit();
            });
        }
    });
    </script>
@endsection
