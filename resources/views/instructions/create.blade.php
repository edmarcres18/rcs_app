@extends('layouts.app')

@section('title', 'Create Instruction')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0 text-gray-800">Create New Instruction</h1>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="{{ route('instructions.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Instructions
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="{{ route('instructions.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror"
                           id="title" name="title" value="{{ old('title') }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="body" class="form-label">Content <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('body') is-invalid @enderror"
                              id="body" name="body" rows="6" required>{{ old('body') }}</textarea>
                    @error('body')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="target_deadline" class="form-label">Target Deadline</label>
                    <div class="input-group">
                        <input type="datetime-local" class="form-control @error('target_deadline') is-invalid @enderror"
                               id="target_deadline" name="target_deadline" value="{{ old('target_deadline') }}">
                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                    </div>
                    <div class="form-text">Set a deadline for when this instruction should be completed.</div>
                    @error('target_deadline')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label d-block mb-2">Recipients <span class="text-danger">*</span></label>

                    @error('recipients')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror

                    <div class="mb-3">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="recipient_type" id="specific_users" value="specific" {{ old('recipient_type', 'specific') == 'specific' ? 'checked' : '' }}>
                            <label class="form-check-label" for="specific_users">Select specific users</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="recipient_type" id="by_role" value="role" {{ old('recipient_type') == 'role' ? 'checked' : '' }}>
                            <label class="form-check-label" for="by_role">Select by role</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="recipient_type" id="all_users" value="all" {{ old('recipient_type') == 'all' ? 'checked' : '' }}>
                            <label class="form-check-label" for="all_users">All users</label>
                        </div>
                    </div>

                    <!-- Specific users selection with Select2 -->
                    <div id="specific_users_container" class="{{ old('recipient_type', 'specific') != 'specific' ? 'd-none' : '' }}">
                        <select class="form-control select2-users" name="recipients[]" multiple="multiple" data-placeholder="Select recipients">
                            @foreach($potentialRecipients as $recipient)
                                <option value="{{ $recipient->id }}" {{ in_array($recipient->id, old('recipients', [])) ? 'selected' : '' }}>
                                    {{ $recipient->full_name }} ({{ $recipient->email }}) - {{ $recipient->roles->value }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">You can search users by name or email</small>
                    </div>

                    <!-- Role-based selection with Select2 -->
                    <div id="by_role_container" class="{{ old('recipient_type') != 'role' ? 'd-none' : '' }}">
                        <select class="form-control select2-roles" name="selected_roles[]" multiple="multiple" data-placeholder="Select roles">
                        @foreach(\App\Enums\UserRole::cases() as $role)
                            @if($role->value !== 'SYSTEM_ADMIN')
                                    <option value="{{ $role->value }}" {{ in_array($role->value, old('selected_roles', [])) ? 'selected' : '' }}>
                                        All {{ $role->value }}s
                                        <small>({{ $potentialRecipients->where('roles', $role)->count() }} users)</small>
                                    </option>
                            @endif
                        @endforeach
                        </select>
                    </div>

                    <!-- All users info -->
                    <div id="all_users_container" class="alert alert-info {{ old('recipient_type') != 'all' ? 'd-none' : '' }}">
                        <i class="fas fa-info-circle me-2"></i>
                        This instruction will be sent to all {{ $potentialRecipients->count() }} users in the system.
                        <input type="hidden" name="send_to_all" value="0" id="send_to_all_input">
                    </div>

                    <!-- Hidden field to track selected type -->
                    <input type="hidden" name="recipient_selection_mode" id="recipient_selection_mode" value="{{ old('recipient_selection_mode', 'specific') }}">
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="reset" class="btn btn-light me-md-2">Reset</button>
                    <button type="submit" class="btn btn-primary">Send Instruction</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    /* Custom styling for Select2 */
    .select2-container--bootstrap-5 .select2-selection {
        min-height: 38px;
        border-color: var(--bs-border-color);
    }
    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__rendered {
        display: flex;
        flex-wrap: wrap;
    }
    .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        margin-right: 5px;
        margin-top: 5px;
        padding: 3px 8px;
        border-radius: 4px;
        display: flex;
        align-items: center;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const specificUsersRadio = document.getElementById('specific_users');
        const byRoleRadio = document.getElementById('by_role');
        const allUsersRadio = document.getElementById('all_users');
        const specificUsersContainer = document.getElementById('specific_users_container');
        const byRoleContainer = document.getElementById('by_role_container');
        const allUsersContainer = document.getElementById('all_users_container');
        const recipientSelectionMode = document.getElementById('recipient_selection_mode');
        const sendToAllInput = document.getElementById('send_to_all_input');

        // Initialize Select2
        $('.select2-users').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Select recipients',
            allowClear: true
        });

        $('.select2-roles').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Select roles',
            allowClear: true
        });

        // Toggle between recipient selection methods
        specificUsersRadio.addEventListener('change', function() {
            if (this.checked) {
                showContainer('specific');
                recipientSelectionMode.value = 'specific';
                sendToAllInput.value = '0';
            }
        });

        byRoleRadio.addEventListener('change', function() {
            if (this.checked) {
                showContainer('role');
                recipientSelectionMode.value = 'role';
                sendToAllInput.value = '0';
            }
        });

        allUsersRadio.addEventListener('change', function() {
            if (this.checked) {
                showContainer('all');
                recipientSelectionMode.value = 'all';
                sendToAllInput.value = '1';
            }
        });

        // Function to show the right container based on selection
        function showContainer(type) {
            // Hide all containers
            specificUsersContainer.classList.add('d-none');
            byRoleContainer.classList.add('d-none');
            allUsersContainer.classList.add('d-none');

            // Show the selected container
            if (type === 'specific') {
                specificUsersContainer.classList.remove('d-none');
            } else if (type === 'role') {
                byRoleContainer.classList.remove('d-none');
            } else if (type === 'all') {
                allUsersContainer.classList.remove('d-none');
                    }
        }

        // Handle form reset
        document.querySelector('button[type="reset"]').addEventListener('click', function() {
            setTimeout(() => {
                $('.select2-users').val(null).trigger('change');
                $('.select2-roles').val(null).trigger('change');
                specificUsersRadio.checked = true;
                showContainer('specific');
                recipientSelectionMode.value = 'specific';
                sendToAllInput.value = '0';
            }, 100);
        });

        // Initialize based on current selection
        if (specificUsersRadio.checked) {
            showContainer('specific');
        } else if (byRoleRadio.checked) {
            showContainer('role');
        } else if (allUsersRadio.checked) {
            showContainer('all');
        }
    });
</script>
@endpush
