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
    @if($activity->user)
    <div class="timeline-icon-avatar" title="{{ ucfirst($activity->action) }} by {{ $activity->user->full_name }}">
        <img src="{{ $activity->user->avatar_url }}" alt="{{ $activity->user->full_name }}" class="rounded-circle" width="40" height="40" style="object-fit: cover; border: 2px solid #e5e7eb;">
        <span class="timeline-badge {{ $iconClass }}" style="position: absolute; bottom: -2px; right: -2px; width: 20px; height: 20px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #fff;">
            <i class="fas {{ $icon }}" style="font-size: 10px;"></i>
        </span>
    </div>
    @else
    <div class="timeline-icon {{ $iconClass }}" title="{{ ucfirst($activity->action) }}">
        <i class="fas {{ $icon }}"></i>
    </div>
    @endif
    <div class="timeline-content ps-3">
        <p class="mb-0 small">{!! $text !!}</p>
        <small class="text-muted" title="{{ $activity->created_at->format('Y-m-d H:i:s') }}">
            {{ $activity->created_at->diffForHumans() }}
        </small>
    </div>
</div>
