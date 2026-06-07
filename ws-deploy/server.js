/**
 * PRB Klinik — WebSocket Deploy & Monitor Server
 * Port 3002 | Authenticated | Command whitelist
 *
 * Fitur:
 *   - Real-time deploy output streaming ke browser
 *   - File viewer/editor (path whitelist)
 *   - Live log tailing (laravel.log, wa-service logs)
 *   - PM2 + WA service status
 *   - Git log & status
 *   - SSH remote command ke Hostinger
 *   - Rsync deploy lokal → Hostinger
 */

const { WebSocketServer, WebSocket } = require('ws');
const { spawn, exec, execSync } = require('child_process');
const fs               = require('fs');
const path             = require('path');
const http             = require('http');

// ── Binary resolver — cari path lengkap saat startup ────────────────────────
function resolveBin(name) {
  try {
    return execSync(`which ${name} 2>/dev/null || command -v ${name} 2>/dev/null`, { shell: true })
      .toString().trim() || null;
  } catch { return null; }
}

const BIN = {
  git: resolveBin('git') || 'git',
  php: resolveBin('php') || 'php',
  pm2: resolveBin('pm2') || null,      // null = tidak terinstall
  rsync: resolveBin('rsync') || 'rsync',
  ssh: resolveBin('ssh') || 'ssh',
};

console.log('  Binaries  :', Object.entries(BIN).map(([k,v])=>`${k}=${v||'❌ missing'}`).join(', '));

const PORT        = process.env.WS_DEPLOY_PORT || 3002;
const SECRET      = process.env.DEPLOY_SECRET   || 'prb-deploy-secret-2024';
const APP_ROOT    = process.env.APP_ROOT         || path.resolve(__dirname, '..');

// ── SSH / Hostinger config ────────────────────────────────────────────────────
const SSH_HOST    = process.env.SSH_HOST    || '153.92.8.132';
const SSH_PORT    = process.env.SSH_PORT    || '65002';
const SSH_USER    = process.env.SSH_USER    || 'u454362045';
const SSH_KEY     = process.env.SSH_KEY     || path.join(process.env.HOME || '/root', '.ssh/id_ed25519');
const REMOTE_ROOT = process.env.REMOTE_ROOT || '/home/u454362045/domains/dokterkuklinik.com/public_html/apotik';

// ── Whitelist command (lokal) ─────────────────────────────────────────────────
// BIN.pm2 bisa null jika pm2 tidak terinstall — handler akan skip gracefully
const COMMANDS = {
  'git:status':        [BIN.git, ['status', '--short']],
  'git:log':           [BIN.git, ['log', '--oneline', '-15', '--decorate']],
  'git:pull':          [BIN.git, ['pull', 'origin', 'main']],
  'git:diff':          [BIN.git, ['diff', '--stat', 'HEAD~1']],
  'migrate':           [BIN.php, ['artisan', 'migrate', '--force']],
  'cache:clear':       [BIN.php, ['artisan', 'cache:clear']],
  'cache:build':       [BIN.php, ['artisan', 'optimize']],
  'view:clear':        [BIN.php, ['artisan', 'view:clear']],
  'route:cache':       [BIN.php, ['artisan', 'route:cache']],
  'queue:restart':     [BIN.php, ['artisan', 'queue:restart']],
  'storage:link':      [BIN.php, ['artisan', 'storage:link']],
  'pm2:list':          BIN.pm2 ? [BIN.pm2, ['jlist']] : null,
  'pm2:restart:wa':    BIN.pm2 ? [BIN.pm2, ['restart', 'wa-klinik',    '--update-env']] : null,
  'pm2:restart:queue': BIN.pm2 ? [BIN.pm2, ['restart', 'queue-klinik', '--update-env']] : null,
  'pm2:start:wa':      BIN.pm2 ? [BIN.pm2, ['start', path.join(APP_ROOT,'wa-service','server.js'), '--name', 'wa-klinik']] : null,
  'deploy:full':       null,  // handled separately
  'deploy:hostinger':  null,  // handled separately
  'deploy:github':     null,  // handled separately
};

