@props([
    'instruction',
    'type' => 'received' // 'received' or 'sent'
])

@php
    $is_unread = $type === 'received' && isset($instruction->pivot) && !$instruction->pivot->is_read;
    $is_forwarded = $type === 'received' && isset($instruction->pivot) && $instruction->pivot->forwarded_by_id;
@endphp

<a href="{{ route('instructions.show', $instruction) }}" class="text-decoration-none text-dark instruction-card-link">
    <div class="card instruction-card {{ $is_unread ? 'instruction-card-unread' : '' }}">
        <div class="card-body p-3 p-md-4">
            <div classd-flex align-items-start>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center">
                        @if($is_unread)
                            <span class="unread-dot me-2" title="Unread"></span>
                        @endif
                        <div class="fw-bold text-dark">
                             @if ($type === 'received')
                                <img src="{{ $instruction->sender->avatar }}" 
                                     alt="{{ $instruction->sender->full_name }}" 
                                     class="rounded-circle border border-1 me-2" 
                                     width="28" 
                                     height="28"
                                     style="object-fit: cover;"
                                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($instruction->sender->full_name) }}&color=7F9CF5&background=EBF4FF';">
                                {{ $instruction->sender->full_name }}
                            @else
                                <i class="fas fa-user-friends me-2 text-muted"></i>
                                To: {{ $instruction->recipientDisplay }}
                            @endif
                        </div>
                    </div>
                    <small class="text-muted flex-shrink-0 ms-3">{{ $instruction->created_at->format('M d, Y') }}</small>
                </div>

                <h5 class="card-title fw-bolder my-2">{{ $instruction->title }}</h5>

                <p class="card-text text-muted small mb-3">
                    {{ \Illuminate\Support\Str::limit(strip_tags($instruction->body), 150) }}
                </p>

                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        @if($instruction->target_deadline)
                            <span class="badge bg-danger-soft text-danger fw-medium">
                                <i class="fas fa-calendar-alt me-1"></i>
                                Due: {{ \Carbon\Carbon::parse($instruction->target_deadline)->format('M d') }}
                            </span>
                        @endif
                        @if ($is_forwarded)
                            <span class="badge bg-info-soft text-info fw-medium ms-2">
                                <i class="fas fa-share me-1"></i>
                                Forwarded
                            </span>
                        @endif
                    </div>
                    <div class="text-muted small d-flex align-items-center">
                        <span title="Replies" class="d-inline-flex align-items-center"><i class="fas fa-reply me-1"></i>{{ (int) ($instruction->replies_count ?? 0) }}</span>
                        <span class="mx-2">·</span>
                        <span title="Attachments" class="d-inline-flex align-items-center"><i class="fas fa-paperclip me-1"></i>{{ (int) ($instruction->attachments_count ?? 0) }}</span>
                        <span class="mx-2">·</span>
                        <span title="Forwards" class="d-inline-flex align-items-center"><i class="fas fa-share me-1"></i>{{ (int) ($instruction->forwards_count ?? 0) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</a>
