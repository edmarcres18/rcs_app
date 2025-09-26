@extends('layouts.app')

@section('title', 'Edit Task Priorities')

@section('content')
<div class="tp-container">
    <div class="tp-header">
        <h1 class="tp-title">Edit Task Priorities</h1>
        <a href="{{ route('task-priorities.index') }}" class="tp-btn tp-btn-ghost">
            <span class="tp-btn-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <span>Back</span>
        </a>
    </div>

    @if ($errors->any())
        <div class="tp-alert tp-alert-danger" role="alert">
            <div class="tp-alert-title">There were some problems with your input</div>
            <ul class="tp-alert-list">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('task-priorities.update', $taskPriority) }}" id="tp-edit-form">
        @csrf
        @method('PUT')

        <div class="tp-card tp-card--section" aria-labelledby="instruction-section">
            <div class="tp-card-header">
                <div class="tp-card-title">Instruction</div>
                <div class="tp-card-sub">Instruction and sender are fixed for this priority group</div>
            </div>
            <div class="tp-grid">
                <div class="tp-field">
                    <label>Instruction Title</label>
                    <input type="text" value="{{ $taskPriority->instruction->title ?? 'N/A' }} — {{ $taskPriority->sender->full_name ?? 'Unknown' }}" disabled>
                </div>
            </div>
        </div>

        <div class="tp-card tp-card--section" aria-labelledby="priority-items-section">
            <div class="tp-card-header">
                <div>
                    <div class="tp-card-title" id="priority-items-section">Priority Items</div>
                    <div class="tp-card-sub">Update, add, or remove items. Dates must be valid and realistic.</div>
                </div>
                <button type="button" class="tp-btn tp-btn-primary" id="tp-add-item">
                    <span class="tp-btn-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </span>
                    <span>Add Item</span>
                </button>
            </div>

            <div id="tp-items" class="tp-items">
                @php($oldItems = old('items'))
                @if($oldItems)
                    @foreach($oldItems as $i => $old)
                        <div class="tp-item" data-item>
                            <div class="tp-item-header">
                                <div class="tp-item-title">Priority</div>
                                <button type="button" class="tp-icon-btn tp-danger" data-remove title="Remove item" aria-label="Remove item">
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M6 7h12M9 7v10m3-10v10m3-10v10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        <path d="M8 7h8l-1 11a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2L8 7zM10 4h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="tp-grid tp-grid--item">
                                <div class="tp-field">
                                    <label>Title <span class="req" aria-hidden="true">*</span></label>
                                    <input type="text" name="items[{{ $i }}][priority_title]" value="{{ $old['priority_title'] ?? '' }}" placeholder="e.g. Draft initial plan" required maxlength="150">
                                    <small class="tp-help">Max 150 characters.</small>
                                </div>
                                <div class="tp-field">
                                    <label>Priority Level <span class="req" aria-hidden="true">*</span></label>
                                    <select name="items[{{ $i }}][priority_level]" required>
                                        <option value="high" @selected(($old['priority_level'] ?? '')==='high')>High</option>
                                        <option value="normal" @selected(($old['priority_level'] ?? 'normal')==='normal')>Normal</option>
                                        <option value="low" @selected(($old['priority_level'] ?? '')==='low')>Low</option>
                                    </select>
                                </div>
                                <div class="tp-field">
                                    <label>Start Date <span class="req" aria-hidden="true">*</span></label>
                                    <input type="date" name="items[{{ $i }}][start_date]" value="{{ $old['start_date'] ?? '' }}" required data-start>
                                </div>
                                <div class="tp-field">
                                    <label>Target Deadline <span class="req" aria-hidden="true">*</span></label>
                                    <input type="date" name="items[{{ $i }}][target_deadline]" value="{{ $old['target_deadline'] ?? '' }}" required data-deadline>
                                </div>
                                <div class="tp-field">
                                    <label>Status</label>
                                    <select name="items[{{ $i }}][status]">
                                        <option value="Not Started" @selected(($old['status'] ?? 'Not Started')==='Not Started')>Not Started</option>
                                        <option value="Processing" @selected(($old['status'] ?? '')==='Processing')>Processing</option>
                                        <option value="Accomplished" @selected(($old['status'] ?? '')==='Accomplished')>Accomplished</option>
                                    </select>
                                </div>
                                <div class="tp-field tp-field--full">
                                    <label>Notes (optional)</label>
                                    <textarea name="items[{{ $i }}][notes]" rows="3" placeholder="Add any details or context..." data-notes maxlength="500">{{ $old['notes'] ?? '' }}</textarea>
                                    <div class="tp-counter" aria-live="polite"><span data-count>0</span>/500</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    @foreach($groupItems as $i => $item)
                        <div class="tp-item" data-item>
                            <div class="tp-item-header">
                                <div class="tp-item-title">Priority</div>
                                <button type="button" class="tp-icon-btn tp-danger" data-remove title="Remove item" aria-label="Remove item">
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                        <path d="M6 7h12M9 7v10m3-10v10m3-10v10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                        <path d="M8 7h8l-1 11a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2L8 7zM10 4h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="tp-grid tp-grid--item">
                                <div class="tp-field">
                                    <label>Title <span class="req" aria-hidden="true">*</span></label>
                                    <input type="text" name="items[{{ $i }}][priority_title]" value="{{ $item->priority_title }}" placeholder="e.g. Draft initial plan" required maxlength="150">
                                    <small class="tp-help">Max 150 characters.</small>
                                </div>
                                <div class="tp-field">
                                    <label>Priority Level <span class="req" aria-hidden="true">*</span></label>
                                    <select name="items[{{ $i }}][priority_level]" required>
                                        <option value="high" @selected($item->priority_level==='high')>High</option>
                                        <option value="normal" @selected($item->priority_level==='normal')>Normal</option>
                                        <option value="low" @selected($item->priority_level==='low')>Low</option>
                                    </select>
                                </div>
                                <div class="tp-field">
                                    <label>Start Date <span class="req" aria-hidden="true">*</span></label>
                                    <input type="date" name="items[{{ $i }}][start_date]" value="{{ optional($item->start_date)->format('Y-m-d') }}" required data-start>
                                </div>
                                <div class="tp-field">
                                    <label>Target Deadline <span class="req" aria-hidden="true">*</span></label>
                                    <input type="date" name="items[{{ $i }}][target_deadline]" value="{{ optional($item->target_deadline)->format('Y-m-d') }}" required data-deadline>
                                </div>
                                <div class="tp-field">
                                    <label>Status</label>
                                    <select name="items[{{ $i }}][status]">
                                        <option value="Not Started" @selected($item->status==='Not Started')>Not Started</option>
                                        <option value="Processing" @selected($item->status==='Processing')>Processing</option>
                                        <option value="Accomplished" @selected($item->status==='Accomplished')>Accomplished</option>
                                    </select>
                                </div>
                                <div class="tp-field tp-field--full">
                                    <label>Notes (optional)</label>
                                    <textarea name="items[{{ $i }}][notes]" rows="3" placeholder="Add any details or context..." data-notes maxlength="500">{{ $item->notes }}</textarea>
                                    <div class="tp-counter" aria-live="polite"><span data-count>0</span>/500</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <template id="tp-item-template">
                <div class="tp-item" data-item>
                    <div class="tp-item-header">
                        <div class="tp-item-title">Priority</div>
                        <button type="button" class="tp-icon-btn tp-danger" data-remove title="Remove item" aria-label="Remove item">
                            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                                <path d="M6 7h12M9 7v10m3-10v10m3-10v10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <path d="M8 7h8l-1 11a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2L8 7zM10 4h4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>
                    <div class="tp-grid tp-grid--item">
                        <div class="tp-field">
                            <label>Title <span class="req" aria-hidden="true">*</span></label>
                            <input type="text" name="items[IDX][priority_title]" placeholder="e.g. Draft initial plan" required maxlength="150">
                            <small class="tp-help">Max 150 characters.</small>
                        </div>
                        <div class="tp-field">
                            <label>Priority Level <span class="req" aria-hidden="true">*</span></label>
                            <select name="items[IDX][priority_level]" required>
                                <option value="high">High</option>
                                <option value="normal" selected>Normal</option>
                                <option value="low">Low</option>
                            </select>
                        </div>
                        <div class="tp-field">
                            <label>Start Date <span class="req" aria-hidden="true">*</span></label>
                            <input type="date" name="items[IDX][start_date]" required data-start>
                        </div>
                        <div class="tp-field">
                            <label>Target Deadline <span class="req" aria-hidden="true">*</span></label>
                            <input type="date" name="items[IDX][target_deadline]" required data-deadline>
                        </div>
                        <div class="tp-field">
                            <label>Status</label>
                            <select name="items[IDX][status]">
                                <option value="Not Started" selected>Not Started</option>
                                <option value="Processing">Processing</option>
                                <option value="Accomplished">Accomplished</option>
                            </select>
                        </div>
                        <div class="tp-field tp-field--full">
                            <label>Notes (optional)</label>
                            <textarea name="items[IDX][notes]" rows="3" placeholder="Add any details or context..." data-notes maxlength="500"></textarea>
                            <div class="tp-counter" aria-live="polite"><span data-count>0</span>/500</div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div class="tp-actions-row">
            <a href="{{ route('task-priorities.index') }}" class="tp-btn tp-btn-ghost">Cancel</a>
            <button type="submit" class="tp-btn tp-btn-primary" id="tp-submit">
                <span class="tp-btn-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M5 12l4 4L19 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span>Save Changes</span>
            </button>
        </div>
    </form>