// ── Whitelist command remote (Hostinger) ──────────────────────────────────────
const REMOTE_COMMANDS = {
  'remote:status':   'php -v | head -1 && mysql --version && echo "---" && df -h /public_html | tail -1',
  'remote:php':      `cd ${REMOTE_ROOT} && php -v`,
  'remote:migrate':  `cd ${REMOTE_ROOT} && php artisan migrate --force`,
  'remote:optimize': `cd ${REMOTE_ROOT} && php artisan optimize`,
  'remote:env':      `ls -la ${REMOTE_ROOT}/.env && head -5 ${REMOTE_ROOT}/.env`,
  'remote:log':      `tail -n 60 ${REMOTE_ROOT}/storage/logs/laravel.log 2>/dev/null || echo 'No log yet'`,
  'remote:ls':       `ls -la ${REMOTE_ROOT}/`,
  'remote:composer': `cd ${REMOTE_ROOT} && composer install --no-dev --optimize-autoloader 2>&1`,
  'remote:storage':  `cd ${REMOTE_ROOT} && php artisan storage:link`,
};

// ── File whitelist (bisa dilihat/edit) ───────────────────────────────────────
const EDITABLE_PATHS = [
  'resources/views',
  'app/Livewire',
  'app/Services',
  'app/Console/Commands',
  'routes/web.php',
  'routes/console.php',
  'wa-service/server.js',
  'wa-service/ecosystem.config.js',
  'ws-deploy/server.js',
];

// ── HTTP server ───────────────────────────────────────────────────────────────
const httpServer = http.createServer((req, res) => {
  res.writeHead(200, { 'Content-Type': 'text/plain' });
  res.end('PRB Klinik WS Deploy Server\n');
});

const wss = new WebSocketServer({ server: httpServer });

// ── Helpers ───────────────────────────────────────────────────────────────────
const send = (ws, type, payload) => {
  if (ws.readyState === WebSocket.OPEN)
    ws.send(JSON.stringify({ type, ...payload, ts: Date.now() }));
};

const sendLine = (ws, text, stream = 'stdout') =>
  send(ws, 'line', { text, stream });

const sendDone = (ws, cmd, code) =>
  send(ws, 'done', { cmd, code, ok: code === 0 });

const sendErr = (ws, msg) =>
  send(ws, 'error', { msg });

// ── SSH args builder ──────────────────────────────────────────────────────────
function sshArgs(remoteCmd) {
  return [
    '-p', SSH_PORT,
    '-i', SSH_KEY,
    '-o', 'StrictHostKeyChecking=no',
    '-o', 'BatchMode=yes',
    '-o', 'ConnectTimeout=15',
    `${SSH_USER}@${SSH_HOST}`,
    remoteCmd,
  ];
}

// ── Run remote SSH command, stream output ─────────────────────────────────────
function runRemoteCommand(ws, label, remoteCmd) {
  send(ws, 'cmd_start', { cmd: label, display: `[Hostinger] ${remoteCmd.slice(0, 80)}` });
  const proc = spawn('ssh', sshArgs(remoteCmd), { env: process.env });
  proc.stdout.on('data', d =>
    d.toString().split('\n').filter(Boolean).forEach(l => sendLine(ws, l, 'stdout')));
  proc.stderr.on('data', d =>
    d.toString().split('\n').filter(Boolean).forEach(l => sendLine(ws, l, 'stderr')));
  proc.on('close', code => sendDone(ws, label, code));
  proc.on('error', err => sendErr(ws, err.message));
}

