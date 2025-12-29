@extends('layouts.app')

@section('title', $user->first_name . ' ' . $user->last_name . ' - ' . $year . ' Wrapped')

@section('content')
<div class="container-fluid">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h2 class="fw-bold">📈 {{ $year }} Wrapped</h2>
            <p class="text-muted mb-0">A friendly recap of {{ $user->first_name }}'s year.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <form class="d-flex justify-content-md-end" method="GET" action="{{ route('users.wrapped', $user->id) }}">
                <input type="number"
                       name="year"
                       class="form-control me-2"
                       min="2000"
                       max="{{ now()->year + 1 }}"
                       value="{{ $year }}"
                       placeholder="Year">
                <button class="btn btn-primary" type="submit">
                    <i class="fas fa-sync-alt me-1"></i> Refresh
                </button>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow-sm border-0" style="background: linear-gradient(120deg, #4f46e5, #7c3aed); color: #fff;">
                <div class="card-body">
                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between">
                        <div>
                            <p class="text-uppercase fw-semibold mb-1" style="letter-spacing: 1px;">Summary</p>
                            <h4 class="fw-bold mb-2">{{ $wrapped['summary'] }}</h4>
                            <div class="d-flex flex-wrap gap-3">
                                <span class="badge bg-light text-dark px-3 py-2">Total: {{ $wrapped['total'] }}</span>
                                @if($wrapped['peak_day']['date'])
                                    <span class="badge bg-light text-dark px-3 py-2">Peak Day: {{ \Carbon\Carbon::parse($wrapped['peak_day']['date'])->format('M d') }}</span>
                                @endif
                                @if($wrapped['peak_month']['month'])
                                    <span class="badge bg-light text-dark px-3 py-2">Peak Month: {{ \Carbon\Carbon::parse($wrapped['peak_month']['month'] . '-01')->format('F') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-end mt-3 mt-md-0">
                            <i class="fas fa-chart-pie fa-3x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-3 col-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Activities</p>
                    <h3 class="fw-bold mb-0">{{ $wrapped['total'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Top Activity Type</p>
                    <h5 class="fw-semibold mb-0">
                        {{ $wrapped['top_activity_type']['value'] ?? '—' }}
                    </h5>
                    <small class="text-muted">{{ $wrapped['top_activity_type']['count'] ?? 0 }} times</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Top Description</p>
                    <h5 class="fw-semibold mb-0">
                        {{ $wrapped['top_activity_description']['value'] ?? '—' }}
                    </h5>
                    <small class="text-muted">{{ $wrapped['top_activity_description']['count'] ?? 0 }} times</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <p class="text-muted mb-1">Top Device / Platform</p>
                    <h5 class="fw-semibold mb-0">
                        {{ $wrapped['top_device']['value'] ?? '—' }}
                        @if($wrapped['top_platform']['value'] ?? false)
                            / {{ $wrapped['top_platform']['value'] }}
                        @endif
                    </h5>
                    <small class="text-muted">{{ $wrapped['top_device']['count'] ?? 0 }} uses</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold">Peaks</h6>
                    <span class="badge bg-primary-subtle text-primary">When you were busiest</span>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Peak Day
                            <span class="fw-semibold">
                                @if($wrapped['peak_day']['date'])
                                    {{ \Carbon\Carbon::parse($wrapped['peak_day']['date'])->format('M d, Y') }}
                                    ({{ $wrapped['peak_day']['count'] }} activities)
                                @else
                                    —
                                @endif
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Peak Month
                            <span class="fw-semibold">
                                @if($wrapped['peak_month']['month'])
                                    {{ \Carbon\Carbon::parse($wrapped['peak_month']['month'] . '-01')->format('F Y') }}
                                    ({{ $wrapped['peak_month']['count'] }} activities)
                                @else
                                    —
                                @endif
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Top Browser
                            <span class="fw-semibold">
                                {{ $wrapped['top_browser']['value'] ?? '—' }} ({{ $wrapped['top_browser']['count'] ?? 0 }} times)
                            </span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Notable Detail
                            <span class="fw-semibold">
                                {{ $wrapped['notable_detail'] ?? '—' }}
                            </span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold">Suggested Visuals</h6>
                    <span class="badge bg-info-subtle text-info">Great for sharing</span>
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
</div>
@endsection
