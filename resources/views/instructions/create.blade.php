@extends('layouts.app')

@section('title', 'New Instruction')

@push('styles')
    {{-- Select2 for rich select boxes --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-color-rgb: 79, 70, 229;
            --text-color: #111827;
            --text-muted: #4b5563;
            --border-color: #e5e7eb;
            --bg-card: #ffffff;
            --bg-light: #f9fafb;
        }

        .page-content {
            background-color: var(--bg-light);
        }

        .card {
            border: 1px solid var(--border-color);
            border-radius: 0.75rem; /* 12px */
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            background-color: var(--bg-card);
            overflow: visible; /* Allow select2 dropdown to overflow */
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid var(--border-color);
            padding: 1.5rem;
        }

        .card-title {
            font-size: 1.5rem; /* 24px */
            font-weight: 700;
            color: var(--text-color);
            margin: 0;
        }

        .card-body {
            padding: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
            font-size: 0.875rem; /* 14px */
        }

        .form-control, .form-select, .select2-selection {
            border: 1px solid #d1d5db;
            border-radius: 0.5rem; /* 8px */
            padding: 0.75rem 1rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            background-color: #fff;
        }

        .form-control:focus, .form-select:focus, .select2-container--bootstrap-5.select2-container--focus .select2-selection {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(var(--primary-color-rgb), 0.2);
            outline: none;
        }

        .form-control::placeholder {
            color: #9ca3af;
        }

        /* Recipient selection as segmented control */
        #recipient-type-selector {
            display: flex;
            background-color: #e5e7eb;
            border-radius: 0.5rem;
            padding: 0.25rem;
            width: 100%;
        }

        #recipient-type-selector .form-check {
            flex: 1;
            position: relative;
            padding-left: 0;
            margin: 0;
        }

        #recipient-type-selector .form-check-input {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        #recipient-type-selector .form-check-label {
            display: block;
            text-align: center;
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            font-weight: 500;
            color: #4b5563;
            transition: background-color 0.2s ease, color 0.2s ease;
            cursor: pointer;
            width: 100%;
            margin-bottom: 0;
        }

        #recipient-type-selector .form-check-input:checked + .form-check-label {
            background-color: #fff;
            color: var(--primary-color);
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        /* Role checkboxes */
        #role-recipients-container .form-check {
            background-color: var(--bg-light);
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid var(--border-color);
            transition: border-color 0.2s, background-color 0.2s;
        }
        #role-recipients-container .form-check:hover {
            border-color: var(--primary-color);
            background-color: #eef2ff;
        }
        #role-recipients-container .form-check-input {
            margin-top: 0.2em;
        }

        /* Buttons */
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: all 0.2s ease;
            border: 1px solid transparent;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(var(--primary-color-rgb), 0.2);
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-secondary {
            background-color: transparent;
            border-color: #d1d5db;
            color: var(--text-muted);
        }
        .btn-secondary:hover {
            background-color: #e5e7eb;
            border-color: #e5e7eb;
            color: var(--text-color);
        }

        /* Select2 customizations */
        .select2-container--bootstrap-5 .select2-selection {
            min-height: calc(1.5em + 1.5rem + 2px);
            padding: .6rem .75rem;
        }
        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__choice {
            background-color: #e0e7ff;
            border: 1px solid #c7d2fe;
            color: var(--primary-color);
            padding: 0.35rem 0.75rem;
            border-radius: 0.375rem;
            margin-top: 0.25rem !important;
        }
        .select2-container--bootstrap-5 .select2-dropdown {
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        }
        .select2-container--bootstrap-5 .select2-selection--multiple .select2-selection__rendered {
            align-items: center;
        }

        @media (max-width: 767px) {
            .card-header, .card-body {
                padding: 1rem;
            }
            .card-title {
                font-size: 1.25rem;
            }
            #recipient-type-selector {
                flex-direction: column;
                gap: 0.25rem;
                max-width: none;
                background-color: transparent;
                padding: 0;
            }
             #recipient-type-selector .form-check-label {
                background-color: #e5e7eb;
            }
            #recipient-type-selector .form-check-input:checked + .form-check-label {
                background-color: var(--primary-color);
                color: #fff;
                box-shadow: none;
            }
            .btn {
                width: 100%;
                margin-bottom: 10px;
            }
            .d-flex.justify-content-end {
                flex-direction: column;
            }
            .btn-secondary.me-3 {
                margin-right: 0 !important;
            }
        }
    </style>