// ── Rsync lokal → Hostinger ───────────────────────────────────────────────────
function runRsync(ws) {
  send(ws, 'cmd_start', { cmd: 'rsync', display: `rsync ${APP_ROOT}/ → ${SSH_HOST}:${REMOTE_ROOT}/` });

  // Exclude: git, node_modules, vendor (akan composer install di remote), .env, storage/logs
  const excludes = [
    '--exclude=.git',
    '--exclude=node_modules',
    '--exclude=ws-deploy/node_modules',
    '--exclude=vendor',
    '--exclude=.env',
    '--exclude=storage/logs',
    '--exclude=storage/framework/cache',
    '--exclude=storage/framework/sessions',
    '--exclude=storage/framework/views',
    '--exclude=bootstrap/cache',
    '--exclude=*.bak',
  ];

  const args = [
    '-avz', '--delete',
    ...excludes,
    '-e', `ssh -p ${SSH_PORT} -i ${SSH_KEY} -o StrictHostKeyChecking=no -o BatchMode=yes`,
    `${APP_ROOT}/`,
    `${SSH_USER}@${SSH_HOST}:${REMOTE_ROOT}/`,
  ];

  const proc = spawn('rsync', args);
  proc.stdout.on('data', d =>
    d.toString().split('\n').filter(Boolean).forEach(l => sendLine(ws, l, 'stdout')));
  proc.stderr.on('data', d =>
    d.toString().split('\n').filter(Boolean).forEach(l => sendLine(ws, l, 'stderr')));
  proc.on('close', code => sendDone(ws, 'rsync', code));
  proc.on('error', err => sendErr(ws, err.message));
}

// ── Full local deploy pipeline ────────────────────────────────────────────────
async function runFullDeploy(ws) {
  send(ws, 'deploy_start', { steps: 6 });

  const steps = [
    ['Git Pull',      'git:pull'],
    ['Migrate',       'migrate'],
    ['Cache Build',   'cache:build'],
    ['Route Cache',   'route:cache'],
    ['Restart WA',    'pm2:restart:wa'],
    ['Restart Queue', 'pm2:restart:queue'],
  ];

  for (const [label, cmdKey] of steps) {
    send(ws, 'step', { label, status: 'running' });

    // Skip git pull gracefully if this is not a git repo
    if (cmdKey === 'git:pull' && !fs.existsSync(path.join(APP_ROOT, '.git'))) {
      sendLine(ws, '  ⚠️  Bukan git repository — git pull dilewati');
      send(ws, 'step', { label, status: 'ok' });
      continue;
    }

    const def = COMMANDS[cmdKey];
    if (!def) {
      // Command null = binary tidak tersedia (misal: pm2 tidak terinstall)
      if (cmdKey.startsWith('pm2:') && !BIN.pm2) {
        sendLine(ws, `  ⚠️  pm2 tidak terinstall — step "${label}" dilewati`);
        sendLine(ws, '      Install pm2: npm install -g pm2');
        send(ws, 'step', { label, status: 'skip' });
      } else {
        send(ws, 'step', { label, status: 'ok' });
      }
      continue;
    }
    const [bin, args] = def;
    const code = await spawnLocal(ws, bin, args);
    send(ws, 'step', { label, status: code === 0 ? 'ok' : 'fail' });
  }

  send(ws, 'deploy_done', { msg: 'Deploy lokal selesai ✅' });
}

// ── Hostinger full deploy pipeline ───────────────────────────────────────────
async function runHostingerDeploy(ws) {
  const hostSteps = [
    { label: 'Rsync Files',       fn: () => spawnPromise(ws, 'rsync', buildRsyncArgs()) },
    { label: 'Composer Install',  fn: () => spawnRemote(ws, `cd ${REMOTE_ROOT} && composer install --no-dev --optimize-autoloader 2>&1`) },
    { label: 'Migrate',           fn: () => spawnRemote(ws, `cd ${REMOTE_ROOT} && php artisan migrate --force`) },
    { label: 'Optimize',          fn: () => spawnRemote(ws, `cd ${REMOTE_ROOT} && php artisan optimize`) },
    { label: 'Storage Link',      fn: () => spawnRemote(ws, `cd ${REMOTE_ROOT} && php artisan storage:link 2>&1 || true`) },
    { label: 'Permissions',       fn: () => spawnRemote(ws, `chmod -R 775 ${REMOTE_ROOT}/storage ${REMOTE_ROOT}/bootstrap/cache`) },
  ];

  send(ws, 'deploy_start', { steps: hostSteps.length, target: 'hostinger' });

  for (const { label, fn } of hostSteps) {
    send(ws, 'step', { label, status: 'running', target: 'hostinger' });
    const code = await fn();
    send(ws, 'step', { label, status: code === 0 ? 'ok' : 'fail', target: 'hostinger' });
    if (code !== 0 && label !== 'Storage Link') break;
  }

  send(ws, 'deploy_done', { msg: 'Deploy Hostinger selesai ✅', target: 'hostinger' });
}

