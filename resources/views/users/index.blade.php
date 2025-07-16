@extends('layouts.app')

@section('title', 'Manage Users')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="fw-bold">User Management</h2>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>Add New User
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h5 class="mb-0">All Users</h5>
            <div class="d-flex mt-2 mt-md-0">
                <form method="GET" action="{{ route('users.index') }}" class="d-flex">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Search users..."
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
                            <th>Avatar</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>
                                    @if($user->avatar)
                                        <img src="{{ $user->avatar }}" alt="Avatar" class="rounded-circle" width="40" height="40">
                                    @else
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->first_name . ' ' . $user->last_name) }}&background=4070f4&color=fff"
                                             alt="Avatar" class="rounded-circle" width="40" height="40">
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $user->first_name }} {{ $user->last_name }}</div>
                                    @if($user->nickname)
                                        <small class="text-muted">({{ $user->nickname }})</small>
                                    @endif
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
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
                                </td>
                                <td>{{ $user->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('users.show', $user->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(Auth::user()->roles === \App\Enums\UserRole::SYSTEM_ADMIN)
                                        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endif
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="fas fa-users fa-3x mb-3 text-muted"></i>
                                        <h5>No users found</h5>
                                        @if(request('search'))
                                            <p>Try adjusting your search criteria</p>
                                            <a href="{{ route('users.index') }}" class="btn btn-outline-primary mt-2">
                                                <i class="fas fa-redo me-2"></i>Reset Search
                                            </a>
                                        @else
                                            <p>Start by adding a new user</p>
                                            <a href="{{ route('users.create') }}" class="btn btn-primary mt-2">
                                                <i class="fas fa-plus-circle me-2"></i>Add New User
                                            </a>
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
                        Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users
                    </span>
                </div>
                <div>
                    {{ $users->withQueryString()->links() }}
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
