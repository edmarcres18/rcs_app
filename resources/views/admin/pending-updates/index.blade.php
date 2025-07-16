@extends('layouts.app')

@section('title', 'Pending User Updates')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="fw-bold">Pending User Updates</h2>
            <p class="text-muted">Review and approve or reject user update and deletion requests.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">All Pending Requests</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Request Type</th>
                            <th>Requested By</th>
                            <th>Requested At</th>
                            <th>Changes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingUpdates as $update)
                            <tr>
                                <td>{{ $update->user->email ?? 'User not found' }}</td>
                                <td>
                                    <span class="badge bg-{{ $update->type === 'update' ? 'info' : 'danger' }}">
                                        {{ ucfirst($update->type) }}
                                    </span>
                                </td>
                                <td>{{ $update->requester->email ?? 'Requester not found' }}</td>
                                <td>{{ $update->created_at->format('M d, Y H:i') }}</td>
                                <td>
                                    @if($update->type === 'update' && is_array($update->data))
                                        <ul>
                                            @foreach($update->data as $key => $value)
                                                <li><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $value }}</li>
                                            @endforeach
                                        </ul>
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <form action="{{ route('admin.pending-updates.approve', $update) }}" method="POST" onsubmit="return confirm('Are you sure you want to approve this request?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fas fa-check-circle me-1"></i> Approve
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.pending-updates.reject', $update) }}" method="POST" onsubmit="return confirm('Are you sure you want to reject this request?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-times-circle me-1"></i> Reject
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <i class="fas fa-inbox fa-3x mb-3 text-muted"></i>
                                    <h5>No pending requests found.</h5>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4">
                {{ $pendingUpdates->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
