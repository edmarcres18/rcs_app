@extends('layouts.app')

@section('title', 'Instructions')

@push('styles')
<style>
    :root {
        --primary-color: #4f46e5;
        --text-color: #111827;
        --text-muted: #6b7280;
        --border-color: #e5e7eb;
        --bg-light: #f9fafb;
    }
    .page-content {
        background-color: var(--bg-light);
    }
    .page-header {
        padding: 1.5rem 0;
        border-bottom: 1px solid var(--border-color);
        margin-bottom: 2rem;
        background-color: #fff;
    }
    .page-title {
        font-size: 1.875rem; /* 30px */
        font-weight: 800;
        color: var(--text-color);
    }
    .nav-pills .nav-link {
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem; /* 8px */
        color: var(--text-muted);
        font-weight: 600;
        transition: all 0.2s ease;
    }
    .nav-pills .nav-link.active, .nav-pills .show>.nav-link {
        background-color: var(--primary-color);
        color: #fff;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -2px rgba(0,0,0,0.06);
    }
    .nav-pills .nav-link i {
        margin-right: 0.5rem;
    }
    .tab-content {
        padding-top: 1.5rem;
    }
    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 4rem 2rem;
        text-align: center;
        border: 2px dashed var(--border-color);
        border-radius: 0.75rem;
        background-color: #fff;
    }
    .empty-state-icon {
        font-size: 4rem;
        color: #d1d5db;
        margin-bottom: 1.5rem;
        line-height: 1;
    }
    .empty-state h5 {
        font-weight: 700;
        font-size: 1.25rem;
        color: var(--text-color);
        margin-bottom: 0.5rem;
    }
    .empty-state p {
        color: var(--text-muted);
        max-width: 450px;
        margin-bottom: 1.5rem;
    }
    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        padding: 0.75rem 1.5rem;
        font-weight: 600;
        border-radius: 0.5rem;
    }

    .instruction-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
        background-color: #fff;
        margin-bottom: 1rem;
    }
    .instruction-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.07), 0 4px 6px -2px rgba(0,0,0,0.05);
    }
    .instruction-card-unread {
        border-left: 4px solid var(--primary-color);
        background-color: #f9fafb;
    }
    .unread-dot {
        height: 10px;
        width: 10px;
        background-color: var(--primary-color);
        border-radius: 50%;
        flex-shrink: 0;
    }
    .bg-danger-soft {
        background-color: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }
    .bg-info-soft {
        background-color: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
    }

    @media (max-width: 767px) {
        .page-title {
            font-size: 1.5rem;
        }
        .nav-pills {
            width: 100%;
            justify-content: space-between;
        }
        .nav-pills .nav-item {
            flex: 1;
            text-align: center;
        }
        .nav-pills .nav-link {
            padding: 0.75rem 0.5rem;
        }
    }
    /* Normalize pagination across this page */
    .pagination {
        justify-content: center;
        margin-bottom: 0;
    }
    .pagination .page-link {
        padding: 6px 10px;
        font-size: 0.875rem;
        line-height: 1.2;
    }
</style>
@endpush

