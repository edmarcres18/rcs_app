@extends('layouts.app')

@section('title', 'System Notifications')

@push('styles')
<style>
    .notification-card {
        transition: all 0.3s ease;
    }
    .notification-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    .status-badge {
        font-size: 0.8rem;
        padding: 0.3em 0.75em;
        border-radius: 50px;
        font-weight: 500;
    }
    .status-active {
        background-color: rgba(40, 167, 69, 0.1);
        color: #28a745;
        border: 1px solid rgba(40, 167, 69, 0.2);
    }
    .status-inactive {
        background-color: rgba(108, 117, 125, 0.1);
        color: #6c757d;
        border: 1px solid rgba(108, 117, 125, 0.2);
    }
    .status-archived {
        background-color: rgba(255, 193, 7, 0.1);
        color: #ffc107;
        border: 1px solid rgba(255, 193, 7, 0.2);
    }
    .type-badge {
        font-size: 0.8rem;
        padding: 0.3em 0.75em;
        border-radius: 50px;
        font-weight: 500;
    }
    .type-update {
        background-color: rgba(0, 123, 255, 0.1);
        color: #007bff;
        border: 1px solid rgba(0, 123, 255, 0.2);
    }
    .type-maintenance {
        background-color: rgba(255, 193, 7, 0.1);
        color: #ffc107;
        border: 1px solid rgba(255, 193, 7, 0.2);
    }
    .type-alert {
        background-color: rgba(220, 53, 69, 0.1);
        color: #dc3545;
        border: 1px solid rgba(220, 53, 69, 0.2);
    }
    .type-info {
        background-color: rgba(23, 162, 184, 0.1);
        color: #17a2b8;
        border: 1px solid rgba(23, 162, 184, 0.2);
    }
    .filter-section {
        background: var(--bg-input);
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border: 1px solid var(--border-color);
    }
    .search-input {
        border-radius: 25px;
        padding-left: 2.5rem;
    }
    .search-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
    }
    .filter-badge {
        display: inline-block;
        margin: 0.2rem;
        padding: 0.4rem 0.8rem;
        background: var(--primary-color);
        color: white;
        border-radius: 15px;
        font-size: 0.8rem;
    }
    .clear-filters {
        color: var(--text-muted);
        text-decoration: none;
        font-size: 0.9rem;
    }
    .clear-filters:hover {
        color: var(--primary-color);
        text-decoration: underline;
    }
    .table th {
        border-top: none;
        font-weight: 600;
        color: var(--text-color);
        background: var(--bg-input);
    }
    .table-hover tbody tr:hover {
        background-color: var(--bg-hover);
    }
    .action-buttons {
        white-space: nowrap;
    }
    .btn-group-sm > .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        border-radius: 0.2rem;
    }
    @media (max-width: 768px) {
        .filter-section {
            padding: 1rem;
        }
        .table-responsive {
            font-size: 0.9rem;
        }
        .action-buttons .btn {
            padding: 0.2rem 0.4rem;
            font-size: 0.8rem;
        }
        .filter-row {
            gap: 0.5rem !important;
        }
        .filter-row .col-md-3,
        .filter-row .col-md-2 {
            margin-bottom: 0.5rem;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid page-content">
    <div class="row">
        <div class="col-12">
            <!-- Success Message -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card notification-card">
                <div class="card-header">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                        <div class="d-flex align-items-center mb-2 mb-md-0">
                            <i class="fas fa-bell me-2 text-primary"></i>
                            <h5 class="card-title mb-0">System Notifications</h5>
                            <span class="badge bg-secondary ms-2">{{ $notifications->total() }} total</span>
                        </div>
                        <a href="{{ route('admin.system-notifications.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Create New
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Advanced Filters -->
                    <div class="filter-section">
                        <form method="GET" action="{{ route('admin.system-notifications.index') }}" id="filterForm">
                            <div class="row filter-row g-3 align-items-end">
                                <!-- Search -->
                                <div class="col-md-4">
                                    <label for="search" class="form-label fw-semibold">Search</label>
                                    <div class="position-relative">
                                        <i class="fas fa-search search-icon"></i>
                                        <input type="text" class="form-control search-input" id="search" name="search" 
                                               value="{{ request('search') }}" placeholder="Search title or message...">
                                    </div>
                                </div>
                                
                                <!-- Type Filter -->
                                <div class="col-md-2">
                                    <label for="type" class="form-label fw-semibold">Type</label>
                                    <select class="form-select" id="type" name="type">
                                        <option value="">All Types</option>
                                        <option value="info" {{ request('type') == 'info' ? 'selected' : '' }}>Info</option>
                                        <option value="update" {{ request('type') == 'update' ? 'selected' : '' }}>Update</option>
                                        <option value="maintenance" {{ request('type') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                        <option value="alert" {{ request('type') == 'alert' ? 'selected' : '' }}>Alert</option>
                                    </select>
                                </div>
                                
                                <!-- Status Filter -->
                                <div class="col-md-2">
                                    <label for="status" class="form-label fw-semibold">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="">All Status</option>
                                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                        <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archived</option>
                                    </select>
                                </div>
                                
                                <!-- Date Range -->
                                <div class="col-md-2">
                                    <label for="date_from" class="form-label fw-semibold">From Date</label>
                                    <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="col-md-2">
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-outline-primary">
                                            <i class="fas fa-filter me-1"></i> Filter
                                        </button>
                                        <a href="{{ route('admin.system-notifications.index') }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-1"></i> Clear
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Active Filters Display -->
                            @if(request()->hasAny(['search', 'type', 'status', 'date_from']))
                                <div class="mt-3 pt-3 border-top">
                                    <div class="d-flex align-items-center flex-wrap">
                                        <span class="text-muted me-2">Active filters:</span>
                                        @if(request('search'))
                                            <span class="filter-badge">Search: "{{ request('search') }}"</span>
                                        @endif
                                        @if(request('type'))
                                            <span class="filter-badge">Type: {{ ucfirst(request('type')) }}</span>
                                        @endif
                                        @if(request('status'))
                                            <span class="filter-badge">Status: {{ ucfirst(request('status')) }}</span>
                                        @endif
                                        @if(request('date_from'))
                                            <span class="filter-badge">From: {{ request('date_from') }}</span>
                                        @endif
                                        <a href="{{ route('admin.system-notifications.index') }}" class="clear-filters ms-2">
                                            <i class="fas fa-times-circle me-1"></i>Clear all filters
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </form>
                    </div>

                    <!-- Results Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th width="25%">Title</th>
                                    <th width="10%">Type</th>
                                    <th width="10%">Status</th>
                                    <th width="15%">Visible From</th>
                                    <th width="15%">Visible Until</th>
                                    <th width="15%">Created By</th>
                                    <th width="10%">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($notifications as $notification)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $notification->title }}</div>
                                            <small class="text-muted">{{ Str::limit($notification->message, 50) }}</small>
                                        </td>
                                        <td>
                                            <span class="badge type-badge type-{{ $notification->type }}">
                                                {{ ucfirst($notification->type) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge status-badge status-{{ $notification->status }}">
                                                {{ ucfirst($notification->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <small>{{ $notification->date_start ? $notification->date_start->format('M d, Y H:i') : 'N/A' }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $notification->date_end ? $notification->date_end->format('M d, Y H:i') : 'N/A' }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $notification->createdBy ? $notification->createdBy->first_name . ' ' . $notification->createdBy->last_name : 'Unknown User' }}</small>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('admin.system-notifications.edit', $notification) }}" 
                                                       class="btn btn-outline-primary" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger delete-btn" 
                                                            data-id="{{ $notification->id }}"
                                                            data-title="{{ $notification->title }}"
                                                            title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                                <p class="mb-0">No system notifications found.</p>
                                                @if(request()->hasAny(['search', 'type', 'status', 'date_from']))
                                                    <small>Try adjusting your filters or <a href="{{ route('admin.system-notifications.index') }}">clear all filters</a>.</small>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($notifications->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Showing {{ $notifications->firstItem() }} to {{ $notifications->lastItem() }} of {{ $notifications->total() }} results
                            </div>
                            <div>
                                {{ $notifications->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    Confirm Deletion
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3">Are you sure you want to delete this notification?</p>
                <div class="bg-light p-3 rounded">
                    <strong id="notificationTitle"></strong>
                </div>
                <p class="text-muted mt-2 mb-0">
                    <small><i class="fas fa-info-circle me-1"></i>This action cannot be undone.</small>
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger" id="confirmDeleteBtn">
                        <span class="spinner-border spinner-border-sm me-2 d-none" id="deleteSpinner"></span>
                        <i class="fas fa-trash me-1" id="deleteIcon"></i>
                        <span id="deleteText">Delete Notification</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Live filtering variables
    let filterTimeout;
    const filterForm = document.getElementById('filterForm');
    const searchInput = document.getElementById('search');
    const typeSelect = document.getElementById('type');
    const statusSelect = document.getElementById('status');
    const dateFromInput = document.getElementById('date_from');
    const tableContainer = document.querySelector('.table-responsive');
    const paginationContainer = document.querySelector('.d-flex.justify-content-between.align-items-center.mt-3');
    const totalBadge = document.querySelector('.badge.bg-secondary');
    
    // Loading overlay
    function showLoading() {
        if (!document.querySelector('.loading-overlay')) {
            const overlay = document.createElement('div');
            overlay.className = 'loading-overlay';
            overlay.style.cssText = `
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(255, 255, 255, 0.8);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 1000;
                border-radius: 8px;
            `;
            overlay.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="mt-2 text-muted">Filtering...</div>
                </div>
            `;
            tableContainer.style.position = 'relative';
            tableContainer.appendChild(overlay);
        }
    }
    
    function hideLoading() {
        const overlay = document.querySelector('.loading-overlay');
        if (overlay) {
            overlay.remove();
        }
    }
    
    // Live filter function
    function performLiveFilter() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(() => {
            showLoading();
            
            const formData = new FormData(filterForm);
            const params = new URLSearchParams(formData);
            
            // Add AJAX header
            fetch(`{{ route('admin.system-notifications.index') }}?${params.toString()}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                // Update table content
                document.querySelector('tbody').innerHTML = data.table_html;
                
                // Update pagination
                if (paginationContainer) {
                    if (data.pagination_html) {
                        paginationContainer.innerHTML = data.pagination_html;
                    } else {
                        paginationContainer.style.display = 'none';
                    }
                }
                
                // Update total count
                if (totalBadge) {
                    totalBadge.textContent = `${data.total} total`;
                }
                
                // Update active filters display
                updateActiveFilters();
                
                // Reattach delete button event listeners
                attachDeleteListeners();
                
                // Update URL without page reload
                const newUrl = `{{ route('admin.system-notifications.index') }}?${params.toString()}`;
                window.history.pushState({}, '', newUrl);
                
                hideLoading();
            })
            .catch(error => {
                console.error('Filter error:', error);
                hideLoading();
                // Show error message
                showErrorMessage('Failed to filter notifications. Please try again.');
            });
        }, 300); // Reduced delay for more responsive feel
    }
    
    // Update active filters display
    function updateActiveFilters() {
        const activeFiltersContainer = document.querySelector('.mt-3.pt-3.border-top');
        const hasFilters = searchInput.value || typeSelect.value || statusSelect.value || dateFromInput.value;
        
        if (hasFilters && activeFiltersContainer) {
            activeFiltersContainer.style.display = 'block';
            
            // Update filter badges
            const filterBadges = activeFiltersContainer.querySelector('.d-flex.align-items-center.flex-wrap');
            if (filterBadges) {
                let badgesHtml = '<span class="text-muted me-2">Active filters:</span>';
                
                if (searchInput.value) {
                    badgesHtml += `<span class="filter-badge">Search: "${searchInput.value}"</span>`;
                }
                if (typeSelect.value) {
                    badgesHtml += `<span class="filter-badge">Type: ${typeSelect.options[typeSelect.selectedIndex].text}</span>`;
                }
                if (statusSelect.value) {
                    badgesHtml += `<span class="filter-badge">Status: ${statusSelect.options[statusSelect.selectedIndex].text}</span>`;
                }
                if (dateFromInput.value) {
                    badgesHtml += `<span class="filter-badge">From: ${dateFromInput.value}</span>`;
                }
                
                badgesHtml += `<a href="{{ route('admin.system-notifications.index') }}" class="clear-filters ms-2">
                    <i class="fas fa-times-circle me-1"></i>Clear all filters
                </a>`;
                
                filterBadges.innerHTML = badgesHtml;
            }
        } else if (activeFiltersContainer) {
            activeFiltersContainer.style.display = 'none';
        }
    }
    
    // Show error message
    function showErrorMessage(message) {
        const alertContainer = document.querySelector('.col-12');
        const existingAlert = alertContainer.querySelector('.alert-danger');
        
        if (existingAlert) {
            existingAlert.remove();
        }
        
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show';
        alert.innerHTML = `
            <i class="fas fa-exclamation-circle me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        alertContainer.insertBefore(alert, alertContainer.firstChild);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }
    
    // Attach delete button event listeners
    function attachDeleteListeners() {
        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function() {
                const notificationId = this.dataset.id;
                const notificationTitle = this.dataset.title;
                
                // Update modal content
                document.getElementById('notificationTitle').textContent = notificationTitle;
                deleteForm.action = `{{ route('admin.system-notifications.index') }}/${notificationId}`;
                
                // Show modal
                deleteModal.show();
            });
        });
    }
    
    // Delete modal functionality
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const deleteForm = document.getElementById('deleteForm');
    const deleteSpinner = document.getElementById('deleteSpinner');
    const deleteIcon = document.getElementById('deleteIcon');
    const deleteText = document.getElementById('deleteText');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    
    // Initial attachment of delete listeners
    attachDeleteListeners();
    
    // Handle delete form submission
    deleteForm.addEventListener('submit', function() {
        // Show loading state
        confirmDeleteBtn.disabled = true;
        deleteSpinner.classList.remove('d-none');
        deleteIcon.classList.add('d-none');
        deleteText.textContent = 'Deleting...';
    });
    
    // Live search (real-time)
    searchInput.addEventListener('input', performLiveFilter);
    
    // Live filter changes
    typeSelect.addEventListener('change', performLiveFilter);
    statusSelect.addEventListener('change', performLiveFilter);
    dateFromInput.addEventListener('change', performLiveFilter);
    
    // Prevent form submission
    filterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        performLiveFilter();
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
            performLiveFilter();
        }
    });
    
    // Add search hint
    searchInput.setAttribute('title', 'Press Ctrl+K to focus, Escape to clear');
    
    // Handle pagination clicks
    document.addEventListener('click', function(e) {
        if (e.target.closest('.pagination a')) {
            e.preventDefault();
            const link = e.target.closest('.pagination a');
            const url = link.href;
            
            showLoading();
            
            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                document.querySelector('tbody').innerHTML = data.table_html;
                
                if (paginationContainer) {
                    if (data.pagination_html) {
                        paginationContainer.innerHTML = data.pagination_html;
                    } else {
                        paginationContainer.style.display = 'none';
                    }
                }
                
                if (totalBadge) {
                    totalBadge.textContent = `${data.total} total`;
                }
                
                attachDeleteListeners();
                window.history.pushState({}, '', url);
                hideLoading();
            })
            .catch(error => {
                console.error('Pagination error:', error);
                hideLoading();
                showErrorMessage('Failed to load page. Please try again.');
            });
        }
    });
    
    // Handle browser back/forward buttons
    window.addEventListener('popstate', function() {
        location.reload();
    });
});
</script>
@endpush
