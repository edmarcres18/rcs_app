<tr data-user-id="{{ $user->id }}">
    <td>
        <input type="checkbox" class="form-check-input user-checkbox" value="{{ $user->id }}">
    </td>
    <td>
        @if($user->avatar)
            <img src="{{ $user->avatar }}" alt="Avatar" class="rounded-circle shadow-sm" width="40" height="40">
        @else
            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->first_name . ' ' . $user->last_name) }}&background=4070f4&color=fff"
                 alt="Avatar" class="rounded-circle shadow-sm" width="40" height="40">
        @endif
    </td>
    <td>
        <div class="fw-semibold">{{ $user->first_name }} {{ $user->last_name }}</div>
        @if($user->nickname)
            <small class="text-muted">({{ $user->nickname }})</small>
        @endif
    </td>
    <td>
        <div>{{ $user->email }}</div>
        <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
    </td>
    <td>
        @if($user->email_verified_at)
            <span class="badge bg-success rounded-pill">
                <i class="fas fa-check-circle me-1"></i>Verified
            </span>
            <div class="small text-muted mt-1">
                {{ $user->email_verified_at->format('M d, Y') }}
            </div>
        @else
            <span class="badge bg-warning text-dark rounded-pill">
                <i class="fas fa-clock me-1"></i>Pending
            </span>
            <div class="small text-muted mt-1">
                Not verified
            </div>
        @endif
    </td>
    <td>
        <span class="badge rounded-pill fs-6
            @switch($user->roles->value)
                @case('SYSTEM_ADMIN')
                    bg-danger
                    @break
                @case('ADMIN')
                    bg-warning text-dark
                    @break
                @case('SUPERVISOR')
                    bg-info
                    @break
                @default
                    bg-success
            @endswitch
        ">
            <i class="fas 
                @switch($user->roles->value)
                    @case('SYSTEM_ADMIN')
                        fa-crown
                        @break
                    @case('ADMIN')
                        fa-user-shield
                        @break
                    @case('SUPERVISOR')
                        fa-user-tie
                        @break
                    @default
                        fa-user
                @endswitch
                me-1
            "></i>
            {{ ucfirst(strtolower(str_replace('_', ' ', $user->roles->value))) }}
        </span>
    </td>
    <td>
        <div class="small">{{ $user->created_at->format('M d, Y') }}</div>
        <div class="text-muted small">{{ $user->created_at->format('H:i') }}</div>
    </td>
    <td>
        <div class="btn-group" role="group">
            <a href="{{ route('users.show', $user->id) }}" 
               class="btn btn-sm btn-outline-info" 
               title="View Details">
                <i class="fas fa-eye"></i>
            </a>
            @if(Auth::user()->roles === \App\Enums\UserRole::SYSTEM_ADMIN)
            <a href="{{ route('users.edit', $user->id) }}" 
               class="btn btn-sm btn-outline-primary" 
               title="Edit User">
                <i class="fas fa-edit"></i>
            </a>
            @endif
            @if($user->id !== Auth::id())
            <button type="button" 
                    class="btn btn-sm btn-outline-danger" 
                    title="Delete User"
                    onclick="confirmDelete({{ $user->id }}, '{{ $user->first_name }} {{ $user->last_name }}')">
                <i class="fas fa-trash"></i>
            </button>
            @endif
        </div>
    </td>
</tr>