function buildRsyncArgs() {
  const excludes = [
    '--exclude=.git', '--exclude=node_modules', '--exclude=ws-deploy/node_modules',
    '--exclude=vendor', '--exclude=.env', '--exclude=storage/logs/*',
    '--exclude=storage/framework/cache/*', '--exclude=storage/framework/sessions/*',
    '--exclude=storage/framework/views/*', '--exclude=bootstrap/cache/*', '--exclude=*.bak',
  ];
  return [
    '-avz', '--delete', ...excludes,
    '-e', `ssh -p ${SSH_PORT} -i ${SSH_KEY} -o StrictHostKeyChecking=no -o BatchMode=yes`,
    `${APP_ROOT}/`,
    `${SSH_USER}@${SSH_HOST}:${REMOTE_ROOT}/`,
  ];
}

function spawnPromise(ws, bin, args) {
  return new Promise(resolve => {
    const proc = spawn(bin, args);
    proc.stdout.on('data', d =>
      d.toString().split('\n').filter(Boolean).forEach(l => sendLine(ws, `  ${l}`)));
    proc.stderr.on('data', d =>
      d.toString().split('\n').filter(Boolean).forEach(l => sendLine(ws, `  ${l}`, 'stderr')));
    proc.on('close', resolve);
    proc.on('error', err => { sendErr(ws, err.message); resolve(1); });
  });
}

// spawnLocal: sama seperti spawnPromise tapi dengan cwd=APP_ROOT (untuk lokal deploy)
function spawnLocal(ws, bin, args) {
  return new Promise(resolve => {
    const proc = spawn(bin, args, { cwd: APP_ROOT, env: { ...process.env } });
    proc.stdout.on('data', d =>
      d.toString().split('\n').filter(Boolean).forEach(l => sendLine(ws, `  ${l}`)));
    proc.stderr.on('data', d =>
      d.toString().split('\n').filter(Boolean).forEach(l => sendLine(ws, `  ${l}`, 'stderr')));
    proc.on('close', resolve);
    proc.on('error', err => { sendErr(ws, err.message); resolve(1); });
  });
}

function spawnRemote(ws, remoteCmd) {
  return spawnPromise(ws, 'ssh', sshArgs(remoteCmd));
}

// ── Git status → kirim daftar file berubah ke client ─────────────────────────
function sendGitChanges(ws) {
  return new Promise(resolve => {
    const proc = spawn(BIN.git, ['status', '--porcelain', '-u'], { cwd: APP_ROOT });
    let out = '';
    proc.stdout.on('data', d => { out += d.toString(); });
    proc.stderr.on('data', d => { out += d.toString(); });
    proc.on('close', () => {
      const lines = out.trim().split('\n').filter(Boolean);
      const files = lines.map(l => ({
        xy: l.slice(0, 2).trim() || '?',
        path: l.slice(3).trim(),
      }));
      send(ws, 'git_changes', { files, count: files.length });
      resolve();
    });
    proc.on('error', () => { send(ws, 'git_changes', { files: [], count: 0 }); resolve(); });
  });
}

