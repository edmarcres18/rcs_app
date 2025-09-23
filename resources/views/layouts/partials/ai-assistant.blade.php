<!-- RCS Assistant Chat Widget -->
@if (config('app.ai_assistant_enabled', false))
<style>
  .rcs-assistant-toggle{position:fixed;right:24px;bottom:24px;z-index:20000;border-radius:50%;width:56px;height:56px;display:flex;align-items:center;justify-content:center;color:#fff;background:#2563eb;box-shadow:0 10px 20px rgba(0,0,0,.2);cursor:pointer}
  .rcs-assistant-toggle:hover{background:#1d4ed8}
  .rcs-assistant-panel{position:fixed;right:24px;bottom:90px;width:360px;max-width:calc(100vw - 48px);height:520px;max-height:70vh;border-radius:14px;background:#fff;box-shadow:0 15px 30px rgba(0,0,0,.2);z-index:20001;display:none;overflow:hidden}
  .rcs-assistant-panel.active{display:flex;flex-direction:column}
  .rcs-assistant-header{padding:12px 14px;border-bottom:1px solid #e5e7eb;display:flex;align-items:center;justify-content:space-between;background:linear-gradient(135deg,#2563eb 0%,#1e40af 100%);color:#fff}
  .rcs-assistant-body{flex:1;overflow:auto;padding:12px;background:#f8fafc}
  .rcs-assistant-input{display:flex;gap:8px;padding:10px;border-top:1px solid #e5e7eb;background:#fff}
  .rcs-assistant-input textarea{flex:1;resize:none;max-height:120px;height:42px;border:1px solid #e5e7eb;border-radius:10px;padding:8px 10px}
  .rcs-assistant-msg{background:#fff;border-radius:10px;padding:10px 12px;margin-bottom:10px;border:1px solid #eef2f7}
  .rcs-assistant-msg.user{background:#e0ecff;border-color:#d6e6ff}
  .rcs-assistant-samples{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:10px}
  .rcs-assistant-sample{background:#eef2ff;color:#1e3a8a;border:1px solid #dbeafe;border-radius:999px;padding:8px 10px;font-size:12px;cursor:pointer}
  .rcs-assistant-send{background:#2563eb;color:#fff;border:none;border-radius:10px;padding:0 14px;min-width:44px}
  @media (max-width:575px){.rcs-assistant-panel{right:10px;left:10px;width:auto;bottom:90px}}
</style>
@php
  $storageLogo = 'storage/app_logo/logo.png';
  $publicLogo = 'images/app_logo/logo.png';
  $assistantLogoPathToggle = file_exists(public_path($storageLogo))
      ? versioned_asset($storageLogo)
      : versioned_asset($publicLogo);
@endphp
<div id="rcsAssistantToggle" class="rcs-assistant-toggle" title="RCS Assistant">
  <img src="{{ $assistantLogoPathToggle }}" alt="{{ config('app.name') }} Logo" style="height:22px;width:auto;object-fit:contain;display:block">
</div>
<div id="rcsAssistantPanel" class="rcs-assistant-panel" aria-live="polite">
  @php
    $storageLogo = 'storage/app_logo/logo.png';
    $publicLogo = 'images/app_logo/logo.png';
    $assistantLogoPath = file_exists(public_path($storageLogo))
        ? versioned_asset($storageLogo)
        : versioned_asset($publicLogo);
  @endphp
  <div class="rcs-assistant-header">
    <div style="display:flex;align-items:center;gap:8px">
      <img src="{{ $assistantLogoPath }}" alt="{{ config('app.name') }} Logo" style="height:18px;width:auto;object-fit:contain;display:block">
      <strong>RCS Assistant</strong>
    </div>
    <button id="rcsAssistantClose" class="btn btn-sm btn-light" style="color:#1e293b">Close</button>
  </div>
  <div class="rcs-assistant-body" id="rcsAssistantBody">
    <div class="rcs-assistant-msg">
      Hi! Ask me anything about using the app. I use the in-app Help as my knowledge base.
    </div>
    <div class="rcs-assistant-samples">
      <button class="rcs-assistant-sample" data-q="How do I create and send an instruction?">How do I create and send an instruction?</button>
      <button class="rcs-assistant-sample" data-q="How do I enable Telegram notifications?">How do I enable Telegram notifications?</button>
      <button class="rcs-assistant-sample" data-q="How do I verify my email using OTP?">How do I verify my email using OTP?</button>
    </div>
  </div>
  <div class="rcs-assistant-input">
    <textarea id="rcsAssistantInput" placeholder="Type your question..."></textarea>
    <button id="rcsAssistantSend" class="rcs-assistant-send"><i class="fas fa-paper-plane"></i></button>
  </div>
</div>
<script>
(function(){
  const toggle = document.getElementById('rcsAssistantToggle');
  const panel = document.getElementById('rcsAssistantPanel');
  const closeBtn = document.getElementById('rcsAssistantClose');
  const body = document.getElementById('rcsAssistantBody');
  const input = document.getElementById('rcsAssistantInput');
  const send = document.getElementById('rcsAssistantSend');

  const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  function alignWithRateUs(){
    try{
      const rateBtn = document.getElementById('rateUsButton') || document.querySelector('[data-role="rate-us"]');
      if(!rateBtn) return;
      // If the rate button is placed inside the sidebar, do not align; keep assistant fixed on the right
      if (rateBtn.closest && rateBtn.closest('.sidebar')) {
        return;
      }
      const r = rateBtn.getBoundingClientRect();
      const right = Math.max(16, Math.round(window.innerWidth - r.right));
      const bottom = Math.max(16, Math.round(window.innerHeight - r.bottom));
      // Keep an 12px gap above the rate button
      const gap = 12;
      toggle.style.right = right + 'px';
      toggle.style.bottom = (bottom + r.height + gap) + 'px';
      // Match size if different
      const size = Math.round(Math.max(r.width, r.height));
      if(size >= 40 && size <= 80){
        toggle.style.width = size + 'px';
        toggle.style.height = size + 'px';
      }
    }catch(e){ /* noop */ }
  }

  function escapeHtml(str){
    return str
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function formatAnswer(text){
    let safe = escapeHtml(text).trim();
    // Convert markdown bold **text** to <strong>text</strong> after escaping HTML
    safe = safe.replace(/\*\*(.+?)\*\*/g, '<strong>$1<\/strong>');
    // Linkify URLs and emails with blue color
    safe = linkifyBlue(safe);
    const lines = safe.split(/\r?\n/).filter(l=>l.trim().length>0);
    const fragments = [];
    let currentOl = null; let lastIndex = 0;

    function closeOl(){ if(currentOl){ fragments.push('</ol>'); currentOl=null; } }

    let inImagesBlock = false;
    for(const raw of lines){
      const line = raw.trim();
      // Headings like "Overview:" or markdown headings like "## Overview"
      const labeledHeadingMatch = /^(Overview|Steps|Notes|Result|Tip|Reminder|Details|Summary):\s*$/i.exec(line);
      const mdHeadingMatch = /^#{1,6}\s+(.+)$/.exec(line);
      const numMatch = /^(\d+(?:\.\d+)*)\.\s+(.*)$/.exec(line);
      const imagesHeader = /^Images:\s*$/i.test(line);
      const imgToken = /^\[IMG\s+src=\"([^\"]+)\"(?:\s+alt=\"([^\"]*)\")?\s*\]$/.exec(line);
      if(labeledHeadingMatch){
        closeOl();
        const title = labeledHeadingMatch[1];
        fragments.push(`<div style=\"font-weight:700;color:#0f172a;margin:6px 0 4px\">${title}</div>`);
        continue;
      }
      if(mdHeadingMatch){
        closeOl();
        const title = mdHeadingMatch[1];
        fragments.push(`<div style=\"font-weight:700;color:#0f172a;margin:6px 0 4px\">${title}</div>`);
        continue;
      }
      if(imagesHeader){
        closeOl();
        inImagesBlock = true;
        fragments.push(`<div style=\"font-weight:700;color:#0f172a;margin:6px 0 4px\">Images</div>`);
        continue;
      }
      if(imgToken){
        closeOl();
        const src = imgToken[1];
        const alt = imgToken[2] || '';
        const figure = `
          <figure style="margin:8px 0;padding:8px;border:1px solid #e5e7eb;border-radius:10px;background:#fff;box-shadow:0 4px 10px rgba(0,0,0,0.04)">
            <a href="${src}" target="_blank" rel="noopener noreferrer">
              <img src="${src}" alt="${alt}" loading="lazy" decoding="async" style="max-width:100%;height:auto;border-radius:8px;display:block;margin:0 auto" />
            </a>
            ${alt ? `<figcaption style="margin-top:6px;color:#64748b;font-size:12px;text-align:center">${alt}</figcaption>` : ''}
          </figure>`;
        fragments.push(figure);
        continue;
      }
      // If we were in images block but encountered a non-image line, close the block implicitly
      if(inImagesBlock && !imgToken){ inImagesBlock = false; }
      if(numMatch){
        if(!currentOl){ currentOl = true; fragments.push('<ol style="margin:0 0 8px 20px;padding:0;">'); }
        fragments.push(`<li style="margin:4px 0;">${numMatch[2]}</li>`);
        continue;
      }
      closeOl();
      fragments.push(`<p style="margin:6px 0;">${line}</p>`);
    }
    closeOl();
    return fragments.join('');
  }

  function linkifyBlue(str){
    // URLs with http/https
    str = str.replace(/\bhttps?:\/\/[\w.-]+(?:\/[\w\-./?%&=#]*)?/gi, function(m){
      const href = m;
      return `<a href="${href}" target="_blank" rel="noopener noreferrer" style="color:#2563eb;text-decoration:underline;">${m}</a>`;
    });
    // URLs starting with www.
    str = str.replace(/\bwww\.[\w.-]+(?:\/[\w\-./?%&=#]*)?/gi, function(m){
      const href = `https://${m}`;
      return `<a href="${href}" target="_blank" rel="noopener noreferrer" style="color:#2563eb;text-decoration:underline;">${m}</a>`;
    });
    // Emails
    str = str.replace(/\b[\w.%+\-]+@[\w.\-]+\.[A-Za-z]{2,}\b/g, function(m){
      const href = `mailto:${m}`;
      return `<a href="${href}" style="color:#2563eb;text-decoration:underline;">${m}</a>`;
    });
    return str;
  }

  function appendMsg(text, role){
    const el = document.createElement('div');
    el.className = 'rcs-assistant-msg' + (role==='user' ? ' user' : '');
    if(role==='assistant'){
      el.innerHTML = formatAnswer(text);
    } else {
      el.textContent = text;
    }
    body.appendChild(el); body.scrollTop = body.scrollHeight;
  }
  function appendLoading(){
    const el = document.createElement('div');
    el.className = 'rcs-assistant-msg';
    el.id = 'rcsAssistantLoading';
    el.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Thinking...';
    body.appendChild(el); body.scrollTop = body.scrollHeight; return el;
  }
  function removeLoading(){
    const el = document.getElementById('rcsAssistantLoading'); if(el) el.remove();
  }

  function revealAnswerGradually(fullText){
    // Split by double newlines to reveal by paragraphs/blocks
    const blocks = fullText.split(/\n{2,}/).filter(Boolean);
    if(blocks.length === 0){ appendMsg(fullText, 'assistant'); return; }
    let idx = 0;
    (function step(){
      appendMsg(blocks[idx], 'assistant');
      idx++;
      body.scrollTop = body.scrollHeight;
      if(idx < blocks.length){
        setTimeout(step, 180); // typing cadence per block
      }
    })();
  }

  toggle.addEventListener('click', ()=> panel.classList.toggle('active'));
  closeBtn.addEventListener('click', ()=> panel.classList.remove('active'));
  document.querySelectorAll('.rcs-assistant-sample').forEach(b=> b.addEventListener('click', ()=>{
    input.value = b.dataset.q; input.focus();
  }));

  // Align with Rate Us button on load and updates
  alignWithRateUs();
  window.addEventListener('resize', alignWithRateUs);
  window.addEventListener('orientationchange', alignWithRateUs);
  setTimeout(alignWithRateUs, 300);
  setTimeout(alignWithRateUs, 1000);
  const mo = new MutationObserver(()=> alignWithRateUs());
  mo.observe(document.body, { childList: true, subtree: true });

  async function ask(){
    const q = (input.value||'').trim(); if(!q) return; appendMsg(q,'user'); input.value='';
    const loading = appendLoading();
    // Preload/disable controls during request
    const prevSendHtml = send.innerHTML;
    send.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    send.disabled = true;
    input.disabled = true;
    try{
      const res = await fetch('{{ route('ai.assistant') }}', {
        method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
        body: JSON.stringify({question: q})
      });
      const data = await res.json();
      removeLoading();
      if(!res.ok || !data.success){
        appendMsg(data.message || 'Sorry, something went wrong.', 'assistant');
      } else {
        revealAnswerGradually(data.answer || '');
      }
    }catch(e){ removeLoading(); appendMsg('Network error. Please try again.','assistant'); }
    finally {
      // Restore controls
      send.innerHTML = prevSendHtml;
      send.disabled = false;
      input.disabled = false;
      input.focus();
    }
  }

  send.addEventListener('click', ask);
  input.addEventListener('keydown', (e)=>{ if(e.key==='Enter' && !e.shiftKey){ e.preventDefault(); ask(); }});
})();
</script>
@endif
