@extends('layouts.app')

@section('title', 'Forward Instruction')

@push('styles')
    {{-- Select2 for rich select boxes --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <style>
        .select2-container--bootstrap-5 .select2-selection {
            min-height: calc(1.5em + .75rem + 2px);
            padding: .375rem .75rem;
        }
        .instruction-summary-card {
            position: sticky;
            top: 80px; /* Adjust based on navbar height */
            max-height: calc(100vh - 100px);
            overflow-y: auto;
        }
    </style>
@endpush

@section('content')
<main class="page-content">
    <div class="container-fluid">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('instructions.index') }}">Instructions</a></li>
                <li class="breadcrumb-item"><a href="{{ route('instructions.show', $instruction) }}">View</a></li>
                <li class="breadcrumb-item active" aria-current="page">Forward</li>
            </ol>
        </nav>

        <div class="row">
            {{-- Right Column: Forward Form --}}
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">Forward Instruction</h4>
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
                        <form action="{{ route('instructions.forward', $instruction) }}" method="POST">
                            @csrf

                            {{-- Recipients --}}
                            <div class="mb-3">
                                <label for="recipients" class="form-label">Forward To</label>
                                <select class="form-control" id="recipients" name="recipients[]" multiple required>
                                    @foreach($potentialRecipients as $recipient)
                                        <option value="{{ $recipient->id }}" {{ (is_array(old('recipients')) && in_array($recipient->id, old('recipients'))) ? 'selected' : '' }}>
                                            {{ $recipient->full_name }} ({{ $recipient->email }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Select one or more users to forward this instruction to.</div>
                            </div>

                            {{-- Forward Message --}}
                            <div class="mb-3">
                                <label for="forward_message" class="form-label">Message (Optional)</label>
                                <textarea class="form-control" id="forward_message" name="forward_message" rows="5" placeholder="Add a message for the new recipients...">{{ old('forward_message') }}</textarea>
                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <a href="{{ route('instructions.show', $instruction) }}" class="btn btn-secondary me-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-share me-1"></i> Forward Instruction
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Left Column: Instruction Summary --}}
            <div class="col-lg-5 d-none d-lg-block">
                <div class="card instruction-summary-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Original Instruction</h5>
                    </div>
                    <div class="card-body">
                        <h4 class="mb-3">{{ $instruction->title }}</h4>

                        <div class="d-flex align-items-center mb-3">
                            <img src="{{ $instruction->sender->avatar_url }}" alt="{{ $instruction->sender->full_name }}" class="rounded-circle" width="40" height="40">
                            <div class="ms-2">
                                <span class="fw-bold">{{ $instruction->sender->full_name }}</span>
                                <div class="text-muted small">Sent: {{ $instruction->created_at->format('M d, Y') }}</div>
                            </div>
                        </div>

                        <p class="text-muted">{{ \Illuminate\Support\Str::limit($instruction->body, 300) }}</p>

                        @if($instruction->target_deadline)
                        <div class="alert alert-danger-soft">
                            <strong>Deadline:</strong> {{ \Carbon\Carbon::parse($instruction->target_deadline)->format('M d, Y') }}
                        </div>
                        @endif

                        <a href="{{ route('instructions.show', $instruction) }}" class="btn btn-outline-primary btn-sm mt-2" target="_blank">
                            View Full Instruction
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
    {{-- Select2 --}}
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('#recipients').select2({
                theme: 'bootstrap-5',
                placeholder: 'Select one or more users',
                allowClear: true,
            });
        });
    </script>
@endpush
