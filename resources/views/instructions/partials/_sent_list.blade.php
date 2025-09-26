<div id="sent-list">
    <div class="row g-3">
        @forelse ($sentInstructions as $instruction)
            <div class="col-12">
                @include('instructions.partials._instruction_card', ['instruction' => $instruction, 'type' => 'sent'])
            </div>
        @empty
            <div class="col-12">
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="fas fa-paper-plane"></i></div>
                    @if(request('sent_q'))
                        <h5>No results found</h5>
                        <p>We couldn't find any sent instructions matching "{{ e(request('sent_q')) }}".</p>
                    @else
                        <h5>You haven't sent any instructions yet.</h5>
                        <p>Click the "New Instruction" button to send your first instruction to your team members.</p>
                        <a href="{{ route('instructions.create') }}" class="btn btn-primary mt-3">
                            <i class="fas fa-plus me-2"></i>
                            Create Instruction
                        </a>
                    @endif
                </div>
            </div>
        @endforelse
    </div>
    <div class="mt-3" id="sent-pagination">
        {{ $sentInstructions->appends(request()->query())->onEachSide(1)->links('pagination::bootstrap-5') }}
    </div>
</div>
