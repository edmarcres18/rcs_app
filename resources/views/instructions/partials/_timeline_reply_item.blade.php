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
            @if ($reply->hasAttachment())
                @php
                    $extension = pathinfo($reply->attachment_original_name, PATHINFO_EXTENSION);
                    $category = \App\Services\FileUploadService::getFileCategory($extension, $reply->attachment_mime_type);
                @endphp
                <div class="mt-3">
                    <div class="attachment-item file-{{ $category }} d-flex align-items-center p-3">
                        <i class="{{ $reply->attachment_icon }} me-3"></i>
                        <div class="flex-grow-1">
                            <a href="{{ route('instructions.replies.download', $reply) }}" 
                               class="file-name text-decoration-none" 
                               target="_blank">
                                {{ $reply->attachment_original_name }}
                            </a>
                            <br>
                            <span class="file-size">{{ $reply->formatted_file_size }}</span>
                        </div>
                        <a href="{{ route('instructions.replies.download', $reply) }}" 
                           class="btn btn-sm btn-outline-primary" 
                           target="_blank"
                           title="Download {{ $reply->attachment_original_name }}">
                            <i class="fas fa-download"></i>
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
