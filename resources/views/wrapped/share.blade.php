<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RCS Wrapped</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --laravel-red: #f55247;
            --ink: #101828;
            --muted: #6b7280;
        }
        body {
            background: #eef2f7;
            margin: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }
        .d-flex { display: flex; }
        .justify-content-between { justify-content: space-between; }
        .align-items-center { align-items: center; }
        .flex-wrap { flex-wrap: wrap; }
        .gap-2 { gap: 0.5rem; }
        .text-center { text-align: center; }
        .text-muted { color: #6b7280; }
        .small { font-size: 0.9rem; }

        .share-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 14px;
        }
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
            padding: clamp(22px, 3vw, 36px) clamp(20px, 3vw, 40px) clamp(28px, 4vw, 46px);
            box-shadow: 0 18px 50px rgba(0,0,0,0.10);
            isolation: isolate;
            max-width: 1240px;
            margin: 0 auto;
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
        .wrapped-export-card::after { inset: 30px; opacity: 0.45; }
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
        .wrapped-grid { display: grid; grid-template-columns: 1fr; gap: 18px; position: relative; z-index: 2; }
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
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        }
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
        .wrapped-title { font-size: clamp(30px, 5vw, 48px); font-weight: 900; color: var(--laravel-red); text-align: center; letter-spacing: -0.5px; margin: 14px 0 6px; }
        .wrapped-subtitle { font-size: clamp(22px, 4vw, 34px); font-weight: 800; color: var(--ink); text-align: center; margin: 0; }
        .wrapped-tagline { text-align: center; color: var(--laravel-red); font-weight: 700; letter-spacing: 0.5px; margin-top: 8px; }
        .wrapped-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: clamp(10px, 2vw, 16px); }
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
        .wrapped-stamp .icon { font-size: 42px; color: #9ca3af; }
        @media (max-width: 1024px) {
            .wrapped-stage { padding: 14px; }
            .wrapped-export-card { max-width: 100%; }
            .wrapped-stats { grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); }
        }
        @media (max-width: 768px) {
            .wrapped-stage { padding: 10px; border-radius: 18px; }
            .wrapped-export-card { padding: 18px 16px 22px; border-radius: 18px; }
            .wrapped-stamp { display: none; }
            .wrapped-grid { gap: 14px; }
            .wrapped-stat { grid-template-columns: 1fr; align-items: flex-start; }
            .wrapped-pill { width: 100%; justify-content: center; }
            .wrapped-user { width: 100%; justify-content: center; }
            .wrapped-title { font-size: clamp(28px, 7vw, 42px); }
            .wrapped-subtitle { font-size: clamp(20px, 6vw, 30px); }
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
</head>
<body>
<div class="share-wrapper">
    <div class="wrapped-stage" id="wrapped-stage">
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
                    <span class="wrapped-user"><i class="fa-regular fa-user"></i> {{ $displayName ?? $user->email }}</span>
                </div>
                <div class="text-center">
                    <div class="text-muted small mb-1">wrapped.rcs</div>
                    <div class="wrapped-title">Wrapped</div>
                    <p class="wrapped-subtitle">Made for {{ config('app.name', 'RCS') }}</p>
                    <div class="wrapped-tagline">MHRPCI · MHRHCI · RCS</div>
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
</div>
</body>
</html>
