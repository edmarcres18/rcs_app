@extends('layouts.app')

@push('styles')
<style>
    /* Hide app chrome for immersive presentation */
    .sidebar, .main-navbar, .sidebar-collapse-btn, .notification-sidebar, .notification-backdrop { display: none !important; }
    .main-content { margin-left: 0 !important; }
    .page-content { padding: 0 !important; }

    .presentation-container {
        position: relative;
        height: 100vh;
        background: radial-gradient(1200px 600px at 10% 10%, rgba(64,112,244,0.06), transparent),
                    radial-gradient(800px 400px at 90% 90%, rgba(64,112,244,0.04), transparent);
    }
    @media (max-width: 768px){ .presentation-container { height: 100vh;} }

    .deck {
        height: 100%;
        display: grid;
        place-items: center;
        overflow: hidden;
        position: relative;
    }

    .slide {
        width: min(1100px, 92vw);
        height: min(640px, 72vh);
        background: var(--bg-card);
        border-radius: 18px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.08);
        position: absolute;
        opacity: 0;
        transform: translateX(40px) scale(.98);
        transition: opacity .45s ease, transform .45s ease;
        display: flex;
        flex-direction: column;
    }

    .slide.active { opacity: 1; transform: translateX(0) scale(1); z-index: 2; }
    .slide.prev { transform: translateX(-40px) scale(.98); z-index: 1; }

    .slide-header {
        padding: 22px 28px;
        border-bottom: 1px solid var(--border-color);
        display: flex; align-items: center; gap: 12px;
    }
    .slide-title { font-size: clamp(18px, 2.6vw, 28px); font-weight: 700; color: var(--primary-color); margin: 0; }

    .slide-body { flex: 1; padding: 26px 28px 32px; overflow: auto; }
    .slide-body h3, .slide-body h4 { margin-top: 0; }
    .slide-body ul { margin: 0; padding-left: 1.2rem; }
    .slide-body li { margin: .4rem 0; line-height: 1.6; }

    .slide-footer {
        padding: 14px 20px; border-top: 1px solid var(--border-color);
        display: flex; align-items: center; justify-content: space-between; color: var(--text-light);
        font-size: 13px;
    }

    .controls {
        position: absolute; inset: auto 0 20px 0; display: flex; justify-content: center; gap: 8px; pointer-events: none;
    }
    .control-btn {
        pointer-events: auto; width: 42px; height: 42px; border-radius: 50%; border: none; background: var(--bg-card);
        box-shadow: 0 8px 24px rgba(0,0,0,0.08); display: grid; place-items: center; color: var(--primary-color);
        transition: transform .2s ease, background .2s ease; cursor: pointer;
    }
    .control-btn:hover { transform: translateY(-1px); background: var(--bg-hover); }
    .control-btn:active { transform: translateY(0); }

    .progress-bar { position: absolute; left: 50%; transform: translateX(-50%); bottom: 12px; width: min(600px, 90vw); height: 4px; background: var(--bg-hover); border-radius: 8px; overflow: hidden; }
    .progress-fill { height: 100%; width: 0%; background: var(--primary-color); transition: width .3s ease; }

    /* Presenter help */
    .key-hints { position: absolute; top: 12px; right: 12px; background: rgba(255,255,255,.9); border: 1px solid var(--border-color); border-radius: 10px; padding: 8px 10px; font-size: 12px; color: var(--text-muted); display: flex; align-items: center; gap: 8px; }
    .kbd { background: #f1f3f5; border: 1px solid #e3e6ea; border-bottom-width: 3px; padding: 2px 6px; border-radius: 6px; font-weight: 700; color: #5a6a85; }

    /* Markdown typography within slides */
    .slide-body h3 { font-size: clamp(18px, 2.2vw, 24px); color: var(--text-color); }
    .slide-body p { color: var(--text-muted); }

    /* Mobile fill viewport better */
    @media (max-width: 768px) {
        .slide { width: 96vw; height: 68vh; border-radius: 14px; }
    }
</style>
@endpush

@section('content')
<div class="presentation-container">
    <div class="deck" id="deck" aria-live="polite" aria-roledescription="carousel">
        <div class="key-hints">
            <span class="kbd">←</span> <span>/</span> <span class="kbd">→</span>
            <span>to navigate</span>
        </div>

        <template id="slide-template">
            <section class="slide" role="group" aria-roledescription="slide">
                <header class="slide-header">
                    <i class="fa-solid fa-display text-primary"></i>
                    <h2 class="slide-title"></h2>
                </header>
                <div class="slide-body"></div>
                <footer class="slide-footer">
                    <span class="slide-count"></span>
                    <span class="brand">{{ $appName }}</span>
                </footer>
            </section>
        </template>

        <div class="controls">
            <button class="control-btn" id="prevBtn" aria-label="Previous slide"><i class="fa-solid fa-arrow-left"></i></button>
            <button class="control-btn" id="nextBtn" aria-label="Next slide"><i class="fa-solid fa-arrow-right"></i></button>
        </div>
        <div class="progress-bar" aria-hidden="true"><div class="progress-fill" id="progressFill"></div></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const markdown = @json($markdownContent);

        // Split slides by headings like "### Slide X: Title" including the very first heading
        const rawSlides = markdown.trim().split(/\n(?=###\s+Slide\s+\d+\s*:\s*)|(?=^###\s+Slide\s+\d+\s*:\s*)/gm).filter(s => s.trim().length);

        // Minimal Markdown rendering for bullet points and headings
        function renderMarkdown(md) {
            let html = md
                .replace(/^###\s+(.+)$/m, '<h3>$1</h3>')
                .replace(/^\-\s+\*\*(.+?)\*\*\:\s*(.+)$/gm, '<li><strong>$1</strong>: $2</li>')
                .replace(/^\-\s+\*\*(.+?)\*\*$/gm, '<li><strong>$1</strong></li>')
                .replace(/^\-\s+(.+)$/gm, '<li>$1</li>');

            // wrap orphan lis into ul
            html = html.replace(/(?:^(?:<li>.*<\/li>)(?:\r?\n)?)+/gms, match => `<ul>${match}\n</ul>`);
            return html;
        }

        const slides = rawSlides.map(chunk => {
            const lines = chunk.trim().split(/\n/);
            const titleLine = lines.shift() || '';
            const title = titleLine.replace(/^###\s+/, '').trim();
            const bodyMd = lines.join('\n');
            return { title, bodyHtml: renderMarkdown(bodyMd) };
        });

        const deck = document.getElementById('deck');
        const template = document.getElementById('slide-template');
        const nextBtn = document.getElementById('nextBtn');
        const prevBtn = document.getElementById('prevBtn');
        const progressFill = document.getElementById('progressFill');

        let index = 0;
        const nodes = [];

        function mountSlides() {
            slides.forEach((s, i) => {
                const node = template.content.firstElementChild.cloneNode(true);
                node.querySelector('.slide-title').textContent = s.title;
                node.querySelector('.slide-body').innerHTML = s.bodyHtml;
                node.querySelector('.slide-count').textContent = `${i+1} / ${slides.length}`;
                if (i === 0) node.classList.add('active');
                deck.appendChild(node);
                nodes.push(node);
            });
            updateProgress();
        }

        function go(to) {
            if (to < 0 || to >= slides.length || to === index) return;
            const current = nodes[index];
            const target = nodes[to];
            current.classList.remove('active');
            current.classList.add(to > index ? 'prev' : '');
            target.classList.add('active');
            index = to;
            updateProgress();
        }

        function next() { go(index + 1); }
        function prev() { go(index - 1); }

        function updateProgress() {
            const pct = ((index + 1) / slides.length) * 100;
            progressFill.style.width = pct + '%';
        }

        // Keyboard and click navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight' || e.key === 'PageDown' || e.key === ' ') { e.preventDefault(); next(); }
            if (e.key === 'ArrowLeft' || e.key === 'PageUp' || e.key === 'Backspace') { e.preventDefault(); prev(); }
            if (e.key.toLowerCase() === 'f') {
                const root = document.documentElement;
                if (!document.fullscreenElement) {
                    (root.requestFullscreen && root.requestFullscreen()) ||
                    (root.webkitRequestFullscreen && root.webkitRequestFullscreen());
                } else {
                    (document.exitFullscreen && document.exitFullscreen()) ||
                    (document.webkitExitFullscreen && document.webkitExitFullscreen());
                }
            }
        });
        nextBtn.addEventListener('click', next);
        prevBtn.addEventListener('click', prev);

        // Touch swipe support
        let startX = 0; let endX = 0;
        deck.addEventListener('touchstart', e => { startX = e.changedTouches[0].screenX; }, { passive: true });
        deck.addEventListener('touchend', e => {
            endX = e.changedTouches[0].screenX;
            if (startX - endX > 40) next();
            if (endX - startX > 40) prev();
        }, { passive: true });

        mountSlides();
    });
</script>
@endpush


