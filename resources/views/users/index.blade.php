@extends('layouts.app')

@section('title', 'Manage Users')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="fw-bold">User Management</h2>
            <p class="text-muted mb-0">Manage and monitor all system users</p>
        </div>
        <div class="col-md-6 text-md-end">
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>Add New User
            </a>
        </div>
    </div>

    <!-- Filter Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Users</h6>
                            <h3 class="mb-0">{{ $roleCounts['all'] }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Employees</h6>
                            <h3 class="mb-0">{{ $roleCounts['employee'] }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Supervisors</h6>
                            <h3 class="mb-0">{{ $roleCounts['supervisor'] }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-tie fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Admins</h6>
                            <h3 class="mb-0">{{ $roleCounts['admin'] }}</h3>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-user-shield fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <!-- Advanced Filters Header -->
        <div class="card-header bg-light">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0 d-flex align-items-center">
                        <i class="fas fa-filter me-2 text-primary"></i>
                        User Directory
                    </h5>
                </div>
                <div class="col-md-6 text-md-end">
                    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#advancedFilters">
                        <i class="fas fa-sliders-h me-1"></i>Advanced Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Collapsible Advanced Filters -->
        <div class="collapse" id="advancedFilters">
            <div class="card-body border-bottom bg-light">
                <form id="filterForm" method="GET" action="{{ route('users.index') }}">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Role Filter</label>
                            <select name="role" class="form-select" id="roleFilter">
                                <option value="">All Roles</option>
                                <option value="EMPLOYEE" {{ request('role') === 'EMPLOYEE' ? 'selected' : '' }}>
                                    Employee ({{ $roleCounts['employee'] }})
                                </option>
                                <option value="SUPERVISOR" {{ request('role') === 'SUPERVISOR' ? 'selected' : '' }}>
                                    Supervisor ({{ $roleCounts['supervisor'] }})
                                </option>
                                <option value="ADMIN" {{ request('role') === 'ADMIN' ? 'selected' : '' }}>
                                    Admin ({{ $roleCounts['admin'] }})
                                </option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Email Verification</label>
                            <select name="email_verified" class="form-select" id="emailVerifiedFilter">
                                <option value="">All Status</option>
                                <option value="verified" {{ request('email_verified') === 'verified' ? 'selected' : '' }}>
                                    Verified ({{ $verificationCounts['verified'] }})
                                </option>
                                <option value="pending" {{ request('email_verified') === 'pending' ? 'selected' : '' }}>
                                    Pending ({{ $verificationCounts['pending'] }})
                                </option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Sort By</label>
                            <div class="input-group">
                                <select name="sort_by" class="form-select" id="sortBy">
                                    <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Created Date</option>
                                    <option value="first_name" {{ request('sort_by') === 'first_name' ? 'selected' : '' }}>Name</option>
                                    <option value="email" {{ request('sort_by') === 'email' ? 'selected' : '' }}>Email</option>
                                    <option value="roles" {{ request('sort_by') === 'roles' ? 'selected' : '' }}>Role</option>
                                </select>
                                <select name="sort_order" class="form-select" id="sortOrder" style="max-width: 100px;">
                                    <option value="desc" {{ request('sort_order') === 'desc' ? 'selected' : '' }}>↓</option>
                                    <option value="asc" {{ request('sort_order') === 'asc' ? 'selected' : '' }}>↑</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-filter me-1"></i>Apply Filters
                            </button>
                            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Clear All
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Search and Quick Filters -->
        <div class="card-body border-bottom">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text bg-light">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" id="realTimeSearch" class="form-control" 
                               placeholder="Search by name, email, or nickname..." 
                               value="{{ request('search') }}"
                               style="background-color: var(--bg-input); color: var(--text-color);">
                        <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-4 mt-2 mt-md-0">
                    <div class="d-flex justify-content-end">
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="quickRole" id="quickAll" value="" {{ !request('role') ? 'checked' : '' }}>
                            <label class="btn btn-outline-primary btn-sm" for="quickAll">All</label>
                            
                            <input type="radio" class="btn-check" name="quickRole" id="quickEmployee" value="EMPLOYEE" {{ request('role') === 'EMPLOYEE' ? 'checked' : '' }}>
                            <label class="btn btn-outline-success btn-sm" for="quickEmployee">Employee</label>
                            
                            <input type="radio" class="btn-check" name="quickRole" id="quickSupervisor" value="SUPERVISOR" {{ request('role') === 'SUPERVISOR' ? 'checked' : '' }}>
                            <label class="btn btn-outline-info btn-sm" for="quickSupervisor">Supervisor</label>
                            
                            <input type="radio" class="btn-check" name="quickRole" id="quickAdmin" value="ADMIN" {{ request('role') === 'ADMIN' ? 'checked' : '' }}>
                            <label class="btn btn-outline-warning btn-sm" for="quickAdmin">Admin</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Users Table -->
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="d-flex align-items-center">
                    <span class="text-muted me-3">
                        <i class="fas fa-list me-1"></i>
                        Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users
                    </span>
                    @if(request()->hasAny(['search', 'role', 'email_verified']))
                        <span class="badge bg-info">
                            <i class="fas fa-filter me-1"></i>Filtered
                        </span>
                    @endif
                </div>
                <div class="text-muted small">
                    <i class="fas fa-clock me-1"></i>
                    Last updated: {{ now()->format('M d, Y H:i') }}
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-user-circle me-2 text-muted"></i>
                                    User
                                </div>
                            </th>
                            <th class="border-0">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-envelope me-2 text-muted"></i>
                                    Email & Status
                                </div>
                            </th>
                            <th class="border-0">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-shield-alt me-2 text-muted"></i>
                                    Role
                                </div>
                            </th>
                            <th class="border-0">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-calendar me-2 text-muted"></i>
                                    Joined
                                </div>
                            </th>
                            <th class="border-0 text-center">
                                <div class="d-flex align-items-center justify-content-center">
                                    <i class="fas fa-cogs me-2 text-muted"></i>
                                    Actions
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr class="user-row">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="position-relative me-3">
                                            @if($user->avatar)
                                                <img src="{{ $user->avatar }}" alt="Avatar" class="rounded-circle shadow-sm" width="45" height="45">
                                            @else
                                                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->first_name . ' ' . $user->last_name) }}&background=4070f4&color=fff&size=45"
                                                     alt="Avatar" class="rounded-circle shadow-sm" width="45" height="45">
                                            @endif
                                            @if($user->email_verified_at)
                                                <span class="position-absolute bottom-0 end-0 badge bg-success rounded-pill p-1" title="Email Verified">
                                                    <i class="fas fa-check" style="font-size: 8px;"></i>
                                                </span>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $user->first_name }} {{ $user->last_name }}</div>
                                            @if($user->nickname)
                                                <small class="text-muted d-block">"{{ $user->nickname }}"</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div class="text-break">{{ $user->email }}</div>
                                        <div class="mt-1">
                                            @if($user->email_verified_at)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                    <i class="fas fa-check-circle me-1"></i>Verified
                                                </span>
                                                <small class="text-muted d-block">{{ $user->email_verified_at->format('M d, Y') }}</small>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                                    <i class="fas fa-clock me-1"></i>Pending
                                                </span>
                                                <small class="text-muted d-block">Verification required</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge fs-6 px-3 py-2
                                        @switch($user->roles->value)
                                            @case('SYSTEM_ADMIN')
                                                bg-danger-subtle text-danger border border-danger-subtle
                                                @break
                                            @case('ADMIN')
                                                bg-warning-subtle text-warning border border-warning-subtle
                                                @break
                                            @case('SUPERVISOR')
                                                bg-info-subtle text-info border border-info-subtle
                                                @break
                                            @default
                                                bg-success-subtle text-success border border-success-subtle
                                        @endswitch
                                    ">
                                        @switch($user->roles->value)
                                            @case('SYSTEM_ADMIN')
                                                <i class="fas fa-crown me-1"></i>
                                                @break
                                            @case('ADMIN')
                                                <i class="fas fa-user-shield me-1"></i>
                                                @break
                                            @case('SUPERVISOR')
                                                <i class="fas fa-user-tie me-1"></i>
                                                @break
                                            @default
                                                <i class="fas fa-user me-1"></i>
                                        @endswitch
                                        {{ ucfirst(strtolower($user->roles->value)) }}
                                    </span>
                                </td>
                                <td>
                                    <div>
                                        <div class="fw-medium">{{ $user->created_at->format('M d, Y') }}</div>
                                        <small class="text-muted">{{ $user->created_at->diffForHumans() }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="{{ route('users.show', $user->id) }}" 
                                           class="btn btn-sm btn-outline-info" 
                                           title="View Details"
                                           data-bs-toggle="tooltip">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(Auth::user()->roles === \App\Enums\UserRole::SYSTEM_ADMIN)
                                        <a href="{{ route('users.edit', $user->id) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="Edit User"
                                           data-bs-toggle="tooltip">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endif
                                        @if($user->id !== Auth::id())
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-danger delete-user-btn" 
                                                    title="Delete User"
                                                    data-bs-toggle="tooltip"
                                                    data-user-name="{{ $user->first_name }} {{ $user->last_name }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <div class="mb-4">
                                            <i class="fas fa-users fa-4x text-muted opacity-50"></i>
                                        </div>
                                        <h5 class="text-muted mb-2">No users found</h5>
                                        @if(request()->hasAny(['search', 'role', 'email_verified']))
                                            <p class="text-muted mb-3">No users match your current filter criteria</p>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('users.index') }}" class="btn btn-outline-primary">
                                                    <i class="fas fa-redo me-2"></i>Clear Filters
                                                </a>
                                                <a href="{{ route('users.create') }}" class="btn btn-primary">
                                                    <i class="fas fa-plus-circle me-2"></i>Add New User
                                                </a>
                                            </div>
                                        @else
                                            <p class="text-muted mb-3">Get started by adding your first user</p>
                                            <a href="{{ route('users.create') }}" class="btn btn-primary btn-lg">
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

            <!-- Enhanced Pagination -->
            @if($users->hasPages())
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 pt-3 border-top">
                <div class="mb-3 mb-md-0">
                    <div class="d-flex align-items-center text-muted">
                        <i class="fas fa-info-circle me-2"></i>
                        <span>
                            Showing <strong>{{ $users->firstItem() }}</strong> to <strong>{{ $users->lastItem() }}</strong> 
                            of <strong>{{ $users->total() }}</strong> users
                        </span>
                    </div>
                    <small class="text-muted">
                        Page {{ $users->currentPage() }} of {{ $users->lastPage() }} • 15 users per page
                    </small>
                </div>
                <div class="d-flex align-items-center">
                    <nav aria-label="User pagination">
                        {{ $users->withQueryString()->links('pagination::bootstrap-4') }}
                    </nav>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Real-time Search and Filter JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Real-time search functionality
    const searchInput = document.getElementById('realTimeSearch');
    const clearSearchBtn = document.getElementById('clearSearch');
    const quickRoleButtons = document.querySelectorAll('input[name="quickRole"]');
    const filterForm = document.getElementById('filterForm');
    let searchTimeout;

    // Real-time search with debouncing
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            performSearch();
        }, 500); // 500ms delay for better UX
    });

    // Clear search functionality
    clearSearchBtn.addEventListener('click', function() {
        searchInput.value = '';
        performSearch();
    });

    // Quick role filter buttons
    quickRoleButtons.forEach(button => {
        button.addEventListener('change', function() {
            if (this.checked) {
                const url = new URL(window.location);
                if (this.value) {
                    url.searchParams.set('role', this.value);
                } else {
                    url.searchParams.delete('role');
                }
                // Preserve other filters
                if (searchInput.value) {
                    url.searchParams.set('search', searchInput.value);
                }
                window.location.href = url.toString();
            }
        });
    });

    // Advanced filter form auto-submit
    const filterSelects = document.querySelectorAll('#filterForm select');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            // Auto-submit advanced filters when changed
            setTimeout(() => {
                filterForm.submit();
            }, 100);
        });
    });

    // Perform search function
    function performSearch() {
        const searchTerm = searchInput.value.trim();
        const url = new URL(window.location);
        
        if (searchTerm) {
            url.searchParams.set('search', searchTerm);
        } else {
            url.searchParams.delete('search');
        }
        
        // Preserve other filters
        const currentRole = document.querySelector('input[name="quickRole"]:checked');
        if (currentRole && currentRole.value) {
            url.searchParams.set('role', currentRole.value);
        }
        
        window.location.href = url.toString();
    }

    // Enhanced delete confirmation
    const deleteButtons = document.querySelectorAll('.delete-user-btn');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const userName = this.getAttribute('data-user-name');
            
            // Create custom confirmation modal
            const confirmModal = document.createElement('div');
            confirmModal.className = 'modal fade';
            confirmModal.innerHTML = `
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Confirm User Deletion
                            </h5>
                        </div>
                        <div class="modal-body">
                            <div class="text-center mb-3">
                                <i class="fas fa-user-times fa-3x text-danger mb-3"></i>
                                <h6>Are you sure you want to delete this user?</h6>
                                <p class="text-muted mb-0">
                                    <strong>${userName}</strong>
                                </p>
                                <small class="text-danger">This action cannot be undone.</small>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times me-1"></i>Cancel
                            </button>
                            <button type="button" class="btn btn-danger" id="confirmDelete">
                                <i class="fas fa-trash me-1"></i>Delete User
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(confirmModal);
            const modal = new bootstrap.Modal(confirmModal);
            modal.show();
            
            // Handle confirmation
            confirmModal.querySelector('#confirmDelete').addEventListener('click', () => {
                this.closest('form').submit();
            });
            
            // Clean up modal after hiding
            confirmModal.addEventListener('hidden.bs.modal', () => {
                document.body.removeChild(confirmModal);
            });
        });
    });

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Loading state for search
    let isSearching = false;
    function showSearchLoading() {
        if (!isSearching) {
            isSearching = true;
            const searchIcon = document.querySelector('.input-group-text i');
            searchIcon.className = 'fas fa-spinner fa-spin text-muted';
        }
    }

    // Show loading on form submissions
    document.addEventListener('submit', function(e) {
        if (e.target.id === 'filterForm' || e.target.closest('.input-group')) {
            showSearchLoading();
        }
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + K to focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            searchInput.focus();
        }
        
        // Escape to clear search
        if (e.key === 'Escape' && document.activeElement === searchInput) {
            searchInput.value = '';
            searchInput.blur();
            performSearch();
        }
    });

    // Add search hint
    searchInput.setAttribute('title', 'Press Ctrl+K to focus, Escape to clear');
});
</script>

<style>
    /* Enhanced styling for production readiness */
    .user-row {
        transition: all 0.2s ease;
    }
    
    .user-row:hover {
        background-color: rgba(0, 123, 255, 0.05);
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    /* Statistics cards hover effects */
    .card.bg-primary:hover,
    .card.bg-success:hover,
    .card.bg-info:hover,
    .card.bg-warning:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        transition: all 0.3s ease;
    }

    /* Enhanced pagination styling */
    .pagination {
        margin-bottom: 0;
    }
    
    .pagination .page-link {
        border-radius: 6px;
        margin: 0 2px;
        border: 1px solid #dee2e6;
        color: #6c757d;
        padding: 8px 12px;
        transition: all 0.2s ease;
    }
    
    .pagination .page-item.active .page-link {
        background: linear-gradient(45deg, #007bff, #0056b3);
        border-color: #007bff;
        color: white;
        box-shadow: 0 2px 4px rgba(0,123,255,0.3);
    }
    
    .pagination .page-link:hover {
        background-color: #e9ecef;
        border-color: #adb5bd;
        transform: translateY(-1px);
    }

    /* Advanced filter section styling */
    #advancedFilters .card-body {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    /* Search input enhancements */
    #realTimeSearch {
        border-radius: 8px;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }
    
    #realTimeSearch:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
    }

    /* Badge enhancements */
    .badge {
        font-weight: 500;
        letter-spacing: 0.5px;
    }

    /* Button group styling */
    .btn-group .btn-check:checked + .btn {
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        transform: translateY(-1px);
    }

    /* Table enhancements */
    .table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
        padding: 1rem 0.75rem;
    }
    
    .table td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
    }

    /* Dark mode compatibility */
    [data-theme="dark"] .table {
        color: var(--text-color);
    }
    
    [data-theme="dark"] .table-light {
        background-color: var(--bg-card);
        color: var(--text-color);
    }

    [data-theme="dark"] .pagination .page-link {
        background-color: var(--bg-card);
        border-color: var(--border-color);
        color: var(--text-color);
    }

    [data-theme="dark"] .pagination .page-item.active .page-link {
        background: linear-gradient(45deg, var(--primary-color), #0056b3);
        border-color: var(--primary-color);
        color: white;
    }

    [data-theme="dark"] .pagination .page-item.disabled .page-link {
        background-color: var(--bg-card);
        color: var(--text-muted);
    }

    [data-theme="dark"] .user-row:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }

    [data-theme="dark"] #advancedFilters .card-body {
        background: linear-gradient(135deg, var(--bg-card) 0%, var(--bg-secondary) 100%);
    }

    /* Loading animation */
    .fa-spinner {
        animation: spin 1s linear infinite;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Responsive enhancements */
    @media (max-width: 768px) {
        .card-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .card-header .d-flex {
            width: 100%;
            margin-top: 10px;
        }

        .btn-group {
            width: 100%;
        }
        
        .btn-group .btn {
            flex: 1;
        }

        .table td, .table th {
            padding: 0.5rem 0.25rem;
            font-size: 0.875rem;
        }
        
        .d-flex.gap-1 {
            flex-direction: column;
            gap: 0.25rem !important;
        }
        
        .d-flex.gap-1 .btn {
            width: 100%;
        }
    }

    @media (max-width: 576px) {
        .col-md-3 {
            margin-bottom: 1rem;
        }
        
        .pagination {
            justify-content: center;
        }
        
        .pagination .page-link {
            padding: 6px 10px;
            font-size: 0.875rem;
        }
    }

    /* Print styles */
    @media print {
        .btn, .pagination, .card-header button {
            display: none !important;
        }
        
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        
        .table {
            font-size: 12px;
        }
    }
</style>
@endsection
