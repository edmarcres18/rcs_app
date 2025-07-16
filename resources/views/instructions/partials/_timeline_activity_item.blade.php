@props(['activity'])

@php
    $icon = '';
    $iconClass = 'bg-secondary-soft text-secondary'; // Default
    $text = '';

    switch ($activity->action) {
        case 'sent':
            $icon = 'fa-paper-plane';
            $iconClass = 'bg-primary-soft text-primary';
            $text = '<strong>' . e($activity->user->full_name) . '</strong> sent the instruction.';
            break;
        case 'read':
            $icon = 'fa-eye';
            $iconClass = 'bg-info-soft text-info';
            $text = '<strong>' . e($activity->user->full_name) . '</strong> read the instruction.';
            break;
        case 'forwarded':
            $icon = 'fa-share';
            $iconClass = 'bg-success-soft text-success';
            $text = '<strong>' . e($activity->user->full_name) . '</strong> forwarded the instruction.';
            if ($activity->details && !empty($activity->details['message'])) {
                 $text .= ' with message: <em class="text-muted d-block ps-3 border-start border-2 my-2">"' . e($activity->details['message']) . '"</em>';
            }
            break;
        case 'replied':
            $icon = 'fa-reply';
            $iconClass = 'bg-primary-soft text-primary';
            $text = '<strong>' . e($activity->user->full_name) . '</strong> replied to the instruction.';
            break;
        default:
            $icon = 'fa-info-circle';
            $text = e($activity->description);
            break;
    }
@endphp

<div class="timeline-item d-flex align-items-start mb-4">
    <div class="timeline-icon {{ $iconClass }}" title="{{ ucfirst($activity->action) }}">
        <i class="fas {{ $icon }}"></i>
    </div>
    <div class="timeline-content ps-3">
        <p class="mb-0 small">{!! $text !!}</p>
        <small class="text-muted" title="{{ $activity->created_at->format('Y-m-d H:i:s') }}">
            {{ $activity->created_at->diffForHumans() }}
        </small>
    </div>
</div>
