@extends('layouts.app')

@section('title', 'Edit System Notification')

@push('styles')
<style>
    .form-label.fw-semibold {
        font-weight: 600 !important;
        color: var(--text-color);
    }
    .text-danger {
        color: #dc3545 !important;
    }
    .form-text {
        font-size: 0.875rem;
        color: var(--text-muted);
    }
    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid rgba(0, 0, 0, 0.125);
    }
    .card-header {
        background-color: var(--bg-card);
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }
    .notification-preview {
        background: var(--bg-input);
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
    }
    @media (max-width: 576px) {
        .container-fluid.page-content {
            padding: 15px 10px;
        }
        .card {
            margin: 0;
        }
        .card-body {
            padding: 1rem;
        }
        .btn {
            width: 100%;
            margin-bottom: 0.5rem;
        }
        .btn:last-child {
            margin-bottom: 0;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid page-content">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-edit me-2 text-primary"></i>
                            <h5 class="card-title mb-0">Edit System Notification</h5>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-{{ $notification->status == 'active' ? 'success' : ($notification->status == 'inactive' ? 'secondary' : 'warning') }} me-2">
                                {{ ucfirst($notification->status) }}
                            </span>
                            <small class="text-muted">ID: {{ $notification->id }}</small>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Current Notification Preview -->
                    <div class="notification-preview">
                        <h6 class="fw-semibold mb-2">
                            <i class="fas fa-eye me-1"></i> Current Notification Preview
                        </h6>
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 me-3">
                                <span class="badge bg-{{ $notification->type == 'info' ? 'info' : ($notification->type == 'update' ? 'primary' : ($notification->type == 'maintenance' ? 'warning' : 'danger')) }}">
                                    {{ ucfirst($notification->type) }}
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $notification->title }}</h6>
                                <p class="mb-1 text-muted">{{ Str::limit($notification->message, 100) }}</p>
                                <small class="text-muted">
                                    Created: {{ $notification->created_at->format('M d, Y H:i') }}
                                    @if($notification->date_start)
                                        | Starts: {{ $notification->date_start->format('M d, Y H:i') }}
                                    @endif
                                    @if($notification->date_end)
                                        | Ends: {{ $notification->date_end->format('M d, Y H:i') }}
                                    @endif
                                </small>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('admin.system-notifications.update', $notification) }}" method="POST" id="notificationForm" novalidate>
                        @method('PUT')
                        @include('system-notifications._form', ['notification' => $notification, 'submitButtonText' => 'Update Notification'])
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('notificationForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitSpinner = document.getElementById('submitSpinner');
    const submitIcon = document.getElementById('submitIcon');
    const submitText = document.getElementById('submitText');
    
    // Form submission handler
    form.addEventListener('submit', function(e) {
        // Prevent double submission
        if (submitBtn.disabled) {
            e.preventDefault();
            return false;
        }
        
        // Show loading state
        submitBtn.disabled = true;
        submitSpinner.classList.remove('d-none');
        submitIcon.classList.add('d-none');
        submitText.textContent = 'Updating...';
        
        // Add visual feedback
        submitBtn.classList.add('btn-loading');
        
        // Re-enable after 10 seconds as fallback (in case of network issues)
        setTimeout(function() {
            if (submitBtn.disabled) {
                resetSubmitButton();
            }
        }, 10000);
    });
    
    // Reset submit button function
    function resetSubmitButton() {
        submitBtn.disabled = false;
        submitSpinner.classList.add('d-none');
        submitIcon.classList.remove('d-none');
        submitText.textContent = 'Update Notification';
        submitBtn.classList.remove('btn-loading');
    }
    
    // Date validation
    const dateStart = document.getElementById('date_start');
    const dateEnd = document.getElementById('date_end');
    
    dateStart.addEventListener('change', function() {
        if (dateEnd.value && dateStart.value > dateEnd.value) {
            dateEnd.setCustomValidity('End date must be after start date');
        } else {
            dateEnd.setCustomValidity('');
        }
    });
    
    dateEnd.addEventListener('change', function() {
        if (dateStart.value && dateEnd.value < dateStart.value) {
            dateEnd.setCustomValidity('End date must be after start date');
        } else {
            dateEnd.setCustomValidity('');
        }
    });
    
    // Character counter for title
    const titleInput = document.getElementById('title');
    const titleCounter = document.createElement('div');
    titleCounter.className = 'form-text text-end';
    titleCounter.innerHTML = '<span id="titleCount">0</span>/255 characters';
    titleInput.parentNode.appendChild(titleCounter);
    
    titleInput.addEventListener('input', function() {
        const count = this.value.length;
        document.getElementById('titleCount').textContent = count;
        
        if (count > 200) {
            titleCounter.classList.add('text-warning');
        } else {
            titleCounter.classList.remove('text-warning');
        }
        
        if (count >= 255) {
            titleCounter.classList.add('text-danger');
            titleCounter.classList.remove('text-warning');
        } else {
            titleCounter.classList.remove('text-danger');
        }
    });
    
    // Trigger initial count
    titleInput.dispatchEvent(new Event('input'));
    
    // Live preview update (optional enhancement)
    const titleField = document.getElementById('title');
    const messageField = document.getElementById('message');
    const typeField = document.getElementById('type');
    
    function updatePreview() {
        // This could update a live preview if needed
        // For now, we'll keep it simple
    }
    
    [titleField, messageField, typeField].forEach(field => {
        if (field) {
            field.addEventListener('input', updatePreview);
        }
    });
});
</script>
@endpush
