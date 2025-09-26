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

<div class="tp-pagination" id="tp-pagination">
    {{ $taskPriorities->appends(request()->query())->links() }}
    <noscript>
        <!-- Fallback for no-JS -->
        {{ $taskPriorities->links() }}
    </noscript>
</div>


