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
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">All Users</h5>
            <div class="d-flex mt-2 mt-md-0 gap-2 flex-wrap align-items-center" id="users-filter-bar">
                <div class="input-group" style="min-width: 280px;">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" id="searchInput" name="search" class="form-control" placeholder="Search by name or email..." value="{{ request('search') }}" style="background-color: var(--bg-input); color: var(--text-color);">
                </div>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user-shield"></i></span>
                    <select id="roleFilter" name="role" class="form-select">
                        <option value="">All Roles</option>
                        <option value="EMPLOYEE" {{ request('role')==='EMPLOYEE' ? 'selected' : '' }}>Employee</option>
                        <option value="SUPERVISOR" {{ request('role')==='SUPERVISOR' ? 'selected' : '' }}>Supervisor</option>
                        <option value="ADMIN" {{ request('role')==='ADMIN' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                    <select id="emailStatusFilter" name="email_status" class="form-select">
                        <option value="">All Email Status</option>
                        <option value="verified" {{ request('email_status')==='verified' ? 'selected' : '' }}>Email Verified</option>
                        <option value="pending" {{ request('email_status')==='pending' ? 'selected' : '' }}>Email Pending</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div id="users-table-wrapper">
                @include('users.partials.table', ['users' => $users])
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
<script>
    (function() {
        const wrapper = document.getElementById('users-table-wrapper');
        const searchInput = document.getElementById('searchInput');
        const roleFilter = document.getElementById('roleFilter');
        const emailStatusFilter = document.getElementById('emailStatusFilter');

        let controller = null;
        function debounce(fn, delay) {
            let t; return function(...args) { clearTimeout(t); t = setTimeout(() => fn.apply(this, args), delay); };
        }

        function buildQuery(params) {
            const url = new URL(window.location.href);
            // Always enforce 15 per page server-side; but we keep params for state
            Object.entries(params).forEach(([k, v]) => {
                if (v !== null && v !== undefined && v !== '') { url.searchParams.set(k, v); }
                else { url.searchParams.delete(k); }
            });
            return url;
        }

        async function fetchUsers(url) {
            try {
                if (controller) controller.abort();
                controller = new AbortController();
                const res = await fetch(url.toString(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                    signal: controller.signal
                });
                const html = await res.text();
                wrapper.innerHTML = html;
                // update URL without full reload
                window.history.replaceState({}, '', url);
            } catch (e) {
                if (e.name !== 'AbortError') { console.error(e); }
            }
        }

        const triggerSearch = debounce(() => {
            const url = buildQuery({
                search: searchInput.value.trim(),
                role: roleFilter.value,
                email_status: emailStatusFilter.value
            });
            fetchUsers(url);
        }, 300);

        searchInput && searchInput.addEventListener('input', triggerSearch);
        roleFilter && roleFilter.addEventListener('change', triggerSearch);
        emailStatusFilter && emailStatusFilter.addEventListener('change', triggerSearch);

        // Handle pagination clicks via event delegation
        document.addEventListener('click', function(e) {
            const a = e.target.closest('.pagination a');
            if (a) {
                e.preventDefault();
                const url = new URL(a.getAttribute('href'), window.location.origin);
                // preserve active filters
                if (searchInput) url.searchParams.set('search', searchInput.value.trim());
                if (roleFilter && roleFilter.value) url.searchParams.set('role', roleFilter.value);
                if (emailStatusFilter && emailStatusFilter.value) url.searchParams.set('email_status', emailStatusFilter.value);
                fetchUsers(url);
            }
        });
    })();
</script>
@endsection
