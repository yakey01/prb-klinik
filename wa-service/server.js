/**
 * PRB Klinik - Free WhatsApp Gateway
 * Self-hosted, no quota, no monthly fee
 * Uses whatsapp-web.js (unofficial WA Web protocol)
 */

const express = require('express');
const { Client, LocalAuth, MessageMedia } = require('whatsapp-web.js');
const qrcode = require('qrcode');
const fs = require('fs');
const path = require('path');

const app = express();
app.use(express.json());

// Simple API key auth (ganti di .env)
const API_SECRET = process.env.WA_SECRET || 'prb-klinik-secret-2024';

function authMiddleware(req, res, next) {
    const key = req.headers['x-api-key'] || req.query.key;
    if (key !== API_SECRET) return res.status(401).json({ error: 'Unauthorized' });
    next();
}

// State
let qrDataUrl = null;
let isReady = false;
let clientInfo = null;
let messagesSent = 0;
let startTime = Date.now();

const client = new Client({
    authStrategy: new LocalAuth({
        dataPath: path.join(__dirname, 'sessions')
    }),
    puppeteer: {
        headless: true,
        args: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-accelerated-2d-canvas',
            '--no-first-run',
            '--no-zygote',
            '--disable-gpu'
        ]
    }
});

client.on('qr', async (qr) => {
    console.log('\n📱 QR Code tersedia! Buka http://localhost:3001/qr di browser');
    qrDataUrl = await qrcode.toDataURL(qr, { scale: 8 });
    isReady = false;
});

client.on('authenticated', () => {
    console.log('✅ WA authenticated');
    qrDataUrl = null;
});

client.on('auth_failure', (msg) => {
    console.error('❌ Auth gagal:', msg);
    isReady = false;
});

client.on('ready', () => {
    isReady = true;
    qrDataUrl = null;
    clientInfo = client.info;
    console.log(`✅ WA Ready! Nomor: ${client.info.wid.user}`);
    console.log(`   Nama: ${client.info.pushname}`);
});

client.on('disconnected', (reason) => {
    console.log('⚠️  WA disconnected:', reason);
    isReady = false;
    clientInfo = null;
});

// Normalize nomor: 08xx -> 628xx
function normalizeNomor(nomor) {
    nomor = nomor.replace(/\D/g, '');
    if (nomor.startsWith('0')) return '62' + nomor.slice(1);
    if (nomor.startsWith('8')) return '62' + nomor;
    return nomor;
}

// ============================================================
// API ENDPOINTS
// ============================================================

// GET /status - cek koneksi (public, no auth)
app.get('/status', (req, res) => {
    res.json({
        ready: isReady,
        uptime_seconds: Math.floor((Date.now() - startTime) / 1000),
        messages_sent: messagesSent,
        nomor: clientInfo?.wid?.user || null,
        nama: clientInfo?.pushname || null
    });
});

