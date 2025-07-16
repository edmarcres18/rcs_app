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
                <div class="row g-3">
                    @forelse ($receivedInstructions as $instruction)
                        <div class="col-12">
                            @include('instructions.partials._instruction_card', ['instruction' => $instruction, 'type' => 'received'])
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="empty-state">
                                <div class="empty-state-icon"><i class="fas fa-envelope-open-text"></i></div>
                                <h5>No Received Instructions</h5>
                                <p>When someone sends you an instruction, it will appear here. Stay tuned!</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
            {{-- Sent Instructions Tab --}}
            <div class="tab-pane fade" id="sent-tab-pane" role="tabpanel" aria-labelledby="sent-tab" tabindex="0">
                <div class="row g-3">
                    @forelse ($sentInstructions as $instruction)
                        <div class="col-12">
                            @include('instructions.partials._instruction_card', ['instruction' => $instruction, 'type' => 'sent'])
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="empty-state">
                                <div class="empty-state-icon"><i class="fas fa-paper-plane"></i></div>
                                <h5>You haven't sent any instructions yet.</h5>
                                <p>Click the "New Instruction" button to send your first instruction to your team members.</p>
                                <a href="{{ route('instructions.create') }}" class="btn btn-primary mt-3">
                                    <i class="fas fa-plus me-2"></i>
                                    Create Instruction
                                </a>
                            </div>
                        </div>
                    @endforelse
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
    });
</script>
@endpush
