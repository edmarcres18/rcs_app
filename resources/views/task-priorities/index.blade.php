@extends('layouts.app')

@section('title', 'Task Priorities')

@section('content')
<div class="tp-container">
    <div class="tp-header">
        <h1 class="tp-title">Task Priorities</h1>
        <div class="tp-header-actions" style="display:flex; gap:8px; flex-wrap:wrap;">
            <a href="{{ route('task-priorities.sent') }}" class="tp-btn tp-btn-ghost" title="Sent (Read-only)">
                <span class="tp-btn-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M3 12l18-9-9 18-2-7-7-2z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span>Sent</span>
            </a>
            @empty($readOnly)
            <a href="{{ route('task-priorities.recycle-bin') }}" class="tp-btn tp-btn-ghost" title="Recycle Bin">
                <span class="tp-btn-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M9 4h6m-9 3h12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <path d="M8 7h8l-1 11a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2L8 7z" stroke="currentColor" stroke-width="2"/>
                    </svg>
                </span>
                <span>Recycle Bin</span>
            </a>
            <a href="{{ route('task-priorities.create') }}" class="tp-btn tp-btn-primary">
                <span class="tp-btn-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </span>
                <span>Create</span>
            </a>
            @endempty
        </div>
    </div>

    <form method="GET" action="{{ route('task-priorities.index') }}" class="tp-filters tp-filters--single" id="tp-filter-form">
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
                <a class="tp-clear" href="{{ route('task-priorities.index') }}" title="Clear search" aria-label="Clear search">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </a>
                @endif
            </div>
        </div>
    </form>

        <div class="tp-table-wrap">
            <table class="tp-table" aria-describedby="tp-selected-count">
                <thead>
                    <tr>
                        @empty($readOnly)
                        <th class="tp-col-select">
                            <label class="sr-only" for="tp-select-all">Select all</label>
                            <input type="checkbox" id="tp-select-all" />
                        </th>
                        @endempty
                        <th>Instruction Title</th>
                        <th>Sender</th>
                        <th class="tp-col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($taskPriorities as $tp)
                        @php $canDelete = empty($readOnly) && isset($deletableGroupKeys) && in_array($tp->group_key, $deletableGroupKeys); @endphp
                        <tr class="tp-row" data-id="{{ $tp->id }}" data-deletable="{{ $canDelete ? '1' : '0' }}">
                            @empty($readOnly)
                            <td class="tp-col-select">
                                <label class="sr-only" for="tp-select-{{ $tp->id }}">Select row</label>
                                <input type="checkbox" class="tp-row-checkbox" id="tp-select-{{ $tp->id }}" name="selected_items[]" value="{{ $tp->id }}" {{ $canDelete ? '' : 'disabled' }} />
                            </td>
                            @endempty
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
                            <td class="tp-col-actions">
                                <div class="tp-actions-inline" role="group" aria-label="Row actions">
                                    <a class="tp-icon-btn" href="{{ route('task-priorities.show', $tp) }}" title="View">
                                        <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M12 5c-7 0-10 7-10 7s3 7 10 7 10-7 10-7-3-7-10-7zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-8a3 3 0 1 0 .001 6.001A3 3 0 0 0 12 9z"/></svg>
                                    </a>
                                    @empty($readOnly)
                                    <a class="tp-icon-btn" href="{{ route('task-priorities.edit', $tp) }}" title="Edit">
                                        <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zm2.92 2.33H5v-.92L14.06 7.52l.92.92L5.92 19.58zM20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/></svg>
                                    </a>
                                    @if($canDelete)
                                        <form action="{{ route('task-priorities.destroy', $tp) }}" method="POST" class="tp-inline-form" onsubmit="return confirm('Delete this task priority group?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="tp-icon-btn tp-danger" title="Delete">
                                                <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="M6 7h12v14H6z" opacity=".3"/><path fill="currentColor" d="M16 4V3a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v1H3v2h18V4h-5zM8 7h8v13H8z"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                    @endempty
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="tp-empty">
                                    <div class="tp-empty-emoji" aria-hidden="true">📭</div>
                                    <div class="tp-empty-title">No task priorities found</div>
                                    <div class="tp-empty-sub">Try adjusting your filters or create a new one.</div>
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
        
        <div id="tp-bulkbar" class="tp-bulkbar" aria-live="polite" aria-atomic="true">
            <form method="POST" action="{{ route('task-priorities.bulk-delete') }}" id="tp-bulk-form">
                @csrf
                <div id="tp-bulk-hidden"></div>
                <div class="tp-bulkbar-content">
                    <div id="tp-selected-count">0 selected</div>
                    <button id="tp-bulk-submit" type="submit" class="tp-btn tp-btn-danger" disabled>
                        <span class="tp-btn-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M9 4h6m-9 3h12M9 9v8m3-8v8m3-8v8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M7 7h10l-1 12a2 2 0 0 1-2 2H10a2 2 0 0 1-2-2L7 7z" stroke="currentColor" stroke-width="2"/>
                            </svg>
                        </span>
                        <span>Bulk Delete</span>
                    </button>
                </div>
            </form>
        </div>