// ── Smart Deploy: git add → commit → push → rsync → optimize ─────────────────
async function runSmartDeploy(ws, commitMsg) {
  const msg = (commitMsg || '').trim() || 'chore: update';
  const steps = [
    { label: 'Git Add',      fn: () => spawnLocal(ws, BIN.git, ['add', '-A']) },
    { label: 'Git Commit',   fn: () => spawnLocal(ws, BIN.git, ['commit', '-m', msg]) },
    { label: 'Git Push',     fn: () => spawnLocal(ws, BIN.git, ['push', 'origin', 'main']) },
    { label: 'Rsync Files',  fn: () => spawnPromise(ws, BIN.rsync, buildRsyncArgs()) },
    { label: 'Remote Cache', fn: () => spawnRemote(ws, `cd ${REMOTE_ROOT} && php artisan optimize`) },
  ];

  send(ws, 'deploy_start', { steps: steps.length, target: 'smart' });

  for (const { label, fn } of steps) {
    send(ws, 'step', { label, status: 'running', target: 'smart' });
    const code = await fn();

    // git commit exit 1 = "nothing to commit" → bukan error fatal
    if (label === 'Git Commit' && code !== 0) {
      sendLine(ws, '  ℹ️  Tidak ada perubahan baru untuk di-commit');
      send(ws, 'step', { label, status: 'skip', target: 'smart' });
      continue;
    }
    send(ws, 'step', { label, status: code === 0 ? 'ok' : 'fail', target: 'smart' });
    if (code !== 0) { sendErr(ws, `Step "${label}" gagal (exit ${code})`); break; }
  }

  send(ws, 'deploy_done', { msg: 'Smart Deploy selesai ✅', target: 'smart' });
  sendGitChanges(ws); // refresh daftar perubahan setelah deploy
}

// ── GitHub-only deploy: git add → commit → push (no rsync) ───────────────────
async function runGithubDeploy(ws, commitMsg) {
  const msg = (commitMsg || '').trim() || 'chore: update';
  const steps = [
    { label: 'Git Add',    fn: () => spawnLocal(ws, BIN.git, ['add', '-A']) },
    { label: 'Git Commit', fn: () => spawnLocal(ws, BIN.git, ['commit', '-m', msg]) },
    { label: 'Git Push',   fn: () => spawnLocal(ws, BIN.git, ['push', 'origin', 'main']) },
  ];

  send(ws, 'deploy_start', { steps: steps.length, target: 'github' });

  for (const { label, fn } of steps) {
    send(ws, 'step', { label, status: 'running', target: 'github' });
    const code = await fn();

    if (label === 'Git Commit' && code !== 0) {
      sendLine(ws, '  ℹ️  Tidak ada perubahan baru untuk di-commit');
      send(ws, 'step', { label, status: 'skip', target: 'github' });
      continue;
    }
    send(ws, 'step', { label, status: code === 0 ? 'ok' : 'fail', target: 'github' });
    if (code !== 0) { sendErr(ws, `Step "${label}" gagal (exit ${code})`); break; }
  }

  send(ws, 'deploy_done', { msg: 'Commit & Push GitHub selesai ✅', target: 'github' });
  sendGitChanges(ws);
}

// ── Run whitelisted local command, stream output ──────────────────────────────
function runCommand(ws, cmdKey, extraArgs = []) {
  if (cmdKey === 'deploy:full')      return runFullDeploy(ws);
  if (cmdKey === 'deploy:hostinger') return runHostingerDeploy(ws);

  // Remote command?
  if (cmdKey.startsWith('remote:')) {
    const remoteCmd = REMOTE_COMMANDS[cmdKey];
    if (!remoteCmd) return sendErr(ws, `Unknown remote command: ${cmdKey}`);
    return runRemoteCommand(ws, cmdKey, remoteCmd);
  }

  const def = COMMANDS[cmdKey];
  if (!def) return sendErr(ws, `Unknown command: ${cmdKey}`);

  const [bin, args] = def;
  const allArgs = [...args, ...extraArgs];
  send(ws, 'cmd_start', { cmd: cmdKey, display: `${bin} ${allArgs.join(' ')}` });

  const proc = spawn(bin, allArgs, {
    cwd: APP_ROOT,
    env: { ...process.env, FORCE_COLOR: '0' },
  });

  proc.stdout.on('data', d =>
    d.toString().split('\n').filter(Boolean).forEach(l => sendLine(ws, l, 'stdout')));
  proc.stderr.on('data', d =>
    d.toString().split('\n').filter(Boolean).forEach(l => sendLine(ws, l, 'stderr')));
  proc.on('close', code => sendDone(ws, cmdKey, code));
  proc.on('error', err => sendErr(ws, err.message));
}