</div>

<style>
/* Layout */
.tp-container { max-width: min(1100px, 94vw); margin: 0 auto; padding: clamp(12px, 2vw, 20px); }
.tp-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 14px; flex-wrap: wrap; }
.tp-title { font-size: clamp(1.15rem, 2.1vw, 1.5rem); font-weight: 800; letter-spacing: .2px; }

/* Buttons */
.tp-btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px; border-radius: 12px; border: 1px solid rgba(17,24,39,.08); background: #fff; color: #111827; font-weight: 600; text-decoration: none; box-shadow: 0 1px 2px rgba(0,0,0,.04); transition: transform .18s ease, box-shadow .18s ease; }
.tp-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 22px rgba(0,0,0,.12); }
.tp-btn-primary { background: linear-gradient(135deg, #3b82f6, #0ea5e9); color: #fff; border: none; }
.tp-btn-ghost { background: transparent; }
.tp-btn-icon { line-height: 0; display: inline-flex; }

/* Alerts */
.tp-alert { border: 1px solid #fee2e2; background: #fff1f2; color: #991b1b; border-radius: 12px; padding: 12px 14px; margin-bottom: 12px; }
.tp-alert-title { font-weight: 700; margin-bottom: 6px; }
.tp-alert-list { margin: 0; padding-left: 18px; }

/* Cards */
.tp-card { background: #fff; border: 1px solid #eee; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,.05); }
.tp-card--section { padding: clamp(12px, 2vw, 16px); margin-bottom: 14px; }
.tp-card-header { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 10px; flex-wrap: wrap; }
.tp-card-title { font-weight: 800; }
.tp-card-sub { color: #6b7280; font-size: .92rem; }
.tp-help { color: #6b7280; font-size: .82rem; }
.req { color: #ef4444; }

/* Grid */
.tp-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 12px; }
.tp-grid--item { grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); }

/* Fields */
.tp-field { display: flex; flex-direction: column; gap: 6px; }
.tp-field--full { grid-column: 1 / -1; }
.tp-field label { font-size: .86rem; color: #374151; font-weight: 700; }
.tp-field input, .tp-field select, .tp-field textarea { border: 1px solid #e5e7eb; border-radius: 12px; padding: 10px 12px; outline: none; background: #fff; transition: border-color .2s ease, box-shadow .2s ease; }
.tp-field input:focus, .tp-field select:focus, .tp-field textarea:focus { border-color: #2563eb; box-shadow: 0 0 0 4px rgba(37,99,235,.12); }

/* Items */
.tp-items { display: flex; flex-direction: column; gap: 12px; }
.tp-item { border: 1px dashed #e5e7eb; border-radius: 12px; padding: 12px; background: #fff; }
.tp-item-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
.tp-item-title { font-weight: 800; color: #111827; }
.tp-counter { font-size: .78rem; color: #6b7280; margin-top: 4px; }

.tp-icon-btn { display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 10px; border: 1px solid #eee; background: #fff; color: #111; transition: .2s; }
.tp-icon-btn:hover { background: #111827; color: #fff; }
.tp-icon-btn.tp-danger:hover { background: #ef4444; color: #fff; }

.tp-actions-row { display: flex; align-items: center; justify-content: flex-end; gap: 10px; margin-top: 12px; }

/* Responsive */
@media (max-width: 768px) {
  .tp-header { flex-direction: column; align-items: stretch; }
  .tp-actions-row { position: sticky; bottom: calc(10px + env(safe-area-inset-bottom)); background: rgba(255,255,255,.92); backdrop-filter: blur(6px); padding: 8px; border-radius: 12px; box-shadow: 0 8px 30px rgba(0,0,0,.12); }
}

/* Reduced motion */
@media (prefers-reduced-motion: reduce) {
  .tp-btn, .tp-icon-btn { transition: none !important; }
}
</style>

<script>
(function() {
  const $ = (s, r=document) => r.querySelector(s);
  const $$ = (s, r=document) => Array.from(r.querySelectorAll(s));
  const itemsWrap = $('#tp-items');
  const addBtn = $('#tp-add-item');
  const tmpl = $('#tp-item-template');
  const form = $('#tp-edit-form');
  const submitBtn = $('#tp-submit');

  let idx = {{ max((old('items') ? count(old('items')) : count($groupItems ?? [])), 0) }};

  function initItem(root) {
    // Attach remove handler
    const removeBtn = root.querySelector('[data-remove]');
    if (removeBtn) {
      removeBtn.addEventListener('click', () => {
        root.remove();
        updateTitles();
      });
    }

    // Date constraints: end >= start
    const startInput = root.querySelector('[data-start]');
    const endInput = root.querySelector('[data-deadline]');
    if (startInput && endInput) {
      endInput.min = startInput.value || '';
      startInput.addEventListener('change', () => {
        endInput.min = startInput.value || '';
        if (endInput.value && startInput.value && endInput.value < startInput.value) {
          endInput.value = startInput.value;
        }
      });
      endInput.addEventListener('change', () => {
        if (startInput.value && endInput.value < startInput.value) {
          endInput.value = startInput.value;
        }
      });
    }

    // Notes counter
    const notes = root.querySelector('[data-notes]');
    const counter = root.querySelector('[data-count]');
    if (notes && counter) {
      const sync = () => counter.textContent = String(notes.value.length);
      notes.addEventListener('input', sync);
      sync();
    }
  }

  function addItem() {
    const clone = tmpl.content.cloneNode(true);
    const root = clone.querySelector('[data-item]');
    const inputs = clone.querySelectorAll('input, select, textarea');
    inputs.forEach(el => {
      el.name = el.name.replace('IDX', String(idx));
    });
    itemsWrap.appendChild(clone);
    initItem(root);
    idx++;
    updateTitles();

    const firstInput = root.querySelector('input, select, textarea');
    if (firstInput) firstInput.focus({ preventScroll: false });
  }

  function updateTitles() {
    $$('.tp-item').forEach((el, i) => {
      const t = el.querySelector('.tp-item-title');
      if (t) t.textContent = `Priority #${i + 1}`;
    });
  }

  // Initialize server-rendered items
  $$('#tp-items [data-item]').forEach(initItem);

  addBtn.addEventListener('click', () => addItem());

  form.addEventListener('submit', (e) => {
    if ($$('.tp-item').length === 0) {
      e.preventDefault();
      alert('Please maintain at least one priority item.');
    }
    if (submitBtn) {
      submitBtn.disabled = true;
      setTimeout(() => { submitBtn.disabled = false; }, 3000);
    }
  });
})();
</script>
@endsection
