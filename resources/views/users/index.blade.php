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

    <!-- Advanced Filters Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">
                <i class="fas fa-filter me-2"></i>Advanced Filters
                <button class="btn btn-sm btn-outline-secondary ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#filtersCollapse">
                    <i class="fas fa-chevron-down"></i>
                </button>
            </h6>
        </div>
        <div class="collapse show" id="filtersCollapse">
            <div class="card-body">
                <form id="filterForm" method="GET" action="{{ route('users.index') }}">
                    <div class="row g-3">
                        <!-- Real-time Search -->
                        <div class="col-md-4">
                            <label for="search" class="form-label">
                                <i class="fas fa-search me-1"></i>Search Users
                                <span class="badge bg-info ms-2" id="searchResultsCount" style="display: none;">0 results</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text" id="searchIcon">
                                    <i class="fas fa-search" aria-hidden="true"></i>
                                </span>
                                <input type="text" id="search" name="search" class="form-control" 
                                       placeholder="Search by name, email, or nickname..." 
                                       value="{{ request('search') }}"
                                       style="background-color: var(--bg-input); color: var(--text-color);"
                                       aria-label="Search users"
                                       aria-describedby="searchIcon searchHelp"
                                       autocomplete="off"
                                       spellcheck="false">
                                <button type="button" id="clearSearch" class="btn btn-outline-secondary" 
                                        style="display: none;" 
                                        aria-label="Clear search"
                                        title="Clear search">
                                    <i class="fas fa-times" aria-hidden="true"></i>
                                </button>
                            </div>
                            <small id="searchHelp" class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>Search updates automatically as you type (minimum 2 characters)
                            </small>
                        </div>

                        <!-- Role Filter -->
                        <div class="col-md-2">
                            <label for="role" class="form-label">
                                <i class="fas fa-user-tag me-1"></i>Role
                            </label>
                            <select id="role" name="role" class="form-select" 
                                    style="background-color: var(--bg-input); color: var(--text-color);"
                                    aria-label="Filter by user role">
                                <option value="">All Roles</option>
                                <option value="EMPLOYEE" {{ request('role') === 'EMPLOYEE' ? 'selected' : '' }}>
                                    👤 Employee
                                </option>
                                <option value="SUPERVISOR" {{ request('role') === 'SUPERVISOR' ? 'selected' : '' }}>
                                    👔 Supervisor
                                </option>
                                <option value="ADMIN" {{ request('role') === 'ADMIN' ? 'selected' : '' }}>
                                    🛡️ Admin
                                </option>
                                <option value="SYSTEM_ADMIN" {{ request('role') === 'SYSTEM_ADMIN' ? 'selected' : '' }}>
                                    👑 System Admin
                                </option>
                            </select>
                        </div>

                        <!-- Email Verification Filter -->
                        <div class="col-md-2">
                            <label for="email_verified" class="form-label">
                                <i class="fas fa-envelope-check me-1"></i>Email Status
                            </label>
                            <select id="email_verified" name="email_verified" class="form-select" 
                                    style="background-color: var(--bg-input); color: var(--text-color);"
                                    aria-label="Filter by email verification status">
                                <option value="">All Status</option>
                                <option value="verified" {{ request('email_verified') === 'verified' ? 'selected' : '' }}>
                                    ✅ Verified
                                </option>
                                <option value="pending" {{ request('email_verified') === 'pending' ? 'selected' : '' }}>
                                    ⏳ Pending
                                </option>
                            </select>
                        </div>

                        <!-- Date Range Filter -->
                        <div class="col-md-2">
                            <label for="date_range" class="form-label">
                                <i class="fas fa-calendar-alt me-1"></i>Created Date
                            </label>
                            <select id="date_range" name="date_range" class="form-select" 
                                    style="background-color: var(--bg-input); color: var(--text-color);"
                                    aria-label="Filter by creation date">
                                <option value="">All Time</option>
                                <option value="today" {{ request('date_range') === 'today' ? 'selected' : '' }}>📅 Today</option>
                                <option value="week" {{ request('date_range') === 'week' ? 'selected' : '' }}>📊 This Week</option>
                                <option value="month" {{ request('date_range') === 'month' ? 'selected' : '' }}>📆 This Month</option>
                                <option value="year" {{ request('date_range') === 'year' ? 'selected' : '' }}>🗓️ This Year</option>
                            </select>
                        </div>

                        <!-- Sort Options -->
                        <div class="col-md-2">
                            <label for="sort_by" class="form-label">
                                <i class="fas fa-sort me-1"></i>Sort By
                            </label>
                            <select id="sort_by" name="sort_by" class="form-select" 
                                    style="background-color: var(--bg-input); color: var(--text-color);"
                                    aria-label="Sort users by">
                                <option value="created_at" {{ request('sort_by', 'created_at') === 'created_at' ? 'selected' : '' }}>🕒 Created Date</option>
                                <option value="name" {{ request('sort_by') === 'name' ? 'selected' : '' }}>🔤 Name (A-Z)</option>
                                <option value="email" {{ request('sort_by') === 'email' ? 'selected' : '' }}>📧 Email</option>
                                <option value="role" {{ request('sort_by') === 'role' ? 'selected' : '' }}>👥 Role</option>
                                <option value="email_verified" {{ request('sort_by') === 'email_verified' ? 'selected' : '' }}>✅ Email Status</option>
                            </select>
                        </div>
                    </div>

                    <!-- Advanced Options Row -->
                    <div class="row mt-3">
                        <div class="col-md-3">
                            <label for="per_page" class="form-label">
                                <i class="fas fa-list-ol me-1"></i>Results Per Page
                            </label>
                            <select id="per_page" name="per_page" class="form-select" 
                                    style="background-color: var(--bg-input); color: var(--text-color);"
                                    aria-label="Number of results per page">
                                <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>📄 10 per page</option>
                                <option value="15" {{ request('per_page', '15') == '15' ? 'selected' : '' }}>📋 15 per page</option>
                                <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>📊 25 per page</option>
                                <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>📈 50 per page</option>
                                <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>📚 100 per page</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="sort_order" class="form-label">
                                <i class="fas fa-sort-amount-down me-1"></i>Sort Order
                            </label>
                            <select id="sort_order" name="sort_order" class="form-select" 
                                    style="background-color: var(--bg-input); color: var(--text-color);"
                                    aria-label="Sort order">
                                <option value="desc" {{ request('sort_order', 'desc') === 'desc' ? 'selected' : '' }}>⬇️ Newest First</option>
                                <option value="asc" {{ request('sort_order') === 'asc' ? 'selected' : '' }}>⬆️ Oldest First</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="fas fa-bolt me-1"></i>Quick Filters
                                <small class="text-muted">(Click for instant filtering)</small>
                            </label>
                            <div class="d-flex gap-2 flex-wrap" role="group" aria-label="Quick filter buttons">
                                <button type="button" class="btn btn-sm btn-outline-success quick-filter" 
                                        data-filter="verified" 
                                        aria-label="Show only verified users"
                                        title="Show only users with verified email addresses">
                                    <i class="fas fa-check-circle me-1" aria-hidden="true"></i>Verified Only
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-warning quick-filter" 
                                        data-filter="pending"
                                        aria-label="Show only pending users"
                                        title="Show only users with pending email verification">
                                    <i class="fas fa-clock me-1" aria-hidden="true"></i>Pending Only
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-info quick-filter" 
                                        data-filter="admins"
                                        aria-label="Show only admin users"
                                        title="Show only users with admin privileges">
                                    <i class="fas fa-user-shield me-1" aria-hidden="true"></i>Admins Only
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary quick-filter" 
                                        data-filter="recent"
                                        aria-label="Show recent users"
                                        title="Show users created in the last 7 days">
                                    <i class="fas fa-calendar-day me-1" aria-hidden="true"></i>Recent (7 days)
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Actions Row -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex gap-2 flex-wrap">
                                    <button type="button" id="resetFilters" class="btn btn-outline-secondary"
                                            aria-label="Reset all filters to default values"
                                            title="Clear all active filters">
                                        <i class="fas fa-undo me-1" aria-hidden="true"></i> 
                                        <span class="d-none d-sm-inline">Reset All Filters</span>
                                        <span class="d-sm-none">Reset</span>
                                    </button>
                                    <button type="button" id="applyFilters" class="btn btn-primary"
                                            aria-label="Apply current filter settings"
                                            title="Apply all selected filters">
                                        <i class="fas fa-filter me-1" aria-hidden="true"></i> 
                                        <span class="d-none d-sm-inline">Apply Filters</span>
                                        <span class="d-sm-none">Apply</span>
                                    </button>
                                    <button type="button" id="saveFilters" class="btn btn-outline-info"
                                            aria-label="Save current filter settings"
                                            title="Save these filter settings for future use">
                                        <i class="fas fa-bookmark me-1" aria-hidden="true"></i> 
                                        <span class="d-none d-sm-inline">Save Filters</span>
                                        <span class="d-sm-none">Save</span>
                                    </button>
                                </div>
                                <div class="d-flex align-items-center gap-3 flex-wrap">
                                    <div class="text-muted small" id="filterStatus" role="status" aria-live="polite">
                                        <i class="fas fa-info-circle me-1" aria-hidden="true"></i>
                                        <span id="activeFiltersCount">0</span> active filters
                                    </div>
                                    <div id="filterError" class="text-danger small" style="display: none;" role="alert" aria-live="assertive">
                                        <i class="fas fa-exclamation-triangle me-1" aria-hidden="true"></i>
                                        <span id="filterErrorMessage"></span>
                                    </div>
                                    <div id="filterSuccess" class="text-success small" style="display: none;" role="status" aria-live="polite">
                                        <i class="fas fa-check-circle me-1" aria-hidden="true"></i>
                                        <span id="filterSuccessMessage"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Users Table Card -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h5 class="mb-0">All Users</h5>
                <small class="text-muted" id="resultsCount">
                    Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users
                </small>
            </div>
            <div class="d-flex align-items-center gap-2">
                <div class="spinner-border spinner-border-sm text-primary" id="loadingSpinner" style="display: none;" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-cog"></i> Options
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="exportUsers()"><i class="fas fa-download me-2"></i>Export Users</a></li>
                        <li><a class="dropdown-item" href="#" onclick="bulkActions()"><i class="fas fa-tasks me-2"></i>Bulk Actions</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="card-body" id="usersTableContainer">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th width="60" scope="col">
                                <input type="checkbox" id="selectAll" class="form-check-input"
                                       aria-label="Select all users on this page"
                                       title="Select/deselect all users">
                            </th>
                            <th width="80" scope="col">
                                <span class="d-none d-md-inline">Avatar</span>
                                <i class="fas fa-user-circle d-md-none" aria-label="Avatar" title="User Avatar"></i>
                            </th>
                            <th scope="col">
                                <i class="fas fa-user me-1 d-none d-sm-inline" aria-hidden="true"></i>Name
                            </th>
                            <th scope="col">
                                <i class="fas fa-envelope me-1 d-none d-sm-inline" aria-hidden="true"></i>Email
                            </th>
                            <th width="120" scope="col">
                                <span class="d-none d-lg-inline">Email Status</span>
                                <span class="d-lg-none">Status</span>
                            </th>
                            <th width="120" scope="col">
                                <i class="fas fa-user-tag me-1 d-none d-sm-inline" aria-hidden="true"></i>Role
                            </th>
                            <th width="100" scope="col">
                                <span class="d-none d-md-inline">Created</span>
                                <i class="fas fa-calendar d-md-none" aria-label="Created date" title="Creation Date"></i>
                            </th>
                            <th width="150" scope="col">
                                <span class="d-none d-sm-inline">Actions</span>
                                <i class="fas fa-cog d-sm-none" aria-label="Actions" title="User Actions"></i>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        @forelse($users as $user)
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
                        @empty
                            <tr id="noResultsRow">
                                <td colspan="8" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="fas fa-users fa-4x mb-3 text-muted opacity-50"></i>
                                        <h5 class="text-muted">No users found</h5>
                                        @if(request()->hasAny(['search', 'role', 'email_verified']))
                                            <p class="text-muted mb-3">Try adjusting your search criteria or filters</p>
                                            <button type="button" id="clearAllFilters" class="btn btn-outline-primary">
                                                <i class="fas fa-redo me-2"></i>Clear All Filters
                                            </button>
                                        @else
                                            <p class="text-muted mb-3">Start by adding your first user</p>
                                            <a href="{{ route('users.create') }}" class="btn btn-primary">
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
            <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap">
                <div class="mb-2 mb-md-0">
                    <div class="d-flex align-items-center gap-3">
                        <span class="text-muted">
                            Showing <strong>{{ $users->firstItem() ?? 0 }}</strong> to <strong>{{ $users->lastItem() ?? 0 }}</strong> 
                            of <strong>{{ $users->total() }}</strong> users
                        </span>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                {{ $users->perPage() }} per page
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['per_page' => 10]) }}">10 per page</a></li>
                                <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['per_page' => 15]) }}">15 per page</a></li>
                                <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['per_page' => 25]) }}">25 per page</a></li>
                                <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['per_page' => 50]) }}">50 per page</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="pagination-wrapper">
                    {{ $users->withQueryString()->links('pagination::bootstrap-4') }}
                </div>
            </div>
            @endif

            <!-- Bulk Actions Bar (Hidden by default) -->
            <div id="bulkActionsBar" class="alert alert-info mt-3" style="display: none;">
                <div class="d-flex justify-content-between align-items-center">
                    <span id="selectedCount">0 users selected</span>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="bulkVerifyEmails()">
                            <i class="fas fa-envelope-check me-1"></i>Verify Emails
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-warning" onclick="bulkChangeRole()">
                            <i class="fas fa-user-tag me-1"></i>Change Role
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="bulkDelete()">
                            <i class="fas fa-trash me-1"></i>Delete Selected
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteUserName"></strong>?</p>
                <p class="text-muted small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete User</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    /* Enhanced styling for production-ready UI */
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
        transition: background-color 0.15s ease-in-out;
    }

    .badge {
        font-size: 0.75rem;
        font-weight: 500;
    }

    .btn-group .btn {
        border-radius: 0.375rem;
        margin-right: 2px;
    }

    .btn-group .btn:last-child {
        margin-right: 0;
    }

    .pagination-wrapper .pagination {
        margin-bottom: 0;
    }

    .pagination .page-link {
        padding: 0.5rem 0.75rem;
        margin: 0 2px;
        border-radius: 0.375rem;
        border: 1px solid #dee2e6;
        color: #6c757d;
        transition: all 0.15s ease-in-out;
    }

    .pagination .page-link:hover {
        background-color: #e9ecef;
        border-color: #adb5bd;
        color: #495057;
    }

    .pagination .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: white;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }

    .pagination .page-item.disabled .page-link {
        color: #6c757d;
        background-color: #fff;
        border-color: #dee2e6;
        opacity: 0.5;
    }

    /* Dark mode compatibility */
    [data-theme="dark"] .table {
        color: var(--text-color);
        --bs-table-bg: transparent;
    }

    [data-theme="dark"] .table-hover tbody tr:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }

    [data-theme="dark"] .table-light {
        background-color: rgba(255, 255, 255, 0.05);
        border-color: var(--border-color);
    }

    [data-theme="dark"] .pagination .page-link {
        background-color: var(--bg-card);
        border-color: var(--border-color);
        color: var(--text-color);
    }

    [data-theme="dark"] .pagination .page-link:hover {
        background-color: rgba(255, 255, 255, 0.1);
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
        opacity: 0.5;
    }

    [data-theme="dark"] .modal-content {
        background-color: var(--bg-card);
        color: var(--text-color);
    }

    [data-theme="dark"] .modal-header {
        border-bottom-color: var(--border-color);
    }

    [data-theme="dark"] .modal-footer {
        border-top-color: var(--border-color);
    }

    /* Loading states */
    .table-loading {
        opacity: 0.6;
        pointer-events: none;
    }

    .search-highlight {
        background-color: yellow;
        padding: 1px 2px;
        border-radius: 2px;
    }

    [data-theme="dark"] .search-highlight {
        background-color: #ffc107;
        color: #000;
    }

    /* Enhanced Responsive Design */
    @media (max-width: 1200px) {
        .container-fluid {
            padding-left: 15px;
            padding-right: 15px;
        }
        
        .row.g-3 > * {
            margin-bottom: 1rem;
        }
    }

    @media (max-width: 992px) {
        /* Tablet optimizations */
        .col-md-4 {
            flex: 0 0 100%;
            max-width: 100%;
        }
        
        .col-md-2, .col-md-3 {
            flex: 0 0 50%;
            max-width: 50%;
        }
        
        .quick-filter {
            font-size: 0.8rem;
            padding: 0.375rem 0.75rem;
        }
        
        .table-responsive {
            border-radius: 0.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    }

    @media (max-width: 768px) {
        /* Mobile tablet optimizations */
        .card-header {
            flex-direction: column;
            align-items: flex-start;
            padding: 1rem;
        }

        .card-header .d-flex {
            width: 100%;
            margin-top: 10px;
        }

        .col-md-2, .col-md-3, .col-md-4, .col-md-6 {
            flex: 0 0 100%;
            max-width: 100%;
            margin-bottom: 1rem;
        }

        .btn-group {
            flex-direction: row;
            width: auto;
            gap: 2px;
        }

        .btn-group .btn {
            margin-right: 0;
            margin-bottom: 0;
            border-radius: 0.375rem;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        .pagination-wrapper {
            overflow-x: auto;
            padding: 0.5rem 0;
        }

        .table-responsive {
            font-size: 0.875rem;
            border: none;
            box-shadow: none;
        }

        .badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }

        /* Mobile-friendly filter collapse */
        .collapse.show {
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Improved mobile table */
        .table th {
            font-size: 0.8rem;
            padding: 0.75rem 0.5rem;
            white-space: nowrap;
        }

        .table td {
            padding: 0.75rem 0.5rem;
            vertical-align: middle;
        }

        /* Mobile-specific quick filters */
        .quick-filter {
            font-size: 0.75rem;
            padding: 0.5rem 0.75rem;
            margin-bottom: 0.5rem;
            white-space: nowrap;
        }

        /* Mobile pagination */
        .pagination .page-link {
            padding: 0.375rem 0.5rem;
            font-size: 0.875rem;
        }
    }

    @media (max-width: 576px) {
        /* Small mobile optimizations */
        .container-fluid {
            padding-left: 8px;
            padding-right: 8px;
        }

        .card {
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .card-body {
            padding: 0.75rem;
        }

        .card-header {
            padding: 0.75rem;
        }

        /* Compact table for small screens */
        .table th,
        .table td {
            padding: 0.5rem 0.25rem;
            font-size: 0.8rem;
        }

        .table th:first-child,
        .table td:first-child {
            padding-left: 0.5rem;
        }

        .table th:last-child,
        .table td:last-child {
            padding-right: 0.5rem;
        }

        /* Stack action buttons vertically on very small screens */
        .btn-group {
            flex-direction: column;
            width: 100%;
        }

        .btn-group .btn {
            margin-bottom: 2px;
            border-radius: 0.375rem;
            width: 100%;
        }

        .btn-sm {
            padding: 0.375rem 0.5rem;
            font-size: 0.75rem;
        }

        /* Mobile-friendly form controls */
        .form-control, .form-select {
            font-size: 16px; /* Prevents zoom on iOS */
            padding: 0.75rem;
        }

        .input-group-text {
            padding: 0.75rem;
        }

        /* Improved mobile search */
        .input-group {
            margin-bottom: 0.5rem;
        }

        /* Mobile filter actions */
        .d-flex.gap-2 {
            flex-direction: column;
            gap: 0.5rem !important;
        }

        .d-flex.gap-2 .btn {
            width: 100%;
            margin-bottom: 0.25rem;
        }

        /* Mobile quick filters - horizontal scroll */
        .d-flex.gap-2.flex-wrap {
            flex-wrap: nowrap;
            overflow-x: auto;
            padding-bottom: 0.5rem;
            gap: 0.5rem !important;
        }

        .quick-filter {
            flex-shrink: 0;
            font-size: 0.7rem;
            padding: 0.4rem 0.6rem;
        }

        /* Mobile pagination improvements */
        .pagination {
            justify-content: center;
            flex-wrap: wrap;
        }

        .pagination .page-item {
            margin: 0.125rem;
        }

        .pagination .page-link {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
            min-width: 2rem;
            text-align: center;
        }

        /* Mobile bulk actions */
        #bulkActionsBar {
            padding: 0.75rem;
            margin: 0.5rem 0;
        }

        #bulkActionsBar .btn-group {
            flex-direction: row;
            flex-wrap: wrap;
            gap: 0.25rem;
        }

        #bulkActionsBar .btn {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
            width: auto;
            margin-bottom: 0;
        }
    }

    /* Touch-friendly improvements */
    @media (hover: none) and (pointer: coarse) {
        .btn, .form-control, .form-select {
            min-height: 44px; /* Apple's recommended touch target size */
        }

        .table-hover tbody tr:hover {
            background-color: transparent; /* Disable hover on touch devices */
        }

        .btn:active {
            transform: scale(0.98);
            transition: transform 0.1s ease;
        }

        .quick-filter:active {
            transform: scale(0.95);
        }
    }

    /* High contrast mode support */
    @media (prefers-contrast: high) {
        .table {
            border: 2px solid;
        }
        
        .btn-outline-secondary,
        .btn-outline-primary,
        .btn-outline-info {
            border-width: 2px;
        }
        
        .badge {
            border: 1px solid;
        }
    }

    /* Reduced motion support */
    @media (prefers-reduced-motion: reduce) {
        .collapse,
        .btn,
        .table-hover tbody tr,
        .quick-filter {
            transition: none !important;
            animation: none !important;
        }
    }

    /* Print styles */
    @media print {
        .card-header .btn,
        .btn-group,
        #bulkActionsBar,
        .pagination-wrapper {
            display: none !important;
        }
        
        .table {
            font-size: 0.8rem;
        }
        
        .card {
            border: 1px solid #000;
            box-shadow: none;
        }
    }

    /* Animation for filter collapse */
    .collapse {
        transition: height 0.35s ease;
    }

    /* Custom scrollbar for table */
    .table-responsive::-webkit-scrollbar {
        height: 8px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    [data-theme="dark"] .table-responsive::-webkit-scrollbar-track {
        background: var(--bg-card);
    }

    [data-theme="dark"] .table-responsive::-webkit-scrollbar-thumb {
        background: var(--border-color);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Real-time search functionality
    let searchTimeout;
    const searchInput = document.getElementById('search');
    const clearSearchBtn = document.getElementById('clearSearch');
    const loadingSpinner = document.getElementById('loadingSpinner');
    const roleFilter = document.getElementById('role');
    const emailVerifiedFilter = document.getElementById('email_verified');
    const dateRangeFilter = document.getElementById('date_range');
    const sortByFilter = document.getElementById('sort_by');
    const perPageFilter = document.getElementById('per_page');
    const sortOrderFilter = document.getElementById('sort_order');
    const resetFiltersBtn = document.getElementById('resetFilters');
    const applyFiltersBtn = document.getElementById('applyFilters');
    const saveFiltersBtn = document.getElementById('saveFilters');
    const clearAllFiltersBtn = document.getElementById('clearAllFilters');
    const quickFilterBtns = document.querySelectorAll('.quick-filter');
    const selectAllCheckbox = document.getElementById('selectAll');
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    const bulkActionsBar = document.getElementById('bulkActionsBar');
    const selectedCountSpan = document.getElementById('selectedCount');
    const activeFiltersCount = document.getElementById('activeFiltersCount');
    const filterError = document.getElementById('filterError');
    const filterErrorMessage = document.getElementById('filterErrorMessage');

    // Show/hide clear search button
    function toggleClearButton() {
        if (searchInput.value.length > 0) {
            clearSearchBtn.style.display = 'block';
        } else {
            clearSearchBtn.style.display = 'none';
        }
    }

    // Real-time search with debouncing
    searchInput.addEventListener('input', function() {
        toggleClearButton();
        
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            if (searchInput.value.length >= 2 || searchInput.value.length === 0) {
                performSearch();
            }
        }, 500); // 500ms delay for better UX
    });

    // Clear search functionality
    clearSearchBtn.addEventListener('click', function() {
        searchInput.value = '';
        toggleClearButton();
        performSearch();
    });

    // Filter change handlers
    roleFilter.addEventListener('change', performSearch);
    emailVerifiedFilter.addEventListener('change', performSearch);
    dateRangeFilter.addEventListener('change', performSearch);
    sortByFilter.addEventListener('change', performSearch);
    perPageFilter.addEventListener('change', performSearch);
    sortOrderFilter.addEventListener('change', performSearch);

    // Apply filters button
    applyFiltersBtn.addEventListener('click', performSearch);

    // Quick filter buttons
    quickFilterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const filter = this.dataset.filter;
            applyQuickFilter(filter);
        });
    });

    // Save filters functionality
    saveFiltersBtn.addEventListener('click', function() {
        const filters = getCurrentFilters();
        localStorage.setItem('userFilters', JSON.stringify(filters));
        showNotification('Filters saved successfully!', 'success');
    });

    // Reset filters
    resetFiltersBtn.addEventListener('click', function() {
        searchInput.value = '';
        roleFilter.value = '';
        emailVerifiedFilter.value = '';
        dateRangeFilter.value = '';
        sortByFilter.value = 'created_at';
        perPageFilter.value = '15';
        sortOrderFilter.value = 'desc';
        toggleClearButton();
        updateActiveFiltersCount();
        clearQuickFilterStates();
        performSearch();
    });

    // Quick filter functions
    function applyQuickFilter(filterType) {
        clearQuickFilterStates();
        
        switch(filterType) {
            case 'verified':
                emailVerifiedFilter.value = 'verified';
                break;
            case 'pending':
                emailVerifiedFilter.value = 'pending';
                break;
            case 'admins':
                roleFilter.value = 'ADMIN';
                break;
            case 'recent':
                dateRangeFilter.value = 'week';
                break;
        }
        
        // Highlight active quick filter
        document.querySelector(`[data-filter="${filterType}"]`).classList.add('active');
        performSearch();
    }

    function clearQuickFilterStates() {
        quickFilterBtns.forEach(btn => btn.classList.remove('active'));
    }

    function getCurrentFilters() {
        return {
            search: searchInput.value,
            role: roleFilter.value,
            email_verified: emailVerifiedFilter.value,
            date_range: dateRangeFilter.value,
            sort_by: sortByFilter.value,
            per_page: perPageFilter.value,
            sort_order: sortOrderFilter.value
        };
    }

    // Update active filters count
    function updateActiveFiltersCount() {
        let count = 0;
        if (searchInput.value) count++;
        if (roleFilter.value) count++;
        if (emailVerifiedFilter.value) count++;
        if (dateRangeFilter.value) count++;
        if (sortByFilter.value && sortByFilter.value !== 'created_at') count++;
        if (perPageFilter.value && perPageFilter.value !== '15') count++;
        if (sortOrderFilter.value && sortOrderFilter.value !== 'desc') count++;
        
        activeFiltersCount.textContent = count;
        
        // Update filter status styling
        const filterStatus = document.getElementById('filterStatus');
        if (count > 0) {
            filterStatus.classList.add('text-primary');
            filterStatus.classList.remove('text-muted');
        } else {
            filterStatus.classList.add('text-muted');
            filterStatus.classList.remove('text-primary');
        }
    }

    // Show success message
    function showFilterSuccess(message) {
        const filterSuccess = document.getElementById('filterSuccess');
        const filterSuccessMessage = document.getElementById('filterSuccessMessage');
        filterSuccessMessage.textContent = message;
        filterSuccess.style.display = 'block';
        setTimeout(() => {
            filterSuccess.style.display = 'none';
        }, 3000);
    }

    // Enhanced search result counter
    function updateSearchResultsCount(total) {
        const searchResultsCount = document.getElementById('searchResultsCount');
        if (searchInput.value && total !== undefined) {
            searchResultsCount.textContent = `${total} results`;
            searchResultsCount.style.display = 'inline';
        } else {
            searchResultsCount.style.display = 'none';
        }
    }

    if (clearAllFiltersBtn) {
        clearAllFiltersBtn.addEventListener('click', function() {
            window.location.href = '{{ route("users.index") }}';
        });
    }

    // Perform search/filter
    function performSearch() {
        showLoading(true);
        updateActiveFiltersCount();
        
        // Build query parameters
        const params = new URLSearchParams();
        if (searchInput.value) params.append('search', searchInput.value);
        if (roleFilter.value) params.append('role', roleFilter.value);
        if (emailVerifiedFilter.value) params.append('email_verified', emailVerifiedFilter.value);
        if (dateRangeFilter.value) params.append('date_range', dateRangeFilter.value);
        if (sortByFilter.value) params.append('sort_by', sortByFilter.value);
        if (perPageFilter.value) params.append('per_page', perPageFilter.value);
        if (sortOrderFilter.value) params.append('sort_order', sortOrderFilter.value);
        params.append('ajax', '1');

        fetch('{{ route("users.index") }}?' + params.toString(), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.html) {
                document.getElementById('usersTableBody').innerHTML = data.html;
                document.getElementById('resultsCount').innerHTML = data.pagination_info;
                
                // Update URL without page reload
                const url = new URL(window.location);
                if (searchInput.value) url.searchParams.set('search', searchInput.value);
                else url.searchParams.delete('search');
                
                if (roleFilter.value) url.searchParams.set('role', roleFilter.value);
                else url.searchParams.delete('role');
                
                if (emailVerifiedFilter.value) url.searchParams.set('email_verified', emailVerifiedFilter.value);
                else url.searchParams.delete('email_verified');
                
                if (dateRangeFilter.value) url.searchParams.set('date_range', dateRangeFilter.value);
                else url.searchParams.delete('date_range');
                
                if (sortByFilter.value) url.searchParams.set('sort_by', sortByFilter.value);
                else url.searchParams.delete('sort_by');
                
                window.history.pushState({}, '', url);
                
                // Reinitialize checkboxes
                initializeCheckboxes();
            }
        })
        .catch(error => {
            console.error('Search error:', error);
            showFilterError('Failed to load results. Please try again.');
        })
        .finally(() => {
            showLoading(false);
        });
    }

    function showLoading(show) {
        if (show) {
            loadingSpinner.style.display = 'block';
            document.getElementById('usersTableContainer').classList.add('table-loading');
        } else {
            loadingSpinner.style.display = 'none';
            document.getElementById('usersTableContainer').classList.remove('table-loading');
        }
    }

    // Checkbox functionality
    function initializeCheckboxes() {
        const newUserCheckboxes = document.querySelectorAll('.user-checkbox');
        const newSelectAllCheckbox = document.getElementById('selectAll');
        
        // Select all functionality
        if (newSelectAllCheckbox) {
            newSelectAllCheckbox.addEventListener('change', function() {
                newUserCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateBulkActions();
            });
        }

        // Individual checkbox functionality
        newUserCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateSelectAllState();
                updateBulkActions();
            });
        });
    }

    function updateSelectAllState() {
        const checkboxes = document.querySelectorAll('.user-checkbox');
        const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
        const selectAll = document.getElementById('selectAll');
        
        if (selectAll) {
            if (checkedBoxes.length === 0) {
                selectAll.indeterminate = false;
                selectAll.checked = false;
            } else if (checkedBoxes.length === checkboxes.length) {
                selectAll.indeterminate = false;
                selectAll.checked = true;
            } else {
                selectAll.indeterminate = true;
                selectAll.checked = false;
            }
        }
    }

    function updateBulkActions() {
        const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
        
        if (checkedBoxes.length > 0) {
            bulkActionsBar.style.display = 'block';
            selectedCountSpan.textContent = `${checkedBoxes.length} user${checkedBoxes.length > 1 ? 's' : ''} selected`;
        } else {
            bulkActionsBar.style.display = 'none';
        }
    }

    // Error handling functions
    function showFilterError(message) {
        filterErrorMessage.textContent = message;
        filterError.style.display = 'block';
        setTimeout(() => {
            filterError.style.display = 'none';
        }, 5000);
    }

    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 3000);
    }

    // Load saved filters on page load
    function loadSavedFilters() {
        const savedFilters = localStorage.getItem('userFilters');
        if (savedFilters) {
            try {
                const filters = JSON.parse(savedFilters);
                if (filters.search) searchInput.value = filters.search;
                if (filters.role) roleFilter.value = filters.role;
                if (filters.email_verified) emailVerifiedFilter.value = filters.email_verified;
                if (filters.date_range) dateRangeFilter.value = filters.date_range;
                if (filters.sort_by) sortByFilter.value = filters.sort_by;
                if (filters.per_page) perPageFilter.value = filters.per_page;
                if (filters.sort_order) sortOrderFilter.value = filters.sort_order;
            } catch (e) {
                console.error('Error loading saved filters:', e);
            }
        }
    }

    // Initialize on page load
    toggleClearButton();
    initializeCheckboxes();
    updateActiveFiltersCount();
    
    // Load saved filters if available
    if (!window.location.search) {
        loadSavedFilters();
    }

    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Delete confirmation
function confirmDelete(userId, userName) {
    document.getElementById('deleteUserName').textContent = userName;
    document.getElementById('deleteForm').action = `/users/${userId}`;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}

// Bulk actions (placeholder functions)
function bulkVerifyEmails() {
    const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
    const userIds = Array.from(checkedBoxes).map(cb => cb.value);
    
    if (confirm(`Verify emails for ${userIds.length} selected users?`)) {
        // Implementation would go here
        console.log('Bulk verify emails for users:', userIds);
    }
}

function bulkChangeRole() {
    const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
    const userIds = Array.from(checkedBoxes).map(cb => cb.value);
    
    // Implementation would show a modal to select new role
    console.log('Bulk change role for users:', userIds);
}

function bulkDelete() {
    const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
    const userIds = Array.from(checkedBoxes).map(cb => cb.value);
    
    if (confirm(`Delete ${userIds.length} selected users? This action cannot be undone.`)) {
        // Implementation would go here
        console.log('Bulk delete users:', userIds);
    }
}

function exportUsers() {
    // Implementation for exporting users
    console.log('Export users functionality');
}
</script>
@endsection
