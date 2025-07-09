<div class="card shadow-sm mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between bg-white">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-file-alt me-1"></i> {{ $instruction->title }}
        </h6>
        <div>
            @if(isset($showStatus) && $showStatus)
                @if(isset($instruction->pivot) && $instruction->pivot->is_read)
                    <span class="badge bg-success me-2">Read</span>
                @else
                    <span class="badge bg-danger me-2">Unread</span>
                @endif
            @endif
            <span class="text-xs text-muted">{{ $instruction->created_at->format('M d, Y') }}</span>
        </div>
    </div>
    <div class="card-body">
        <div class="mb-2">
            @if(isset($showSender) && $showSender)
                <p class="text-muted mb-2">
                    <small>
                        <i class="fas fa-user me-1"></i> From: {{ $instruction->sender->full_name }}
                    </small>
                </p>
            @endif

            @if(isset($showRecipients) && $showRecipients)
                <p class="text-muted mb-2">
                    <small>
                        <i class="fas fa-users me-1"></i> To:
                        {{ $instruction->recipients->count() }} {{ \Illuminate\Support\Str::plural('recipient', $instruction->recipients->count()) }}
                    </small>
                </p>
            @endif

            <p class="text-truncate mb-2">
                {{ \Illuminate\Support\Str::limit($instruction->body, 150) }}
            </p>
        </div>

        <div class="d-flex justify-content-between align-items-center">
            @if(isset($instruction->pivot) && $instruction->pivot->forwarded_by_id)
                <small class="text-info">
                    <i class="fas fa-share me-1"></i> Forwarded to you
                </small>
            @else
                <span></span>
            @endif

            <a href="{{ route('instructions.show', $instruction) }}" class="btn btn-sm btn-primary">
                View Details
            </a>
        </div>
    </div>
    <div class="card-footer bg-white py-2">
        <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">
                <i class="fas fa-clock me-1"></i> {{ $instruction->created_at->diffForHumans() }}
            </small>

            @if(isset($showReadStats) && $showReadStats)
                <small class="text-muted">
                    <i class="fas fa-check-circle text-success me-1"></i>
                    {{ $instruction->recipients->where('pivot.is_read', true)->count() }} read /
                    {{ $instruction->recipients->where('pivot.is_read', false)->count() }} unread
                </small>
            @endif
        </div>
    </div>
</div>
