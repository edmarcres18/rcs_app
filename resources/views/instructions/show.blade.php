@extends('layouts.app')

@section('title', $instruction->title)

@push('styles')
    @vite('resources/css/instruction-paper.css')
@endpush

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0 text-gray-800">{{ $instruction->title }}</h1>
            <p class="text-muted">
                <small>
                    From: {{ $instruction->sender->full_name }} •
                    Sent: {{ $instruction->created_at->format('M d, Y g:i A') }}
                </small>
            </p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="{{ route('instructions.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Instructions
            </a>
            @if(Auth::id() != $instruction->sender_id && Auth::user()->roles !== \App\Enums\UserRole::SYSTEM_ADMIN)
                <a href="{{ route('instructions.show-forward', $instruction) }}" class="btn btn-info ms-2">
                    <i class="fas fa-share me-1"></i> Forward
                </a>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Instruction Content -->
        <div class="col-lg-8">
            <!-- Instruction View Button -->
            <div class="card shadow-sm mb-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1">Instruction Memo</h5>
                        <p class="card-text text-muted mb-0">Click to view the full instruction document.</p>
                    </div>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#instructionModal">
                        <i class="fas fa-expand-arrows-alt me-1"></i> View Fullscreen
                    </button>
                </div>
            </div>

            <!-- Instruction Modal -->
            <div class="modal fade" id="instructionModal" tabindex="-1" aria-labelledby="instructionModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-fullscreen">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="instructionModalLabel">{{ $instruction->title }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body bg-light py-5" style="overflow-y: auto;">
                            <div class="instruction-paper-container mx-auto">
                                @php
                                    $originalRecipients = $instruction->recipients->where('pivot.forwarded_by_id', null);
                                    $forwardedRecipients = $instruction->recipients->where('pivot.forwarded_by_id', '!=', null);

                                    $toRecipients = $originalRecipients->filter(function ($user) {
                                        return in_array($user->roles, [\App\Enums\UserRole::ADMIN, \App\Enums\UserRole::SUPERVISOR]);
                                    });

                                    $ccRecipients = $originalRecipients->filter(function ($user) {
                                        return $user->roles === \App\Enums\UserRole::EMPLOYEE;
                                    });

                                    // If no specific toRecipients, all are TO
                                    if ($toRecipients->isEmpty() && $ccRecipients->isNotEmpty()) {
                                        $toRecipients = $originalRecipients;
                                        $ccRecipients = collect();
                                    }

                                    $isCcAllEmployees = $ccRecipients->count() > 0 && $ccRecipients->count() === \App\Models\User::where('roles', \App\Enums\UserRole::EMPLOYEE)->count();
                                @endphp
                                <div class="instruction-paper">
                                    <div class="instruction-paper-header">
                                        <div></div>
                                        <div class="header-logo">
                                            <img src="{{ asset('images/instructions_logo/inslogo.png') }}" alt="Logo" class="logo">
                                        </div>
                                    </div>

                                    <div class="instruction-paper-details">
                                        <div class="detail-item">
                                            <span class="detail-label">T O</span>
                                            <span class="detail-colon">:</span>
                                            <span class="detail-value">
                                                {{ format_recipients($toRecipients) }}
                                            </span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">R E</span>
                                            <span class="detail-colon">:</span>
                                            <span class="detail-value">{{ $instruction->title }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">DATE</span>
                                            <span class="detail-colon">:</span>
                                            <span class="detail-value">{{ $instruction->created_at->format('d F Y') }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">REF. NO.</span>
                                            <span class="detail-colon">:</span>
                                            <span class="detail-value">INST-{{ str_pad($instruction->id, 4, '0', STR_PAD_LEFT) }}</span>
                                        </div>
                                        @if($isCcAllEmployees)
                                        <div class="detail-item">
                                            <span class="detail-label">CC</span>
                                            <span class="detail-colon">:</span>
                                            <span class="detail-value">ALL EMPLOYEES</span>
                                        </div>
                                        @elseif($ccRecipients->isNotEmpty())
                                        <div class="detail-item">
                                            <span class="detail-label">CC</span>
                                            <span class="detail-colon">:</span>
                                            <span class="detail-value">
                                                {{ format_recipients($ccRecipients) }}
                                            </span>
                                        </div>
                                        @endif
                                        @if($instruction->target_deadline)
                                            <div class="detail-item">
                                                <span class="detail-label">DEADLINE</span>
                                                <span class="detail-colon">:</span>
                                                <span class="detail-value text-danger fw-bold">{{ \Carbon\Carbon::parse($instruction->target_deadline)->format('F d, Y') }}</span>
                                            </div>
                                        @endif
                                        @if($forwardedRecipients->isNotEmpty())
                                            <div class="detail-item">
                                                <span class="detail-label">FORWARDED</span>
                                                <span class="detail-colon">:</span>
                                                <span class="detail-value">
                                                    @foreach($forwardedRecipients as $recipient)
                                                        {{ $recipient->full_name }}
                                                        @if(!$loop->last), @endif
                                                    @endforeach
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="instruction-paper-body">
                                        {!! parse_instruction_body($instruction->body) !!}
                                    </div>

                                    <div class="instruction-paper-footer">
                                        <div class="sender-signature">
                                            <div class="sender-name">{{ $instruction->sender->full_name }}</div>
                                            <div class="sender-role">{{ optional($instruction->sender->roles)->value }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" onclick="window.print()">
                                <i class="fas fa-print me-1"></i> Print
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Replies -->
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-comments me-1"></i> Replies
                    </h6>
                    <span class="badge bg-primary">{{ $replies->count() }}</span>
                </div>

                <div class="card-body">
                    <div class="replies-list" id="replies-list">
                        @forelse($replies as $reply)
                            <div class="reply-item mb-4" id="reply-{{ $reply->id }}">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <img class="rounded-circle" src="https://ui-avatars.com/api/?name={{ urlencode($reply->user->full_name) }}&background=4070f4&color=fff" width="40" height="40" alt="{{ $reply->user->full_name }}">
                                    </div>
                                    <div class="ms-3 flex-grow-1">
                                        <div class="d-flex align-items-center mb-1">
                                            <h6 class="fw-bold mb-0">{{ $reply->user->full_name }}</h6>
                                            <small class="ms-auto text-muted">{{ $reply->created_at->format('M d, Y g:i A') }}</small>
                                        </div>
                                        <div class="reply-content">
                                            {!! nl2br(e($reply->content)) !!}
                                        </div>
                                        @if($reply->attachment)
                                        <div class="reply-attachment mt-2">
                                            <a href="{{ $reply->attachment_url }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                                <i class="fas fa-paperclip me-1"></i> Attachment
                                            </a>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-comments mb-3" style="font-size: 2rem;"></i>
                                <p class="mb-0">No replies yet. Be the first to reply!</p>
                            </div>
                        @endforelse
                    </div>

                    @if(Auth::user()->roles !== \App\Enums\UserRole::SYSTEM_ADMIN)
                        <form id="reply-form" action="{{ route('instructions.reply', $instruction) }}" method="POST" enctype="multipart/form-data" class="mt-4">
                            @csrf
                            <div class="mb-3">
                                <label for="content" class="form-label">Your Reply</label>
                                <textarea class="form-control @error('content') is-invalid @enderror"
                                        id="content" name="content" rows="3" required>{{ old('content') }}</textarea>
                                @error('content')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="attachment" class="form-label">Attachment (optional)</label>
                                <input type="file" class="form-control @error('attachment') is-invalid @enderror"
                                       id="attachment" name="attachment">
                                <div class="form-text">Max file size: 10MB</div>
                                @error('attachment')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" id="submit-reply" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i> Send Reply
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Instruction Details -->
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle me-1"></i> Instruction Details
                    </h6>
                </div>
                <div class="card-body">
                    <div class="details-item mb-3">
                        <small class="text-muted d-block">From</small>
                        <div class="fw-bold">{{ $instruction->sender->full_name }}</div>
                    </div>
                    <div class="details-item mb-3">
                        <small class="text-muted d-block">Date Sent</small>
                        <div>{{ $instruction->created_at->format('M d, Y g:i A') }}</div>
                    </div>
                    <div class="details-item mb-3">
                        <small class="text-muted d-block">Recipients</small>
                        <div>
                            @foreach($instruction->recipients as $recipient)
                                <div class="recipient-item">
                                    <span @if(isset($recipient->pivot) && $recipient->pivot->is_read) class="text-success" @endif>
                                        {{ $recipient->full_name }}
                                    </span>
                                    @if(isset($recipient->pivot) && $recipient->pivot->is_read)
                                        <i class="fas fa-check-circle text-success ms-1"
                                           title="Read on {{ \Carbon\Carbon::parse($recipient->pivot->updated_at)->format('M d, Y g:i A') }}"></i>
                                    @else
                                        <i class="fas fa-circle text-muted ms-1" title="Not read yet"></i>
                                    @endif
                                    @if(isset($recipient->pivot) && $recipient->pivot->forwarded_by_id)
                                        <span class="badge bg-info ms-1">Forwarded</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if(Auth::user()->id === $instruction->sender_id)
                        <div class="d-grid">
                            <a href="{{ route('instructions.show-forward', $instruction) }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-share me-1"></i> Forward This Instruction
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Activity Timeline -->
            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history me-1"></i> Activity Timeline
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline" id="activity-timeline">
                        @foreach($activities as $activity)
                            <div class="timeline-item mb-3">
                                <div class="timeline-icon bg-{{ $activity->action === 'sent' ? 'primary' : ($activity->action === 'read' ? 'success' : ($activity->action === 'forwarded' ? 'info' : 'secondary')) }} text-white">
                                    <i class="fas fa-{{ $activity->action === 'sent' ? 'paper-plane' : ($activity->action === 'read' ? 'eye' : ($activity->action === 'forwarded' ? 'share' : 'comment')) }}"></i>
                                </div>
                                <div class="timeline-content ms-3">
                                    <div class="text-muted small mb-1">{{ $activity->created_at->format('M d, Y g:i A') }}</div>
                                    <div>
                                        <strong>{{ $activity->user->full_name }}</strong>
                                        @if($activity->action === 'sent')
                                            sent this instruction
                                        @elseif($activity->action === 'read')
                                            read this instruction
                                        @elseif($activity->action === 'replied')
                                            replied to this instruction
                                        @elseif($activity->action === 'forwarded')
                                            forwarded this instruction
                                            @if($activity->targetUser)
                                                to <strong>{{ $activity->targetUser->full_name }}</strong>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function() {
        // Submit form with AJAX
        $('#reply-form').on('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const submitBtn = $('#submit-reply');

            // Disable button and show loading
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Sending...');

            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        // Add the new reply to the list
                        const attachmentHtml = response.reply.attachment
                            ? `<div class="reply-attachment mt-2">
                                <a href="${response.reply.attachment}" class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="fas fa-paperclip me-1"></i> Attachment
                                </a>
                               </div>`
                            : '';

                        const newReplyHtml = `
                            <div class="reply-item mb-4" id="reply-${response.reply.id}">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <img class="rounded-circle" src="https://ui-avatars.com/api/?name=${encodeURIComponent(response.reply.user.name)}&background=4070f4&color=fff" width="40" height="40" alt="${response.reply.user.name}">
                                    </div>
                                    <div class="ms-3 flex-grow-1">
                                        <div class="d-flex align-items-center mb-1">
                                            <h6 class="fw-bold mb-0">${response.reply.user.name}</h6>
                                            <small class="ms-auto text-muted">${response.reply.created_at}</small>
                                        </div>
                                        <div class="reply-content">
                                            ${response.reply.content.replace(/\n/g, '<br>')}
                                        </div>
                                        ${attachmentHtml}
                                    </div>
                                </div>
                            </div>
                        `;

                        // If there are no replies yet, clear the "no replies" message
                        if ($('#replies-list').find('.text-center.text-muted').length) {
                            $('#replies-list').empty();
                        }

                        // Add the new reply
                        $('#replies-list').append(newReplyHtml);

                        // Add to activity timeline
                        const activityHtml = `
                            <div class="timeline-item mb-3">
                                <div class="timeline-icon bg-secondary text-white">
                                    <i class="fas fa-comment"></i>
                                </div>
                                <div class="timeline-content ms-3">
                                    <div class="text-muted small mb-1">Just now</div>
                                    <div>
                                        <strong>${response.reply.user.name}</strong>
                                        replied to this instruction
                                    </div>
                                </div>
                            </div>
                        `;
                        $('#activity-timeline').append(activityHtml);

                        // Update the reply count
                        const replyCountBadge = $('.card-header .badge');
                        const currentCount = parseInt(replyCountBadge.text());
                        replyCountBadge.text(currentCount + 1);

                        // Reset the form
                        $('#reply-form')[0].reset();

                        // Show success message
                        const successAlert = `
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>Reply added successfully.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        `;
                        $('#reply-form').before(successAlert);

                        // Scroll to the new reply
                        $([document.documentElement, document.body]).animate({
                            scrollTop: $(`#reply-${response.reply.id}`).offset().top - 100
                        }, 500);
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'Failed to add reply';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    const errorAlert = `
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>${errorMessage}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                    $('#reply-form').before(errorAlert);
                },
                complete: function() {
                    // Re-enable the button
                    submitBtn.prop('disabled', false).html('<i class="fas fa-paper-plane me-1"></i> Send Reply');
                }
            });
        });

                // Set up real-time updates with Pusher and Laravel Echo
        window.instructionId = {{ $instruction->id }};

        // Listen for new replies on this instruction
        if (window.Echo) {
            window.Echo.private(`instruction.${instructionId}`)
                .listen('.instruction.new-reply', (e) => {
                    const reply = e.reply;

                    // Only add if it doesn't already exist
                    if ($(`#reply-${reply.id}`).length === 0) {
                        const attachmentHtml = reply.attachment
                            ? `<div class="reply-attachment mt-2">
                                <a href="${reply.attachment}" class="btn btn-sm btn-outline-primary" target="_blank">
                                    <i class="fas fa-paperclip me-1"></i> Attachment
                                </a>
                               </div>`
                            : '';

                        const newReplyHtml = `
                            <div class="reply-item mb-4" id="reply-${reply.id}">
                                <div class="d-flex">
                                    <div class="flex-shrink-0">
                                        <img class="rounded-circle" src="https://ui-avatars.com/api/?name=${encodeURIComponent(reply.user.name)}&background=4070f4&color=fff" width="40" height="40" alt="${reply.user.name}">
                                    </div>
                                    <div class="ms-3 flex-grow-1">
                                        <div class="d-flex align-items-center mb-1">
                                            <h6 class="fw-bold mb-0">${reply.user.name}</h6>
                                            <small class="ms-auto text-muted">${reply.created_at}</small>
                                        </div>
                                        <div class="reply-content">
                                            ${reply.content.replace(/\n/g, '<br>')}
                                        </div>
                                        ${attachmentHtml}
                                    </div>
                                </div>
                            </div>
                        `;

                        // If there are no replies yet, clear the "no replies" message
                        if ($('#replies-list').find('.text-center.text-muted').length) {
                            $('#replies-list').empty();
                        }

                        // Add the new reply
                        $('#replies-list').append(newReplyHtml);

                        // Add to activity timeline
                        const activityHtml = `
                            <div class="timeline-item mb-3">
                                <div class="timeline-icon bg-secondary text-white">
                                    <i class="fas fa-comment"></i>
                                </div>
                                <div class="timeline-content ms-3">
                                    <div class="text-muted small mb-1">Just now</div>
                                    <div>
                                        <strong>${reply.user.name}</strong>
                                        replied to this instruction
                                    </div>
                                </div>
                            </div>
                        `;
                        $('#activity-timeline').append(activityHtml);

                        // Update the reply count
                        const replyCountBadge = $('.card-header .badge');
                        const currentCount = parseInt(replyCountBadge.text());
                        replyCountBadge.text(currentCount + 1);

                        // Show notification
                        showNotification(`${reply.user.name} replied to this instruction`, 'info', true);
                    }
                });
        }

        // Function to show a notification
        function showNotification(message, type = 'info', playSound = true) {
            const notificationHtml = `
                <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-${type === 'info' ? 'info' : type === 'success' ? 'check' : 'exclamation'}-circle me-2"></i>
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `;

            // Create toast container if it doesn't exist
            if ($('.toast-container').length === 0) {
                $('body').append('<div class="toast-container position-fixed bottom-0 end-0 p-3"></div>');
            }

            // Add toast to container
            const toastElement = $(notificationHtml);
            $('.toast-container').append(toastElement);

            // Initialize and show toast
            const toast = new bootstrap.Toast(toastElement[0], { delay: 5000 });
            toast.show();
        };
    });
</script>
@endsection
