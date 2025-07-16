@extends('layouts.app')

@section('title', 'New Instruction')

@push('styles')
    {{-- Select2 for rich select boxes --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <style>
        .select2-container--bootstrap-5 .select2-selection {
            min-height: calc(1.5em + .75rem + 2px);
            padding: .375rem .75rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
        }
        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__rendered {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
        }
        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
            margin-top: 0.25rem !important;
        }
    </style>
@endpush

@section('content')
<main class="page-content">
    <div class="container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('instructions.index') }}">Instructions</a></li>
                <li class="breadcrumb-item active" aria-current="page">New Instruction</li>
            </ol>
        </nav>

        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Create New Instruction</h4>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('instructions.store') }}" method="POST">
                            @csrf
                            {{-- Title --}}
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}" required>
                            </div>

                            {{-- Body --}}
                            <div class="mb-3">
                                <label for="body" class="form-label">Instruction Body</label>
                                <textarea class="form-control" id="body" name="body" rows="8" required>{{ old('body') }}</textarea>
                            </div>

                            {{-- Target Deadline --}}
                            <div class="mb-3">
                                <label for="target_deadline" class="form-label">Target Deadline (Optional)</label>
                                <input type="datetime-local" class="form-control" id="target_deadline" name="target_deadline" value="{{ old('target_deadline') }}">
                            </div>

                            <hr class="my-4">

                            {{-- Recipient Selection --}}
                            <div class="mb-3">
                                <label class="form-label">Recipients</label>
                                <div id="recipient-type-selector">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="recipient_type" id="recipient_specific" value="specific" checked>
                                        <label class="form-check-label" for="recipient_specific">Specific Users</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="recipient_type" id="recipient_role" value="role">
                                        <label class="form-check-label" for="recipient_role">By Role</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="recipient_type" id="recipient_all" value="all">
                                        <label class="form-check-label" for="recipient_all">All Users</label>
                                    </div>
                                </div>
                            </div>

                            {{-- Specific Users Selector --}}
                            <div id="specific-recipients-container" class="mb-3">
                                <label for="recipients" class="form-label visually-hidden">Select Users</label>
                                <select class="form-control" id="recipients" name="recipients[]" multiple>
                                    @foreach($potentialRecipients as $recipient)
                                        <option value="{{ $recipient->id }}" {{ (is_array(old('recipients')) && in_array($recipient->id, old('recipients'))) ? 'selected' : '' }}>
                                            {{ $recipient->full_name }} ({{ $recipient->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Role Selector --}}
                            <div id="role-recipients-container" class="mb-3 d-none">
                                <label class="form-label">Select Roles</label>
                                <div class="d-flex flex-wrap gap-3">
                                    @foreach(App\Enums\UserRole::getSelectableRoles() as $role)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="selected_roles[]" value="{{ $role->value }}" id="role_{{ $role->value }}">
                                            <label class="form-check-label" for="role_{{ $role->value }}">
                                                {{ $role->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- All Users Info --}}
                            <div id="all-recipients-container" class="alert alert-info d-none">
                                This instruction will be sent to all users in the system (excluding System Administrators).
                            </div>


                            <div class="d-flex justify-content-end mt-4">
                                <a href="{{ route('instructions.index') }}" class="btn btn-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i> Send Instruction
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
    {{-- Select2 --}}
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script> {{-- Select2 needs jQuery --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Select2
            $('#recipients').select2({
                theme: 'bootstrap-5',
                placeholder: 'Select one or more users',
                allowClear: true,
            });

            // Recipient selection logic
            const specificContainer = document.getElementById('specific-recipients-container');
            const roleContainer = document.getElementById('role-recipients-container');
            const allContainer = document.getElementById('all-recipients-container');

            document.querySelectorAll('input[name="recipient_type"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    specificContainer.classList.toggle('d-none', this.value !== 'specific');
                    roleContainer.classList.toggle('d-none', this.value !== 'role');
                    allContainer.classList.toggle('d-none', this.value !== 'all');
                });
            });

            // Trigger change on page load to set initial state based on old() data
            const checkedRadio = document.querySelector('input[name="recipient_type"]:checked');
            if (checkedRadio) {
                checkedRadio.dispatchEvent(new Event('change'));
            }
        });
    </script>
@endpush
