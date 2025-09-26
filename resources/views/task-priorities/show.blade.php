@extends('layouts.app')

@section('title', 'Task Priority Details')

@section('content')
<div class="tp-container">
    <div class="tp-header">
        <div class="tp-title-wrap">
            <h1 class="tp-title">Task Priority Details</h1>
            <div class="tp-subtitle">Instruction: <span title="{{ $taskPriority->instruction->title ?? 'N/A' }}">{{ \Illuminate\Support\Str::limit($taskPriority->instruction->title ?? 'N/A', 90) }}</span></div>
        </div>
        <div class="tp-header-actions">
            <a href="{{ route('task-priorities.index') }}" class="tp-btn tp-btn-ghost">
                <span class="tp-btn-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span>Back</span>
            </a>
            <a href="{{ route('task-priorities.export-group', $taskPriority) }}" class="tp-btn tp-btn-ghost" title="Export CSV">
                <span class="tp-btn-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M12 3v12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <path d="M8 11l4 4 4-4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M4 17h16v3H4z" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </span>
                <span>Export</span>
            </a>
            @if(!empty($canModify))
            <a href="{{ route('task-priorities.edit', $taskPriority) }}" class="tp-btn tp-btn-primary">
                <span class="tp-btn-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25z" stroke="currentColor" stroke-width="1.5"/>
                        <path d="M14.06 7.52l.92.92" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                    </svg>
                </span>
                <span>Edit</span>
            </a>
            <form action="{{ route('task-priorities.destroy', $taskPriority) }}" method="POST" class="tp-inline-form" onsubmit="return confirm('Delete this entire task priority group?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="tp-btn tp-btn-danger">
                    <span class="tp-btn-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M9 4h6m-9 3h12M9 9v8m3-8v8m3-8v8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            <path d="M7 7h10l-1 12a2 2 0 0 1-2 2H10a2 2 0 0 1-2-2L7 7z" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </span>
                    <span>Delete</span>
                </button>
            </form>
            @endif
        </div>
    </div>

    <div class="tp-grid tp-grid--two" role="region" aria-label="Overview and Summary">
        <div class="tp-card tp-card--section">
            <div class="tp-card-header">
                <div class="tp-card-title">Instruction</div>
                <div class="tp-card-sub">Overview</div>
            </div>
            <div class="tp-info">
                <div class="tp-info-row">
                    <div class="tp-info-label">Title</div>
                    <div class="tp-info-value">{{ $taskPriority->instruction->title ?? 'N/A' }}</div>
                </div>
                <div class="tp-info-row">
                    <div class="tp-info-label">Sender</div>
                    <div class="tp-info-value tp-sender">
                        @php $sender = $taskPriority->sender; @endphp
                        <div class="tp-avatar" aria-hidden="true">
                            @if($sender && $sender->avatar_url)
                                <img src="{{ $sender->avatar_url }}" alt="" />
                            @else
                                <span class="tp-avatar-fallback">{{ $sender ? \Illuminate\Support\Str::of($sender->full_name)->substr(0,1)->upper() : '?' }}</span>
                            @endif
                        </div>
                        <span>{{ $sender->full_name ?? 'Unknown' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="tp-card tp-card--section">
            <div class="tp-card-header">
                <div class="tp-card-title">Summary</div>
                <div class="tp-card-sub">Quick stats</div>
            </div>
            @php
                $total = $groupItems->count();
                $done = $groupItems->where('status', 'Accomplished')->count();
                $processing = $groupItems->where('status', 'Processing')->count();
                $notStarted = $groupItems->where('status', 'Not Started')->count();
            @endphp
            <div class="tp-chips" role="list">
                <div class="tp-chip" role="listitem" title="Total items"><span class="tp-chip-num">{{ $total }}</span><span>Total</span></div>
                <div class="tp-chip tp-chip--success" role="listitem" title="Accomplished"><span class="tp-chip-num">{{ $done }}</span><span>Accomplished</span></div>
                <div class="tp-chip tp-chip--primary" role="listitem" title="Processing"><span class="tp-chip-num">{{ $processing }}</span><span>Processing</span></div>
                <div class="tp-chip tp-chip--muted" role="listitem" title="Not Started"><span class="tp-chip-num">{{ $notStarted }}</span><span>Not Started</span></div>
            </div>
        </div>
    </div>

    <div class="tp-card tp-card--section" role="region" aria-label="Items">
        <div class="tp-card-header">
            <div class="tp-card-title">Items</div>
            <div class="tp-card-sub">All items in this priority group</div>
        </div>

        <div class="tp-items-list">
            @forelse($groupItems as $i => $item)
                <div class="tp-item-row">
                    <div class="tp-item-main">
                        <div class="tp-item-title" title="{{ $item->priority_title }}">{{ $item->priority_title }}</div>
                        <div class="tp-badges" role="list">
                            <span class="tp-badge tp-badge-priority tp-badge-{{ $item->priority_level }}" role="listitem">{{ ucfirst($item->priority_level) }}</span>
                            <span class="tp-badge tp-badge-status tp-badge-status-{{ \Illuminate\Support\Str::slug($item->status) }}" role="listitem">{{ $item->status }}</span>
                            <span class="tp-badge tp-badge-weak" role="listitem">Week {{ $item->week_range }}</span>
                        </div>
                    </div>
                    <div class="tp-item-meta">
                        <div class="tp-dates">
                            <div class="tp-date"><span>Start:</span> {{ optional($item->start_date)->format('Y-m-d') }}</div>
                            <div class="tp-date"><span>Deadline:</span> {{ optional($item->target_deadline)->format('Y-m-d') }}</div>
                        </div>
                        @if($item->notes)
                            <div class="tp-notes" title="{{ $item->notes }}">{{ \Illuminate\Support\Str::limit($item->notes, 140) }}</div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="tp-empty">No items found in this group.</div>
            @endforelse
        </div>
    </div>
</div>

<style>
.tp-container { max-width: min(1100px, 94vw); margin: 0 auto; padding: clamp(12px, 2vw, 20px); }
.tp-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 14px; flex-wrap: wrap; }
.tp-title-wrap { display:flex; flex-direction:column; gap:4px; }
.tp-title { font-size: clamp(1.15rem, 2.1vw, 1.6rem); font-weight: 800; letter-spacing:.2px; }
.tp-subtitle { color:#6b7280; font-weight:600; }
.tp-header-actions { display: inline-flex; align-items: center; gap: 8px; flex-wrap: wrap; }

.tp-btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px; border-radius: 12px; border: 1px solid rgba(17,24,39,.08); background: #fff; color: #111827; font-weight: 600; text-decoration: none; box-shadow: 0 1px 2px rgba(0,0,0,.04); transition: transform .18s ease, box-shadow .18s ease; }
.tp-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 22px rgba(0,0,0,.12); }
.tp-btn-primary { background: linear-gradient(135deg, #3b82f6, #0ea5e9); color: #fff; border: none; }
.tp-btn-danger { background: linear-gradient(135deg, #ef4444, #f97316); color: #fff; border: none; }
.tp-btn-ghost { background: transparent; }
.tp-btn-icon { line-height: 0; display: inline-flex; }

.tp-card { background: #fff; border: 1px solid #eee; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,.05); }
.tp-card--section { padding: clamp(12px, 2vw, 16px); margin-bottom: 14px; }
.tp-card-header { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 10px; flex-wrap: wrap; }
.tp-card-title { font-weight: 800; }
.tp-card-sub { color: #6b7280; font-size: .92rem; }

.tp-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 12px; }
.tp-grid--two { grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); }

.tp-info { display: flex; flex-direction: column; gap: 10px; }
.tp-info-row { display: grid; grid-template-columns: 120px 1fr; gap: 10px; align-items: baseline; }
.tp-info-label { font-size: .86rem; color: #6b7280; font-weight: 700; }
.tp-info-value { font-weight: 700; color: #111827; overflow-wrap: anywhere; }
.mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; font-size: .9rem; }

.tp-sender { display: inline-flex; align-items: center; gap: 10px; }
.tp-avatar { width: 32px; height: 32px; border-radius: 50%; background: #f3f4f6; display: inline-flex; align-items: center; justify-content: center; overflow: hidden; border: 1px solid #eee; }
.tp-avatar img { width: 100%; height: 100%; object-fit: cover; }
.tp-avatar-fallback { font-weight: 700; color: #6b7280; }

.tp-chips { display:flex; gap:10px; flex-wrap:wrap; }
.tp-chip { display:inline-flex; align-items:center; gap:8px; background:#f9fafb; border:1px solid #eee; border-radius:999px; padding:6px 10px; font-weight:700; color:#111827; }
.tp-chip-num { background:#111827; color:#fff; border-radius:999px; padding:2px 8px; font-size:.78rem; }
.tp-chip--success .tp-chip-num { background:#16a34a; }
.tp-chip--primary .tp-chip-num { background:#2563eb; }
.tp-chip--muted .tp-chip-num { background:#6b7280; }

.tp-items-list { display: flex; flex-direction: column; gap: 10px; }
.tp-item-row { display: grid; grid-template-columns: 1.4fr .9fr; gap: 12px; border: 1px solid #eee; border-radius: 14px; padding: 12px; background:#fff; box-shadow: 0 1px 8px rgba(0,0,0,.04); }
.tp-item-main { display: flex; flex-direction: column; gap: 6px; }
.tp-item-title { font-weight: 800; color: #111827; line-height:1.3; }
.tp-badges { display: inline-flex; align-items: center; flex-wrap: wrap; gap: 6px; }
.tp-badge { display: inline-flex; align-items: center; height: 26px; border-radius: 999px; padding: 0 10px; font-weight: 700; font-size: .8rem; border: 1px solid #eee; }
.tp-badge-weak { background: #f9fafb; color: #6b7280; }
.tp-badge-priority.tp-badge-high { background: #fee2e2; color: #991b1b; }
.tp-badge-priority.tp-badge-normal { background: #ffedd5; color: #9a3412; }
.tp-badge-priority.tp-badge-low { background: #dcfce7; color: #166534; }
.tp-badge-status.tp-badge-status-not-started { background: #e5e7eb; color: #374151; }
.tp-badge-status.tp-badge-status-processing { background: #dbeafe; color: #1e40af; }
.tp-badge-status.tp-badge-status-accomplished { background: #dcfce7; color: #166534; }

.tp-item-meta { display: flex; flex-direction: column; gap: 8px; justify-content: center; }
.tp-dates { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.tp-date span { color: #6b7280; font-weight: 600; margin-right: 6px; }
.tp-notes { background: #f9fafb; border: 1px solid #eee; border-radius: 10px; padding: 8px 10px; color: #374151; }

@media (max-width: 900px) {
  .tp-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 768px) {
  .tp-item-row { grid-template-columns: 1fr; }
  .tp-dates { grid-template-columns: 1fr; }
}

@media (prefers-reduced-motion: reduce) {
  .tp-btn { transition: none !important; }
}
</style>
@endsection
