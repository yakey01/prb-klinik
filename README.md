# Sistem Manajemen Obat PRB — Klinik Dokterku

**Aplikasi internal manajemen obat Program Rujuk Balik (PRB) BPJS Kesehatan**
Klinik Dokterku · Kabupaten Kediri · Sistem Internal

---

## Stack Teknologi

| Komponen | Versi |
|----------|-------|
| PHP | 8.4+ |
| Laravel | 12.x |
| Livewire | 4.x |
| Alpine.js | 3.x |
| MySQL / MariaDB | 8.0+ / 11.x |
| Node.js (WA & Deploy) | 18+ |

---

## Fitur Sistem

| Modul | Deskripsi |
|-------|-----------|
| **Dashboard** | KPI real-time: laba, stok kritis, alert kedaluwarsa |
| **Katalog Obat** | Master 62+ obat PRB, laba/unit otomatis dari resep aktif |
| **Pengadaan** | Form PO ke distributor, kebutuhan obat kronis |
| **Stok & Inventori** | Kelola stok masuk/keluar, alert stok minimum |
| **Pasien & Resep** | Data pasien PRB, resep aktif, jadwal pengambilan |
| **Laporan** | Laporan bulanan BPJS, rekonsiliasi, tagihan |
| **Diagnosis** | Master data diagnosis PRB (DM, Hipertensi, Jantung, dll) |
| **Manajemen User** | Role: admin, apoteker, viewer |
| **Audit Log** | Riwayat semua aktivitas pengguna |
| **Deploy Panel** | Deploy ke Hostinger via rsync + WebSocket realtime |

---

## Setup Lokal

### 1. Prasyarat

```bash
php >= 8.4
composer >= 2.x
node >= 18
mysql atau mariadb
```

### 2. Instalasi

```bash
git clone <repository-url> prb-klinik
cd prb-klinik

# PHP dependencies
composer install

# Buat file environment
cp .env.example .env
php artisan key:generate

# Sesuaikan DB di .env, lalu:
php artisan migrate --seed

# Node dependencies (opsional — untuk WA service & deploy panel)
cd wa-service && npm install && cd ..
cd ws-deploy  && npm install && cd ..
```

### 3. Jalankan

```bash
php artisan serve --port=8181
# → http://localhost:8181

# Deploy panel (WebSocket):
node ws-deploy/server.js
```

### 4. Login default (seeder)

```
Email    : admin@klinikdokterku.id
Password : klinik2024
```

> **Ganti password setelah login pertama di halaman Pengaturan.**

---

## Struktur Direktori

```
prb-klinik/
├── app/
│   ├── Http/Controllers/     # Controller utama
│   ├── Livewire/             # Komponen Livewire interaktif
│   ├── Models/               # Eloquent models
│   └── Services/             # Business logic (kalkulasi laba, dll)
├── database/
│   ├── migrations/           # Skema database
│   └── seeders/              # Data awal (obat PRB, user admin)
├── resources/views/          # Blade templates
├── routes/web.php            # Definisi route
├── wa-service/               # WhatsApp notification service (Node.js)
├── ws-deploy/                # WebSocket deploy server (Node.js)
└── .env.example              # Template konfigurasi
```

---

## Deploy ke Hostinger

Deploy dilakukan via **rsync + SSH** (bukan git pull) karena shared hosting.

```bash
# Satu perintah deploy:
rsync -avz --delete \
  --exclude='.git' --exclude='vendor' --exclude='node_modules' \
  --exclude='.env' --exclude='storage/logs/*' \
  --exclude='storage/framework/cache/*' \
  --exclude='storage/framework/sessions/*' \
  --exclude='storage/framework/views/*' \
  --exclude='bootstrap/cache/*' \
  -e "ssh -p 65002 -i ~/.ssh/id_ed25519" \
  ./ u454362045@153.92.8.132:/home/u454362045/domains/dokterkuklinik.com/public_html/apotik/

# Setelah rsync, clear cache di remote:
ssh -p 65002 u454362045@153.92.8.132 \
  "cd ~/domains/dokterkuklinik.com/public_html/apotik && php artisan optimize"
```

Atau gunakan **Deploy Panel** di `/deploy` — klik "Deploy ke Hostinger".

### Info Hostinger

| Parameter | Nilai |
|-----------|-------|
| Domain | apotik.dokterkuklinik.com |
| SSH | `u454362045@153.92.8.132:65002` |
| PHP | 8.4 |
| Remote path | `/home/u454362045/domains/dokterkuklinik.com/public_html/apotik` |

---

## Konvensi Commit

```
feat:     Fitur baru
fix:      Perbaikan bug
refactor: Refactoring tanpa perubahan perilaku
style:    Perubahan CSS/UI
chore:    Update dependencies, config, tooling
deploy:   Perubahan konfigurasi deploy
docs:     Update dokumentasi
```

**Contoh:**
```bash
git commit -m "feat: tambah alert stok kritis di dashboard"
git commit -m "fix: KatalogTable hitung pasien dari resep aktif"
git commit -m "deploy: update rsync exclude storage/framework"
```

---

## Branch Strategy

| Branch | Fungsi |
|--------|--------|
| `main` | Production-ready — di-sync ke Hostinger |
| `develop` | Pengembangan aktif (opsional) |
| `hotfix/*` | Perbaikan darurat production |

---

## Environment Variables Penting

Lihat `.env.example` untuk daftar lengkap. Yang kritis:

```env
APP_ENV=production
APP_KEY=             # generate: php artisan key:generate
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```

> File `.env` tidak pernah di-commit. Selalu gunakan `.env.example` sebagai template.

---

## Lisensi & Penggunaan

Sistem ini dibangun khusus untuk **Klinik Dokterku, Kabupaten Kediri**.
Penggunaan di luar institusi tersebut memerlukan izin tertulis.

---

*PRB Klinik · Laravel 12 · © 2025 Klinik Dokterku*
