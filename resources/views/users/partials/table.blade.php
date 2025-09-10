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
                    <td>
                        {{ $user->email }}
                        @if($user->email_verified_at)
                            <span class="badge bg-success ms-2">Verified</span>
                        @else
                            <span class="badge bg-secondary ms-2">Pending</span>
                        @endif
                    </td>
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
    <div class="pagination-wrapper">
        {{ $users->withQueryString()->links() }}
    </div>
</div>