// ── File operations ───────────────────────────────────────────────────────────
function isAllowedPath(filePath) {
  const normalized = path.normalize(filePath).replace(/\\/g, '/');
  return EDITABLE_PATHS.some(allowed => normalized.startsWith(allowed));
}

function handleFileRead(ws, filePath) {
  if (!isAllowedPath(filePath)) return sendErr(ws, `Path tidak diizinkan: ${filePath}`);
  const abs = path.join(APP_ROOT, filePath);
  try {
    const content = fs.readFileSync(abs, 'utf8');
    send(ws, 'file_content', { path: filePath, content, size: content.length });
  } catch (e) {
    sendErr(ws, `Tidak bisa baca: ${e.message}`);
  }
}

function handleFileWrite(ws, filePath, content) {
  if (!isAllowedPath(filePath)) return sendErr(ws, `Path tidak diizinkan: ${filePath}`);
  const abs = path.join(APP_ROOT, filePath);
  try {
    const bak = abs + '.bak';
    if (fs.existsSync(abs)) fs.copyFileSync(abs, bak);
    fs.writeFileSync(abs, content, 'utf8');
    send(ws, 'file_saved', { path: filePath, bytes: Buffer.byteLength(content) });
  } catch (e) {
    sendErr(ws, `Tidak bisa tulis: ${e.message}`);
  }
}

function handleFileList(ws, dirPath) {
  const abs = path.join(APP_ROOT, dirPath || '');
  try {
    const entries = fs.readdirSync(abs, { withFileTypes: true }).map(e => ({
      name: e.name,
      type: e.isDirectory() ? 'dir' : 'file',
      size: e.isFile() ? fs.statSync(path.join(abs, e.name)).size : 0,
    }));
    send(ws, 'file_list', { path: dirPath, entries });
  } catch (e) {
    sendErr(ws, `Tidak bisa list: ${e.message}`);
  }
}

// ── Log tail (live streaming) ─────────────────────────────────────────────────
const logWatchers = new Map();

function startLogTail(ws, logFile) {
  const logPath = logFile === 'laravel'
    ? path.join(APP_ROOT, 'storage/logs/laravel.log')
    : logFile === 'wa'
      ? path.join(APP_ROOT, 'storage/logs/wa-service-out.log')
      : null;

  if (!logPath) return sendErr(ws, `Log tidak dikenal: ${logFile}`);
  if (!fs.existsSync(logPath)) return sendErr(ws, `File log tidak ada: ${logPath}`);

  const tail = spawn('tail', ['-n', '50', logPath]);
  tail.stdout.on('data', d =>
    d.toString().split('\n').filter(Boolean).forEach(l =>
      send(ws, 'log_line', { file: logFile, text: l })));
  tail.on('close', () => {
    const watcher = spawn('tail', ['-f', '-n', '0', logPath]);
    watcher.stdout.on('data', d =>
      d.toString().split('\n').filter(Boolean).forEach(l =>
        send(ws, 'log_line', { file: logFile, text: l, live: true })));
    logWatchers.set(ws, watcher);
  });
}

function stopLogTail(ws) {
  const w = logWatchers.get(ws);
  if (w) { w.kill(); logWatchers.delete(ws); }
}

