@extends('layouts.app')

@section('title', 'Forward Instruction')

@push('styles')
    {{-- Select2 for rich select boxes --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-color-rgb: 79, 70, 229;
            --text-color: #111827;
            --text-muted: #6c757d;
            --border-color: #e5e7eb;
            --bg-card: #ffffff;
            --bg-light: #f9fafb;
        }

        .page-content {
            background-color: var(--bg-light);
        }

        .card {
            border: 1px solid var(--border-color);
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            background-color: var(--bg-card);
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid var(--border-color);
            padding: 1.25rem 1.5rem;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-color);
            margin: 0;
        }

        .form-label {
            font-weight: 600;
            color: #4b5563;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .form-control, .select2-selection {
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-control:focus, .select2-container--bootstrap-5.select2-container--focus .select2-selection {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(var(--primary-color-rgb), 0.2);
            outline: none;
        }

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
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(var(--primary-color-rgb), 0.2);
        }

        .btn-secondary {
            background-color: transparent;
            border-color: #d1d5db;
            color: var(--text-muted);
        }
        .btn-secondary:hover {
            background-color: #e5e7eb;
            color: var(--text-color);
        }

        .select2-container--bootstrap-5 .select2-selection {
            min-height: calc(1.5em + 1.5rem + 2px);
        }

        .instruction-summary-card {
            position: sticky;
            top: 20px;
        }

        .summary-item {
            display: flex;
            align-items: flex-start;
        }

        .summary-icon {
            flex-shrink: 0;
            width: 3rem;
            height: 3rem;
            background-color: #eef2ff;
            color: var(--primary-color);
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }
    </style>
@endpush

@section('content')
<main class="page-content">
    <div class="container py-4">
        <div class="row g-4">
            {{-- Main Column: Forward Form --}}
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Forward Instruction</h4>
                    </div>
                    <div class="card-body p-4">
                         @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form action="{{ route('instructions.forward', $instruction) }}" method="POST">
                            @csrf

                            {{-- Recipients --}}
                            <div class="mb-4">
                                <label for="recipients" class="form-label">Forward To</label>
                                <select class="form-control" id="recipients" name="recipients[]" multiple required>
                                    @foreach($potentialRecipients as $recipient)
                                        <option value="{{ $recipient->id }}" {{ (is_array(old('recipients')) && in_array($recipient->id, old('recipients'))) ? 'selected' : '' }}>
                                            {{ $recipient->full_name }} ({{ $recipient->email }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text small mt-2">Select one or more users to forward this instruction to.</div>
                            </div>

                            {{-- Forward Message --}}
                            <div class="mb-4">
                                <label for="forward_message" class="form-label">Message (Optional)</label>
                                <textarea class="form-control" id="forward_message" name="forward_message" rows="6" placeholder="Add a message for the new recipients...">{{ old('forward_message') }}</textarea>
                            </div>

                            <div class="d-flex justify-content-end mt-4 pt-4 border-top">
                                <a href="{{ route('instructions.show', $instruction) }}" class="btn btn-secondary me-3">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-share me-2"></i> Forward
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Sidebar Column: Instruction Summary --}}
            <div class="col-lg-5">
                <div class="instruction-summary-card">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Original Instruction</h5>
                        </div>
                        <div class="card-body p-4">
                            <h4 class="h5 mb-3 fw-bold">{{ $instruction->title }}</h4>

                            <div class="summary-item mb-4">
                                <div class="summary-icon me-3">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">From</h6>
                                    <p class="mb-0 text-muted">{{ $instruction->sender->full_name }}</p>
                                    <small class="text-muted">{{ $instruction->created_at->format('M d, Y, h:i A') }}</small>
                                </div>
                            </div>

                            @if($instruction->target_deadline)
                            <div class="summary-item mb-4">
                                <div class="summary-icon me-3 bg-danger-soft text-danger">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Deadline</h6>
                                    <p class="mb-0 text-danger fw-medium">{{ \Carbon\Carbon::parse($instruction->target_deadline)->format('M d, Y, h:i A') }}</p>
                                </div>
                            </div>
                            @endif

                            <div class="summary-item">
                                <div class="summary-icon me-3">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Content</h6>
                                    <p class="text-muted mb-0">{{ \Illuminate\Support\Str::limit($instruction->body, 200) }}</p>
                                </div>
                            </div>
                            <hr class="my-4">
                            <a href="{{ route('instructions.show', $instruction) }}" class="btn btn-outline-primary w-100" target="_blank">
                                View Full Instruction
                            </a>
                        </div>
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
            $('#recipients').select2({
                theme: 'bootstrap-5',
                placeholder: 'Select one or more users',
                allowClear: true,
                width: '100%',
            });
        });
    </script>
@endpush
