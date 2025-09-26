@extends('layouts.app')

@section('title', 'Task Priorities – Recycle Bin')

@section('content')
<div class="tp-container">
    <div class="tp-header">
        <h1 class="tp-title">Recycle Bin</h1>
        <div class="tp-actions">
            <a href="{{ route('task-priorities.index') }}" class="tp-btn tp-btn-ghost">
                <span class="tp-btn-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span>Back to List</span>
            </a>
        </div>
    </div>

    <div class="tp-table-wrap">
        <table class="tp-table">
            <thead>
                <tr>
                    <th>Instruction Title</th>
                    <th>Sender</th>
                    <th>Deleted At</th>
                    <th class="tp-col-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($taskPriorities as $tp)
                    <tr class="tp-row">
                        <td data-label="Instruction Title">
                            <div class="tp-instruction">
                                <span class="tp-instruction-title" title="{{ $tp->instruction->title ?? 'N/A' }}">{{ \Illuminate\Support\Str::limit($tp->instruction->title ?? 'N/A', 80) }}</span>
                            </div>
                        </td>
                        <td data-label="Sender">
                            <div class="tp-sender">
                                @php $sender = $tp->sender ?? null; @endphp
                                <div class="tp-avatar" aria-hidden="true">
                                    @if($sender && $sender->avatar_url)
                                        <img src="{{ $sender->avatar_url }}" alt="" />
                                    @else
                                        <span class="tp-avatar-fallback">{{ $sender ? \Illuminate\Support\Str::of($sender->full_name)->substr(0,1)->upper() : '?' }}</span>
                                    @endif
                                </div>
                                <div class="tp-sender-name">{{ $sender->full_name ?? 'Unknown' }}</div>
                            </div>
                        </td>
                        <td data-label="Deleted At">
                            {{ optional($tp->deleted_at)->format('Y-m-d H:i') }}
                        </td>
                        <td class="tp-col-actions">
                            <div class="tp-actions-inline">
                                <form action="{{ route('task-priorities.restore-group', $tp->group_key) }}" method="POST" class="tp-inline-form" onsubmit="return confirm('Restore this task priority group?');">
                                    @csrf
                                    <button type="submit" class="tp-icon-btn" title="Restore">
                                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path d="M12 6v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M21 12a9 9 0 1 1-3-6.7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        </svg>
                                    </button>
                                </form>
                                <form action="{{ route('task-priorities.force-delete-group', $tp->group_key) }}" method="POST" class="tp-inline-form" onsubmit="return confirm('Permanently delete this group? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="tp-icon-btn tp-danger" title="Permanently Delete">
                                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                            <path d="M9 4h6m-9 3h12M9 9v8m3-8v8m3-8v8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                            <path d="M7 7h10l-1 12a2 2 0 0 1-2 2H10a2 2 0 0 1-2-2L7 7z" stroke="currentColor" stroke-width="2"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <div class="tp-empty">
                                <div class="tp-empty-emoji" aria-hidden="true">🗑️</div>
                                <div class="tp-empty-title">Recycle Bin is empty</div>
                                <div class="tp-empty-sub">Deleted task priorities will appear here. You can restore them or permanently delete.</div>
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

.tp-btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px; border-radius: 12px; border: 1px solid rgba(17,24,39,.08); background: #fff; color: #111827; font-weight: 600; text-decoration: none; box-shadow: 0 1px 2px rgba(0,0,0,.04); transition: transform .18s ease, box-shadow .18s ease; }
.tp-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 22px rgba(0,0,0,.12); }
.tp-btn-ghost { background: transparent; }
.tp-btn-icon { line-height: 0; display: inline-flex; }

.tp-table-wrap { overflow-x: auto; background: #fff; border: 1px solid #eee; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,.05); }
.tp-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.tp-table thead th { text-align: left; font-size: .78rem; text-transform: uppercase; letter-spacing: .06em; color: #6b7280; background: #fafafa; padding: 12px 14px; position: sticky; top: 0; z-index: 1; }
.tp-table tbody td { padding: clamp(12px, 1.4vw, 16px); border-top: 1px solid #f0f0f0; vertical-align: middle; }
.tp-col-actions { width: 160px; }

.tp-actions-inline { display: inline-flex; align-items: center; gap: 8px; }
.tp-icon-btn { display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 10px; border: 1px solid #eee; background: #fff; color: #111; transition: .2s; }
.tp-icon-btn:hover { background: #111827; color: #fff; }
.tp-icon-btn.tp-danger:hover { background: #ef4444; color: #fff; }

.tp-sender { display: inline-flex; align-items: center; gap: 10px; }
.tp-avatar { width: 32px; height: 32px; border-radius: 50%; background: #f3f4f6; display: inline-flex; align-items: center; justify-content: center; overflow: hidden; border: 1px solid #eee; }
.tp-avatar img { width: 100%; height: 100%; object-fit: cover; }
.tp-avatar-fallback { font-weight: 700; color: #6b7280; }
.tp-sender-name { font-weight: 600; color: #111827; }

.tp-instruction-title { font-weight: 700; color: #111827; }

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
  .tp-col-actions { padding-top: 8px; }
}

@media (prefers-reduced-motion: reduce) {
  .tp-btn, .tp-icon-btn { transition: none !important; }
}
</style>
@endsection
