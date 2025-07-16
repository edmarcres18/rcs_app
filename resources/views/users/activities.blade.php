@extends('layouts.app')

@section('title', 'User Activities')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="fw-bold">Activity Log</h2>
            <p class="text-muted">Viewing activity log for {{ $user->first_name }} {{ $user->last_name }}</p>
        </div>
        <div class="col-md-6 text-md-end">
            <div class="btn-group">
                <a href="{{ route('users.show', $user->id) }}" class="btn btn-info">
                    <i class="fas fa-user me-2"></i>Back to Profile
                </a>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                    <i class="fas fa-users me-2"></i>All Users
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h5 class="mb-0">Activity History</h5>
            <div class="d-flex mt-2 mt-md-0">
                <form method="GET" action="{{ route('users.activities', $user->id) }}" class="d-flex">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search activities..."
                               value="{{ request('search') }}" style="background-color: var(--bg-input); color: var(--text-color);">
                        <button class="btn btn-outline-primary" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Activity</th>
                            <th>Description</th>
                            <th>IP Address</th>
                            <th>Device</th>
                            <th>Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activities as $activity)
                            <tr>
                                <td width="15%">{{ $activity->created_at->format('M d, Y h:i A') }}</td>
                                <td>
                                    <span class="badge bg-{{ getActivityColor($activity->activity_type) }}">
                                        {{ ucfirst(str_replace('_', ' ', $activity->activity_type)) }}
                                    </span>
                                </td>
                                <td>{{ $activity->activity_description }}</td>
                                <td>{{ $activity->ip_address }}</td>
                                <td>
                                    {{ $activity->browser }} on {{ $activity->platform }}
                                    <div class="small text-muted">{{ $activity->device }}</div>
                                </td>
                                <td>{{ $activity->location ?: 'Unknown' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="fas fa-history fa-3x mb-3 text-muted"></i>
                                        <h5>No activities found</h5>
                                        @if(request('search'))
                                            <p>Try adjusting your search criteria</p>
                                            <a href="{{ route('users.activities', $user->id) }}" class="btn btn-outline-primary mt-2">
                                                <i class="fas fa-redo me-2"></i>Reset Search
                                            </a>
                                        @else
                                            <p>The activity history for this user is empty</p>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap">
                <div class="mb-2 mb-md-0">
                    <span class="text-muted">
                        Showing {{ $activities->firstItem() ?? 0 }} to {{ $activities->lastItem() ?? 0 }} of {{ $activities->total() }} activities
                    </span>
                </div>
                <div>
                    {{ $activities->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Dark mode compatibility fixes */
    [data-theme="dark"] .table {
        color: var(--text-color);
    }

    [data-theme="dark"] .pagination .page-link {
        background-color: var(--bg-card);
        border-color: var(--border-color);
        color: var(--text-color);
    }

    [data-theme="dark"] .pagination .page-item.active .page-link {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
    }

    [data-theme="dark"] .pagination .page-item.disabled .page-link {
        background-color: var(--bg-card);
        color: var(--text-muted);
    }

    /* Responsive fixes */
    @media (max-width: 768px) {
        .card-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .card-header .d-flex {
            width: 100%;
            margin-top: 10px;
        }

        .card-header .input-group {
            width: 100%;
        }

        .btn-group {
            flex-direction: column;
            width: 100%;
        }

        .btn-group .btn {
            width: 100%;
            margin-bottom: 5px;
            border-radius: 0.25rem !important;
        }
    }
</style>
@endsection
