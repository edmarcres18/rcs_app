@props(['activity'])

@php
    $icon = '';
    $text = '';

    switch ($activity->action) {
        case 'sent':
            $icon = 'fa-paper-plane';
            $text = '<strong>' . e($activity->user->full_name) . '</strong> sent the instruction.';
            break;
        case 'read':
            $icon = 'fa-eye';
            $text = '<strong>' . e($activity->user->full_name) . '</strong> read the instruction.';
            break;
        case 'forwarded':
            $icon = 'fa-share';
            $text = '<strong>' . e($activity->user->full_name) . '</strong> forwarded the instruction.';
            if ($activity->content) {
                $text .= ' with message: <em class="text-muted">"' . e($activity->content) . '"</em>';
            }
            break;
        default:
            $icon = 'fa-info-circle';
            $text = 'An action was performed.';
            break;
    }
@endphp

<div class="timeline-item">
    <div class="timeline-icon" title="{{ ucfirst($activity->action) }}">
        <i class="fas {{ $icon }}"></i>
    </div>
    <div class="timeline-content">
        <div class="d-flex align-items-center">
            <div class="text-muted">
                <span>{!! $text !!}</span>
                <small class="ms-2" title="{{ $activity->created_at->format('Y-m-d H:i:s') }}">
                    {{ $activity->created_at->diffForHumans() }}
                </small>
            </div>
        </div>
    </div>
</div>
