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

    <form method="GET" action="{{ route('task-priorities.sent') }}" class="tp-filters" style="display:flex; gap:8px; flex-wrap:wrap; align-items:center; margin-bottom:10px;">
        <input type="hidden" name="page" value="1" />
        <div class="tp-input-wrap" style="flex:1 1 300px; min-width:240px;">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search instruction title or receiver" class="tp-input js-live-search" style="width:100%; padding:10px 12px; border:1px solid #e5e7eb; border-radius:10px;" autocomplete="off" />
        </div>
        <div class="tp-select-wrap" style="flex:0 0 auto;">
            <label for="per_page" class="sr-only">Per page</label>
            <select id="per_page" name="per_page" class="tp-select js-live-per-page" style="padding:10px 12px; border:1px solid #e5e7eb; border-radius:10px;">
                @php $pp = (int) request('per_page', 10); if($pp < 5) { $pp = 5; } if($pp > 10) { $pp = 10; } @endphp
                <option value="5" {{ $pp === 5 ? 'selected' : '' }}>5 / page</option>
                <option value="10" {{ $pp === 10 ? 'selected' : '' }}>10 / page</option>
            </select>
        </div>
        <a href="{{ route('task-priorities.sent') }}" class="tp-btn tp-btn-ghost">Reset</a>
    </form>

    <div id="tp-partial-container">
        @include('task-priorities.partials._sent_table', ['taskPriorities' => $taskPriorities])
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
<script>
document.addEventListener('DOMContentLoaded', function () {
  var form = document.querySelector('form.tp-filters');
  if (!form) { return; }
  var searchInput = form.querySelector('.js-live-search');
  var perPageSelect = form.querySelector('.js-live-per-page');
  var debounceTimer = null;

  function submitWithResetPage() {
    var pageField = form.querySelector('input[name="page"]');
    if (pageField) { pageField.value = '1'; }
    form.requestSubmit ? form.requestSubmit() : form.submit();
  }

  if (searchInput) {
    searchInput.addEventListener('input', function () {
      if (debounceTimer) { clearTimeout(debounceTimer); }
      debounceTimer = setTimeout(submitWithResetPage, 350);
    });
  }

  if (perPageSelect) {
    perPageSelect.addEventListener('change', function () {
      submitWithResetPage();
    });
  }
});
</script>
@endsection
