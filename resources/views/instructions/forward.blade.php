@extends('layouts.app')

@section('title', 'Forward Instruction')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0 text-gray-800">Forward Instruction</h1>
            <p class="text-muted">{{ $instruction->title }}</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="{{ route('instructions.show', $instruction) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Instruction
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Original Instruction</h6>
                </div>
                <div class="card-body">
                    <h5 class="mb-3">{{ $instruction->title }}</h5>
                    <p class="text-muted mb-3">
                        <small>
                            From: {{ $instruction->sender->full_name }} •
                            Sent: {{ $instruction->created_at->format('M d, Y g:i A') }}
                        </small>
                    </p>
                    <div class="bg-light p-3 mb-4 rounded">
                        {!! nl2br(e($instruction->body)) !!}
                    </div>

                    <form action="{{ route('instructions.forward', $instruction) }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="forward_message" class="form-label">Additional Message (Optional)</label>
                            <textarea class="form-control @error('forward_message') is-invalid @enderror"
                                    id="forward_message" name="forward_message" rows="3">{{ old('forward_message') }}</textarea>
                            @error('forward_message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Include any additional context or information for the recipients.</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Select Recipients <span class="text-danger">*</span></label>

                            @error('recipients')
                                <div class="alert alert-danger">{{ $message }}</div>
                            @enderror

                            @if($potentialRecipients->count() > 0)
                                <div class="row">
                                    @foreach($potentialRecipients->chunk(ceil($potentialRecipients->count() / 3)) as $chunk)
                                        <div class="col-md-4">
                                            @foreach($chunk as $recipient)
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="recipients[]" value="{{ $recipient->id }}"
                                                        id="user-{{ $recipient->id }}"
                                                        {{ in_array($recipient->id, old('recipients', [])) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="user-{{ $recipient->id }}">
                                                        {{ $recipient->full_name }}
                                                        <small class="text-muted d-block">{{ $recipient->roles->value }}</small>
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-info">
                                    There are no eligible recipients to forward this instruction to. All users have already received it.
                                </div>
                            @endif
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('instructions.show', $instruction) }}" class="btn btn-light me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary" {{ $potentialRecipients->count() == 0 ? 'disabled' : '' }}>
                                <i class="fas fa-share me-1"></i> Forward Instruction
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Current Recipients</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($instruction->recipients as $recipient)
                            <li class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($recipient->full_name) }}&size=24&background=4070f4&color=fff"
                                         class="rounded-circle me-2" alt="{{ $recipient->full_name }}" width="24" height="24">
                                    <div>
                                        {{ $recipient->full_name }}
                                        <small class="text-muted d-block">{{ $recipient->email }}</small>
                                    </div>
                                    @if(isset($recipient->pivot) && $recipient->pivot->is_read)
                                        <span class="badge bg-success ms-auto">Read</span>
                                    @else
                                        <span class="badge bg-danger ms-auto">Unread</span>
                                    @endif
                                </div>
                                @if(isset($recipient->pivot) && $recipient->pivot->forwarded_by_id)
                                    <div class="mt-1">
                                        <small class="text-info">
                                            @php
                                                $forwarder = \App\Models\User::find($recipient->pivot->forwarded_by_id);
                                            @endphp
                                            <i class="fas fa-share me-1"></i> Forwarded by {{ $forwarder ? $forwarder->full_name : 'Unknown' }}
                                        </small>
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Forwarding Guidelines</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li class="mb-2">Only forward instructions when necessary.</li>
                        <li class="mb-2">Provide context when forwarding to explain why the recipient needs to see this.</li>
                        <li class="mb-2">Respect confidentiality and only forward to relevant parties.</li>
                        <li class="mb-2">You cannot forward to users who have already received the instruction.</li>
                        <li>All forwarding actions are logged and traceable.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
