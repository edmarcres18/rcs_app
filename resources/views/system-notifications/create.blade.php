@extends('layouts.app')

@section('title', 'Create System Notification')

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
                    <div class="d-flex align-items-center">
                        <i class="fas fa-plus-circle me-2 text-primary"></i>
                        <h5 class="card-title mb-0">Create New System Notification</h5>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.system-notifications.store') }}" method="POST" id="notificationForm" novalidate>
                        @include('system-notifications._form', ['submitButtonText' => 'Create Notification'])
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
        submitText.textContent = 'Creating...';
        
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
        submitText.textContent = 'Create Notification';
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
});
</script>
@endpush
