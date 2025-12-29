@extends('layouts.app')

@push('styles')
<style>
    :root {
        --laravel-red: #f55247;
        --ink: #101828;
        --muted: #6b7280;
    }
    .wrapped-card {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 12px 30px rgba(0,0,0,0.08);
        border: 1px solid rgba(0,0,0,0.04);
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .wrapped-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 16px 40px rgba(0,0,0,0.10);
    }
    .count-up { font-variant-numeric: tabular-nums; }
    .chart-container { position: relative; min-height: 260px; }
    .milestone-item { border-left: 3px solid var(--laravel-red); padding-left: 12px; margin-bottom: 12px; }

    /* Laravel Wrapped inspired export card */
    .wrapped-stage {
        background: #f7f9fb;
        padding: 18px;
        border-radius: 26px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.08);
        max-width: 1380px;
        width: 100%;
        margin: 0 auto;
    }
    .wrapped-export-card {
        position: relative;
        overflow: hidden;
        background: radial-gradient(circle at 18% 22%, rgba(245,82,71,0.10), transparent 40%),
                    radial-gradient(circle at 82% 28%, rgba(16,185,129,0.09), transparent 36%),
                    radial-gradient(circle at 72% 82%, rgba(37,99,235,0.07), transparent 44%),
                    #f9fafb;
        border-radius: 24px;
        border: 1px solid #e5e7eb;
        padding: 36px 40px 46px;
        box-shadow: 0 18px 50px rgba(0,0,0,0.10);
        isolation: isolate;
        animation: bgShift 12s ease-in-out infinite alternate;
    }
    .wrapped-export-card::before,
    .wrapped-export-card::after {
        content: '';
        position: absolute;
        border-radius: 50%;
        border: 1px dashed #e5e7eb;
        inset: 14px;
        opacity: 0.7;
        z-index: 0;
    }
    .wrapped-export-card::after {
        inset: 30px;
        opacity: 0.45;
    }
    .wrapped-export-card .floater {
        position: absolute;
        border-radius: 50%;
        border: 1px dashed rgba(0,0,0,0.08);
        animation: float 8s ease-in-out infinite;
        z-index: 0;
        pointer-events: none;
    }
    .floater.one { width: 140px; height: 140px; top: 14%; left: -50px; }
    .floater.two { width: 110px; height: 110px; bottom: 10%; right: -40px; animation-duration: 10s; }
    .floater.three { width: 80px; height: 80px; top: 50%; right: 18%; animation-duration: 9s; }
    .sparkle {
        position: absolute;
        width: 10px;
        height: 10px;
        background: radial-gradient(circle, #f97316 0%, #f55247 40%, transparent 60%);
        border-radius: 50%;
        box-shadow: 0 0 12px rgba(245,82,71,0.55);
        animation: sparkle 2.6s ease-in-out infinite;
        z-index: 1;
        pointer-events: none;
    }
    .sparkle.s1 { top: 20%; left: 18%; animation-delay: 0.1s; }
    .sparkle.s2 { top: 36%; right: 22%; animation-delay: 0.5s; }
    .sparkle.s3 { bottom: 18%; left: 32%; animation-delay: 0.9s; }
    .sparkle.s4 { bottom: 26%; right: 30%; animation-delay: 1.2s; }
    .wrapped-pill {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 999px;
        padding: 8px 16px;
        font-weight: 700;
        color: var(--ink);
        display: inline-flex;
        align-items: center;
        gap: 10px;
        z-index: 2;
        position: relative;
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
    }
    .wrapped-stamp {
        position: absolute;
        width: 180px;
        height: 180px;
        border-radius: 50%;
        border: 2px dashed #d1d5db;
        color: #9ca3af;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        display: grid;
        place-items: center;
        z-index: 1;
    }
    .wrapped-stamp.left { top: -40px; left: -40px; }
    .wrapped-stamp.right { bottom: -40px; right: -40px; }
    .wrapped-stamp .icon {
        font-size: 42px;
        color: #9ca3af;
    }
    .wrapped-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 18px;
        z-index: 2;
        position: relative;
    }
    .wrapped-title {
        font-size: clamp(30px, 5vw, 48px);
        font-weight: 900;
        color: var(--laravel-red);
        text-align: center;
        letter-spacing: -0.5px;
        margin: 14px 0 6px;
    }
    .wrapped-subtitle {
        font-size: clamp(22px, 4vw, 34px);
        font-weight: 800;
        color: var(--ink);
        text-align: center;
        margin: 0;
    }
    .wrapped-tagline {
        text-align: center;
        color: var(--laravel-red);
        font-weight: 700;
        letter-spacing: 0.5px;
        margin-top: 8px;
    }
    .wrapped-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 14px;
    }
    .wrapped-stat {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 16px;
        padding: 14px 16px;
        display: grid;
        grid-template-columns: 48px 1fr;
        align-items: center;
        gap: 8px;
        box-shadow: 0 10px 24px rgba(0,0,0,0.06);
    }
    .wrapped-stat .icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: rgba(245,82,71,0.12);
        color: var(--laravel-red);
        display: grid;
        place-items: center;
        font-size: 18px;
        font-weight: 800;
        box-shadow: 0 6px 14px rgba(0,0,0,0.06);
        animation: iconPulse 3.4s ease-in-out infinite;
    }
    .wrapped-stat .text { display: flex; flex-direction: column; gap: 2px; }
    .wrapped-stat .label { color: var(--muted); font-size: 0.95rem; letter-spacing: 0.1px; }
    .wrapped-stat .value { font-size: 1.5rem; font-weight: 900; color: var(--ink); }
    .wrapped-stat .hint { color: #9ca3af; font-size: 0.9rem; }
    .wrapped-user {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 8px 12px;
        font-weight: 700;
    }
    @media (max-width: 768px) {
        .wrapped-export-card { padding: 20px; }
        .wrapped-stamp { display: none; }
        .wrapped-grid { gap: 14px; }
        .wrapped-stat { grid-template-columns: 1fr; align-items: flex-start; }
    }

    @keyframes bgShift {
        0% { background-position: 0 0, 0 0, 0 0, 0 0; }
        100% { background-position: 20px 10px, -18px 12px, 16px -10px, 0 0; }
    }
    @keyframes float {
        0% { transform: translateY(0px) translateX(0px) rotate(0deg); opacity: 0.7; }
        50% { transform: translateY(-10px) translateX(6px) rotate(3deg); opacity: 0.9; }
        100% { transform: translateY(0px) translateX(0px) rotate(0deg); opacity: 0.7; }
    }
    @keyframes iconPulse {
        0% { transform: translateY(0); box-shadow: 0 6px 14px rgba(0,0,0,0.06); }
        50% { transform: translateY(-2px); box-shadow: 0 10px 18px rgba(0,0,0,0.10); }
        100% { transform: translateY(0); box-shadow: 0 6px 14px rgba(0,0,0,0.06); }
    }
    @keyframes sparkle {
        0% { transform: scale(0.8); opacity: 0.7; }
        50% { transform: scale(1.3); opacity: 1; }
        100% { transform: scale(0.8); opacity: 0.6; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-3 align-items-center">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Wrapped</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold mb-0">RCS Wrapped</h1>
        </div>
        <div class="col-auto d-flex gap-2">
            <form method="GET" action="{{ route('wrapped.index') }}" class="d-flex align-items-center">
                <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach($availableYears as $year)
                        <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                    @if($availableYears->isEmpty())
                        <option value="{{ $selectedYear }}" selected>{{ $selectedYear }}</option>
                    @endif
                </select>
            </form>
            <button class="btn btn-outline-secondary btn-sm" id="btn-export-png">
                <i class="fa-solid fa-image me-1"></i> Export PNG
            </button>
            <a class="btn btn-outline-primary btn-sm" id="btn-share" href="{{ route('wrapped.share', ['user' => $user->id, 'year' => $selectedYear]) }}" target="_blank" rel="noopener" data-share-url="{{ route('wrapped.share', ['user' => $user->id, 'year' => $selectedYear]) }}">
                <i class="fa-solid fa-share-nodes me-1"></i> Share
            </a>
        </div>
    </div>

    <div class="wrapped-stage mb-4" id="wrapped-stage">
        <div class="wrapped-export-card" id="wrapped-card">
            <div class="floater one"></div>
            <div class="floater two"></div>
            <div class="floater three"></div>
            <div class="sparkle s1"></div>
            <div class="sparkle s2"></div>
            <div class="sparkle s3"></div>
            <div class="sparkle s4"></div>
            <div class="wrapped-stamp left">Laravel Wrapped</div>
            <div class="wrapped-stamp right"><i class="fa-solid fa-cube icon"></i></div>
            <div class="wrapped-grid">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="wrapped-pill"><i class="fa-regular fa-calendar"></i> {{ $selectedYear }} RCS Wrapped</span>
                    <span class="wrapped-user"><i class="fa-regular fa-user"></i> {{ $user->full_name ?? $user->email }}</span>
                </div>
                <div class="text-center">
                    <div class="text-muted small mb-1">wrapped.rcs</div>
                    <div class="wrapped-title">Wrapped</div>
                    <p class="wrapped-subtitle">Made for {{ config('app.name', 'RCS') }}</p>
                    <div class="wrapped-tagline">Laravel · Team · Productivity</div>
                </div>
                <div class="wrapped-stats">
                    <div class="wrapped-stat">
                        <div class="icon"><i class="fa-solid fa-bolt"></i></div>
                        <div class="text">
                            <div class="label">Total Activities</div>
                            <div class="value">{{ number_format($summary['total_activities']) }}</div>
                            <div class="hint">{{ count($summary['activity_types']) }} activity types</div>
                        </div>
                    </div>
                    <div class="wrapped-stat">
                        <div class="icon" style="background:rgba(16,185,129,0.15); color:#0f766e;"><i class="fa-solid fa-star"></i></div>
                        <div class="text">
                            <div class="label">Top Activity</div>
                            <div class="value">{{ $summary['top_activity_type']['label'] ?? '—' }}</div>
                            <div class="hint">{{ $summary['top_activity_type']['count'] ?? '' }} times</div>
                        </div>
                    </div>
                    <div class="wrapped-stat">
                        <div class="icon" style="background:rgba(37,99,235,0.15); color:#1d4ed8;"><i class="fa-regular fa-calendar"></i></div>
                        <div class="text">
                            <div class="label">Peak Day</div>
                            <div class="value">{{ $summary['peak_day']['date'] ?? '—' }}</div>
                            <div class="hint">{{ $summary['peak_day']['count'] ?? '' }} actions</div>
                        </div>
                    </div>
                    <div class="wrapped-stat">
                        <div class="icon" style="background:rgba(249,115,22,0.18); color:#c2410c;"><i class="fa-solid fa-chart-line"></i></div>
                        <div class="text">
                            <div class="label">Peak Month</div>
                            <div class="value">{{ $summary['peak_month']['label'] ?? '—' }}</div>
                            <div class="hint">{{ $summary['peak_month']['count'] ?? '' }} actions</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-8">
            <div class="wrapped-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Activity Highlights</h5>
                    <small class="text-muted">Professional overview</small>
                </div>
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="text-muted small">Total Activities</div>
                        <div class="h3 fw-bold count-up" data-count="{{ $summary['total_activities'] }}">{{ $summary['total_activities'] }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted small">Peak Day</div>
                        <div class="h6 fw-semibold">{{ $summary['peak_day']['date'] ?? '—' }}</div>
                        <small class="text-muted">{{ $summary['peak_day']['count'] ?? '' }}</small>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted small">Peak Month</div>
                        <div class="h6 fw-semibold">{{ $summary['peak_month']['label'] ?? '—' }}</div>
                        <small class="text-muted">{{ $summary['peak_month']['count'] ?? '' }}</small>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted small">Top Activity</div>
                        <div class="h6 fw-semibold">{{ $summary['top_activity_type']['label'] ?? '—' }}</div>
                        <small class="text-muted">{{ $summary['top_activity_type']['count'] ?? '' }}</small>
                    </div>
                </div>

                <div class="chart-container mt-3">
                    <canvas id="chart-activities"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="wrapped-card p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Top Descriptions</h5>
                    <i class="fa-solid fa-list text-muted"></i>
                </div>
                @forelse($summary['top_activity_descriptions'] as $item)
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="fw-semibold">{{ $item['label'] }}</div>
                        <div class="text-muted small">{{ $item['count'] }} ({{ $item['percentage'] }}%)</div>
                    </div>
                @empty
                    <p class="text-muted small mb-0">No activity descriptions found.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12 col-lg-4">
            <div class="wrapped-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Devices</h5>
                    <i class="fa-solid fa-mobile-screen text-muted"></i>
                </div>
                <div class="chart-container">
                    <canvas id="chart-devices"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="wrapped-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Platforms</h5>
                    <i class="fa-solid fa-laptop-code text-muted"></i>
                </div>
                <div class="chart-container">
                    <canvas id="chart-platforms"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="wrapped-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Browsers</h5>
                    <i class="fa-solid fa-globe text-muted"></i>
                </div>
                <div class="chart-container">
                    <canvas id="chart-browsers"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12 col-lg-6">
            <div class="wrapped-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Monthly Activity</h5>
                    <i class="fa-solid fa-chart-line text-muted"></i>
                </div>
                <div class="chart-container">
                    <canvas id="chart-monthly"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="wrapped-card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Milestones</h5>
                    <span class="text-muted small">Highlights & achievements</span>
                </div>
                @forelse($summary['milestones'] as $milestone)
                    <div class="milestone-item">
                        <div class="fw-semibold">{{ $milestone }}</div>
                    </div>
                @empty
                    <p class="text-muted mb-0">No milestones captured yet. Keep achieving!</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html-to-image@1.11.11/dist/html-to-image.min.js"></script>
<script>
    const summary = @json($summary);

    const palette = ['#2563eb','#22c55e','#f59e0b','#ec4899','#8b5cf6','#0ea5e9','#14b8a6','#f97316'];

    function donutChart(ctxId, items) {
        const ctx = document.getElementById(ctxId);
        if (!ctx) return;
        const labels = items.map(i => i.label);
        const data = items.map(i => i.count);
        return new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    data,
                    backgroundColor: palette,
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                },
                cutout: '65%',
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const shareUrl = document.getElementById('btn-share')?.dataset.shareUrl || "{{ route('wrapped.share', ['user' => $user->id, 'year' => $selectedYear]) }}";

        // Activities bar
        new Chart(document.getElementById('chart-activities'), {
            type: 'bar',
            data: {
                labels: summary.activity_types.map(i => i.label),
                datasets: [{
                    label: 'Counts',
                    data: summary.activity_types.map(i => i.count),
                    backgroundColor: palette[0],
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });

        donutChart('chart-devices', summary.devices);
        donutChart('chart-platforms', summary.platforms);
        donutChart('chart-browsers', summary.browsers);

        // Monthly line
        new Chart(document.getElementById('chart-monthly'), {
            type: 'line',
            data: {
                labels: summary.monthly_activity.labels,
                datasets: [{
                    label: 'Monthly',
                    data: summary.monthly_activity.data,
                    borderColor: palette[0],
                    backgroundColor: 'rgba(37, 99, 235, 0.15)',
                    tension: 0.35,
                    fill: true,
                    borderWidth: 3,
                    pointRadius: 4,
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });

        // Count-up animation
        document.querySelectorAll('.count-up').forEach(el => {
            const target = parseInt(el.dataset.count || '0', 10);
            let current = 0;
            const step = Math.max(1, Math.floor(target / 40));
            const interval = setInterval(() => {
                current += step;
                if (current >= target) {
                    current = target;
                    clearInterval(interval);
                }
                el.textContent = current.toLocaleString();
            }, 20);
        });

        // Export PNG of hero
        const exportOpts = { width: 1920, height: 1080 };
        document.getElementById('btn-export-png').addEventListener('click', () => exportNodeAsPng('#wrapped-stage', 'rcs-wrapped-card.png', exportOpts));
        document.getElementById('btn-download-card').addEventListener('click', () => exportNodeAsPng('#wrapped-stage', `rcs-wrapped-{{ $selectedYear }}.png`, exportOpts));

        // Web share
        document.getElementById('btn-share').addEventListener('click', async () => {
            try {
                if (navigator.share) {
                    await navigator.share({
                        title: `My {{ $selectedYear }} RCS Wrapped`,
                        text: 'Check out my yearly RCS Wrapped summary!',
                        url: shareUrl
                    });
                    return;
                }
            } catch (err) {
                // fall through to fallback
            }

            try {
                await navigator.clipboard.writeText(shareUrl);
                alert('Share link copied to clipboard');
            } catch (err) {
                window.open(shareUrl, '_blank', 'noopener');
            }
        });
    });

    function exportNodeAsPng(selector, filename, size = { width: 1920, height: 1080 }) {
        const node = document.querySelector(selector);
        if (!node) return;
        htmlToImage.toPng(node, {
                pixelRatio: 1,
                width: size.width,
                height: size.height,
                style: {
                    width: `${size.width}px`,
                    height: `${size.height}px`,
                }
            })
            .then(dataUrl => {
                const link = document.createElement('a');
                link.download = filename;
                link.href = dataUrl;
                link.click();
            })
            .catch(console.error);
    }
</script>
@endpush
