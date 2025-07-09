@extends('layouts.app')

@section('title', 'Instructions')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h1 class="h3 mb-2 text-gray-800 fw-bold">
                <span class="position-relative d-inline-block">
                    Instructions
                    <span class="position-absolute bottom-0 start-0 w-100 border-2 border-primary" style="height: 4px; border-bottom-style: solid; opacity: 0.6;"></span>
                </span>
            </h1>
            <p class="mb-0 text-muted">Manage your communication workflow efficiently</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <a href="{{ route('instructions.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus-circle me-1"></i> New Instruction
            </a>
        </div>
    </div>

    <!-- Instructions Tabs -->
    <div class="row">
        <div class="col-12 mb-4">
            <ul class="nav nav-pills nav-fill instruction-tabs mb-3" id="instructionTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active px-4 py-3" id="received-tab" data-bs-toggle="tab" data-bs-target="#received-instructions" type="button" role="tab" aria-controls="received-instructions" aria-selected="true">
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="tab-icon me-2 bg-primary bg-opacity-10 rounded-circle p-2">
                                <i class="fas fa-inbox text-primary"></i>
                            </div>
                            <div class="tab-text">
                                <span class="d-block">Received</span>
                                <div class="badges mt-1">
                                    <span class="badge bg-primary rounded-pill">{{ $receivedInstructions->count() }}</span>
                                    @if($receivedInstructions->where('pivot.is_read', false)->count() > 0)
                                        <span class="badge bg-danger rounded-pill">{{ $receivedInstructions->where('pivot.is_read', false)->count() }} New</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link px-4 py-3" id="sent-tab" data-bs-toggle="tab" data-bs-target="#sent-instructions" type="button" role="tab" aria-controls="sent-instructions" aria-selected="false">
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="tab-icon me-2 bg-info bg-opacity-10 rounded-circle p-2">
                                <i class="fas fa-paper-plane text-info"></i>
                            </div>
                            <div class="tab-text">
                                <span class="d-block">Sent</span>
                                <span class="badge bg-info rounded-pill mt-1">{{ $sentInstructions->count() }}</span>
                            </div>
                        </div>
                    </button>
                </li>
            </ul>

            <div class="tab-content instruction-tab-content" id="instructionTabsContent">
                <!-- Received Instructions Tab -->
                <div class="tab-pane fade show active" id="received-instructions" role="tabpanel" aria-labelledby="received-tab">
                    <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-0">
                            <h6 class="mb-0 fw-bold text-primary">
                                <i class="fas fa-inbox me-2"></i> Inbox
                            </h6>
                            <span class="text-muted small">{{ $receivedInstructions->count() }} {{ \Illuminate\Support\Str::plural('instruction', $receivedInstructions->count()) }}</span>
                        </div>
                        <div class="card-body p-0">
                            @if($receivedInstructions->count() > 0)
                                <div class="list-group list-group-flush instruction-list">
                                    @foreach($receivedInstructions as $instruction)
                                        <a href="{{ route('instructions.show', $instruction) }}" class="list-group-item list-group-item-action py-3 px-4 instruction-item {{ isset($instruction->pivot) && !$instruction->pivot->is_read ? 'unread-instruction' : '' }}">
                                            <div class="row align-items-center">
                                                <div class="col-lg-8 col-md-7">
                                                    <div class="d-flex align-items-center">
                                                        @if(isset($instruction->pivot) && !$instruction->pivot->is_read)
                                                            <div class="me-3">
                                                                <span class="unread-dot"></span>
                                                            </div>
                                                        @endif
                                                        <div class="instruction-preview">
                                                            <h5 class="mb-1 {{ isset($instruction->pivot) && !$instruction->pivot->is_read ? 'fw-bold' : '' }}">{{ $instruction->title }}</h5>
                                                            <div class="mb-2 text-truncate instruction-body">{{ \Illuminate\Support\Str::limit($instruction->body, 100) }}</div>
                                                            <div class="d-flex align-items-center instruction-meta">
                                                                <div class="avatar-wrapper me-2">
                                                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($instruction->sender->full_name) }}&background=4070f4&color=fff" alt="{{ $instruction->sender->full_name }}" class="rounded-circle" width="28" height="28">
                                                                </div>
                                                                <span class="text-muted">
                                                                    {{ $instruction->sender->full_name }}
                                                                    @if(isset($instruction->pivot) && $instruction->pivot->forwarded_by_id)
                                                                        <span class="ms-2 badge bg-info bg-opacity-10 text-info rounded-pill px-2 py-1">
                                                                            <i class="fas fa-share me-1"></i> Forwarded
                                                                        </span>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-md-5 mt-3 mt-md-0">
                                                    <div class="d-flex justify-content-md-end align-items-center">
                                                        <div class="text-end">
                                                            <span class="badge bg-light text-dark rounded-pill px-3 py-2" title="{{ $instruction->created_at->format('M d, Y g:i A') }}">
                                                                <i class="far fa-clock me-1 text-primary"></i> {{ $instruction->created_at->diffForHumans() }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-5 my-4">
                                    <div class="empty-state-icon mb-3">
                                        <div class="icon-circle bg-primary bg-opacity-10 mx-auto p-4 rounded-circle">
                                            <i class="fas fa-inbox fa-3x text-primary"></i>
                                        </div>
                                    </div>
                                    <h5 class="text-dark">No Instructions Received</h5>
                                    <p class="text-muted mb-3 col-md-6 mx-auto">Your inbox is empty. Instructions sent to you will appear here.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sent Instructions Tab -->
                <div class="tab-pane fade" id="sent-instructions" role="tabpanel" aria-labelledby="sent-tab">
                    <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-0">
                            <h6 class="mb-0 fw-bold text-info">
                                <i class="fas fa-paper-plane me-2"></i> Outbox
                            </h6>
                            <span class="text-muted small">{{ $sentInstructions->count() }} {{ \Illuminate\Support\Str::plural('instruction', $sentInstructions->count()) }}</span>
                        </div>
                        <div class="card-body p-0">
                            @if($sentInstructions->count() > 0)
                                <div class="list-group list-group-flush instruction-list">
                                    @foreach($sentInstructions as $instruction)
                                        <a href="{{ route('instructions.show', $instruction) }}" class="list-group-item list-group-item-action py-3 px-4 instruction-item">
                                            <div class="row align-items-center">
                                                <div class="col-lg-8 col-md-7">
                                                    <div class="instruction-preview">
                                                        <h5 class="mb-1">{{ $instruction->title }}</h5>
                                                        <div class="mb-2 text-truncate instruction-body">{{ \Illuminate\Support\Str::limit($instruction->body, 100) }}</div>
                                                        <div class="d-flex align-items-center instruction-meta">
                                                            <span class="recipients-badge me-2 bg-light text-dark rounded-pill px-2 py-1">
                                                                <i class="fas fa-users text-info me-1"></i> {{ $instruction->recipients->count() }}
                                                            </span>
                                                            <div class="status-badges">
                                                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1" title="Read by recipients">
                                                                    <i class="fas fa-check-double me-1"></i> {{ $instruction->recipients->where('pivot.is_read', true)->count() }}
                                                                </span>
                                                                @if($instruction->recipients->where('pivot.is_read', false)->count() > 0)
                                                                <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill px-2 py-1 ms-1" title="Unread by recipients">
                                                                    <i class="fas fa-check me-1"></i> {{ $instruction->recipients->where('pivot.is_read', false)->count() }}
                                                                </span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-lg-4 col-md-5 mt-3 mt-md-0">
                                                    <div class="d-flex justify-content-md-end align-items-center">
                                                        <div class="text-end">
                                                            <span class="badge bg-light text-dark rounded-pill px-3 py-2" title="{{ $instruction->created_at->format('M d, Y g:i A') }}">
                                                                <i class="far fa-clock me-1 text-info"></i> {{ $instruction->created_at->diffForHumans() }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-5 my-4">
                                    <div class="empty-state-icon mb-3">
                                        <div class="icon-circle bg-info bg-opacity-10 mx-auto p-4 rounded-circle">
                                            <i class="fas fa-paper-plane fa-3x text-info"></i>
                                        </div>
                                    </div>
                                    <h5 class="text-dark">No Instructions Sent</h5>
                                    <p class="text-muted mb-3 col-md-6 mx-auto">Instructions you send will appear here.</p>
                                    <a href="{{ route('instructions.create') }}" class="btn btn-info rounded-pill px-4">
                                        <i class="fas fa-plus-circle me-1"></i> Create New Instruction
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Base Styles */
.instruction-tabs .nav-link {
    border-radius: 10px;
    font-weight: 500;
    transition: all 0.3s ease;
    color: #6c757d;
    margin: 0 0.25rem;
    position: relative;
    overflow: hidden;
}

.instruction-tabs .nav-link.active {
    color: #0d6efd;
    background-color: rgba(13, 110, 253, 0.05);
    box-shadow: 0 2px 10px rgba(13, 110, 253, 0.1);
    transform: translateY(-2px);
}

.instruction-tabs .nav-link:not(.active):hover {
    background-color: rgba(0, 0, 0, 0.03);
    transform: translateY(-1px);
}

.tab-icon {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Instruction Items */
.instruction-item {
    transition: all 0.3s ease;
    border-left: 0;
    border-right: 0;
    border-top-width: 0;
    position: relative;
    overflow: hidden;
}

.instruction-item:first-child {
    border-top: 0;
}

.instruction-item:hover {
    transform: translateY(-2px);
    z-index: 2;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    background-color: rgba(248, 249, 250, 0.7);
}

.unread-instruction {
    background-color: rgba(13, 110, 253, 0.03);
    position: relative;
    border-left: 4px solid #0d6efd;
}

.unread-instruction:before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    width: 4px;
    background-color: #0d6efd;
    opacity: 0.5;
}

.unread-dot {
    width: 12px;
    height: 12px;
    background-color: #0d6efd;
    border-radius: 50%;
    display: inline-block;
    box-shadow: 0 0 0 rgba(13, 110, 253, 0.4);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.4);
    }
    70% {
        box-shadow: 0 0 0 6px rgba(13, 110, 253, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(13, 110, 253, 0);
    }
}

/* Avatar Styles */
.avatar-wrapper {
    position: relative;
    width: 28px;
    height: 28px;
}

.avatar-wrapper img {
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.instruction-item:hover .avatar-wrapper img {
    transform: scale(1.1);
}

/* Text Styles */
.instruction-meta {
    font-size: 0.85rem;
}

.instruction-body {
    color: #6c757d;
    max-width: 95%;
}

/* Empty States */
.empty-state-icon {
    animation: float 3s ease-in-out infinite;
}

@keyframes float {
    0% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-10px);
    }
    100% {
        transform: translateY(0px);
    }
}

