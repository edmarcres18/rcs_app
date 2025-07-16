@extends('layouts.app')

@section('title', 'All User Activities')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="fw-bold">System Activity Log</h2>
            <p class="text-muted">Monitor all user activities across the system</p>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Users
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Filter Activities</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('users.all-activities') }}" class="row g-3">
                <div class="col-md-3 col-sm-6">
                    <label for="user_id" class="form-label">User</label>
                    <select name="user_id" id="user_id" class="form-select" style="background-color: var(--bg-input); color: var(--text-color);">
                        <option value="">All Users</option>
                        @foreach($users as $userOption)
                            <option value="{{ $userOption->id }}" {{ request('user_id') == $userOption->id ? 'selected' : '' }}>
                                {{ $userOption->first_name }} {{ $userOption->last_name }} ({{ $userOption->email }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-sm-6">
                    <label for="type" class="form-label">Activity Type</label>
                    <select name="type" id="type" class="form-select" style="background-color: var(--bg-input); color: var(--text-color);">
                        <option value="">All Activities</option>
                        @foreach($activityTypes as $type)
                            <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $type)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-sm-6">
                    <label for="from_date" class="form-label">From Date</label>
                    <input type="date" name="from_date" id="from_date" class="form-control"
                           value="{{ request('from_date') }}" style="background-color: var(--bg-input); color: var(--text-color);">
                </div>
                <div class="col-md-2 col-sm-6">
                    <label for="to_date" class="form-label">To Date</label>
                    <input type="date" name="to_date" id="to_date" class="form-control"
                           value="{{ request('to_date') }}" style="background-color: var(--bg-input); color: var(--text-color);">
                </div>
                <div class="col-md-2 col-12">
                    <label for="search" class="form-label">Search</label>
                    <div class="input-group">
                        <input type="text" name="search" id="search" class="form-control" placeholder="Search..."
                               value="{{ request('search') }}" style="background-color: var(--bg-input); color: var(--text-color);">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-12 mt-2 text-end">
                    <a href="{{ route('users.all-activities') }}" class="btn btn-secondary">
                        <i class="fas fa-redo me-2"></i>Reset Filters
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Activity History</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>User</th>
                            <th>Activity</th>
                            <th>Description</th>
                            <th>IP Address</th>
                            <th>Device Info</th>
                            <th>Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activities as $activity)
                            <tr>
                                <td width="15%">{{ $activity->created_at->format('M d, Y h:i A') }}</td>
                                <td>
                                    <a href="{{ route('users.show', $activity->user->id) }}" class="text-decoration-none">
                                        {{ $activity->user->first_name }} {{ $activity->user->last_name }}
                                    </a>
                                    <div class="small text-muted">{{ $activity->user->email }}</div>
                                </td>
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
                                <td colspan="7" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="fas fa-history fa-3x mb-3 text-muted"></i>
                                        <h5>No activities found</h5>
                                        @if(request()->anyFilled(['search', 'user_id', 'type', 'from_date', 'to_date']))
                                            <p>Try adjusting your filter criteria</p>
                                            <a href="{{ route('users.all-activities') }}" class="btn btn-outline-primary mt-2">
                                                <i class="fas fa-redo me-2"></i>Reset Filters
                                            </a>
                                        @else
                                            <p>The system activity log is empty</p>
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
    }
</style>
@endsection
