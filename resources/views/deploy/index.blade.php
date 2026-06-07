<x-app-layout>
<x-slot name="title">Deploy Panel</x-slot>

<style>
.terminal {
    background:#0a0e0a; color:#c8ffc8; font-family:'Fira Code','Courier New',monospace;
    font-size:.78rem; line-height:1.6; border-radius:.6rem;
    border:1px solid rgba(63,207,142,.15); padding:1rem;
    height:380px; overflow-y:auto; white-space:pre-wrap; word-break:break-all;
}
.terminal .stderr { color:#ff8080; }
.terminal .cmd    { color:#ffd700; font-weight:700; }
.terminal .ok     { color:#3fcf8e; }
.terminal .info   { color:#6fb1e0; }
.terminal .dim    { color:#4a6a4a; }
.ws-dot { width:9px;height:9px;border-radius:50%;display:inline-block;flex-shrink:0; }
.btn-deploy { padding:.5rem 1.1rem;border-radius:.5rem;cursor:pointer;font-size:.78rem;font-weight:600;border:none;transition:.15s; }
.btn-deploy:disabled { opacity:.4;cursor:not-allowed; }
.file-tree { background:#0d1a0d;border:1px solid rgba(63,207,142,.12);border-radius:.5rem;overflow:hidden; }
.file-entry { padding:.35rem .8rem;cursor:pointer;font-size:.76rem;font-family:monospace;color:#a0c8a0;border-bottom:1px solid rgba(63,207,142,.05); }
.file-entry:hover { background:rgba(63,207,142,.07);color:#3fcf8e; }
.file-entry.dir { color:#6fb1e0; }
.editor-area { background:#0a0e0a;color:#e0e8e0;font-family:'Fira Code','Courier New',monospace;font-size:.77rem;line-height:1.6;width:100%;resize:vertical;border:1px solid rgba(63,207,142,.2);border-radius:.4rem;padding:.7rem; }
.step-row { display:flex;align-items:center;gap:.6rem;padding:.3rem 0;font-size:.78rem; }
.step-status { font-size:.85rem;min-width:1.2rem; }
</style>

<div x-data="deployPanel()" x-init="init()">

{{-- HEADER --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.2rem;flex-wrap:wrap;gap:.8rem;">
    <div>
        <h2 class="font-heading" style="font-size:1.4rem;color:var(--ink);margin:0 0 .2rem;">
            Deploy Panel
        </h2>
        <p style="font-size:.78rem;color:var(--mut);margin:0;">
            WebSocket realtime — git, migrate, cache, WA service, file editor
        </p>
    </div>
    <div style="display:flex;align-items:center;gap:.8rem;">
        {{-- WS Status --}}
        <div style="display:flex;align-items:center;gap:.5rem;padding:.4rem .9rem;border-radius:.5rem;border:1px solid rgba(63,207,142,.2);background:rgba(63,207,142,.04);">
            <span class="ws-dot" :style="wsReady ? 'background:#3fcf8e' : (wsConnecting ? 'background:#d9a441;animation:pulse 1s infinite' : 'background:#e8645a')" ></span>
            <span style="font-size:.73rem;font-weight:600;" :style="wsReady ? 'color:#3fcf8e' : 'color:#e8645a'" x-text="wsReady ? 'WebSocket Connected' : (wsConnecting ? 'Connecting...' : 'Disconnected')"></span>
        </div>
        <button @click="connect()" x-show="!wsReady && !wsConnecting" class="btn-deploy" style="background:rgba(63,207,142,.12);color:var(--emer2);border:1px solid rgba(63,207,142,.25);">
            ⚡ Connect
        </button>
        <button @click="disconnect()" x-show="wsReady" class="btn-deploy" style="background:rgba(232,100,90,.1);color:var(--red2);border:1px solid rgba(232,100,90,.2);">
            Disconnect
        </button>
    </div>
</div>

{{-- STATUS CARDS --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.8rem;margin-bottom:1.2rem;">
    <div class="glass-card" style="padding:.8rem 1rem;">
        <div style="font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.3rem;">WhatsApp Service</div>
        <div style="display:flex;align-items:center;gap:.5rem;">
            <span class="ws-dot" :style="waStatus.ready ? 'background:#3fcf8e' : 'background:#e8645a'"></span>
            <span style="font-size:.82rem;font-weight:600;" x-text="waStatus.ready ? (waStatus.nomor || 'Ready') : 'Offline'"></span>
        </div>
        <div style="font-size:.67rem;color:var(--mut);margin-top:.2rem;" x-text="waStatus.ready ? `${waStatus.messages_sent ?? 0} pesan terkirim` : 'node wa-service/server.js'"></div>
    </div>
    <div class="glass-card" style="padding:.8rem 1rem;">
        <div style="font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.3rem;">Git Branch</div>
        <div style="font-size:.82rem;font-weight:600;color:var(--gold2);font-family:monospace;" x-text="gitBranch || '—'"></div>
        <div style="font-size:.67rem;color:var(--mut);margin-top:.2rem;" x-text="lastCommit || 'belum connect'"></div>
    </div>
    <div class="glass-card" style="padding:.8rem 1rem;">
        <div style="font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.3rem;">System (Lokal)</div>
        <div style="font-size:.78rem;font-family:monospace;color:var(--mut2);white-space:pre-wrap;" x-text="systemInfo || '—'"></div>
    </div>
    <div class="glass-card" style="padding:.8rem 1rem;border-color:rgba(111,177,224,.2);">
        <div style="font-size:.65rem;color:var(--mut);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.3rem;">Hostinger SSH</div>
        <div style="display:flex;align-items:center;gap:.5rem;">
            <span class="ws-dot" :style="hostingerOnline ? 'background:#3fcf8e' : 'background:#888'"></span>
            <span style="font-size:.82rem;font-weight:600;" x-text="hostingerOnline ? 'Online' : 'Checking...'"></span>
        </div>
        <div style="font-size:.67rem;color:var(--mut);margin-top:.2rem;">153.92.8.132:65002</div>
    </div>
</div>

{{-- MAIN GRID: Terminal + Actions --}}
<div style="display:grid;grid-template-columns:1fr 280px;gap:1rem;margin-bottom:1rem;align-items:start;">

    {{-- Terminal output --}}
    <div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.4rem;">
            <span style="font-size:.72rem;font-weight:600;color:var(--mut2);text-transform:uppercase;letter-spacing:.1em;">Terminal Output</span>
            <button @click="clearTerm()" style="font-size:.68rem;color:var(--mut);background:none;border:none;cursor:pointer;padding:.2rem .5rem;">clear</button>
        </div>
        <div class="terminal" x-ref="terminal" x-html="termOutput"></div>
    </div>

    {{-- ═══ SMART DEPLOY PANEL ═══ --}}
    <div style="background:rgba(10,20,10,.7);border:1px solid rgba(63,207,142,.18);border-radius:.6rem;padding:.9rem 1rem;display:flex;flex-direction:column;gap:.6rem;">

        {{-- Header --}}
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <div>
                <div style="font-size:.8rem;font-weight:700;color:var(--emer2);letter-spacing:.06em;">⚡ SMART DEPLOY</div>
                <div style="font-size:.7rem;color:var(--mut);margin-top:.1rem;">Detect perubahan lokal → Commit → Push GitHub → Deploy Hostinger</div>
            </div>
            <button @click="refreshChanges()" :disabled="!wsReady"
                style="background:rgba(63,207,142,.08);color:var(--emer2);border:1px solid rgba(63,207,142,.2);border-radius:.35rem;padding:.3rem .65rem;font-size:.7rem;cursor:pointer;"
                :style="!wsReady ? 'opacity:.4;cursor:not-allowed' : ''">
                ↻ Refresh
            </button>
        </div>

        {{-- Changed files list --}}
        <div style="background:#070d07;border-radius:.4rem;border:1px solid rgba(63,207,142,.08);min-height:52px;padding:.5rem .65rem;">
            <template x-if="!wsReady">
                <div style="color:var(--mut);font-size:.72rem;font-style:italic;">Hubungkan WebSocket untuk melihat perubahan...</div>
            </template>
            <template x-if="wsReady && localChanges.length === 0">
                <div style="color:#3fcf8e;font-size:.72rem;">✅ Tidak ada perubahan — working tree bersih</div>
            </template>
            <template x-if="wsReady && localChanges.length > 0">
                <div>
                    <div style="font-size:.68rem;color:var(--mut);margin-bottom:.3rem;"
                         x-text="localChanges.length + ' file berubah'"></div>
                    <div style="max-height:140px;overflow-y:auto;">
                        <template x-for="f in localChanges" :key="f.path">
                            <div style="display:flex;align-items:center;gap:.45rem;padding:.15rem 0;font-size:.72rem;font-family:monospace;">
                                <span style="min-width:1.4rem;text-align:center;font-weight:700;border-radius:.25rem;padding:.05rem .3rem;"
                                    :style="f.xy==='M'||f.xy==='MM'?'background:rgba(217,164,65,.15);color:#d9a441':
                                            f.xy==='A'||f.xy==='?'||f.xy==='??'?'background:rgba(63,207,142,.12);color:#3fcf8e':
                                            f.xy==='D'?'background:rgba(232,100,90,.12);color:#e8645a':
                                            'background:rgba(111,177,224,.1);color:#6fb1e0'"
                                    x-text="f.xy==='??'?'?':f.xy"></span>
                                <span style="color:#c0d8c0;" x-text="f.path"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        {{-- Commit message input --}}
        <div style="display:flex;flex-direction:column;gap:.3rem;">
            <label style="font-size:.68rem;color:var(--mut);">Pesan commit <span style="color:#e8645a;">*</span></label>
            <div style="display:flex;gap:.4rem;align-items:center;">
                <select x-model="commitType"
                    style="background:#0d180d;border:1px solid rgba(63,207,142,.2);color:var(--mut2);border-radius:.35rem;padding:.3rem .4rem;font-size:.72rem;cursor:pointer;min-width:90px;">
                    <option value="feat">feat</option>
                    <option value="fix">fix</option>
                    <option value="style">style</option>
                    <option value="refactor">refactor</option>
                    <option value="chore">chore</option>
                    <option value="deploy">deploy</option>
                    <option value="docs">docs</option>
                </select>
                <input type="text" x-model="commitDesc"
                    placeholder="deskripsi singkat perubahan..."
                    style="flex:1;background:#0d180d;border:1px solid rgba(63,207,142,.2);color:#c0d8c0;border-radius:.35rem;padding:.3rem .55rem;font-size:.75rem;outline:none;"
                    @keydown.enter="localChanges.length > 0 && commitDesc.trim() && !deploying && smartDeploy()" />
            </div>
            <div style="font-size:.66rem;color:var(--mut);font-family:monospace;"
                 x-text="'→  ' + commitType + ': ' + (commitDesc.trim() || '...')"></div>
        </div>

        {{-- Smart Deploy steps tracker --}}
        <template x-if="smartSteps.length">
            <div style="background:#070d07;border-radius:.4rem;padding:.5rem .7rem;border:1px solid rgba(63,207,142,.1);">
                <template x-for="step in smartSteps" :key="step.label">
                    <div style="display:flex;align-items:center;gap:.5rem;padding:.15rem 0;">
                        <span style="font-size:.8rem;min-width:1.2rem;"
                            :style="step.status==='ok'?'color:#3fcf8e':step.status==='fail'?'color:#e8645a':step.status==='running'?'color:#d9a441':step.status==='skip'?'color:#4a7a7a':'color:#3a5a3a'"
                            x-text="step.status==='ok'?'✅':step.status==='fail'?'❌':step.status==='running'?'⏳':step.status==='skip'?'⊘':'○'">
                        </span>
                        <span style="font-size:.73rem;"
                            :style="step.status==='skip'?'color:#4a7a7a;text-decoration:line-through':'color:#a0c8a0'"
                            x-text="step.label"></span>
                    </div>
                </template>
            </div>
        </template>

        {{-- GitHub Only steps tracker --}}
        <template x-if="githubSteps.length">
            <div style="background:#070d07;border-radius:.4rem;padding:.5rem .7rem;border:1px solid rgba(111,177,224,.15);">
                <div style="font-size:.66rem;color:#6fb1e0;font-weight:700;margin-bottom:.3rem;letter-spacing:.06em;">GITHUB PUSH</div>
                <template x-for="step in githubSteps" :key="step.label">
                    <div style="display:flex;align-items:center;gap:.5rem;padding:.15rem 0;">
                        <span style="font-size:.8rem;min-width:1.2rem;"
                            :style="step.status==='ok'?'color:#3fcf8e':step.status==='fail'?'color:#e8645a':step.status==='running'?'color:#d9a441':step.status==='skip'?'color:#4a7a7a':'color:#3a5a3a'"
                            x-text="step.status==='ok'?'✅':step.status==='fail'?'❌':step.status==='running'?'⏳':step.status==='skip'?'⊘':'○'">
                        </span>
                        <span style="font-size:.73rem;"
                            :style="step.status==='skip'?'color:#4a7a7a;text-decoration:line-through':'color:#a0c8a0'"
                            x-text="step.label"></span>
                    </div>
                </template>
            </div>
        </template>

        {{-- Two deploy buttons --}}
        <div style="display:flex;flex-direction:column;gap:.4rem;">
            {{-- GitHub only --}}
            <button @click="githubDeploy()"
                :disabled="!wsReady || deploying || localChanges.length === 0 || !commitDesc.trim()"
                style="padding:.55rem;border-radius:.4rem;font-size:.78rem;font-weight:700;cursor:pointer;transition:all .2s;border:1px solid rgba(111,177,224,.45);background:rgba(111,177,224,.12);color:#6fb1e0;"
                :style="(!wsReady || deploying || localChanges.length === 0 || !commitDesc.trim()) ? 'opacity:.4;cursor:not-allowed' : 'opacity:1'">
                <span x-show="!deploying">📦 Commit + Push GitHub</span>
                <span x-show="deploying" style="animation:pulse 1s infinite">⏳ Pushing...</span>
            </button>

            {{-- Full smart deploy --}}
            <button @click="smartDeploy()"
                :disabled="!wsReady || deploying || localChanges.length === 0 || !commitDesc.trim()"
                style="padding:.55rem;border-radius:.4rem;font-size:.78rem;font-weight:700;cursor:pointer;transition:all .2s;border:1px solid rgba(63,207,142,.4);background:rgba(63,207,142,.12);color:#3fcf8e;"
                :style="(!wsReady || deploying || localChanges.length === 0 || !commitDesc.trim()) ? 'opacity:.4;cursor:not-allowed' : 'opacity:1'">
                <span x-show="!deploying">🚀 Commit + Push + Deploy Hostinger</span>
                <span x-show="deploying" style="animation:pulse 1s infinite">⏳ Deploying...</span>
            </button>
        </div>

    </div>

    {{-- Action buttons --}}
    <div style="display:flex;flex-direction:column;gap:.5rem;">
        <div style="font-size:.7rem;font-weight:700;color:var(--mut);text-transform:uppercase;letter-spacing:.1em;margin-bottom:.2rem;">QUICK ACTIONS</div>

        {{-- Full deploy --}}
        <button @click="fullDeploy()" :disabled="!wsReady || deploying" class="btn-deploy"
            style="background:rgba(217,164,65,.12);color:var(--gold2);border:1px solid rgba(217,164,65,.3);padding:.65rem;">
            <span x-show="!deploying">🚀 Full Deploy</span>
            <span x-show="deploying" style="animation:pulse 1s infinite">⏳ Deploying...</span>
        </button>

        {{-- Deploy steps --}}
        <template x-if="deploySteps.length">
            <div style="background:#0a0e0a;border-radius:.4rem;padding:.6rem .8rem;border:1px solid rgba(63,207,142,.1);">
                <template x-for="step in deploySteps" :key="step.label">
                    <div class="step-row">
                        <span class="step-status"
                            :style="step.status==='ok'?'color:#3fcf8e':step.status==='fail'?'color:#e8645a':step.status==='running'?'color:#d9a441':step.status==='skip'?'color:#4a7a7a':'color:#4a6a4a'"
                            x-text="step.status==='ok'?'✅':step.status==='fail'?'❌':step.status==='running'?'⏳':step.status==='skip'?'⊘':'○'">
                        </span>
                        <span :style="step.status==='skip'?'color:#4a7a7a;text-decoration:line-through':'color:#a0c8a0'"
                              x-text="step.label + (step.status==='skip' ? ' (n/a)' : '')"></span>
                    </div>
                </template>
            </div>
        </template>

        <div style="height:.5px;background:var(--line);margin:.2rem 0;"></div>

        {{-- Git --}}
        <div style="font-size:.67rem;color:var(--mut);font-weight:600;margin-top:.1rem;">GIT</div>
        <button @click="runCmd('git:pull')" :disabled="!wsReady" class="btn-deploy" style="background:rgba(63,207,142,.08);color:var(--emer2);border:1px solid rgba(63,207,142,.2);">
            ↓ git pull
        </button>
        <button @click="runCmd('git:status')" :disabled="!wsReady" class="btn-deploy" style="background:rgba(255,255,255,.03);color:var(--mut2);border:1px solid var(--line);">
            git status
        </button>
        <button @click="runCmd('git:log')" :disabled="!wsReady" class="btn-deploy" style="background:rgba(255,255,255,.03);color:var(--mut2);border:1px solid var(--line);">
            git log
        </button>

        <div style="height:.5px;background:var(--line);margin:.2rem 0;"></div>

        {{-- Laravel --}}
        <div style="font-size:.67rem;color:var(--mut);font-weight:600;">LARAVEL</div>
        <button @click="runCmd('migrate')" :disabled="!wsReady" class="btn-deploy" style="background:rgba(111,177,224,.08);color:var(--blue);border:1px solid rgba(111,177,224,.2);">
            artisan migrate
        </button>
        <button @click="runCmd('cache:build')" :disabled="!wsReady" class="btn-deploy" style="background:rgba(255,255,255,.03);color:var(--mut2);border:1px solid var(--line);">
            optimize (cache)
        </button>
        <button @click="runCmd('cache:clear')" :disabled="!wsReady" class="btn-deploy" style="background:rgba(255,255,255,.03);color:var(--mut2);border:1px solid var(--line);">
            cache:clear
        </button>
        <button @click="runCmd('queue:restart')" :disabled="!wsReady" class="btn-deploy" style="background:rgba(255,255,255,.03);color:var(--mut2);border:1px solid var(--line);">
            queue:restart
        </button>

        <div style="height:.5px;background:var(--line);margin:.2rem 0;"></div>

        {{-- PM2 --}}
        <div style="font-size:.67rem;color:var(--mut);font-weight:600;">PM2 / SERVICES</div>
        <button @click="runCmd('pm2:restart:wa')" :disabled="!wsReady" class="btn-deploy" style="background:rgba(63,207,142,.08);color:var(--emer2);border:1px solid rgba(63,207,142,.2);">
            restart WA service
        </button>
        <button @click="runCmd('pm2:list')" :disabled="!wsReady" class="btn-deploy" style="background:rgba(255,255,255,.03);color:var(--mut2);border:1px solid var(--line);">
            pm2 list
        </button>

        <div style="height:.5px;background:var(--line);margin:.3rem 0;"></div>

        {{-- Hostinger --}}
        <div style="font-size:.67rem;font-weight:700;letter-spacing:.08em;margin-top:.1rem;display:flex;align-items:center;gap:.4rem;">
            <span class="ws-dot" :style="hostingerOnline?'background:#3fcf8e':'background:#888'"></span>
            <span style="color:var(--blue);">HOSTINGER</span>
        </div>

        <button @click="deployHostinger()" :disabled="!wsReady || deploying" class="btn-deploy"
            style="background:rgba(111,177,224,.12);color:var(--blue);border:1px solid rgba(111,177,224,.35);padding:.65rem;">
            <span x-show="!deploying">🌐 Deploy ke Hostinger</span>
            <span x-show="deploying" style="animation:pulse 1s infinite">⏳ Deploying...</span>
        </button>

        {{-- Hostinger deploy steps --}}
        <template x-if="hostSteps.length">
            <div style="background:#0a0e0a;border-radius:.4rem;padding:.6rem .8rem;border:1px solid rgba(111,177,224,.15);">
                <template x-for="step in hostSteps" :key="step.label">
                    <div class="step-row">
                        <span class="step-status"
                            :style="step.status==='ok'?'color:#3fcf8e':step.status==='fail'?'color:#e8645a':step.status==='running'?'color:#d9a441':step.status==='skip'?'color:#4a7a7a':'color:#4a6a4a'"
                            x-text="step.status==='ok'?'✅':step.status==='fail'?'❌':step.status==='running'?'⏳':step.status==='skip'?'⊘':'○'">
                        </span>
                        <span :style="step.status==='skip'?'color:#4a7a7a;text-decoration:line-through':'color:#a0c8a0'"
                              x-text="step.label + (step.status==='skip' ? ' (n/a)' : '')"></span>
                    </div>
                </template>
            </div>
        </template>

        <button @click="runCmd('remote:status')" :disabled="!wsReady" class="btn-deploy" style="background:rgba(255,255,255,.03);color:var(--mut2);border:1px solid var(--line);">
            remote: status
        </button>
        <button @click="runCmd('remote:migrate')" :disabled="!wsReady" class="btn-deploy" style="background:rgba(111,177,224,.06);color:var(--blue);border:1px solid rgba(111,177,224,.2);">
            remote: migrate
        </button>
        <button @click="runCmd('remote:optimize')" :disabled="!wsReady" class="btn-deploy" style="background:rgba(255,255,255,.03);color:var(--mut2);border:1px solid var(--line);">
            remote: optimize
        </button>
        <button @click="runCmd('remote:log')" :disabled="!wsReady" class="btn-deploy" style="background:rgba(255,255,255,.03);color:var(--mut2);border:1px solid var(--line);">
            remote: log laravel
        </button>
        <button @click="runCmd('remote:composer')" :disabled="!wsReady" class="btn-deploy" style="background:rgba(255,255,255,.03);color:var(--mut2);border:1px solid var(--line);">
            remote: composer install
        </button>
    </div>
</div>

{{-- FILE EDITOR + LOG TABS --}}
<div class="glass-card" style="overflow:hidden;">
    <div style="display:flex;gap:0;border-bottom:1px solid var(--line);">
        <button @click="activeTab='files'" :style="activeTab==='files'?'border-bottom:2px solid var(--emer);color:var(--emer2);':'color:var(--mut);'"
            style="padding:.6rem 1.1rem;background:none;border:none;border-bottom:2px solid transparent;cursor:pointer;font-size:.78rem;font-weight:600;">
            📁 File Editor
        </button>
        <button @click="activeTab='log';startLog('laravel')" :style="activeTab==='log'?'border-bottom:2px solid var(--gold);color:var(--gold2);':'color:var(--mut);'"
            style="padding:.6rem 1.1rem;background:none;border:none;border-bottom:2px solid transparent;cursor:pointer;font-size:.78rem;font-weight:600;">
            📋 Laravel Log
        </button>
        <button @click="activeTab='walog';startLog('wa')" :style="activeTab==='walog'?'border-bottom:2px solid var(--blue);color:var(--blue);':'color:var(--mut);'"
            style="padding:.6rem 1.1rem;background:none;border:none;border-bottom:2px solid transparent;cursor:pointer;font-size:.78rem;font-weight:600;">
            💬 WA Log
        </button>
        <button @click="activeTab='hostlog';runCmd('remote:log')" :style="activeTab==='hostlog'?'border-bottom:2px solid #6fb1e0;color:#6fb1e0;':'color:var(--mut);'"
            style="padding:.6rem 1.1rem;background:none;border:none;border-bottom:2px solid transparent;cursor:pointer;font-size:.78rem;font-weight:600;">
            🌐 Hostinger Log
        </button>
    </div>

    {{-- File Editor --}}
    <div x-show="activeTab==='files'" style="display:grid;grid-template-columns:220px 1fr;min-height:300px;">
        <div style="border-right:1px solid var(--line);overflow-y:auto;max-height:400px;">
            <div style="padding:.5rem .8rem;font-size:.67rem;color:var(--mut);font-weight:600;text-transform:uppercase;letter-spacing:.08em;border-bottom:1px solid var(--line);">
                Direktori
            </div>
            <div class="file-tree">
                <template x-for="dir in editableDirs" :key="dir">
                    <div class="file-entry dir" @click="listFiles(dir)" x-text="'📁 '+dir"></div>
                </template>
            </div>
            <template x-if="fileList.length">
                <div>
                    <div style="padding:.4rem .8rem;font-size:.67rem;color:var(--mut);border-top:1px solid var(--line);border-bottom:1px solid var(--line);background:rgba(0,0,0,.2);" x-text="currentDir"></div>
                    <template x-for="f in fileList" :key="f.name">
                        <div class="file-entry" :class="f.type==='dir'?'dir':''"
                            @click="f.type==='file' ? readFile(currentDir+'/'+f.name) : listFiles(currentDir+'/'+f.name)"
                            x-text="(f.type==='dir'?'📁 ':'📄 ')+f.name+(f.type==='file'?' ('+Math.round(f.size/1024*10)/10+'KB)':'')">
                        </div>
                    </template>
                </div>
            </template>
        </div>
        <div style="display:flex;flex-direction:column;padding:1rem;gap:.7rem;">
            <template x-if="currentFile">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:.75rem;font-family:monospace;color:var(--gold2);" x-text="currentFile"></span>
                    <div style="display:flex;gap:.5rem;">
                        <button @click="saveFile()" :disabled="!wsReady" class="btn-deploy"
                            style="background:rgba(63,207,142,.1);color:var(--emer2);border:1px solid rgba(63,207,142,.25);">
                            💾 Simpan
                        </button>
                        <button @click="currentFile=null;fileContent=''" class="btn-deploy"
                            style="background:rgba(255,255,255,.03);color:var(--mut);border:1px solid var(--line);">
                            ✕
                        </button>
                    </div>
                </div>
            </template>
            <template x-if="!currentFile">
                <div style="color:var(--mut);font-size:.8rem;text-align:center;padding:2rem;">
                    Pilih file dari daftar untuk edit
                </div>
            </template>
            <textarea x-show="currentFile" class="editor-area" x-model="fileContent" rows="16"
                :placeholder="'Loading ' + currentFile + '...'"></textarea>
        </div>
    </div>

    {{-- Log viewer --}}
    <div x-show="activeTab==='log' || activeTab==='walog' || activeTab==='hostlog'" style="padding:1rem;">
        <div style="display:flex;justify-content:space-between;margin-bottom:.5rem;">
            <span style="font-size:.72rem;color:var(--mut);" x-text="activeTab==='log'?'Laravel Log (live tail)':activeTab==='hostlog'?'Hostinger Laravel Log':' WA Service Log (live tail)'"></span>
            <button @click="clearLog()" style="font-size:.68rem;color:var(--mut);background:none;border:none;cursor:pointer;">clear</button>
        </div>
        <div class="terminal" x-ref="logTerminal" x-html="logOutput" style="height:280px;"></div>
    </div>
</div>

</div>{{-- end x-data --}}

<script>
function deployPanel() {
    return {
        ws: null,
        wsReady: false,
        wsConnecting: false,
        _reconnectTimer: null,
        _reconnectDelay: 3000,

        waStatus: { ready: false },
        gitBranch: '',
        lastCommit: '',
        systemInfo: '',

        termOutput: '<span class="dim">// Klik Connect untuk mulai WebSocket session...</span>\n',
        logOutput: '',
        activeTab: 'files',

        deploying: false,
        deploySteps: [],
        hostSteps: [],
        smartSteps: [],
        githubSteps: [],
        hostingerOnline: false,

        // Smart Deploy state
        localChanges: [],
        commitType: 'fix',
        commitDesc: '',

        fileList: [],
        currentDir: '',
        currentFile: null,
        fileContent: '',
        editableDirs: [
            'resources/views',
            'app/Livewire',
            'app/Services',
            'app/Console/Commands',
            'routes',
            'wa-service',
        ],

        WS_URL: 'ws://localhost:3002',
        SECRET: '{{ env("DEPLOY_SECRET", "prb-deploy-secret-2024") }}',

        init() {
            // Auto-connect jika session sudah ada
            setTimeout(() => this.connect(), 500);
        },

        connect() {
            if (this.wsConnecting || this.wsReady) return;
            this.wsConnecting = true;
            this._reconnectDelay = 4000; // reset delay setiap connect baru
            this.appendTerm('Connecting to WebSocket deploy server...', 'info');

            this.ws = new WebSocket(this.WS_URL);

            this.ws.onopen = () => {
                // Auth handshake
                this.ws.send(JSON.stringify({
                    type: 'auth',
                    secret: this.SECRET,
                    user: '{{ auth()->user()->name ?? "admin" }}',
                }));
            };

            this.ws.onmessage = (e) => {
                const msg = JSON.parse(e.data);
                this.handleMessage(msg);
            };

            this.ws.onclose = () => {
                this.wsReady = false;
                this.wsConnecting = false;
                if (this.deploying) this.deploying = false;
                this.appendTerm('WebSocket disconnected. Reconnecting in 4s...', 'stderr');
                clearTimeout(this._reconnectTimer);
                this._reconnectTimer = setTimeout(() => {
                    if (!this.wsReady && !this.wsConnecting) this.connect();
                }, this._reconnectDelay);
            };

            this.ws.onerror = () => {
                this.wsConnecting = false;
                this.wsReady = false;
                this.appendTerm('❌ Tidak bisa connect ke ws://localhost:3002\nJalankan dulu: node ws-deploy/server.js', 'stderr');
            };
        },

        disconnect() {
            clearTimeout(this._reconnectTimer);
            this._reconnectDelay = 999999; // cegah auto-reconnect setelah manual disconnect
            this.ws?.close();
            this.wsReady = false;
        },

        handleMessage(msg) {
            switch (msg.type) {
                case 'hello':
                    break;
                case 'auth_ok':
                    this.wsReady = true;
                    this.wsConnecting = false;
                    this.appendTerm(`✅ Authenticated sebagai ${msg.user}`, 'ok');
                    break;
                case 'auth_fail':
                    this.wsConnecting = false;
                    this.appendTerm('❌ Auth gagal: ' + msg.msg, 'stderr');
                    break;
                case 'cmd_start':
                    this.appendTerm('\n$ ' + msg.display, 'cmd');
                    break;
                case 'line':
                    this.appendTerm(msg.text, msg.stream === 'stderr' ? 'stderr' : '');
                    break;
                case 'done':
                    this.appendTerm(msg.ok ? '✅ OK (exit 0)' : `❌ Exit ${msg.code}`, msg.ok ? 'ok' : 'stderr');
                    this.deploying = false;
                    break;
                case 'error':
                    this.appendTerm('❌ ' + msg.msg, 'stderr');
                    this.deploying = false;
                    break;
                case 'step': {
                    let target;
                    if (msg.target === 'hostinger') target = this.hostSteps;
                    else if (msg.target === 'smart') target = this.smartSteps;
                    else if (msg.target === 'github') target = this.githubSteps;
                    else target = this.deploySteps;
                    const i = target.findIndex(s => s.label === msg.label);
                    if (i >= 0) target[i].status = msg.status;
                    else target.push({ label: msg.label, status: msg.status });
                    const icon = msg.status==='ok'?'✅':msg.status==='fail'?'❌':msg.status==='skip'?'⊘':'⏳';
                    this.appendTerm(`  ${icon} ${msg.label}`, msg.status==='ok'?'ok':'');
                    break;
                }
                case 'deploy_done':
                    this.deploying = false;
                    this.appendTerm('\n' + msg.msg, 'ok');
                    this.sendMsg('status');
                    break;
                case 'git_changes':
                    this.localChanges = msg.files || [];
                    break;
                case 'wa_status':
                    this.waStatus = msg;
                    break;
                case 'git_info':
                    const lines = (msg.output || '').trim().split('\n');
                    this.lastCommit = lines[0] || '';
                    const branchLine = lines.find(l => l.startsWith('On branch') || l.startsWith('HEAD'));
                    this.gitBranch = branchLine ? branchLine.replace('On branch ', '') : 'main';
                    break;
                case 'system_info':
                    this.systemInfo = msg.output?.trim() || '';
                    break;
                case 'hostinger_status':
                    this.hostingerOnline = msg.online;
                    break;
                case 'file_content':
                    this.currentFile = msg.path;
                    this.fileContent = msg.content;
                    break;
                case 'file_saved':
                    this.appendTerm(`💾 Tersimpan: ${msg.path} (${msg.bytes} bytes)`, 'ok');
                    break;
                case 'file_list':
                    this.currentDir = msg.path;
                    this.fileList = msg.entries;
                    break;
                case 'log_line':
                    this.appendLog(msg.text, msg.live);
                    break;
                case 'pong':
                    break;
            }
        },

        sendMsg(type, extra = {}) {
            if (this.wsReady) this.ws.send(JSON.stringify({ type, ...extra }));
        },

        runCmd(cmd) {
            this.sendMsg('cmd', { cmd });
        },

        refreshChanges() {
            if (!this.wsReady) return;
            this.sendMsg('git:changes');
        },

        githubDeploy() {
            if (!this.wsReady || this.deploying) return;
            if (this.localChanges.length === 0) {
                this.appendTerm('ℹ️  Tidak ada perubahan untuk di-commit', 'info');
                return;
            }
            if (!this.commitDesc.trim()) {
                this.appendTerm('⚠️  Isi pesan commit terlebih dahulu', 'stderr');
                return;
            }
            const fullMsg = `${this.commitType}: ${this.commitDesc.trim()}`;
            this.deploying = true;
            this.githubSteps = [];
            this.smartSteps = [];
            this.appendTerm(`\n📦 Commit + Push GitHub\n   Commit: "${fullMsg}"\n   Files: ${this.localChanges.length} perubahan`, 'info');
            this.ws.send(JSON.stringify({ type: 'deploy:github', commitMsg: fullMsg }));
        },

        smartDeploy() {
            if (!this.wsReady || this.deploying) return;
            if (this.localChanges.length === 0) {
                this.appendTerm('ℹ️  Tidak ada perubahan untuk di-deploy', 'info');
                return;
            }
            if (!this.commitDesc.trim()) {
                this.appendTerm('⚠️  Isi pesan commit terlebih dahulu', 'stderr');
                return;
            }
            const fullMsg = `${this.commitType}: ${this.commitDesc.trim()}`;
            this.deploying = true;
            this.smartSteps = [];
            this.githubSteps = [];
            this.appendTerm(`\n🚀 Smart Deploy dimulai\n   Commit: "${fullMsg}"\n   Files: ${this.localChanges.length} perubahan`, 'info');
            this.ws.send(JSON.stringify({ type: 'deploy:smart', commitMsg: fullMsg }));
        },

        fullDeploy() {
            this.deploying = true;
            this.deploySteps = [];
            this.sendMsg('cmd', { cmd: 'deploy:full' });
        },

        deployHostinger() {
            if (!confirm('Deploy ke Hostinger apotik.dokterkuklinik.com?\n\nIni akan rsync semua file lokal ke server.')) return;
            this.deploying = true;
            this.hostSteps = [];
            this.appendTerm('\n🌐 Memulai deploy ke Hostinger...', 'info');
            this.sendMsg('cmd', { cmd: 'deploy:hostinger' });
        },

        appendTerm(text, cls = '') {
            const span = cls ? `<span class="${cls}">${this.esc(text)}</span>\n` : this.esc(text) + '\n';
            this.termOutput += span;
            this.$nextTick(() => {
                if (this.$refs.terminal) this.$refs.terminal.scrollTop = this.$refs.terminal.scrollHeight;
            });
        },

        clearTerm() { this.termOutput = ''; },

        appendLog(text, live = false) {
            const cls = live ? 'info' : 'dim';
            this.logOutput += `<span class="${cls}">${this.esc(text)}</span>\n`;
            this.$nextTick(() => {
                if (this.$refs.logTerminal) this.$refs.logTerminal.scrollTop = this.$refs.logTerminal.scrollHeight;
            });
        },

        clearLog() { this.logOutput = ''; },

        startLog(file) {
            this.clearLog();
            this.sendMsg('log:tail', { file });
        },

        listFiles(dir) {
            this.sendMsg('file:list', { path: dir });
        },

        readFile(path) {
            this.sendMsg('file:read', { path });
        },

        saveFile() {
            if (!this.currentFile) return;
            if (!confirm(`Simpan perubahan ke ${this.currentFile}?`)) return;
            this.sendMsg('file:write', { path: this.currentFile, content: this.fileContent });
        },

        esc(str) {
            return String(str)
                .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        },
    };
}
</script>
</x-app-layout>
