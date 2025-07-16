@extends('layouts.app')

@section('title', 'Instruction Monitoring Dashboard')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0 text-gray-800">Instruction Monitoring Dashboard</h1>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="{{ route('instructions.monitor.all-activities') }}" class="btn btn-info">
                <i class="fas fa-history me-1"></i> Activity Logs
            </a>
            <a href="{{ route('instructions.monitor.reports') }}" class="btn btn-primary ms-2">
                <i class="fas fa-chart-bar me-1"></i> Reports
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Instructions</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Read</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['read'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Unread</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['unread'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-envelope fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Forwarded</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['forwarded'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-share fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter me-1"></i> Filters
            </h6>
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('instructions.monitor') }}">
                <i class="fas fa-redo-alt me-1"></i> Reset
            </a>
        </div>
        <div class="card-body">
            <form action="{{ route('instructions.monitor') }}" method="GET">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search"
                               value="{{ request('search') }}" placeholder="Title or content...">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="sender_id" class="form-label">Sender</label>
                        <select class="form-select" id="sender_id" name="sender_id">
                            <option value="">All Senders</option>
                            @foreach(\App\Models\User::orderBy('first_name')->get() as $user)
                                <option value="{{ $user->id }}" {{ request('sender_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="from_date" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="from_date" name="from_date"
                               value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-2 mb-3">
                        <label for="to_date" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="to_date" name="to_date"
                               value="{{ request('to_date') }}">
                    </div>
                    <div class="col-md-2 mb-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> Apply Filters
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Instructions Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list me-1"></i> All Instructions
            </h6>
            <span class="badge bg-primary">{{ $instructions->total() }}</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Sender</th>
                            <th>Recipients</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($instructions as $instruction)
                            <tr>
                                <td>{{ $instruction->id }}</td>
                                <td>
                                    <a href="{{ route('instructions.show', $instruction) }}">
                                        {{ $instruction->title }}
                                    </a>
                                </td>
                                <td>{{ $instruction->sender->full_name }}</td>
                                <td>
                                    {{ $instruction->recipients->count() }} recipient(s)
                                    <div class="mt-1">
                                        <span class="badge bg-success">
                                            {{ $instruction->recipients->where('pivot.is_read', true)->count() }} read
                                        </span>
                                        <span class="badge bg-danger">
                                            {{ $instruction->recipients->where('pivot.is_read', false)->count() }} unread
                                        </span>
                                    </div>
                                </td>
                                <td>{{ $instruction->created_at->format('M d, Y g:i A') }}</td>
                                <td>
                                    @if($instruction->recipients->where('pivot.is_read', false)->count() == 0)
                                        <span class="badge bg-success">All Read</span>
                                    @elseif($instruction->recipients->where('pivot.is_read', true)->count() == 0)
                                        <span class="badge bg-danger">None Read</span>
                                    @else
                                        <span class="badge bg-warning">Partially Read</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('instructions.show', $instruction) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('instructions.monitor.activities', $instruction) }}" class="btn btn-sm btn-secondary">
                                        <i class="fas fa-history"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">No instructions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $instructions->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>

<style>
    .border-left-primary {
        border-left: 4px solid var(--primary-color);
    }
    .border-left-success {
        border-left: 4px solid #28a745;
    }
    .border-left-danger {
        border-left: 4px solid #dc3545;
    }
    .border-left-info {
        border-left: 4px solid #17a2b8;
    }
</style>
@endsection
