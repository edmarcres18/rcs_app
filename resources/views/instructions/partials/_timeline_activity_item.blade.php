@props(['activity'])

@php
    $icon = '';
    $iconClass = 'bg-secondary-soft text-secondary'; // Default
    $actionText = '';

    switch ($activity->action) {
        case 'sent':
            $icon = 'fa-paper-plane';
            $iconClass = 'bg-primary-soft text-primary';
            $actionText = 'sent the instruction';
            break;
        case 'read':
            $icon = 'fa-eye';
            $iconClass = 'bg-info-soft text-info';
            $actionText = 'read the instruction';
            break;
        case 'forwarded':
            $icon = 'fa-share';
            $iconClass = 'bg-success-soft text-success';
            $actionText = 'forwarded the instruction';
            break;
        case 'replied':
            $icon = 'fa-reply';
            $iconClass = 'bg-primary-soft text-primary';
            $actionText = 'replied to the instruction';
            break;
        default:
            $icon = 'fa-info-circle';
            $actionText = e($activity->description ?? 'performed an action');
            break;
    }
@endphp

<div class="timeline-item d-flex align-items-start mb-4">
    <div class="timeline-icon {{ $iconClass }}" title="{{ ucfirst($activity->action) }}">
        <i class="fas {{ $icon }}"></i>
    </div>
    <div class="timeline-content ps-3 flex-grow-1">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                @if($activity->user)
                    <img src="{{ $activity->user->avatar_url }}" 
                         alt="{{ $activity->user->full_name }}" 
                         class="rounded-circle border border-2 border-light shadow-sm" 
                         width="28" 
                         height="28"
                         style="object-fit: cover;"
                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($activity->user->full_name) }}&color=7F9CF5&background=EBF4FF';">
                    <div class="ms-2">
                        <span class="small"><strong>{{ $activity->user->full_name }}</strong> {{ $actionText }}</span>
                    </div>
                @else
                    <span class="small">{{ $actionText }}</span>
                @endif
            </div>
            <small class="text-muted text-nowrap ms-2" title="{{ $activity->created_at->format('Y-m-d H:i:s') }}">
                {{ $activity->created_at->diffForHumans() }}
            </small>
        </div>
        @if($activity->action === 'forwarded' && $activity->content)
            <div class="mt-2 ps-2 border-start border-2 border-secondary">
                <em class="text-muted small">"{{ $activity->content }}"</em>
            </div>
        @endif
    </div>
</div>