</div>

<style>
/***** Layout *****/
.tp-container { max-width: 1100px; margin: 0 auto; padding: 16px; }
.tp-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
.tp-title { font-size: 1.5rem; font-weight: 700; letter-spacing: .2px; }

/***** Buttons *****/
.tp-btn { display: inline-flex; align-items: center; gap: 8px; padding: 9px 14px; border-radius: 10px; border: 1px solid rgba(0,0,0,.08); background: #fff; color: #111; font-weight: 600; text-decoration: none; box-shadow: 0 1px 2px rgba(0,0,0,.06); transition: .2s ease; }
.tp-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(0,0,0,.12); }
.tp-btn-primary { background: linear-gradient(135deg, #4f46e5, #06b6d4); color: #fff; border: none; }
.tp-btn-ghost { background: transparent; }
.tp-btn-danger { background: linear-gradient(135deg, #ef4444, #f97316); color: #fff; border: none; }
.tp-btn-icon { font-size: 16px; display: inline-flex; line-height: 0; }
.tp-btn[disabled] { opacity: .6; cursor: not-allowed; box-shadow: none; transform: none; }

.tp-filters { background: linear-gradient(180deg, #fff, #fafafa); padding: 12px; border-radius: 12px; border: 1px solid #ececec; box-shadow: 0 2px 8px rgba(0,0,0,.04); margin-bottom: 12px; }
.tp-filters--single { display: block; }
.tp-field { display: flex; flex-direction: column; gap: 6px; }
.tp-field label { font-size: .82rem; color: #555; font-weight: 600; }
.tp-field input { height: 44px; border: 1px solid #e5e7eb; border-radius: 12px; padding: 0 14px 0 40px; outline: none; background: #fff; transition: border-color .2s ease, box-shadow .2s ease; width: 100%; }
.tp-field input:focus { border-color: #4f46e5; box-shadow: 0 0 0 4px rgba(79,70,229,.12); }
.tp-field--search .tp-search-wrap { position: relative; display: flex; align-items: center; }
.tp-search-icon { position: absolute; left: 12px; font-size: 16px; color: #6b7280; }
.tp-clear { position: absolute; right: 10px; color: #9ca3af; text-decoration: none; font-size: 16px; }
.tp-clear:hover { color: #111827; }

/***** Table *****/
.tp-table-wrap { overflow-x: auto; background: #fff; border: 1px solid #eee; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,.04); }
.tp-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.tp-table thead th { text-align: left; font-size: .78rem; text-transform: uppercase; letter-spacing: .06em; color: #6b7280; background: #fafafa; padding: 12px 14px; position: sticky; top: 0; z-index: 1; }
.tp-table tbody td { padding: 14px; border-top: 1px solid #f0f0f0; vertical-align: middle; }
.tp-col-select { width: 44px; text-align: center; }
.tp-col-actions { width: 120px; }

.tp-row:hover { background: linear-gradient(180deg, #ffffff, #fbfbff); }

/***** Empty State *****/
.tp-empty { text-align: center; padding: 48px 16px; color: #6b7280; }
.tp-empty-emoji { font-size: 0; margin-bottom: 8px; }
.tp-empty-title { font-weight: 700; margin-bottom: 6px; color: #111827; }
.tp-empty-sub { font-size: .95rem; }

/***** Sender *****/
.tp-sender { display: inline-flex; align-items: center; gap: 10px; }
.tp-avatar { width: 32px; height: 32px; border-radius: 50%; background: #f3f4f6; display: inline-flex; align-items: center; justify-content: center; overflow: hidden; border: 1px solid #eee; }
.tp-avatar img { width: 100%; height: 100%; object-fit: cover; }
.tp-avatar-fallback { font-weight: 700; color: #6b7280; }
.tp-sender-name { font-weight: 600; color: #111827; }

/***** Instruction *****/
.tp-instruction-title { font-weight: 600; color: #111827; }

/***** Row Actions *****/
.tp-actions-inline { display: inline-flex; align-items: center; gap: 8px; }
.tp-icon-btn { display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 10px; border: 1px solid #eee; background: #fff; color: #111; transition: .2s; }
.tp-icon-btn:hover { background: #111827; color: #fff; }
.tp-icon-btn.tp-danger:hover { background: #ef4444; color: #fff; }

/***** Bulk Bar *****/
.tp-bulkbar { position: fixed; left: 50%; transform: translateX(-50%); bottom: -80px; transition: bottom .25s ease; background: #111827; color: #fff; padding: 10px 14px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,.25); border: 1px solid rgba(255,255,255,.08); }
.tp-bulkbar--visible { bottom: 18px; }
.tp-bulkbar-content { display: inline-flex; align-items: center; gap: 12px; }

/***** Pagination *****/
.tp-pagination { display: flex; justify-content: center; padding: 14px 8px; }

/***** Accessibility helpers *****/
.sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }

/***** Responsive *****/
@media (max-width: 900px) {
  .tp-header { gap: 10px; }
}
@media (max-width: 768px) {
  .tp-header { flex-direction: column; align-items: stretch; }
  .tp-title { font-size: 1.25rem; }
  .tp-col-actions { width: 100px; }
}
@media (max-width: 768px) {
  /* Transform table to card-like rows */
  .tp-table thead { display: none; }
  .tp-table tbody, .tp-table tr, .tp-table td { display: block; width: 100%; }
  .tp-row { border-bottom: 1px solid #f0f0f0; padding: 10px 10px 12px; background: #fff; border-radius: 12px; margin: 8px; box-shadow: 0 1px 6px rgba(0,0,0,.06); }
  .tp-col-select { position: absolute; right: 12px; top: 8px; }
  .tp-table tbody td { border: 0; padding: 6px 8px; }
  .tp-table tbody td[data-label]::before { content: attr(data-label) ": "; font-weight: 700; color: #6b7280; margin-right: 6px; }
  .tp-col-actions { padding-top: 10px; }
  .tp-actions-inline { gap: 6px; }
}

/* Reduced motion preference */
@media (prefers-reduced-motion: reduce) {
  .tp-btn, .tp-row, .tp-bulkbar { transition: none !important; }
}
</style>

<script>
(function() {
  const $ = (s, r=document) => r.querySelector(s);
  const $$ = (s, r=document) => Array.from(r.querySelectorAll(s));
  const bulkBar = $('#tp-bulkbar');
  const selectAll = $('#tp-select-all');
  const rowCheckboxes = $$('.tp-row-checkbox');
  const selectedCount = $('#tp-selected-count');
  const filterForm = $('#tp-filter-form');
  const searchInput = $('#instruction_title');
  const bulkHidden = $('#tp-bulk-hidden');
  const bulkSubmit = $('#tp-bulk-submit');

  function updateBulkBar() {
    const enabled = rowCheckboxes.filter(cb => !cb.disabled);
    const count = enabled.filter(cb => cb.checked).length;
    selectedCount && (selectedCount.textContent = `${count} selected`);
    if (count > 0) {
      bulkBar && bulkBar.classList.add('tp-bulkbar--visible');
    } else {
      bulkBar && bulkBar.classList.remove('tp-bulkbar--visible');
    }
    if (selectAll) {
      const totalEnabled = enabled.length;
      const all = totalEnabled > 0 && count === totalEnabled;
      const some = count > 0 && count < totalEnabled;
      selectAll.checked = all;
      selectAll.indeterminate = some;
    }

    // Rebuild hidden inputs for bulk form
    if (bulkHidden) {
      bulkHidden.innerHTML = '';
      enabled.filter(cb => cb.checked).forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'selected_items[]';
        input.value = cb.value;
        bulkHidden.appendChild(input);
      });
    }

    // Enable/disable bulk submit button
    if (bulkSubmit) {
      bulkSubmit.disabled = count === 0;
    }
  }

  if (selectAll) {
    selectAll.addEventListener('change', (e) => {
      rowCheckboxes.forEach(cb => {
        if (!cb.disabled) cb.checked = e.target.checked;
      });
      updateBulkBar();
    });
  }

  rowCheckboxes.forEach(cb => cb.addEventListener('change', updateBulkBar));
  updateBulkBar();

  // Debounced live search submit
  if (filterForm && searchInput) {
    let t;
    const debounceMs = 350;
    // Prevent accidental full submit on Enter (mobile/desktop)
    searchInput.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
      }
    });
    searchInput.addEventListener('input', () => {
      clearTimeout(t);
      t = setTimeout(() => {
        filterForm.requestSubmit ? filterForm.requestSubmit() : filterForm.submit();
      }, debounceMs);
    });
  }

  // Confirm before bulk delete submit and block if none selected
  const bulkForm = $('#tp-bulk-form');
  if (bulkForm) {
    bulkForm.addEventListener('submit', (e) => {
      const hasAny = rowCheckboxes.some(cb => cb.checked);
      if (!hasAny) {
        e.preventDefault();
        return;
      }
      const ok = window.confirm('Delete selected task priority groups?');
      if (!ok) {
        e.preventDefault();
      }
    });
  }
})();
</script>
@endsection
