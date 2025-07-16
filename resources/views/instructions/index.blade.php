@extends('layouts.app')

{{--
    This template displays the list of received and sent instructions.
    It uses a tabbed interface to separate the two lists.

    Variables passed to this view:
    - $receivedInstructions: A collection of instructions received by the user.
    - $sentInstructions: A collection of instructions sent by the user.

    For optimal performance and to display reply/attachment counts,
    the controller should eager load these counts like so:

    Instruction::withCount(['replies', 'replies as attachments_count' => function ($query) {
        $query->whereNotNull('attachment');
    }])->...
--}}

@section('title', 'Instructions')

@push('styles')
<style>
    .nav-tabs .nav-link {
        border-bottom-width: 2px;
    }
    .nav-tabs .nav-link.active {
        border-color: var(--primary-color);
        color: var(--primary-color);
        font-weight: 600;
    }
    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 50px;
        text-align: center;
        border: 2px dashed var(--border-color);
        border-radius: 10px;
        background-color: var(--bg-card);
    }
    .empty-state-icon {
        font-size: 4rem;
        color: var(--text-light);
        margin-bottom: 20px;
    }
    .empty-state h5 {
        font-weight: 600;
    }
    .empty-state p {
        color: var(--text-muted);
        max-width: 400px;
    }
</style>
@endpush

@section('content')
<main class="page-content">
    <div class="container-fluid">
        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
            <h1 class="page-title mb-2 mb-md-0">My Instructions</h1>
            <a href="{{ route('instructions.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>
                New Instruction
            </a>
        </div>

        {{-- Tabs for Received and Sent Instructions --}}
        <div class="card">
            <div class="card-header border-bottom-0">
                <ul class="nav nav-tabs card-header-tabs" id="instructionTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="received-tab" data-bs-toggle="tab" data-bs-target="#received" type="button" role="tab" aria-controls="received" aria-selected="true">
                            <i class="fas fa-inbox me-2"></i>Received
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="sent-tab" data-bs-toggle="tab" data-bs-target="#sent" type="button" role="tab" aria-controls="sent" aria-selected="false">
                            <i class="fas fa-paper-plane me-2"></i>Sent
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="instructionTabsContent">
                    {{-- Received Instructions Tab --}}
                    <div class="tab-pane fade show active" id="received" role="tabpanel" aria-labelledby="received-tab">
                        @forelse ($receivedInstructions as $instruction)
                            @include('instructions.partials._instruction_card', ['instruction' => $instruction, 'type' => 'received'])
                        @empty
                            <div class="empty-state">
                                <i class="fas fa-envelope-open-text empty-state-icon"></i>
                                <h5>No Received Instructions</h5>
                                <p>When someone sends you an instruction, it will appear here. Stay tuned!</p>
                            </div>
                        @endforelse
                    </div>
                    {{-- Sent Instructions Tab --}}
                    <div class="tab-pane fade" id="sent" role="tabpanel" aria-labelledby="sent-tab">
                         @forelse ($sentInstructions as $instruction)
                            @include('instructions.partials._instruction_card', ['instruction' => $instruction, 'type' => 'sent'])
                        @empty
                            <div class="empty-state">
                                <i class="fas fa-paper-plane empty-state-icon"></i>
                                <h5>You haven't sent any instructions yet.</h5>
                                <p>Click the "New Instruction" button to send your first instruction to your team members.</p>
                                <a href="{{ route('instructions.create') }}" class="btn btn-primary mt-3">
                                    <i class="fas fa-plus me-1"></i>
                                    Create Instruction
                                </a>
                            </div>
                        @endforelse
                    </div>
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
        // if the URL has a hash (e.g., #sent).
        var triggerTabList = [].slice.call(document.querySelectorAll('#instructionTabs button'));
        triggerTabList.forEach(function (triggerEl) {
            var tabTrigger = new bootstrap.Tab(triggerEl);

            triggerEl.addEventListener('click', function (event) {
                event.preventDefault();
                tabTrigger.show();
                // Optionally update URL hash
                // window.location.hash = triggerEl.getAttribute('data-bs-target');
            });
        });

        // Open tab based on URL hash
        if (window.location.hash) {
            var hash = window.location.hash;
            var tabToActivate = document.querySelector('button[data-bs-target="' + hash + '"]');
            if (tabToActivate) {
                var tab = new bootstrap.Tab(tabToActivate);
                tab.show();
            }
        }
    });
</script>
@endpush
