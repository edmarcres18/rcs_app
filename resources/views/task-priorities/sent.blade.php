@extends('layouts.app')

@section('title', 'Task Priorities – Sent (Read-only)')

@section('content')
<div class="tp-container">
    <div class="tp-header">
        <h1 class="tp-title">Task Priorities <span class="tp-title-muted">(Sent)</span></h1>
        <div class="tp-header-actions" style="display:flex; gap:8px; flex-wrap:wrap;">
            <a href="{{ route('task-priorities.index') }}" class="tp-btn tp-btn-ghost" title="My Priorities">
                <span class="tp-btn-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span>Back</span>
            </a>
        </div>
    </div>

    <form method="GET" action="{{ route('task-priorities.sent') }}" class="tp-filters tp-filters--single" id="tp-filter-form">
        <div class="tp-field tp-field--search">
            <label for="instruction_title" class="sr-only">Search Instruction Title</label>
            <div class="tp-search-wrap">
                <span class="tp-search-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2"/>
                        <path d="M20 20l-3.5-3.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </span>
                <input type="text" id="instruction_title" name="instruction_title" value="{{ request('instruction_title') }}" placeholder="Search instruction title..." autocomplete="off" />
                @if(request('instruction_title'))
                <a class="tp-clear" href="{{ route('task-priorities.sent') }}" title="Clear search" aria-label="Clear search">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </a>
                @endif
            </div>
        </div>
    </form>

    <div class="tp-table-wrap">
        <table class="tp-table">
            <thead>
                <tr>
                    <th>Instruction Title</th>
                    <th>Receiver</th>
                    <th class="tp-col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($taskPriorities as $tp)
                    <tr class="tp-row" data-id="{{ $tp->id }}">
                        <td data-label="Instruction Title">
                                <span class="tp-instruction-title" title="{{ $tp->instruction->title ?? 'N/A' }}">{{ \Illuminate\Support\Str::limit($tp->instruction->title ?? 'N/A', 80) }}</span>
                            </div>
                        </td>
                        <td data-label="Receiver">
                            <div class="tp-sender">
                                @php $receiver = $tp->createdBy ?? null; @endphp
                                <div class="tp-avatar" aria-hidden="true">
                                    @if($receiver && $receiver->avatar_url)
                                        <img src="{{ $receiver->avatar_url }}" alt="" />
                                    @else
                                        <span class="tp-avatar-fallback">{{ $receiver ? \Illuminate\Support\Str::of($receiver->first_name.' '.($receiver->last_name ?? ''))->substr(0,1)->upper() : '?' }}</span>
                                    @endif
                                </div>
                                <div class="tp-sender-name">{{ $receiver ? trim(($receiver->first_name ?? '').' '.($receiver->last_name ?? '')) : 'Unknown' }}</div>
                            </div>
                        </td>
                        <td class="tp-col-actions">
                            <div class="tp-actions-inline" role="group" aria-label="Row actions">
                                <a class="tp-icon-btn" href="{{ route('task-priorities.show', $tp) }}" title="View">
                                    <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M12 5c-7 0-10 7-10 7s3 7 10 7 10-7 10-7-3-7-10-7zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-8a3 3 0 1 0 .001 6.001A3 3 0 0 0 12 9z"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">
                            <div class="tp-empty">
                                <div class="tp-empty-emoji" aria-hidden="true">📭</div>
                                <div class="tp-empty-title">No task priorities found</div>
                                <div class="tp-empty-sub">When receivers create task priorities for your instructions, they will appear here.</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="tp-pagination">
        {{ $taskPriorities->links() }}
    </div>
</div>

<style>
.tp-container { max-width: min(1100px, 94vw); margin: 0 auto; padding: clamp(12px, 2vw, 20px); }
.tp-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 14px; flex-wrap: wrap; }
.tp-title { font-size: clamp(1.15rem, 2.1vw, 1.5rem); font-weight: 800; }
.tp-title-muted { color:#6b7280; font-size:.9em; font-weight:700; }

.tp-btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px; border-radius: 12px; border: 1px solid rgba(17,24,39,.08); background: #fff; color: #111827; font-weight: 600; text-decoration: none; box-shadow: 0 1px 2px rgba(0,0,0,.04); transition: transform .18s ease, box-shadow .18s ease; }
.tp-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 22px rgba(0,0,0,.12); }
.tp-btn-ghost { background: transparent; }
.tp-btn-icon { line-height: 0; display: inline-flex; }

.tp-table-wrap { overflow-x: auto; background: #fff; border: 1px solid #eee; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,.05); }
.tp-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.tp-table thead th { text-align: left; font-size: .78rem; text-transform: uppercase; letter-spacing: .06em; color: #6b7280; background: linear-gradient(180deg,#fafafa,#f3f4f6); padding: 12px 14px; position: sticky; top: 0; z-index: 1; }
.tp-table tbody td { padding: clamp(12px, 1.4vw, 16px); border-top: 1px solid #eef2f7; vertical-align: middle; }
.tp-col-actions { width: 160px; }

.tp-actions-inline { display: inline-flex; align-items: center; gap: 8px; }
.tp-icon-btn { display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 10px; border: 1px solid #eee; background: #fff; color: #111; transition: .2s; }
.tp-icon-btn:hover { background: #111827; color: #fff; box-shadow: 0 6px 18px rgba(0,0,0,.12); transform: translateY(-1px); }

.tp-sender { display: inline-flex; align-items: center; gap: 10px; }
.tp-avatar { width: 32px; height: 32px; border-radius: 50%; background: #f3f4f6; display: inline-flex; align-items: center; justify-content: center; overflow: hidden; border: 1px solid #eee; }
.tp-avatar img { width: 100%; height: 100%; object-fit: cover; }
.tp-avatar-fallback { font-weight: 700; color: #6b7280; }
.tp-sender-name { font-weight: 600; color: #111827; }

.tp-instruction-title { font-weight: 800; color: #111827; letter-spacing:.2px; }

.tp-empty { text-align: center; padding: 48px 16px; color: #6b7280; }
.tp-empty-emoji { font-size: 40px; margin-bottom: 8px; }
.tp-empty-title { font-weight: 800; margin-bottom: 6px; color: #111827; }
.tp-empty-sub { font-size: .95rem; }

.tp-pagination { display: flex; justify-content: center; padding: 14px 8px; }

@media (max-width: 768px) {
  .tp-table thead { display: none; }
  .tp-table tbody, .tp-table tr, .tp-table td { display: block; width: 100%; }
  .tp-row { border-bottom: 1px solid #f0f0f0; padding: 10px 10px 12px; background: #fff; border-radius: 12px; margin: 8px; box-shadow: 0 1px 6px rgba(0,0,0,.06); }
  .tp-table tbody td { border: 0; padding: 6px 8px; }
  .tp-table tbody td[data-label]::before { content: attr(data-label) ": "; font-weight: 700; color: #6b7280; margin-right: 6px; }
  .tp-col-actions { padding-top: 10px; }
}

@media (prefers-reduced-motion: reduce) {
  .tp-btn, .tp-icon-btn { transition: none !important; }
}
</style>
@endsection