@section('content')
<main class="page-content">
    <div class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h1 class="page-title mb-0">My Instructions</h1>
                <a href="{{ route('instructions.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>
                    <span>New Instruction</span>
                </a>
            </div>
        </div>
    </div>

    <div class="container">
        {{-- Tabs for Received and Sent Instructions --}}
        <ul class="nav nav-pills mb-3" id="instructionTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="received-tab" data-bs-toggle="tab" data-bs-target="#received-tab-pane" type="button" role="tab" aria-controls="received-tab-pane" aria-selected="true">
                    <i class="fas fa-inbox"></i>Received
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="sent-tab" data-bs-toggle="tab" data-bs-target="#sent-tab-pane" type="button" role="tab" aria-controls="sent-tab-pane" aria-selected="false">
                    <i class="fas fa-paper-plane"></i>Sent
                </button>
            </li>
        </ul>

        <div class="tab-content" id="instructionTabsContent">
            {{-- Received Instructions Tab --}}
            <div class="tab-pane fade show active" id="received-tab-pane" role="tabpanel" aria-labelledby="received-tab" tabindex="0">
                <form id="received-filters" method="GET" action="{{ route('instructions.index') }}" class="mb-3 d-flex gap-2 align-items-center flex-wrap">
                    <input type="hidden" name="received_page" value="1" />
                    <div class="flex-grow-1" style="min-width:240px;">
                        <input type="text" name="received_q" value="{{ request('received_q') }}" class="form-control" placeholder="Search received by title, body, or sender" autocomplete="off" />
                    </div>
                    <div>
                        @php $rp = (int) request('received_per_page', 10); if ($rp < 5) { $rp = 5; } if ($rp > 10) { $rp = 10; } @endphp
                        <select name="received_per_page" class="form-select" style="width:auto;">
                            <option value="5" {{ $rp === 5 ? 'selected' : '' }}>5 / page</option>
                            <option value="10" {{ $rp === 10 ? 'selected' : '' }}>10 / page</option>
                        </select>
                    </div>
                </form>
                <div id="received-container">
                    @include('instructions.partials._received_list', ['receivedInstructions' => $receivedInstructions])
                </div>
            </div>
            {{-- Sent Instructions Tab --}}
            <div class="tab-pane fade" id="sent-tab-pane" role="tabpanel" aria-labelledby="sent-tab" tabindex="0">
                <form id="sent-filters" method="GET" action="{{ route('instructions.index') }}" class="mb-3 d-flex gap-2 align-items-center flex-wrap">
                    <input type="hidden" name="sent_page" value="1" />
                    <div class="flex-grow-1" style="min-width:240px;">
                        <input type="text" name="sent_q" value="{{ request('sent_q') }}" class="form-control" placeholder="Search sent by title, body, or recipient" autocomplete="off" />
                    </div>
                    <div>
                        @php $sp = (int) request('sent_per_page', 10); if ($sp < 5) { $sp = 5; } if ($sp > 10) { $sp = 10; } @endphp
                        <select name="sent_per_page" class="form-select" style="width:auto;">
                            <option value="5" {{ $sp === 5 ? 'selected' : '' }}>5 / page</option>
                            <option value="10" {{ $sp === 10 ? 'selected' : '' }}>10 / page</option>
                        </select>
                    </div>
                </form>
                <div id="sent-container">
                    @include('instructions.partials._sent_list', ['sentInstructions' => $sentInstructions])
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // This script handles the bootstrap tabs and ensures the correct tab is shown
        // if the URL has a hash (e.g., #sent-tab-pane).
        const triggerTabList = document.querySelectorAll('#instructionTabs button');
        triggerTabList.forEach(triggerEl => {
            const tabTrigger = new bootstrap.Tab(triggerEl);

            triggerEl.addEventListener('click', event => {
                event.preventDefault();
                tabTrigger.show();

                // Update URL hash without jumping
                history.pushState(null, null, event.target.dataset.bsTarget);
            });
        });

        // Open tab based on URL hash
        if (window.location.hash) {
            const hash = window.location.hash;
            const tabToActivate = document.querySelector(`button[data-bs-target="${hash}"]`);
            if (tabToActivate) {
                const tab = new bootstrap.Tab(tabToActivate);
                tab.show();
            }
        }
        // Live search + AJAX pagination for Received
        (function initReceivedAjax(){
            var form = document.getElementById('received-filters');
            var container = document.getElementById('received-container');
            if (!form || !container) return;
            var debounceTimer = null;
            function buildParams() {
                return new URLSearchParams(new FormData(form)).toString();
            }
            function loadPartial(pushState) {
                var qs = buildParams();
                var url = form.action + '?partial_received=1&' + qs;
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
                    .then(function(r){ return r.text(); })
                    .then(function(html){
                        container.innerHTML = html;
                        attachReceivedPagination();
                        if (pushState !== false) {
                            var fullUrl = form.action + '?' + qs;
                            window.history.pushState({ tab: 'received' }, '', fullUrl);
                        }
                    })
                    .catch(function(){});
            }
            function attachReceivedPagination(){
                var pag = container.querySelector('#received-pagination');
                if (!pag) return;
                var links = pag.querySelectorAll('a');
                links.forEach(function(a){
                    a.addEventListener('click', function(e){
                        e.preventDefault();
                        var url = new URL(a.href);
                        var page = url.searchParams.get('received_page') || '1';
                        var pageField = form.querySelector('input[name="received_page"]');
                        if (pageField) pageField.value = page;
                        loadPartial(true);
                    });
                });
            }
            var qInput = form.querySelector('input[name="received_q"]');
            var perPage = form.querySelector('select[name="received_per_page"]');
            function resetToFirstPage(){
                var pageField = form.querySelector('input[name="received_page"]');
                if (pageField) pageField.value = '1';
            }
            if (qInput) {
                qInput.addEventListener('input', function(){
                    if (debounceTimer) clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(function(){ resetToFirstPage(); loadPartial(true); }, 350);
                });
            }
            if (perPage) {
                perPage.addEventListener('change', function(){ resetToFirstPage(); loadPartial(true); });
            }
            attachReceivedPagination();
        })();

        // Live search + AJAX pagination for Sent
        (function initSentAjax(){
            var form = document.getElementById('sent-filters');
            var container = document.getElementById('sent-container');
            if (!form || !container) return;
            var debounceTimer = null;
            function buildParams() {
                return new URLSearchParams(new FormData(form)).toString();
            }
            function loadPartial(pushState) {
                var qs = buildParams();
                var url = form.action + '?partial_sent=1&' + qs;
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
                    .then(function(r){ return r.text(); })
                    .then(function(html){
                        container.innerHTML = html;
                        attachSentPagination();
                        if (pushState !== false) {
                            var fullUrl = form.action + '?' + qs + '#sent-tab-pane';
                            window.history.pushState({ tab: 'sent' }, '', fullUrl);
                        }
                    })
                    .catch(function(){});
            }
            function attachSentPagination(){
                var pag = container.querySelector('#sent-pagination');
                if (!pag) return;
                var links = pag.querySelectorAll('a');
                links.forEach(function(a){
                    a.addEventListener('click', function(e){
                        e.preventDefault();
                        var url = new URL(a.href);
                        var page = url.searchParams.get('sent_page') || '1';
                        var pageField = form.querySelector('input[name="sent_page"]');
                        if (pageField) pageField.value = page;
                        loadPartial(true);
                    });
                });
            }
            var qInput = form.querySelector('input[name="sent_q"]');
            var perPage = form.querySelector('select[name="sent_per_page"]');
            function resetToFirstPage(){
                var pageField = form.querySelector('input[name="sent_page"]');
                if (pageField) pageField.value = '1';
            }
            if (qInput) {
                qInput.addEventListener('input', function(){
                    if (debounceTimer) clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(function(){ resetToFirstPage(); loadPartial(true); }, 350);
                });
            }
            if (perPage) {
                perPage.addEventListener('change', function(){ resetToFirstPage(); loadPartial(true); });
            }
            attachSentPagination();
        })();

        // Handle back/forward for restoring lists
        window.addEventListener('popstate', function(){
            var hash = window.location.hash;
            var url = new URL(window.location.href);
            var params = new URLSearchParams(url.search);
            // Received
            var rf = document.getElementById('received-filters');
            if (rf) {
                var rq = rf.querySelector('input[name="received_q"]');
                var rpp = rf.querySelector('select[name="received_per_page"]');
                var rp = rf.querySelector('input[name="received_page"]');
                if (rq) rq.value = params.get('received_q') || '';
                if (rpp) rpp.value = params.get('received_per_page') || rpp.value;
                if (rp) rp.value = params.get('received_page') || '1';
                fetch(rf.action + '?partial_received=1&' + new URLSearchParams(new FormData(rf)).toString())
                    .then(function(r){ return r.text(); })
                    .then(function(html){ document.getElementById('received-container').innerHTML = html; });
            }
            // Sent
            var sf = document.getElementById('sent-filters');
            if (sf) {
                var sq = sf.querySelector('input[name="sent_q"]');
                var spp = sf.querySelector('select[name="sent_per_page"]');
                var sp = sf.querySelector('input[name="sent_page"]');
                if (sq) sq.value = params.get('sent_q') || '';
                if (spp) spp.value = params.get('sent_per_page') || spp.value;
                if (sp) sp.value = params.get('sent_page') || '1';
                fetch(sf.action + '?partial_sent=1&' + new URLSearchParams(new FormData(sf)).toString())
                    .then(function(r){ return r.text(); })
                    .then(function(html){ document.getElementById('sent-container').innerHTML = html; });
            }
            // Restore tab focus from hash
            if (hash) {
                var tabToActivate = document.querySelector('button[data-bs-target="' + hash + '"]');
                if (tabToActivate) { new bootstrap.Tab(tabToActivate).show(); }
            }
        });
    });
</script>
@endpush