/* Card and Container Styles */
.card {
    transition: all 0.3s ease;
    border-color: rgba(0, 0, 0, 0.05);
}

.instruction-tab-content > .tab-pane {
    animation: fadeIn 0.5s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive Fixes */
@media (max-width: 767px) {
    .instruction-meta {
        flex-direction: column;
        align-items: flex-start;
    }

    .instruction-meta .ms-3,
    .status-badges {
        margin-left: 0 !important;
        margin-top: 0.5rem;
    }

    .instruction-tabs .nav-link {
        font-size: 0.875rem;
        padding-left: 0.5rem;
        padding-right: 0.5rem;
    }

    .tab-icon {
        width: 30px;
        height: 30px;
    }
}
</style>

@endsection

@section('scripts')
<script>
$(function() {
    // Enhance tabs with ripple effect
    $(".nav-link").on("click", function(e) {
        const $this = $(this);

        // Create ripple effect
        const $ripple = $("<span class='ripple'></span>");
        const x = e.pageX - $this.offset().left;
        const y = e.pageY - $this.offset().top;

        $ripple.css({
            top: y + "px",
            left: x + "px"
        });

        $this.append($ripple);

        setTimeout(function() {
            $ripple.remove();
        }, 600);
    });

    // Add a subtle shine effect to unread items
    setInterval(function() {
        $(".unread-instruction").each(function() {
            $(this).append('<div class="shine"></div>');
            setTimeout(function() {
                $(".shine").remove();
            }, 1000);
        });
    }, 5000);
});
</script>

<style>
/* Shine Effect */
.shine {
    position: absolute;
    top: 0;
    left: -100%;
    width: 50%;
    height: 100%;
    background: linear-gradient(
        to right,
        rgba(255, 255, 255, 0) 0%,
        rgba(255, 255, 255, 0.3) 50%,
        rgba(255, 255, 255, 0) 100%
    );
    animation: shine 1s;
    pointer-events: none;
}

@keyframes shine {
    100% {
        left: 150%;
    }
}

/* Ripple Effect */
.ripple {
    position: absolute;
    background-color: rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    transform: scale(0);
    animation: ripple 0.6s linear;
    pointer-events: none;
}

@keyframes ripple {
    to {
        transform: scale(2.5);
        opacity: 0;
    }
}
</style>
@endsection
