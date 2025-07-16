@props(['reply'])

<div class="timeline-item">
    <div class="timeline-icon" title="Reply">
        <i class="fas fa-reply"></i>
    </div>
    <div class="timeline-content">
        {{-- Reply Header --}}
        <div class="d-flex align-items-center mb-2">
            <img src="{{ $reply->user->avatar_url }}" alt="{{ $reply->user->full_name }}" class="rounded-circle" width="32" height="32">
            <span class="fw-bold ms-2">{{ $reply->user->full_name }}</span>
            <small class="text-muted ms-auto" title="{{ $reply->created_at->format('Y-m-d H:i:s') }}">
                {{ $reply->created_at->diffForHumans() }}
            </small>
        </div>

        {{-- Reply Content --}}
        <div class="card reply-card">
            <div class="card-body p-3">
                <p class="mb-0" style="white-space: pre-wrap;">{{ $reply->content }}</p>

                {{-- Attachment --}}
                @if ($reply->attachment)
                <hr>
                <div class="mt-2">
                    <a href="{{ $reply->attachment_url }}" target="_blank" class="attachment-link text-decoration-none" download>
                        <i class="fas fa-paperclip me-2 text-muted"></i>
                        <span class="text-dark">{{ $reply->attachment }}</span>
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