// GET /qr - tampilkan QR code di browser (public, no auth)
app.get('/qr', (req, res) => {
    if (isReady) {
        return res.send(`
            <!DOCTYPE html><html><head><title>WA Service</title>
            <style>body{font-family:sans-serif;text-align:center;padding:50px;background:#0a1410;color:#3fcf8e;}
            .check{font-size:80px;margin:20px;}</style></head>
            <body><div class="check">✅</div>
            <h2 style="color:#d9a441">WhatsApp Terhubung!</h2>
            <p>Nomor: <strong>${clientInfo?.wid?.user}</strong></p>
            <p>Nama: <strong>${clientInfo?.pushname}</strong></p>
            <p style="color:#aaa">Pesan terkirim: ${messagesSent}</p>
            </body></html>
        `);
    }
    if (!qrDataUrl) {
        return res.send(`
            <!DOCTYPE html><html><head><title>WA Service</title>
            <meta http-equiv="refresh" content="3">
            <style>body{font-family:sans-serif;text-align:center;padding:50px;background:#0a1410;color:#d9a441;}</style></head>
            <body><h2>⏳ Memulai WhatsApp...</h2>
            <p style="color:#aaa">Halaman akan refresh otomatis</p></body></html>
        `);
    }
    res.send(`
        <!DOCTYPE html><html><head><title>Scan QR WA</title>
        <meta http-equiv="refresh" content="30">
        <style>
        body{font-family:sans-serif;text-align:center;padding:30px;background:#0a1410;color:#fff;}
        img{border:8px solid #d9a441;border-radius:12px;max-width:300px;}
        h2{color:#d9a441} p{color:#aaa} .warn{color:#e8645a;font-weight:bold;}
        </style></head>
        <body>
        <h2>📱 Scan QR Code WhatsApp</h2>
        <p>Buka WhatsApp di HP → <strong>Perangkat Tertaut</strong> → <strong>Tambahkan Perangkat</strong></p>
        <img src="${qrDataUrl}" alt="QR Code">
        <p class="warn">⚠️ QR expire dalam 30 detik, halaman auto-refresh</p>
        <p style="color:#3fcf8e">Gunakan nomor khusus klinik (bukan nomor pribadi)</p>
        </body></html>
    `);
});

// POST /send - kirim pesan WA (requires auth)
app.post('/send', authMiddleware, async (req, res) => {
    const { to, message } = req.body;

    if (!to || !message) {
        return res.status(400).json({ error: 'Field "to" dan "message" wajib diisi' });
    }
    if (!isReady) {
        return res.status(503).json({
            error: 'WhatsApp belum terhubung',
            hint: 'Buka http://localhost:3001/qr untuk scan QR code'
        });
    }

    const nomor = normalizeNomor(String(to));
    const chatId = `${nomor}@c.us`;

    try {
        await client.sendMessage(chatId, message);
        messagesSent++;
        console.log(`📤 Sent to ${nomor}: ${message.slice(0, 50)}...`);
        return res.json({
            success: true,
            to: nomor,
            messages_sent_total: messagesSent
        });
    } catch (err) {
        console.error(`❌ Send error to ${nomor}:`, err.message);
        return res.status(500).json({
            success: false,
            error: err.message,
            to: nomor
        });
    }
});

// POST /send-bulk - kirim ke banyak nomor (requires auth)
app.post('/send-bulk', authMiddleware, async (req, res) => {
    const { messages } = req.body; // [{to, message}, ...]

    if (!Array.isArray(messages) || messages.length === 0) {
        return res.status(400).json({ error: 'Field "messages" harus array' });
    }
    if (!isReady) {
        return res.status(503).json({ error: 'WhatsApp belum terhubung' });
    }

    const results = [];
    for (const item of messages) {
        const nomor = normalizeNomor(String(item.to));
        const chatId = `${nomor}@c.us`;
        try {
            await client.sendMessage(chatId, item.message);
            messagesSent++;
            results.push({ to: nomor, success: true });
            // Jeda 1 detik antar pesan (hindari spam detection)
            await new Promise(r => setTimeout(r, 1000));
        } catch (err) {
            results.push({ to: nomor, success: false, error: err.message });
        }
    }

    res.json({ results, total: results.length, success: results.filter(r => r.success).length });
});

// POST /logout - logout WA session (requires auth)
app.post('/logout', authMiddleware, async (req, res) => {
    try {
        await client.logout();
        isReady = false;
        res.json({ success: true, message: 'Logged out' });
    } catch (err) {
        res.status(500).json({ error: err.message });
    }
});

// Start
const PORT = process.env.PORT || 3001;
app.listen(PORT, () => {
    console.log(`\n🚀 PRB Klinik WA Service`);
    console.log(`   Port    : ${PORT}`);
    console.log(`   QR URL  : http://localhost:${PORT}/qr`);
    console.log(`   Status  : http://localhost:${PORT}/status`);
    console.log(`   Secret  : ${API_SECRET}`);
    console.log('\n⏳ Menginisialisasi WhatsApp...\n');
});

client.initialize();
