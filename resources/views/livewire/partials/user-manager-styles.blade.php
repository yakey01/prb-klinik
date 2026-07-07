<style>
    [x-cloak] { display: none !important; }

    /* ── Header ─────────────────────────────────────────────────── */
    .um-head { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; margin-bottom:1.4rem; flex-wrap:wrap; }
    .um-title { font-size:1.35rem; color:var(--ink); margin:0 0 .25rem; }
    .um-sub { font-size:.8rem; color:var(--mut); margin:0; max-width:40rem; }
    .um-add { min-height:42px; }

    /* ── Stat cards ─────────────────────────────────────────────── */
    .um-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:.85rem; margin-bottom:1.3rem; }
    .um-stat { display:flex; align-items:center; gap:.85rem; background:linear-gradient(135deg,var(--card) 0%,var(--panel) 100%); border:1px solid var(--line); border-radius:.9rem; padding:1rem 1.15rem; transition:border-color .2s, transform .2s; }
    .um-stat:hover { border-color:var(--line2); transform:translateY(-2px); }
    .um-stat-ico { width:40px; height:40px; border-radius:.65rem; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
    .um-stat-num { font-size:1.5rem; font-weight:700; color:var(--ink); line-height:1; font-family:'Inter',sans-serif; }
    .um-stat-lbl { font-size:.7rem; color:var(--mut); text-transform:uppercase; letter-spacing:.05em; margin-top:.3rem; }
    .um-stat-roles { flex-direction:column; align-items:flex-start; justify-content:center; gap:.45rem; }
    .um-rolechip { display:flex; align-items:center; gap:.4rem; font-size:.78rem; color:var(--mut); font-weight:500; }
    .um-dot { width:7px; height:7px; border-radius:50%; flex-shrink:0; }

    /* ── Toolbar ────────────────────────────────────────────────── */
    .um-toolbar { display:flex; gap:.6rem; margin-bottom:1rem; flex-wrap:wrap; align-items:center; }
    .um-search { position:relative; flex:1; min-width:220px; display:flex; align-items:center; }
    .um-search svg { position:absolute; left:.85rem; color:var(--mut2); pointer-events:none; }
    .um-search-input { width:100%; background:var(--panel); border:1px solid var(--line); color:var(--ink); border-radius:.6rem; padding:.6rem 2.2rem; font-size:.85rem; font-family:'Inter',sans-serif; transition:border-color .2s, box-shadow .2s; }
    .um-search-input:focus { outline:none; border-color:var(--gold); box-shadow:0 0 0 3px rgba(217,164,65,.12); }
    .um-search-input::placeholder { color:var(--mut2); }
    .um-search-clear { position:absolute; right:.6rem; background:none; border:none; color:var(--mut2); cursor:pointer; font-size:.85rem; padding:.2rem; }
    .um-search-clear:hover { color:var(--ink); }
    .um-filter { width:auto; min-width:150px; cursor:pointer; }
    .um-reset { min-height:40px; }

    /* ── Table ──────────────────────────────────────────────────── */
    .um-tablewrap { overflow:hidden; }
    .um-table { width:100%; }
    .um-sortable { user-select:none; }
    .um-caret { font-size:.6rem; margin-left:.25rem; color:var(--gold2); }
    .um-caret-dim { color:var(--mut2); opacity:.5; }
    .um-row-off td { opacity:.55; }
    .um-userc { display:flex; align-items:center; gap:.8rem; min-width:0; }
    .um-avatar { width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.85rem; flex-shrink:0; box-shadow:0 2px 8px rgba(0,0,0,.25); }
    .um-userinfo { min-width:0; }
    .um-username { font-weight:600; font-size:.88rem; color:var(--ink); display:flex; align-items:center; gap:.45rem; }
    .um-you { font-size:.62rem; background:rgba(111,177,224,.12); border:1px solid rgba(111,177,224,.25); color:var(--blue); border-radius:999px; padding:.1rem .5rem; font-weight:600; }
    .um-useremail { font-size:.72rem; color:var(--mut); margin-top:.1rem; }
    .um-jabatan { font-size:.68rem; color:var(--mut2); margin-top:.1rem; }
    .um-rolebadge { display:inline-flex; align-items:center; padding:.2rem .65rem; border-radius:999px; font-size:.7rem; font-weight:600; white-space:nowrap; }

    /* status toggle */
    .um-statustoggle { display:inline-flex; align-items:center; gap:.4rem; padding:.25rem .65rem; border-radius:999px; font-size:.72rem; font-weight:600; cursor:pointer; border:1px solid transparent; transition:.15s; background:none; }
    .um-statustoggle.on { background:rgba(63,207,142,.1); color:var(--emer2); border-color:rgba(63,207,142,.25); }
    .um-statustoggle.off { background:rgba(232,100,90,.1); color:var(--red2); border-color:rgba(232,100,90,.25); }
    .um-statustoggle:not(.um-static):hover { filter:brightness(1.2); }
    .um-static { cursor:default; }
    .um-statusdot { width:6px; height:6px; border-radius:50%; background:currentColor; }
    .um-lastlogin { font-size:.8rem; color:var(--ink); }
    .um-logincount { font-size:.68rem; color:var(--mut2); margin-top:.1rem; }

    /* actions */
    .um-actions { display:flex; align-items:center; gap:.3rem; justify-content:flex-end; position:relative; }
    .um-actbtn { width:30px; height:30px; display:flex; align-items:center; justify-content:center; background:var(--panel); border:1px solid var(--line); color:var(--mut); border-radius:.45rem; cursor:pointer; transition:.15s; }
    .um-actbtn:hover { color:var(--ink); border-color:var(--line3); background:var(--card); }
    .um-actbtn.um-edit:hover { color:var(--gold2); border-color:rgba(217,164,65,.4); }
    .um-menu { position:absolute; top:34px; right:0; z-index:20; background:var(--card); border:1px solid var(--line2); border-radius:.6rem; box-shadow:0 12px 32px rgba(0,0,0,.45); padding:.3rem; min-width:170px; }
    .um-menu-item { display:flex; align-items:center; gap:.55rem; width:100%; background:none; border:none; color:var(--ink); font-size:.8rem; padding:.5rem .65rem; border-radius:.4rem; cursor:pointer; text-align:left; transition:background .12s; }
    .um-menu-item:hover { background:rgba(255,255,255,.05); }
    .um-menu-danger { color:var(--red2); }
    .um-menu-danger:hover { background:rgba(232,100,90,.12); }

    .um-empty { display:flex; flex-direction:column; align-items:center; justify-content:center; gap:.5rem; padding:2.5rem 1rem; color:var(--mut2); }
    .um-empty p { font-size:.82rem; margin:0; color:var(--mut); }

    /* ── Role matrix ────────────────────────────────────────────── */
    .um-matrix { display:grid; grid-template-columns:repeat(3,1fr); gap:.85rem; margin-top:1.2rem; }
    .um-matrix-card { background:rgba(17,36,28,.5); border:1px solid var(--line); border-radius:.85rem; padding:1.1rem 1.2rem; }
    .um-matrix-head { margin-bottom:.6rem; }
    .um-matrix-desc { font-size:.76rem; color:var(--mut); margin:0 0 .7rem; line-height:1.45; }
    .um-matrix-perms { list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:.4rem; }
    .um-matrix-perms li { display:flex; align-items:flex-start; gap:.5rem; font-size:.75rem; color:var(--mut); }
    .um-matrix-perms li svg { flex-shrink:0; margin-top:.15rem; }

    /* ── Overlay + slide-over ───────────────────────────────────── */
    .um-overlay { position:fixed; inset:0; z-index:200; display:flex; justify-content:flex-end; }
    .um-overlay-bg { position:absolute; inset:0; background:rgba(4,10,7,.65); backdrop-filter:blur(3px); animation:umFade .2s ease; }
    .um-panel { position:relative; width:560px; max-width:94vw; height:100%; background:var(--bg); border-left:1px solid var(--line2); box-shadow:-20px 0 60px rgba(0,0,0,.5); overflow-y:auto; animation:umSlide .28s cubic-bezier(.16,1,.3,1); display:flex; flex-direction:column; }
    .um-panel-sm { width:440px; }
    @keyframes umFade { from{opacity:0} to{opacity:1} }
    @keyframes umSlide { from{transform:translateX(40px);opacity:.4} to{transform:translateX(0);opacity:1} }
    .um-panel-head { display:flex; align-items:flex-start; justify-content:space-between; padding:1.5rem 1.6rem 1.1rem; border-bottom:1px solid var(--line); position:sticky; top:0; background:var(--bg); z-index:2; }
    .um-panel-title { font-size:1.15rem; color:var(--ink); }
    .um-panel-sub { font-size:.76rem; color:var(--mut); margin-top:.2rem; }
    .um-panel-close { width:32px; height:32px; border-radius:.45rem; background:var(--panel); border:1px solid var(--line); color:var(--mut); cursor:pointer; font-size:1rem; transition:.15s; flex-shrink:0; }
    .um-panel-close:hover { color:var(--ink); border-color:var(--line3); }
    .um-panel-body { padding:1.4rem 1.6rem; display:flex; flex-direction:column; gap:1.1rem; flex:1; }

    .um-preview { display:flex; align-items:center; gap:.9rem; background:rgba(17,36,28,.5); border:1px solid var(--line); border-radius:.8rem; padding:.9rem 1.1rem; }
    .um-preview-av { width:48px; height:48px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:1rem; flex-shrink:0; box-shadow:0 2px 8px rgba(0,0,0,.3); transition:background .2s; }
    .um-preview-name { font-weight:600; font-size:.92rem; color:var(--ink); }
    .um-preview-mail { font-size:.74rem; color:var(--mut); margin-top:.15rem; }

    .um-field-grid { display:grid; grid-template-columns:1fr 1fr; gap:.85rem; }
    .um-field { display:flex; flex-direction:column; }
    .um-err { color:var(--red); font-size:.7rem; margin-top:.25rem; }

    /* role cards */
    .um-roles { display:flex; flex-direction:column; gap:.5rem; }
    .um-rolecard { position:relative; display:flex; flex-direction:column; gap:.25rem; padding:.7rem .9rem; background:var(--panel); border:1px solid var(--line); border-radius:.6rem; cursor:pointer; transition:.15s; }
    .um-rolecard:hover { border-color:var(--line3); }
    .um-rolecard.sel { border-color:var(--rc,var(--gold)); background:rgba(255,255,255,.03); box-shadow:0 0 0 1px var(--rb,rgba(217,164,65,.3)) inset; }
    .um-rolecard-radio { position:absolute; opacity:0; pointer-events:none; }
    .um-rolecard-top { display:flex; align-items:center; justify-content:space-between; }
    .um-rolecard-name { font-size:.85rem; font-weight:700; }
    .um-rolecard-check { opacity:0; color:var(--rc,var(--gold2)); font-weight:800; transition:opacity .15s; }
    .um-rolecard.sel .um-rolecard-check { opacity:1; }
    .um-rolecard-desc { font-size:.72rem; color:var(--mut); line-height:1.4; }

    /* status switch */
    .um-switch-row { display:flex; align-items:center; gap:.85rem; cursor:pointer; }
    .um-switch { width:44px; height:24px; border-radius:999px; background:var(--line2); position:relative; flex-shrink:0; transition:background .2s; }
    .um-switch.on { background:var(--emer); }
    .um-switch-knob { position:absolute; top:3px; left:3px; width:18px; height:18px; border-radius:50%; background:#fff; transition:transform .2s; box-shadow:0 1px 3px rgba(0,0,0,.3); }
    .um-switch.on .um-switch-knob { transform:translateX(20px); }
    .um-switch-text { display:flex; flex-direction:column; gap:.1rem; }
    .um-switch-text strong { font-size:.85rem; color:var(--ink); }
    .um-switch-text span { font-size:.72rem; color:var(--mut); }
    .um-hint { font-size:.7rem; color:var(--gold3); margin-top:.4rem; }

    /* password */
    .um-pwtoggle { display:flex; align-items:center; gap:.6rem; cursor:pointer; font-size:.82rem; color:var(--mut); padding:.6rem .9rem; background:var(--panel); border:1px solid var(--line); border-radius:.5rem; width:fit-content; }
    .um-check { accent-color:var(--gold); width:15px; height:15px; cursor:pointer; }
    .um-pwsection { display:flex; flex-direction:column; gap:.85rem; }
    .um-pwlabelrow { display:flex; align-items:center; justify-content:space-between; margin-bottom:.35rem; }
    .um-genbtn { display:inline-flex; align-items:center; gap:.35rem; background:rgba(217,164,65,.1); border:1px solid rgba(217,164,65,.25); color:var(--gold2); font-size:.7rem; font-weight:600; padding:.3rem .6rem; border-radius:.4rem; cursor:pointer; transition:.15s; }
    .um-genbtn:hover { background:rgba(217,164,65,.18); }
    .um-pwwrap { position:relative; }
    .um-pweye { position:absolute; right:.6rem; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--mut2); cursor:pointer; padding:.2rem; display:flex; }
    .um-pweye:hover { color:var(--ink); }
    .um-strength { display:flex; align-items:center; gap:.7rem; margin-top:.5rem; }
    .um-strength-bars { display:flex; gap:.25rem; flex:1; }
    .um-strength-bars span { flex:1; height:4px; border-radius:2px; background:var(--line2); transition:background .2s; }
    .um-strength-lbl { font-size:.7rem; font-weight:600; white-space:nowrap; }

    .um-panel-foot { display:flex; gap:.6rem; justify-content:flex-end; padding-top:.5rem; margin-top:auto; }

    /* activity log */
    .um-log { display:flex; gap:.7rem; padding:.7rem 0; border-bottom:1px solid rgba(31,61,48,.5); }
    .um-log:last-child { border-bottom:none; }
    .um-log-dot { width:8px; height:8px; border-radius:50%; margin-top:.4rem; flex-shrink:0; }
    .um-log-desc { font-size:.83rem; color:var(--ink); line-height:1.4; }
    .um-log-meta { font-size:.7rem; color:var(--mut2); margin-top:.2rem; }
    .um-log-action { font-weight:600; }

    /* generated pw modal */
    .um-pwmodal { position:relative; margin:auto; width:400px; max-width:92vw; background:var(--card); border:1px solid var(--line2); border-radius:1rem; padding:1.8rem; text-align:center; box-shadow:0 24px 60px rgba(0,0,0,.5); animation:umPop .25s cubic-bezier(.16,1,.3,1); }
    @keyframes umPop { from{transform:scale(.94);opacity:0} to{transform:scale(1);opacity:1} }
    .um-pwmodal-ico { width:52px; height:52px; border-radius:50%; background:rgba(217,164,65,.12); color:var(--gold2); display:flex; align-items:center; justify-content:center; margin:0 auto 1rem; }
    .um-pwmodal-title { font-size:1.15rem; color:var(--ink); }
    .um-pwmodal-sub { font-size:.78rem; color:var(--mut); margin:.4rem 0 1.2rem; line-height:1.45; }
    .um-pwmodal-code { display:flex; align-items:center; justify-content:space-between; gap:.6rem; background:var(--bg); border:1px dashed var(--line3); border-radius:.6rem; padding:.7rem 1rem; margin-bottom:1.2rem; }
    .um-pwmodal-code code { font-size:1.05rem; color:var(--gold3); letter-spacing:.05em; }
    .um-copybtn { background:var(--panel); border:1px solid var(--line2); color:var(--ink); font-size:.74rem; font-weight:600; padding:.4rem .8rem; border-radius:.4rem; cursor:pointer; white-space:nowrap; transition:.15s; }
    .um-copybtn:hover { border-color:var(--line3); }

    /* ── Responsive ─────────────────────────────────────────────── */
    @media (max-width:1000px) {
        .um-stats { grid-template-columns:repeat(2,1fr); }
        .um-matrix { grid-template-columns:1fr; }
    }
    @media (max-width:680px) {
        .um-stats { grid-template-columns:1fr; }
        .um-field-grid { grid-template-columns:1fr; }
        .um-table thead { display:none; }
        .um-table, .um-table tbody, .um-table tr, .um-table td { display:block; width:100%; }
        .um-table tr { border-bottom:1px solid var(--line); padding:.6rem 0; }
        .um-table td { border:none; padding:.35rem 1rem; }
        .um-actions { justify-content:flex-start; }
        .um-panel { width:100%; max-width:100vw; }
    }
</style>
