@props(['activity'])

@php
    $icon = '';
    $iconClass = 'bg-secondary-soft text-secondary'; // Default
    $text = '';

    switch ($activity->action) {
        case 'sent':
            $icon = 'fa-paper-plane';
            $iconClass = 'bg-primary-soft text-primary';
            $text = 'sent the instruction.';
            break;
        case 'read':
            $icon = 'fa-eye';
            $iconClass = 'bg-info-soft text-info';
            $text = 'read the instruction.';
            break;
        case 'forwarded':
            $icon = 'fa-share';
            $iconClass = 'bg-success-soft text-success';
            $text = 'forwarded the instruction.';
            if ($activity->details && !empty($activity->details['message'])) {
                 $text .= ' with message: <em class="text-muted d-block ps-3 border-start border-2 my-2">"' . e($activity->details['message']) . '"</em>';
            }
            break;
        case 'replied':
            $icon = 'fa-reply';
            $iconClass = 'bg-primary-soft text-primary';
            $text = 'replied to the instruction.';
            break;
        default:
            $icon = 'fa-info-circle';
            $text = e($activity->description);
            break;
    }
@endphp

<div class="timeline-item d-flex align-items-start mb-4">
    <div class="timeline-avatar me-3">
        <img src="{{ $activity->user->avatar_url }}" alt="{{ $activity->user->full_name }}" class="rounded-circle" width="40" height="40">
    </div>
    <div class="timeline-content flex-grow-1">
        <div class="d-flex align-items-center mb-1">
            <div class="timeline-icon {{ $iconClass }} me-2" title="{{ ucfirst($activity->action) }}">
                <i class="fas {{ $icon }}"></i>
            </div>
            <span class="fw-bold small">{{ $activity->user->full_name }}</span>
            <small class="text-muted ms-auto" title="{{ $activity->created_at->format('Y-m-d H:i:s') }}">
                {{ $activity->created_at->diffForHumans() }}
            </small>
        </div>
        <p class="mb-0 small text-muted">{!! $text !!}</p>
    </div>
</div>
