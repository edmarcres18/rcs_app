<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Rating</th>
                <th>Comment</th>
                <th>Submitted At</th>
                <th>IP</th>
                <th>User Agent</th>
            </tr>
        </thead>
        <tbody>
        @forelse ($ratings as $rating)
            <tr>
                <td class="text-muted">#{{ $rating->id }}</td>
                <td>
                    @if($rating->user)
                        <div class="fw-semibold">{{ $rating->user->first_name }} {{ $rating->user->last_name }}</div>
                        <div class="text-muted small">{{ $rating->user->email }}</div>
                    @else
                        <span class="badge bg-secondary">User deleted</span>
                    @endif
                </td>
                <td>
                    <div class="text-warning">
                        @for ($i = 1; $i <= 5; $i++)
                            @if ($i <= $rating->rating)
                                <i class="fas fa-star"></i>
                            @else
                                <i class="far fa-star"></i>
                            @endif
                        @endfor
                    </div>
                    <div class="small text-muted">{{ $rating->rating_text ?? '' }}</div>
                </td>
                <td style="max-width: 360px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    {{ $rating->comment ?? '—' }}
                </td>
                <td>
                    <div>{{ optional($rating->submitted_at)->format('Y-m-d H:i') }}</div>
                    <div class="small text-muted">{{ optional($rating->submitted_at)->diffForHumans() }}</div>
                </td>
                <td class="text-muted small">{{ $rating->ip_address ?? '—' }}</td>
                <td class="text-muted small" style="max-width: 280px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $rating->user_agent ?? '—' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center text-muted py-4">No ratings found.</td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
@if ($ratings->hasPages())
    <div class="card-footer d-flex justify-content-end">
        {!! $ratings->appends(request()->except('page'))->links() !!}
    </div>
@endif
