@extends('layouts.app')

@section('title', $user->first_name . ' ' . $user->last_name . ' - ' . $year . ' Wrapped')

@push('styles')
<style>
    .wrapped-hero {
        background: radial-gradient(120% 120% at 10% 20%, #4f46e5 0%, #312e81 40%, #0f172a 100%);
        color: #f8fafc;
        border: none;
        position: relative;
        overflow: hidden;
    }
    .wrapped-hero::after {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(80% 80% at 80% 0%, rgba(124,58,237,0.25), transparent 60%);
    }
    .wrapped-hero .hero-content { position: relative; z-index: 1; }
    .metric-card { border: 1px solid #e5e7eb; }
    .metric-value { font-size: 1.6rem; font-weight: 800; }
    .soft-badge { background: #eef2ff; color: #4338ca; font-weight: 600; }
    .list-tile { border: none; border-bottom: 1px solid #f1f5f9; }
    .list-tile:last-child { border-bottom: none; }
    .chip { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px; background: #f8fafc; border: 1px solid #e2e8f0; }
    .chip i { color: #6366f1; }
    @media (max-width: 576px) { .metric-value { font-size: 1.4rem; } }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h2 class="fw-bold d-flex align-items-center gap-2">
                <span class="badge soft-badge rounded-pill text-uppercase">Wrapped</span>
                {{ $year }} Recap
            </h2>
            <p class="text-muted mb-0">A professional, friendly snapshot of {{ $user->first_name }}’s activity story.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <form class="d-flex justify-content-md-end" method="GET" action="{{ route('users.wrapped', $user->id) }}" aria-label="Select year to view Wrapped">
                <input type="number"
                       name="year"
                       class="form-control me-2"
                       min="2000"
                       max="{{ now()->year + 1 }}"
                       value="{{ $year }}"
                       placeholder="Year">
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-rotate-right me-1"></i> Refresh
                </button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card wrapped-hero shadow-lg">
                <div class="card-body hero-content">
                    <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between">
                        <div class="mb-3 mb-lg-0">
                            <p class="text-uppercase fw-semibold mb-1" style="letter-spacing: 1px;">Summary</p>
                            <h4 class="fw-bold mb-3">{{ $wrapped['summary'] }}</h4>
                            <div class="d-flex flex-wrap gap-2">
                                <span class="chip"><i class="fas fa-bolt"></i> Total: {{ $wrapped['total'] }}</span>
                                @if($wrapped['peak_day']['date'])
                                    <span class="chip"><i class="fas fa-calendar-day"></i> Peak Day: {{ \Carbon\Carbon::parse($wrapped['peak_day']['date'])->format('M d') }}</span>
                                @endif
                                @if($wrapped['peak_month']['month'])
                                    <span class="chip"><i class="fas fa-calendar-alt"></i> Peak Month: {{ \Carbon\Carbon::parse($wrapped['peak_month']['month'] . '-01')->format('F') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-lg-end">
                            <div class="chip bg-white text-dark">
                                <i class="fas fa-share-alt"></i>
                                Share-ready insights
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-3 col-6">
            <div class="card metric-card shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Activities</p>
                    <div class="metric-value">{{ $wrapped['total'] }}</div>
                    <small class="text-muted">Across all tracked actions</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card metric-card shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Top Activity Type</p>
                    <div class="metric-value" style="font-size:1.2rem;">
                        {{ $wrapped['top_activity_type']['value'] ?? '—' }}
                    </div>
                    <small class="text-muted">{{ $wrapped['top_activity_type']['count'] ?? 0 }} times</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card metric-card shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Top Description</p>
                    <div class="metric-value" style="font-size:1.2rem;">
                        {{ $wrapped['top_activity_description']['value'] ?? '—' }}
                    </div>
                    <small class="text-muted">{{ $wrapped['top_activity_description']['count'] ?? 0 }} times</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card metric-card shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Top Device / Platform</p>
                    <div class="metric-value" style="font-size:1.2rem;">
                        {{ $wrapped['top_device']['value'] ?? '—' }}
                        @if($wrapped['top_platform']['value'] ?? false)
                            / {{ $wrapped['top_platform']['value'] }}
                        @endif
                    </div>
                    <small class="text-muted">Browser: {{ $wrapped['top_browser']['value'] ?? '—' }}</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-2">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold">Peaks & Rhythm</h6>
                    <span class="badge bg-primary-subtle text-primary">When you were busiest</span>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item list-tile d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-calendar-day text-primary"></i>
                                <span>Peak Day</span>
                            </div>
                            <span class="fw-semibold">
                                @if($wrapped['peak_day']['date'])
                                    {{ \Carbon\Carbon::parse($wrapped['peak_day']['date'])->format('M d, Y') }}
                                    ({{ $wrapped['peak_day']['count'] }} activities)
                                @else
                                    —
                                @endif
                            </span>
                        </li>
                        <li class="list-group-item list-tile d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-calendar-alt text-primary"></i>
                                <span>Peak Month</span>
                            </div>
                            <span class="fw-semibold">
                                @if($wrapped['peak_month']['month'])
                                    {{ \Carbon\Carbon::parse($wrapped['peak_month']['month'] . '-01')->format('F Y') }}
                                    ({{ $wrapped['peak_month']['count'] }} activities)
                                @else
                                    —
                                @endif
                            </span>
                        </li>
                        <li class="list-group-item list-tile d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-globe text-primary"></i>
                                <span>Top Browser</span>
                            </div>
                            <span class="fw-semibold">
                                {{ $wrapped['top_browser']['value'] ?? '—' }} ({{ $wrapped['top_browser']['count'] ?? 0 }} times)
                            </span>
                        </li>
                        <li class="list-group-item list-tile d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-star text-primary"></i>
                                <span>Notable Detail</span>
                            </div>
                            <span class="fw-semibold">
                                {{ $wrapped['notable_detail'] ?? '—' }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold">Suggested Visuals</h6>
                    <span class="badge bg-info-subtle text-info">Ready-to-share</span>
                </div>
                <div class="card-body">
                    @if(!empty($wrapped['suggested_visuals']))
                        <div class="list-group">
                            @foreach($wrapped['suggested_visuals'] as $visual)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ ucfirst($visual['type']) }}: {{ $visual['title'] }}</h6>
                                        <p class="mb-0 text-muted">{{ $visual['description'] }}</p>
                                    </div>
                                    <span class="badge bg-primary">{{ ucfirst($visual['type']) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">No visuals suggested.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-3">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold">Highlights at a glance</h6>
                    <span class="badge bg-success-subtle text-success">User friendly</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="chip w-100 justify-content-start">
                                <i class="fas fa-user-check"></i>
                                Most frequent type:
                                <strong class="ms-1">{{ $wrapped['top_activity_type']['value'] ?? '—' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="chip w-100 justify-content-start">
                                <i class="fas fa-quote-left"></i>
                                Top description:
                                <strong class="ms-1">{{ $wrapped['top_activity_description']['value'] ?? '—' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="chip w-100 justify-content-start">
                                <i class="fas fa-mobile-alt"></i>
                                Favorite device/platform:
                                <strong class="ms-1">
                                    {{ $wrapped['top_device']['value'] ?? '—' }}
                                    @if($wrapped['top_platform']['value'] ?? false)
                                        / {{ $wrapped['top_platform']['value'] }}
                                    @endif
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
