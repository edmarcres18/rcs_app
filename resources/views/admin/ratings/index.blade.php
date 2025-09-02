@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h4 class="mb-0">Ratings Monitor</h4>
            <small class="text-muted">View and audit user-submitted ratings</small>
        </div>
        <div class="col-auto">
            <a href="{{ route('admin.ratings.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-rotate"></i> Refresh
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="card">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3 text-primary"><i class="fas fa-list fa-2x"></i></div>
                    <div>
                        <div class="text-muted">Total Ratings</div>
                        <div class="fs-4 fw-bold">{{ number_format($stats['total']) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3 text-warning"><i class="fas fa-star fa-2x"></i></div>
                    <div>
                        <div class="text-muted">Average Rating</div>
                        <div class="fs-4 fw-bold">{{ $stats['avg'] }} / 5</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card">
                <div class="card-body d-flex align-items-center">
                    <div class="me-3 text-success"><i class="fas fa-clock fa-2x"></i></div>
                    <div>
                        <div class="text-muted">Last 24 Hours</div>
                        <div class="fs-4 fw-bold">{{ number_format($stats['last_24h']) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div class="fw-bold">All Ratings</div>
            <form id="ratings-filter-form" method="GET" class="d-flex gap-2">
                <select name="rating" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All ratings</option>
                    @for ($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>{{ $i }} star{{ $i>1?'s':'' }}</option>
                    @endfor
                </select>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search user/comment">
                <button class="btn btn-sm btn-primary" type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
        <div id="ratings-table-container">
            @include('admin.ratings._table', ['ratings' => $ratings])
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('ratings-filter-form');
    const container = document.getElementById('ratings-table-container');

    function buildQueryString(formEl) {
        const params = new URLSearchParams(new FormData(formEl));
        return params.toString();
    }

    async function loadTable(url) {
        container.classList.add('position-relative');
        const overlay = document.createElement('div');
        overlay.style.position = 'absolute';
        overlay.style.inset = 0;
        overlay.style.background = 'rgba(255,255,255,0.6)';
        overlay.innerHTML = '<div class="d-flex justify-content-center align-items-center h-100"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i></div>';
        container.appendChild(overlay);

        try {
            const res = await fetch(url, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            });
            const html = await res.text();
            container.innerHTML = html;

            // Re-bind pagination link clicks after replace
            bindPagination();
        } catch (e) {
            console.error('Failed to load ratings table:', e);
        }
    }

    function bindPagination() {
        container.querySelectorAll('.pagination a').forEach(a => {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                const url = this.href;
                history.pushState({}, '', url);
                loadTable(url);
            });
        });
    }

    // Intercept form submit
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const qs = buildQueryString(form);
        const baseUrl = '{{ route('admin.ratings.index') }}';
        const url = qs ? `${baseUrl}?${qs}` : baseUrl;
        history.pushState({}, '', url);
        loadTable(url);
    });

    // Change on select should submit via JS without full reload
    form.querySelector('select[name="rating"]').addEventListener('change', function () {
        form.requestSubmit();
    });

    // Initial bind for existing pagination
    bindPagination();
});
</script>
@endpush