@endpush

@section('content')
<main class="page-content">
    <div class="container py-4">

        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Create New Instruction</h4>
                    </div>
                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger mb-4">
                                <h5 class="font-medium text-red-600">There were some errors with your submission:</h5>
                                <ul class="mt-2 list-disc list-inside text-sm text-red-600">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('instructions.store') }}" method="POST">
                            @csrf

                            {{-- Title --}}
                            <div class="mb-4">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" placeholder="e.g., Weekly Project Update Reminder" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Body --}}
                            <div class="mb-4">
                                <label for="body" class="form-label">Instruction Body</label>
                                <textarea class="form-control @error('body') is-invalid @enderror" id="body" name="body" rows="8" placeholder="Enter the main content of the instruction here..." required>{{ old('body') }}</textarea>
                                @error('body')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Target Deadline --}}
                            <div class="mb-4">
                                <label for="target_deadline" class="form-label">Target Deadline (Optional)</label>
                                <input type="datetime-local" class="form-control @error('target_deadline') is-invalid @enderror" id="target_deadline" name="target_deadline" value="{{ old('target_deadline') }}">
                                @error('target_deadline')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <hr class="my-5 border-gray-200">

                            {{-- Recipient Selection --}}
                            <div class="mb-4">
                                <label class="form-label">Recipients</label>
                                <div id="recipient-type-selector" class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="recipient_type" id="recipient_specific" value="specific" checked>
                                        <label class="form-check-label" for="recipient_specific">Specific Users</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="recipient_type" id="recipient_role" value="role">
                                        <label class="form-check-label" for="recipient_role">By Role</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="recipient_type" id="recipient_all" value="all">
                                        <label class="form-check-label" for="recipient_all">All Users</label>
                                    </div>
                                </div>
                            </div>

                            {{-- Specific Users Selector --}}
                            <div id="specific-recipients-container" class="mb-4">
                                <label for="recipients" class="form-label">Select Users</label>
                                <select class="form-control @error('recipients') is-invalid @enderror" id="recipients" name="recipients[]" multiple>
                                    @foreach($potentialRecipients as $recipient)
                                        <option value="{{ $recipient->id }}" {{ (is_array(old('recipients')) && in_array($recipient->id, old('recipients'))) ? 'selected' : '' }}>
                                            {{ $recipient->full_name }} ({{ $recipient->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('recipients')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Role Selector --}}
                            <div id="role-recipients-container" class="mb-4 d-none">
                                <label class="form-label">Select Roles</label>
                                <div class="d-flex flex-wrap gap-3">
                                    @foreach(App\Enums\UserRole::getSelectableRoles() as $role)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="selected_roles[]" value="{{ $role->value }}" id="role_{{ $role->value }}" {{ (is_array(old('selected_roles')) && in_array($role->value, old('selected_roles'))) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="role_{{ $role->value }}">
                                                {{ $role->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                @error('selected_roles')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- All Users Info --}}
                            <div id="all-recipients-container" class="alert alert-info d-none">
                                <i class="fas fa-info-circle me-2"></i> This instruction will be sent to all users in the system (excluding System Administrators).
                            </div>

                            <div class="d-flex justify-content-end mt-5 pt-3 border-top">
                                <a href="{{ route('instructions.index') }}" class="btn btn-secondary me-3">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i> Send Instruction
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Select2
            $('#recipients').select2({
                theme: 'bootstrap-5',
                placeholder: 'Select one or more users',
                allowClear: true,
                width: '100%',
            });

            // Recipient selection logic
            const specificContainer = document.getElementById('specific-recipients-container');
            const roleContainer = document.getElementById('role-recipients-container');
            const allContainer = document.getElementById('all-recipients-container');

            function handleRecipientTypeChange(value) {
                specificContainer.classList.toggle('d-none', value !== 'specific');
                roleContainer.classList.toggle('d-none', value !== 'role');
                allContainer.classList.toggle('d-none', value !== 'all');
            }

            document.querySelectorAll('input[name="recipient_type"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    handleRecipientTypeChange(this.value);
                });
            });

            // Trigger change on page load to set initial state based on old() data
            const checkedRadio = document.querySelector('input[name="recipient_type"]:checked');
            if (checkedRadio) {
                handleRecipientTypeChange(checkedRadio.value);
            }
        });
    </script>
@endpush
