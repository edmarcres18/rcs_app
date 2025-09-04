<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 Server Error</title>
    <style>
        :root {
            color-scheme: light dark;
            --bg: #0b1220;
            --surface: #0f1629;
            --surface-2: #121c33;
            --text: #e6edf3;
            --muted: #9aa4b2;
            --border: #1e293b;
            --accent: #f59e0b; /* 500 accent - amber */
            --accent-2: #fbbf24;
            --shadow: 0 10px 30px rgba(0,0,0,.35);
            --radius: 16px;
            --ring: 0 0 0 3px rgba(245, 158, 11, .2);
        }
        @media (prefers-color-scheme: light) {
            :root {
                --bg: #f8fafc;
                --surface: #ffffff;
                --surface-2: #f1f5f9;
                --text: #0f172a;
                --muted: #475569;
                --border: #e2e8f0;
                --shadow: 0 10px 30px rgba(2, 6, 23, .08);
                --ring: 0 0 0 3px rgba(245, 158, 11, .18);
            }
        }

        body {
            margin: 0;
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, Helvetica, Arial, "Apple Color Emoji", "Segoe UI Emoji";
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: radial-gradient(1200px 600px at 10% 10%, rgba(245, 158, 11, .08), transparent 60%),
                        radial-gradient(900px 500px at 90% 0%, rgba(251, 191, 36, .08), transparent 60%),
                        var(--bg);
            color: var(--text);
        }

        .wrapper { width: 100%; padding: 28px; box-sizing: border-box; }
        .card {
            max-width: 920px;
            margin: 0 auto;
            background: linear-gradient(180deg, var(--surface), var(--surface-2));
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 28px clamp(20px, 4vw, 40px);
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
            animation: cardIn .5s ease-out both;
        }

        .orb { position: absolute; border-radius: 50%; filter: blur(24px); opacity: .25; pointer-events: none; background: radial-gradient(circle at 30% 30%, var(--accent-2), transparent 60%); animation: float 8s ease-in-out infinite; }
        .orb.one { width: 220px; height: 220px; top: -80px; right: -60px; }
        .orb.two { width: 160px; height: 160px; bottom: -60px; left: -40px; animation-delay: -2s; }

        header.head { display: flex; align-items: center; gap: 14px; margin-bottom: 14px; }
        .code { display: inline-flex; align-items: center; gap: 10px; padding: 6px 12px; border-radius: 999px; border: 1px solid var(--border); background: color-mix(in oklab, var(--surface) 90%, var(--accent) 10%); color: var(--accent); font-weight: 700; letter-spacing: .3px; box-shadow: 0 0 0 1px color-mix(in oklab, var(--accent) 30%, transparent); }

        h1 { margin: 10px 0 6px; font-size: clamp(22px, 2.6vw, 34px); letter-spacing: .3px; }
        p { margin: 10px 0 0; line-height: 1.7; color: var(--muted); font-size: clamp(14px, 1.2vw, 16px); }
        .message { margin-top: 14px; padding: 14px 16px; border-radius: 12px; border: 1px dashed color-mix(in oklab, var(--accent) 30%, var(--border)); background: color-mix(in oklab, var(--surface) 85%, var(--accent-2) 15%); color: var(--text); }

        .illustration { margin: 16px 0 8px; display: grid; place-items: center; }
        .gear { width: clamp(72px, 10vw, 96px); height: auto; filter: drop-shadow(0 6px 16px rgba(0,0,0,.2)); transform: translateY(6px); animation: rise .7s ease-out .1s both; }

        .actions { margin-top: 18px; display: flex; flex-wrap: wrap; gap: 10px; }
        a.button { text-decoration: none; padding: 12px 16px; border-radius: 12px; border: 1px solid var(--border); font-weight: 700; }
        a.button.primary { background: linear-gradient(180deg, color-mix(in oklab, var(--accent) 88%, #fff 12%), var(--accent)); color: #fff; }
        a.button.secondary { background: transparent; color: var(--text); }
        a.button:hover { box-shadow: var(--ring); transform: translateY(-1px); transition: all .18s ease; }
        a.button:active { transform: translateY(0); }

        .footer { margin-top: 14px; display: flex; align-items: center; gap: 8px; color: var(--muted); font-size: 13px; flex-wrap: wrap; }
        .dot { width: 6px; height: 6px; background: var(--accent); border-radius: 999px; opacity: .75; }

        @keyframes cardIn { from { opacity: 0; transform: translateY(10px) scale(.98); } to { opacity: 1; transform: translateY(0) scale(1); } }
        @keyframes float { 0%,100% { transform: translateY(0) } 50% { transform: translateY(-10px) } }
        @keyframes rise { from { opacity: 0; transform: translateY(18px) scale(.9) } to { opacity: 1; transform: translateY(6px) scale(1) } }

        @media (prefers-reduced-motion: reduce) { * { animation: none !important; transition: none !important; } }
        @media (max-width: 480px) { .wrapper { padding: 18px; } .actions a.button { width: 100%; text-align: center; } }
    </style>
</head>
<body>
    <div class="wrapper">
        <main class="card" role="main" aria-labelledby="title">
            <span class="orb one"></span>
            <span class="orb two"></span>
            <header class="head">
                <div class="code" aria-label="Error code">500 • Server Error</div>
            </header>

            <div class="illustration" aria-hidden="true">
                <svg class="gear" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                    <defs>
                        <linearGradient id="g" x1="12" y1="8" x2="52" y2="56" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#f59e0b"/>
                            <stop offset="1" stop-color="#fbbf24"/>
                        </linearGradient>
                    </defs>
                    <path d="M40 8l4 4 6-2 4 8-5 3 1 6 6 3-3 9-6-1-4 5 2 6-9 4-3-5-6 1-4 6-9-4 2-6-5-4-6 2-4-9 5-3-1-6-6-3 3-8 6 1 4-5-2-6 9-4 3 5 6-1 4-6 9 4-2 6z" fill="url(#g)" opacity=".35"/>
                    <circle cx="32" cy="32" r="10" stroke="url(#g)" stroke-width="4"/>
                </svg>
            </div>

            <h1 id="title">Something went wrong</h1>
            <p>An unexpected error occurred. Please try again later. If the issue persists, contact support.</p>

            @if(!empty($message))
                <div class="message" role="note">{{ $message }}</div>
            @endif

            <div class="actions" aria-label="Quick actions">
                <a class="button primary" href="{{ url()->previous() }}">Go back</a>
                <a class="button secondary" href="{{ url('/home') }}">Home</a>
                <a class="button secondary" href="https://t.me/edmarcrescencio" rel="noopener">Contact via Telegram</a>
            </div>

            <div class="footer" aria-label="Helpful info">
                <span class="dot" aria-hidden="true"></span>
                <span>Time of error: {{ now()->toDayDateTimeString() }}</span>
            </div>
        </main>
    </div>
</body>
</html>