// ── WA + PM2 + Hostinger status snapshot ─────────────────────────────────────
function sendStatusSnapshot(ws) {
  http.get('http://localhost:3001/status', res => {
    let d = '';
    res.on('data', c => d += c);
    res.on('end', () => {
      try { send(ws, 'wa_status', JSON.parse(d)); }
      catch { send(ws, 'wa_status', { ready: false }); }
    });
  }).on('error', () => send(ws, 'wa_status', { ready: false }));

  exec(`cd ${APP_ROOT} && git log --oneline -5 && echo "---" && git status --short`, (e, out) => {
    send(ws, 'git_info', { output: out || '' });
  });

  sendGitChanges(ws); // kirim daftar file berubah saat connect

  exec('df -h . | tail -1 && php --version | head -1', { cwd: APP_ROOT }, (e, out) => {
    send(ws, 'system_info', { output: out || '' });
  });

  // Hostinger quick ping (non-blocking)
  exec(`ssh -p ${SSH_PORT} -i ${SSH_KEY} -o StrictHostKeyChecking=no -o BatchMode=yes -o ConnectTimeout=5 ${SSH_USER}@${SSH_HOST} "echo PONG"`,
    (e, out) => {
      send(ws, 'hostinger_status', { online: !e && out.includes('PONG'), host: SSH_HOST });
    });
}

// ── WebSocket connection handler ──────────────────────────────────────────────
wss.on('connection', (ws, req) => {
  const ip = req.socket.remoteAddress;
  let authenticated = false;

  console.log(`[+] Connect dari ${ip}`);
  send(ws, 'hello', { msg: 'PRB Klinik WS Deploy. Kirim auth terlebih dahulu.' });

  ws.on('message', raw => {
    let msg;
    try { msg = JSON.parse(raw); }
    catch { return sendErr(ws, 'JSON tidak valid'); }

    if (!authenticated) {
      if (msg.type === 'auth' && msg.secret === SECRET) {
        authenticated = true;
        send(ws, 'auth_ok', { user: msg.user || 'admin' });
        sendStatusSnapshot(ws);
        console.log(`[✓] Auth OK: ${msg.user || '?'} dari ${ip}`);
        return;
      }
      return send(ws, 'auth_fail', { msg: 'Secret salah atau belum auth' });
    }

    switch (msg.type) {
      case 'cmd':
        runCommand(ws, msg.cmd, msg.args || []);
        break;
      case 'git:changes':
        sendGitChanges(ws);
        break;
      case 'deploy:smart':
        runSmartDeploy(ws, msg.commitMsg || '');
        break;
      case 'deploy:github':
        runGithubDeploy(ws, msg.commitMsg || '');
        break;
      case 'file:read':
        handleFileRead(ws, msg.path);
        break;
      case 'file:write':
        handleFileWrite(ws, msg.path, msg.content);
        break;
      case 'file:list':
        handleFileList(ws, msg.path);
        break;
      case 'log:tail':
        startLogTail(ws, msg.file);
        break;
      case 'log:stop':
        stopLogTail(ws);
        break;
      case 'status':
        sendStatusSnapshot(ws);
        break;
      case 'ping':
        send(ws, 'pong', { ts: Date.now() });
        break;
      default:
        sendErr(ws, `Tipe tidak dikenal: ${msg.type}`);
    }
  });

  ws.on('close', () => {
    stopLogTail(ws);
    console.log(`[-] Disconnect ${ip}`);
  });
});

httpServer.listen(PORT, () => {
  console.log(`\n🚀 PRB Klinik WS Deploy Server`);
  console.log(`   Port      : ${PORT}`);
  console.log(`   AppRoot   : ${APP_ROOT}`);
  console.log(`   SSH Host  : ${SSH_USER}@${SSH_HOST}:${SSH_PORT}`);
  console.log(`   RemoteRoot: ${REMOTE_ROOT}`);
  console.log(`   Secret    : ${SECRET.slice(0, 8)}...`);
  console.log(`\n   Connect   : ws://localhost:${PORT}\n`);
});
