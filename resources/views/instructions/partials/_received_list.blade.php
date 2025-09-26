<div id="received-list">
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
    <div class="mt-3" id="received-pagination">
        {{ $receivedInstructions->appends(request()->query())->onEachSide(1)->links('pagination::bootstrap-5') }}
    </div>
</div>
