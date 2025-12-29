@extends('layouts.app')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700&family=Pacifico&display=swap');

    .wrapped-hero {
        position: relative;
        overflow: hidden;
        border-radius: 22px;
        background: radial-gradient(circle at 20% 20%, rgba(255, 76, 48, 0.08), transparent 35%),
                    radial-gradient(circle at 80% 0%, rgba(255, 76, 48, 0.18), transparent 40%),
                    #0b0d12;
        color: #fefefe;
        padding: 26px;
        box-shadow: 0 24px 60px rgba(0,0,0,0.3);
        isolation: isolate;
        border: 1px solid rgba(255,255,255,0.06);
    }
    .wrapped-hero::before, .wrapped-hero::after {
        content: '';
        position: absolute;
        inset: 14px;
        border-radius: 18px;
        background-image: linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px),
                          linear-gradient(0deg, rgba(255,255,255,0.04) 1px, transparent 1px);
        background-size: 28px 28px;
        opacity: 0.4;
        pointer-events: none;
    }
    .wrapped-hero::after {
        inset: 0;
        background: radial-gradient(circle at 15% 85%, rgba(255, 76, 48, 0.22), transparent 35%),
                    radial-gradient(circle at 90% 30%, rgba(255, 115, 90, 0.18), transparent 38%);
        z-index: 0;
    }
    .wrapped-hero .badge-ring {
        position: absolute;
        width: 140px;
        height: 140px;
        border: 1px dashed rgba(255,255,255,0.15);
        border-radius: 50%;
        display: grid;
        place-items: center;
        color: #fefefe;
        font-size: 12px;
        letter-spacing: 1px;
        text-transform: uppercase;
        z-index: 1;
    }
    .badge-ring.left { top: -20px; left: -20px; rotate: -8deg; }
    .badge-ring.right { bottom: -10px; right: -10px; rotate: 12deg; color: #d1d5db; }
    .badge-ring svg { width: 26px; height: 26px; }
    .hero-title-script {
        font-family: 'Pacifico', cursive;
        font-size: 46px;
        color: #ff4c30;
        letter-spacing: 1px;
    }
    .hero-subtitle {
        font-family: 'Manrope', sans-serif;
        font-size: 42px;
        font-weight: 700;
        margin-bottom: 4px;
    }
    .hero-tagline {
        color: #cbd5e1;
        font-size: 14px;
        letter-spacing: 0.6px;
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
    .stat-badge {
        background: #fff4f2;
        color: #d92d20;
        border-radius: 12px;
        padding: 8px 12px;
        font-weight: 700;
        font-size: 0.95rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid rgba(217, 45, 32, 0.12);
        box-shadow: 0 6px 14px rgba(217, 45, 32, 0.12);
    }
    .milestone-item {
        border-left: 3px solid #ff4c30;
        padding-left: 12px;
        margin-bottom: 12px;
        background: #fff7f5;
        border-radius: 8px;
        padding-top: 8px;
        padding-bottom: 8px;
    }
    .chart-container {
        position: relative;
        min-height: 260px;
    }
    .year-pill {
        background: rgba(255,76,48,0.18);
        color: #ffe5df;
        padding: 8px 14px;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,0.25);
        backdrop-filter: blur(6px);
        font-weight: 700;
        letter-spacing: 0.6px;
    }
    .cta-btn {
        background: linear-gradient(120deg, #ff4c30, #ff7a64);
        color: #fff;
        font-weight: 800;
        border: none;
        border-radius: 12px;
        padding: 12px 16px;
        box-shadow: 0 10px 25px rgba(255, 76, 48, 0.28);
    }
    .cta-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 32px rgba(255, 76, 48, 0.32);
    }
    .count-up {
        font-variant-numeric: tabular-nums;
    }
    .sticker {
        position: absolute;
        width: 120px;
        border-radius: 18px;
        filter: drop-shadow(0 14px 24px rgba(0,0,0,0.25));
        z-index: 2;
        transition: transform .2s ease;
        color: #fff;
        font-family: 'Manrope', sans-serif;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border: 3px solid #fff;
        padding: 12px 14px;
    }
    .sticker:hover { transform: scale(1.03) rotate(-2deg); }
    .sticker-ship {
        bottom: -12px; left: -6px;
        background: linear-gradient(140deg, #f9322c, #ff6b4a);
        rotate: -4deg;
        text-align: left;
    }
    .sticker-ship .title { font-size: 20px; font-weight: 900; display: block; line-height: 1; }
    .sticker-ship .subtitle { font-size: 11px; font-weight: 700; opacity: 0.92; }
    .sticker-php {
        top: -10px; right: -4px;
        background: linear-gradient(145deg, #0f172a, #111827);
        color: #ff4c30;
        rotate: 6deg;
        display: grid;
        place-items: center;
        font-weight: 900;
        font-size: 22px;
    }
    .wrapped-grid-title {
        font-family: 'Manrope', sans-serif;
    }
    @media (max-width: 768px) {
        .wrapped-hero { padding: 18px; }
        .wrapped-card { margin-bottom: 12px; }
        .hero-title-script { font-size: 34px; }
        .hero-subtitle { font-size: 32px; }
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
            <button class="btn btn-outline-primary btn-sm" id="btn-share">
                <i class="fa-solid fa-share-nodes me-1"></i> Share
            </button>
        </div>
    </div>

    <div class="wrapped-hero mb-4 position-relative" id="wrapped-hero">
        <div class="badge-ring left">
            <div class="text-center">
                <div>Laravel Wrapped</div>
                <div>{{ $selectedYear }}</div>
            </div>
        </div>
        <div class="badge-ring right">
            <div class="text-center">
                <div>RCS Edition</div>
                <div>{{ $selectedYear }}</div>
            </div>
        </div>
        <img class="sticker sticker ship" alt="Ship it" src="https://dummyimage.com/220x420/f9322c/fefefe&text=Ship" />
        <img class="sticker sticker php" alt="PHP cap" src="https://dummyimage.com/200x200/0b0d12/f9322c&text=$PHP" />
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 position-relative" style="z-index:3;">
            <div>
                <div class="hero-tagline">wrapped.rcs</div>
                <div class="hero-title-script">Wrapped</div>
                <div class="hero-subtitle">Made for Laravel</div>
                <div class="d-flex flex-wrap gap-2">
                    <span class="year-pill"><i class="fa-regular fa-calendar me-2"></i>{{ $selectedYear }}</span>
                    <span class="stat-badge"><i class="fa-solid fa-bolt"></i> {{ $summary['total_activities'] }} activities</span>
                    @if($summary['top_activity_type'])
                        <span class="stat-badge"><i class="fa-solid fa-star"></i> Top: {{ ucfirst($summary['top_activity_type']['label']) }} ({{ $summary['top_activity_type']['count'] }})</span>
                    @endif
                </div>
            </div>
            <div class="text-end">
                <button class="cta-btn btn btn-lg" id="btn-download-card">
                    <i class="fa-solid fa-arrow-down me-1"></i> Download Card
                </button>
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
        document.getElementById('btn-export-png').addEventListener('click', () => exportNodeAsPng('#wrapped-hero', 'rcs-wrapped-hero.png'));
        document.getElementById('btn-download-card').addEventListener('click', () => exportNodeAsPng('body', `rcs-wrapped-{{ $selectedYear }}.png`));

        // Web share
        document.getElementById('btn-share').addEventListener('click', async () => {
            if (navigator.share) {
                await navigator.share({
                    title: `My {{ $selectedYear }} RCS Wrapped`,
                    text: 'Check out my yearly RCS Wrapped summary!',
                    url: window.location.href
                });
            } else {
                navigator.clipboard.writeText(window.location.href);
                alert('Link copied to clipboard');
            }
        });
    });

    function exportNodeAsPng(selector, filename) {
        const node = document.querySelector(selector);
        if (!node) return;
        htmlToImage.toPng(node, { pixelRatio: 2 })
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
