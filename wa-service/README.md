# WA Service — Gratis, Self-hosted

WhatsApp gateway gratis untuk notifikasi pasien PRB Klinik Dokterku.
Menggunakan `whatsapp-web.js` (unofficial WA Web protocol) — **gratis selamanya, tanpa quota**.

## Kebutuhan
- Node.js 18+ (install: `brew install node`)
- Nomor WA dedikasi (SIM card baru, bukan nomor pribadi)

## Cara Install & Jalankan

```bash
# 1. Masuk folder
cd /Users/yaya/Documents/prb-klinik/wa-service

# 2. Install dependencies (~5 menit, unduh Chromium)
npm install

# 3. Jalankan
node server.js
```

## Scan QR

Setelah server jalan, buka browser:
```
http://localhost:3001/qr
```

Scan QR dengan HP pakai WA nomor klinik.

## Cek Status

```
http://localhost:3001/status
```

## Jalankan Otomatis (Production)

Install PM2 agar service tetap aktif setelah restart:

```bash
npm install -g pm2
pm2 start server.js --name wa-klinik
pm2 save
pm2 startup
```

## API

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/status` | GET | Cek koneksi WA |
| `/qr` | GET | Halaman scan QR (browser) |
| `/send` | POST | Kirim pesan (butuh header `x-api-key`) |
| `/logout` | POST | Logout WA session |

### Contoh kirim pesan:
```bash
curl -X POST http://localhost:3001/send \
  -H "Content-Type: application/json" \
  -H "x-api-key: prb-klinik-secret-2024" \
  -d '{"to":"08123456789","message":"Test pesan"}'
```

## Konfigurasi Laravel

Di `/notifikasi` → Pengaturan → pilih **WA Lokal (Gratis)** → simpan.

API Secret default: `prb-klinik-secret-2024` (ubah di `.env` dengan `WA_SECRET=xxx`)
