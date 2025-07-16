@props(['reply'])

<div class="timeline-item d-flex align-items-start mb-4">
    <div class="timeline-icon bg-primary-soft text-primary" title="Reply">
        <i class="fas fa-reply"></i>
    </div>
    <div class="timeline-content ps-3 flex-grow-1">
        {{-- Reply Header --}}
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <img src="{{ $reply->user->avatar_url }}" alt="{{ $reply->user->full_name }}" class="rounded-circle" width="30" height="30">
                <div class="ms-2">
                    <span class="fw-bold d-block small">{{ $reply->user->full_name }}</span>
                </div>
            </div>
            <small class="text-muted" title="{{ $reply->created_at->format('Y-m-d H:i:s') }}">
                {{ $reply->created_at->diffForHumans() }}
            </small>
        </div>

        {{-- Reply Content --}}
        <div class="reply-bubble mt-2">
            <p class="mb-0" style="white-space: pre-wrap;">{{ $reply->content }}</p>

            {{-- Attachment --}}
            @if ($reply->attachment_url)
                <div class="reply-attachment">
                    <a href="{{ $reply->attachment_url }}" target="_blank" class="attachment-link" download>
                        @php
                            $fileExt = pathinfo($reply->attachment, PATHINFO_EXTENSION);
                            $fileIcon = 'fa-paperclip'; // Default icon
                            if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif'])) {
                                $fileIcon = 'fa-file-image';
                            } elseif ($fileExt === 'pdf') {
                                $fileIcon = 'fa-file-pdf';
                            } elseif (in_array($fileExt, ['doc', 'docx'])) {
                                $fileIcon = 'fa-file-word';
                            } elseif (in_array($fileExt, ['xls', 'xlsx'])) {
                                $fileIcon = 'fa-file-excel';
                            }
                        @endphp
                        <i class="fas {{ $fileIcon }} me-2"></i>
                        <span>{{ $reply->attachment }}</span>
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
