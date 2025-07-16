@props([
    'instruction',
    'type' => 'received' // 'received' or 'sent'
])

@php
    $is_unread = $type === 'received' && isset($instruction->pivot) && !$instruction->pivot->is_read;
    $is_forwarded = $type === 'received' && isset($instruction->pivot) && $instruction->pivot->forwarded_by_id;
@endphp

<a href="{{ route('instructions.show', $instruction) }}" class="text-decoration-none text-dark instruction-card-link">
    <div class="card instruction-card mb-3 {{ $is_unread ? 'unread' : '' }}">
        <div class="card-body">
            <div class="d-flex align-items-start">
                @if ($is_unread)
                    <span class="unread-dot me-3" title="Unread"></span>
                @endif
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="fw-bold">
                            @if ($type === 'received')
                                <i class="fas fa-user-friends me-1 text-muted"></i>
                                From: {{ $instruction->sender->full_name }}
                            @else
                                <i class="fas fa-user-friends me-1 text-muted"></i>
                                To:
                                @forelse($instruction->recipients as $recipient)
                                    {{ $recipient->full_name }}{{ !$loop->last ? ',' : '' }}
                                @empty
                                    No recipients.
                                @endforelse
            @endif
        </div>
                        <small class="text-muted">{{ $instruction->created_at->format('M d, Y, h:i A') }}</small>
    </div>

                    <h5 class="card-title mb-1">{{ $instruction->title }}</h5>

                    <p class="card-text text-muted mb-2">
                        {{ \Illuminate\Support\Str::limit(strip_tags($instruction->body), 120) }}
            </p>

        <div class="d-flex justify-content-between align-items-center">
                        <div>
                            @if($instruction->target_deadline)
                                <span class="badge bg-danger-soft text-danger">
                                    <i class="fas fa-clock me-1"></i>
                                    Deadline: {{ \Carbon\Carbon::parse($instruction->target_deadline)->format('M d, Y') }}
                                </span>
                            @endif
                             @if ($is_forwarded)
                                <span class="badge bg-info-soft text-info ms-2">
                                    <i class="fas fa-share me-1"></i>
                                    Forwarded
                                </span>
            @endif
                        </div>
                        <div class="text-muted">
                            <i class="fas fa-reply me-1"></i> {{ $instruction->replies_count ?? '0' }}
                            <i class="fas fa-paperclip ms-2 me-1"></i> {{ $instruction->attachments_count ?? '0' }}
                        </div>
                    </div>
        </div>
    </div>
        </div>
    </div>
</a>

<style>
    .instruction-card {
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        border: 1px solid var(--border-color);
        border-left: 4px solid transparent;
    }
    .instruction-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px var(--shadow-color);
    }
    .instruction-card.unread {
        background-color: var(--bg-hover);
        border-left-color: var(--primary-color);
        font-weight: 500;
    }
    .unread-dot {
        height: 10px;
        width: 10px;
        background-color: var(--primary-color);
        border-radius: 50%;
        display: inline-block;
        flex-shrink: 0;
        margin-top: 8px;
    }
    .bg-danger-soft {
        background-color: rgba(244, 67, 54, 0.1);
    }
    .text-danger {
        color: #d9534f !important;
    }
    .bg-info-soft {
        background-color: rgba(23, 162, 184, 0.1);
    }
    .text-info {
        color: #17a2b8 !important;
    }
</style>
