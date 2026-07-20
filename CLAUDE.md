# CLAUDE.md — PRB Klinik (Sistem Manajemen Obat Klinik)

## ⚡ PERUBAHAN SERVER PRODUKSI (2026-07-20) — WAJIB BACA

**Produksi PINDAH: Biznet VPS → Mac Mini (Docker + Cloudflare Tunnel).** apotik PRB kini dilayani dari **Mac Mini** di **`apotik.klinikdokterku.id`** — via Cloudflare Tunnel. Biznet `103.93.133.244` = **FALLBACK 2 bulan**.

**DEPLOY sekarang:** edit di MacBook → `git push`; lalu **DI MAC MINI** jalankan **`~/production/deploy.sh apotik`** (atau `all`) → git pull+composer+build+migrate+optimize+queue:restart. `bin/ship`→Biznet dipakai hanya untuk jaga fallback sinkron. Stack: `~/production` (nginx:8090+php-fpm 48w+mariadb 2G+redis+JIT). Hardening AKTIF (auto-start reboot): tunnel/backup-02:00/monitor-5mnt via launchd, OrbStack start-at-login, `pmset` no-sleep+autorestart. Mac Mini kini DEDIKASI klinik (stack trading dimatikan, ~8,4GB bebas; WhatsApp/personal tetap jalan). Runbook: `~/production/RUNBOOK.md`. Detail: memory `project_vps_to_macmini_migration` (di Mac Mini).

---

## Ringkasan Proyek

Aplikasi Laravel 12 untuk manajemen obat BPJS/PRB (Program Rujuk Balik) klinik.
Fitur: manajemen pasien & resep, katalog obat, stok, pengadaan, kebutuhan obat kronis, laporan.

- **Path**: `/Users/kym/prb-klinik`
- **Stack**: Laravel 12.61 · PHP 8.4 · Livewire 4.3.1 · Alpine.js · MySQL
- **Dev server**: `php artisan serve --port=8181` → `http://localhost:8181`
- **Login**: `admin@klinikdokterku.id` / `klinik2024`
- **DB**: MySQL, database `prb_klinik`, user `root`

---

## Arsitektur

```
app/
  Http/Controllers/      # Controller biasa (non-Livewire)
    DashboardController  # GET /dashboard
    ObatController       # GET /katalog
    PengadaanController  # GET /pengadaan/baru · POST /pengadaan
    RiwayatController    # GET /riwayat · DELETE /riwayat/{po}
  Livewire/              # Semua komponen interaktif
  Models/                # Eloquent models
resources/views/
  layouts/app.blade.php  # Layout utama + CSS variabel tema
  livewire/              # View untuk Livewire components
  pasien/, pengadaan/,   # View statis per fitur
  stok/, distributor/, ...
database/migrations/     # Semua migrasi — urutan penting!
```

---

## Routes Utama

| URL | Komponen / Controller |
|-----|-----------------------|
| `/dashboard` | `DashboardController` |
| `/katalog` | `ObatController` → `livewire:katalog-table` |
| `/pasien` | `livewire:pasien-manager` |
| `/stok` | `livewire:stok-table` |
| `/stok-keluar` | `livewire:stok-keluar-manager` |
| `/distributor` | `livewire:distributor-manager` |
| `/pengadaan/baru` | `PengadaanController` → `livewire:pengadaan-form` |
| `/pengadaan/kebutuhan` | `livewire:kebutuhan-obat-kronis` |
| `/riwayat` | `RiwayatController` (server-side pagination 15/hal) |
| `/laporan` | `livewire:laporan-bulanan` |

---

## Livewire Components — Props & Methods

### `PasienManager` (pasien-manager)
**Props:** `$nama`, `$no_bpjs`, `$kategori_diagnosis`, `$jenis_kelamin`, `$tanggal_lahir`, `$telepon`, `$alamat`, `$catatan`, `$showForm`, `$editId`, `$drawerPasienId`, `$resepEditing`, `$resepRows`, `$search`, `$filterDiagnosis`, `$filterStatus`

**Methods:**
- `openAdd()`, `save()`, `cancel()`, `openEdit(int $id)`
- `deletePasien(int $id)`, `toggleStatus(int $id)`, `catat(int $id)`
- `openDrawer(int $id)`, `closeDrawer()`
- `startResepEdit()`, `addResepRow()`, `removeResepRow(int $i)`, `saveResep()`, `cancelResepEdit()`

**PENTING — Resep:**
- `startResepEdit()` **otomatis tambah 1 row default** jika pasien belum punya resep
- Untuk pasien baru: jangan panggil `addResepRow()` setelah `startResepEdit()` — langsung isi `resepRows.0`
- Untuk pasien dengan N resep existing: bisa pakai `addResepRow()` untuk tambah baris baru
- `saveResep()` validasi: `obat_id min:1`, `jumlah_default min:1`

### `KatalogTable` (katalog-table)
**Props:** `$showForm`, `$editId`, `$nama_obat`, `$kode_obat`, `$kategori_diagnosis`, `$jumlah_pasien`, `$unit_per_bulan`, `$harga_beli_per_unit`, `$sumber_harga` (`EST`/`PO`/`REAL`), `$klaim_bpjs_per_unit`, `$faktor_jasa_farmasi` (default `1.15`), `$is_active`, `$search`, `$filter`, `$showInactive`

**Methods:** `openAdd()`, `openEdit(int $id)`, `save()`, `cancel()`, `toggleActive(int $id)`, `importCsv()`

**PENTING:** `save()` hanya menyimpan field: `nama_obat`, `kode_obat`, `kategori_diagnosis`, `klaim_bpjs_per_unit`, `faktor_jasa_farmasi`, `is_active`, `tipe_obat`. Field `harga_beli_per_unit` **tidak disimpan** dari form katalog — update via Stok atau langsung DB.

**`satuan` bukan public prop** di KatalogTable — jangan set via `$wire.set('satuan', ...)`. Gunakan update langsung ke DB.

### `StokTable` (stok-table)
Inline edit only — tidak ada form modal.

**Methods:**
- `updateStok(int $id, int $value)` — update `stok_aktual`
- `updateMinimum(int $id, int $value)` — update `stok_minimum`
- `updateKadaluarsa(int $id, ?string $value)` — format `Y-m-d`
- `sortBy(string $col)`

### `StokKeluarManager` (stok-keluar-manager)
**Props:** `$showForm`, `$editId`, `$obat_id`, `$tanggal_keluar`, `$jumlah_unit`, `$satuan`, `$harga_jual_per_unit`, `$keterangan`, `$search`, `$filterBulan`

**Methods:** `openAdd()`, `openEdit(int $id)`, `save()`, `delete(int $id)`, `cancel()`, `updatedObatId(int $value)`

`updatedObatId()` otomatis isi `$satuan` dan `$harga_jual_per_unit` dari data obat.

**PENTING — Nama komponen Livewire:** `stok-keluar-manager` (ada hyphen). Saat cari dengan `Livewire.all()`, gunakan hint ganda: `n.includes('stok') && n.includes('keluar')`.

### `DistributorManager` (distributor-manager)
**Props:** `$showForm`, `$editId`, `$name`, `$phone`, `$address`

**Methods:** `openAdd()`, `openEdit(int $id)`, `save()`, `cancel()`, `toggleActive(int $id)`

### `PengadaanForm` (pengadaan-form)
**Props:** `$distributor_id`, `$nomor_invoice`, `$tanggal_po`, `$catatan`, `$rows[]`

**Row structure:**
```php
[
    'obat_id'            => 0,       // required|exists:obat,id
    'tipe_obat'          => 'kronis',
    'jumlah_box'         => 1,       // required|integer|min:1
    'isi_per_box'        => 1,       // required|integer|min:1
    'harga_per_box'      => 0,       // required|numeric|min:1
    'subtotal'           => 0,       // auto: jumlah_box * harga_per_box
    'tanggal_kadaluarsa' => '',
]
```

**Methods:** `mount()`, `addRow()`, `removeRow(int $index)`, `save()`, `updatedRows($value, $key)`

**KRITIS — `mount()` otomatis panggil `addRow()`** → selalu ada 1 row default saat halaman load.
Untuk N baris: panggil `addRow()` sebanyak **N-1** kali (bukan N kali).
- 1 baris: 0 `addRow()` tambahan
- 3 baris: 2 `addRow()` tambahan

### `KebutuhanObatKronis` (kebutuhan-obat-kronis)
**Props:** `$filterDiagnosis`, `$filterStatus` (`habis`/`kritis`/`hampir_habis`/`perhatian`/`aman`), `$search`, `$horizon` (int, bulan, default `3`)

Data dari join `resep_pasien` → `obat` → `pasien` where `is_aktif=true`.
Real-time: resep baru langsung muncul di halaman ini (tidak perlu cache flush).

---

## Database Schema Penting

### `obat`
`id, nama_obat, kode_obat, kategori_diagnosis, tipe_obat` (`kronis`/`non_kronis`)`, jumlah_pasien, unit_per_bulan, satuan, harga_beli_per_unit, harga_jual_per_unit, sumber_harga` (`EST`/`PO`/`REAL`)`, klaim_bpjs_per_unit, faktor_jasa_farmasi, is_active, stok_aktual, stok_minimum, tanggal_kadaluarsa`

### `pasien`
`id, nama, no_bpjs, kategori_diagnosis, telepon, alamat, tanggal_lahir, jenis_kelamin` (`L`/`P`)`, is_aktif, catatan`

### `resep_pasien`
`id, pasien_id, obat_id, jumlah_default, satuan, catatan, urutan, is_aktif`

### `purchase_orders`
`id, distributor_id, nomor_invoice, tanggal_po, total_nilai, catatan, status_bayar, tanggal_jatuh_tempo, tanggal_bayar, jumlah_bayar`

### `purchase_order_items`
`id, purchase_order_id, obat_id, tipe_obat, jumlah_box, isi_per_box, harga_per_box, harga_per_unit, subtotal, tanggal_kadaluarsa`

### `stok_keluar`
`id, obat_id, tanggal_keluar, jumlah_unit, satuan, harga_beli_snapshot, harga_jual_per_unit, keterangan, dicatat_oleh`

### `distributors`
`id, name, phone, address, is_active`

---

## Tema Visual

Dark forest-green SaaS theme — CSS variables di `resources/views/layouts/app.blade.php`:

```css
--bg:    #0a1410;   /* background utama */
--panel: #11241c;   /* sidebar, panel */
--card:  #152b21;   /* card, modal */
--gold:  #d9a441;   /* aksen kuning emas */
--gold2: #f2c668;
--emer:  #3fcf8e;   /* hijau emerald (sukses) */
--emer2: #5ce0a4;
--line:  (border)
--mut:   (muted text)
--ink:   (primary text)
```

---

## CDP / Browser Automation (Livewire 4)

### Koneksi CDP
```bash
# Buka Chrome dengan remote debugging
open -a "Google Chrome" --args --remote-debugging-port=9223
# Cek tabs
curl http://localhost:9223/json
```

### Livewire 4 JS API — WAJIB pakai `$wire`
```javascript
// Livewire 4: component dari Livewire.all() punya property $wire
const pm = (Livewire.all()||[]).find(c => c.name.includes('pasien-manager'))
pm.$wire.call('openAdd')           // panggil method
pm.$wire.set('nama', 'Budi')      // set property
pm.$wire.get('showForm')           // baca property
```

**JANGAN gunakan** `pm.call()` atau `pm.set()` langsung — itu Livewire 3.

### Cari Komponen dengan Nama Hyphenated
```javascript
// Benar — StokKeluarManager = 'stok-keluar-manager'
(Livewire.all()||[]).find(c => {
    const n = (c.name||'').toLowerCase();
    return n.includes('stok') && n.includes('keluar');
})

// Salah — 'stokkeluar' tidak ada di 'stok-keluar-manager'
n.includes('stokkeluar')
```

### Set Input (wire:model) tanpa CSS Selector Error
```javascript
// ':' dalam wire:model membreak querySelector — pakai getAttribute loop
const inp = Array.from(document.querySelectorAll('input'))
    .find(i => i.getAttribute('wire:model') === 'nama')

// Native setter untuk trigger Livewire watchers
Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value')
    .set.call(inp, 'nilai baru')
inp.dispatchEvent(new Event('input', {bubbles: true}))
inp.dispatchEvent(new Event('change', {bubbles: true}))
```

### Set Select
```javascript
const sel = Array.from(document.querySelectorAll('select'))
    .find(s => (s.getAttribute('wire:model.live')||s.getAttribute('wire:model')||'')
               .includes('obat_id'))
Object.getOwnPropertyDescriptor(window.HTMLSelectElement.prototype, 'value')
    .set.call(sel, '3')
sel.dispatchEvent(new Event('input', {bubbles: true}))
sel.dispatchEvent(new Event('change', {bubbles: true}))
```

### Klik Button (wire:click)
```javascript
// Pakai getAttribute — tidak ada CSS selector escaping issue
Array.from(document.querySelectorAll('button'))
    .find(b => b.getAttribute('wire:click') === 'openAdd')
    ?.click()
```

### Halaman Riwayat — Struktur Accordion
Halaman `/riwayat` **bukan** `<table><tbody><tr>` sederhana. Setiap PO adalah **accordion card Alpine.js**. Inner `<table><tbody><tr>` berisi item obat per PO, bukan PO itu sendiri.

```javascript
// Benar — cari di body text
document.body.innerText.includes('INV-001')

// Salah — ini menghitung item obat, bukan jumlah PO
document.querySelectorAll('table tbody tr').length
```

---

## Pola Pengerjaan Umum

### Tambah Pasien + Resep (dari CDP)
```python
# 1. Buka form pasien
click_btn(s, 'openAdd')
lw_set(s, 'pasien', 'nama', 'Nama Pasien')
lw_set(s, 'pasien', 'no_bpjs', '1234567')
lw_set(s, 'pasien', 'kategori_diagnosis', 'Hipertensi')
lw_set(s, 'pasien', 'jenis_kelamin', 'L')
lw_set(s, 'pasien', 'tanggal_lahir', '1970-01-01')
lw_call(s, 'pasien', 'save')

# 2. Buka drawer pasien
click_btn(s, f'openDrawer({pasien_id})')
# Tunggu 5 detik — drawerPasienId harus match dulu!
# Klik tab Resep
js(s, "document.querySelectorAll('button').find(b=>b.textContent==='Resep').click()")
click_btn(s, 'startResepEdit')
# Tunggu 5 detik — default row sudah ada di resepRows[0]
# JANGAN panggil addResepRow() untuk pasien baru!
# Isi row[0] langsung:
sel_by_val(s, 'obat_id', obat_id)
set_input(s, 'resepRows.0.jumlah_default', 30)
click_btn(s, 'saveResep')
```

### Buat Purchase Order (dari CDP)
```python
nav(s, 'http://localhost:8181/pengadaan/baru', 5)
# mount() otomatis addRow() → sudah ada 1 row default

lw_set(s, 'pengadaan', 'distributor_id', 1)
lw_set(s, 'pengadaan', 'nomor_invoice', 'INV-001')
lw_set(s, 'pengadaan', 'tanggal_po', '2026-06-06')

# Untuk 3 baris: addRow() 2 kali saja (1 sudah ada dari mount)
for _ in range(2): lw_call(s, 'pengadaan', 'addRow'); time.sleep(2.5)

# Set setiap baris dengan field yang benar:
for i, (oid, jb, ib, hb) in enumerate(rows):
    lw_set(s, 'pengadaan', f'rows.{i}.obat_id', oid)
    lw_set(s, 'pengadaan', f'rows.{i}.jumlah_box', jb)    # BUKAN 'jumlah'
    lw_set(s, 'pengadaan', f'rows.{i}.isi_per_box', ib)   # BUKAN 'isi'
    lw_set(s, 'pengadaan', f'rows.{i}.harga_per_box', hb) # BUKAN 'harga_beli'

lw_call(s, 'pengadaan', 'save')
```

---

## Perintah Berguna

```bash
# Jalankan server
cd /Users/kym/prb-klinik
php artisan serve --port=8181

# Tinker (debug DB)
php artisan tinker --execute='echo DB::table("obat")->count();'

# Lihat log Laravel
tail -f storage/logs/laravel.log

# Reset dan seed ulang
php artisan migrate:fresh --seed

# Cek komponen Livewire terdaftar
php artisan livewire:list
```

---

## Hal-Hal yang Mudah Salah

1. **`satuan` bukan Livewire prop di KatalogTable** — jangan set via `$wire.set('satuan', ...)`, akan error silent.

2. **PengadaanForm mount() tambah 1 row** — saat nav ke `/pengadaan/baru`, sudah ada 1 row. Untuk N baris, panggil `addRow()` sebanyak N-1 kali.

3. **Field nama row PengadaanForm** — `jumlah_box`, `isi_per_box`, `harga_per_box`. Bukan `jumlah`, `isi`, `harga_beli`, `harga_per_unit`.

4. **Nama komponen Livewire ada hyphen** — `StokKeluarManager` → `stok-keluar-manager`. Cari dengan dua hint: `n.includes('stok') && n.includes('keluar')`.

5. **Timing `openDrawer` + `startResepEdit`** — tunggu 5 detik setelah `openDrawer` sebelum klik `startResepEdit`, agar `drawerPasienId` sudah terisi di PHP. Kalau terlalu cepat, resep pasien sebelumnya ikut ter-load.

6. **Riwayat pakai accordion** — jangan gunakan `table tbody tr` untuk hitung jumlah PO. Gunakan `document.body.innerText.includes('nomor_invoice')`.

7. **`harga_beli_per_unit` tidak disimpan** dari form KatalogTable — untuk update harga, langsung update DB atau lewat proses pengadaan (PO yang masuk otomatis update stok).

8. **Livewire 4 `$wire`** — selalu akses method via `pm.$wire.call()`, `pm.$wire.set()`, `pm.$wire.get()`. Jangan gunakan `pm.call()` (Livewire 3 style).

9. **`jadwal_berikutnya` wajib diisi** saat `PengambilanObat::create()` — sistem notifikasi filter berdasarkan kolom ini, bukan `tanggal_pengambilan`. Jika hanya `tanggal_pengambilan` yang diisi, pasien tidak akan muncul di dashboard notifikasi. Selalu set keduanya bersamaan.

10. **`wire:click` duplikat pada 1 elemen** — di Livewire 4 hanya event terakhir yang dieksekusi. Gunakan satu dedicated method per tombol (contoh: `testKirimWa()`, `testKirimTelegram()` bukan `wire:click="testChannel='wa'" wire:click="testKirim"`).

---

## Sistem Notifikasi WA

### Arsitektur

```
routes/console.php          → schedule: everyMinute() + when(WIB == jam_kirim)
  └─ notifikasi:kirim       → SendNotifikasi command
       ├─ H-1 (besok)       → template_h1 + tipe=H1
       ├─ Hari Ini          → template_harian + tipe=HARIAN
       └─ Overdue ≤5x       → template_overdue + tipe=OVERDUE (anti-spam 5 hari)
            ↓
       NotifikasiService::buildPesanUntuk($template, $pasien, $jadwal)
            ↓
       kirimWa() → provider local (whatsapp-web.js) atau fonnte (cloud)
            ↓
       NotifikasiLog (status: pending→sent|failed|skipped)
```

### Routes & Komponen

| URL | Komponen |
|-----|----------|
| `/notifikasi` | `livewire:notifikasi-manager` |

**Tabs:** Overview · Jadwal Ambil · Log Notifikasi · Pengaturan

### NotifikasiManager — Key Methods

- `kirimNotifikasi(int $id)` — kirim manual ke 1 pasien (cek anti-spam hari ini)
- `kirimSemua()` — kirim ke H-1 + hari ini + overdue (tombol besar kuning)
- `kirimTelegramSummary()` — ringkasan stats ke Telegram staff
- `simpanSetting()` — simpan semua setting termasuk `wa_endpoint_url`
- `cekStatusWaLokal()` — ping `/status` ke WA service endpoint

### Template Variables

Semua template mendukung variabel berikut (via `buildPesanUntuk()`):

| Variable | Contoh | Sumber |
|----------|--------|--------|
| `{nama}` | Ny. Istinganah | `pasien.nama` |
| `{sapaan_formal}` | Ibu / Bapak | dari `jenis_kelamin` (P→Ibu, L→Bapak) |
| `{diagnosa}` | Hipertensi | `pasien.kategori_diagnosis` |
| `{hari_tanggal}` | Senin 8 Juni 2026 | Carbon + nama hari/bulan Indonesia |
| `{tanggal}` | Senin 8 Juni 2026 | alias `{hari_tanggal}` |
| `{hari}` | Senin | nama hari Indonesia saja |

### Setting DB (`notifikasi_settings`)

| Kolom | Keterangan |
|-------|------------|
| `wa_provider` | `local` (gratis) atau `fonnte` (cloud) |
| `wa_endpoint_url` | URL tunnel ke WA service lokal (ngrok/serveo) |
| `wa_api_key` | API key Fonnte (jika provider=fonnte) |
| `jam_kirim` | Jam kirim otomatis dalam WIB format `HH:mm:ss` |
| `is_aktif_wa` | Toggle WA on/off |
| `template_h1` | Template H-1 (besok jadwal) |
| `template_harian` | Template hari H (hari ini jadwal) |
| `template_overdue` | Template overdue (lewat jadwal, spam ≤5x) |

### Overdue Anti-Spam

```php
NotifikasiService::sudahKirimOverdueMaxHari(int $pengambilanId, int $maxHari = 5): bool
// Count NotifikasiLog WHERE tipe=OVERDUE AND status=sent >= $maxHari
```

---

## WA Service Lokal (whatsapp-web.js)

Service berjalan di **komputer lokal** port 3001, diekspos ke internet via tunnel.

### Jalankan Service

```bash
cd /path/to/wa-service
node server.js
# → listening at http://localhost:3001
# → QR scan via http://localhost:3001/qr
```

### Tunnel ke Internet (Serveo — tanpa akun)

```bash
ssh -R prb-klinik-wa:80:localhost:3001 serveo.net
# URL: https://prb-klinik-wa.serveousercontent.com
# Jika subdomain conflict, gunakan random:
ssh -R 80:localhost:3001 serveo.net
```

Simpan URL tunnel ke DB:
```bash
php artisan tinker
App\Models\NotifikasiSetting::getSetting()->update([
    'wa_endpoint_url' => 'https://xxx.serveousercontent.com',
    'wa_provider' => 'local',
    'is_aktif_wa' => true,
]);
```

### API WA Service

| Endpoint | Method | Deskripsi |
|----------|--------|-----------|
| `/status` | GET | Cek koneksi (`ready: true/false`) |
| `/send` | POST | Kirim pesan `{to, message}` |
| `/qr` | GET | Tampilkan QR untuk scan |

Header: `x-api-key: prb-klinik-secret-2024`

---

## Laravel Scheduler & Cron (Hostinger)

### Schedule Config (`routes/console.php`)

```php
Schedule::command('notifikasi:kirim')
    ->everyMinute()
    ->when(function () {
        $cfg    = NotifikasiSetting::getSetting();
        $jam    = substr($cfg->jam_kirim ?? '07:00:00', 0, 5);
        $nowWib = Carbon::now('Asia/Jakarta')->format('H:i');
        return $nowWib === $jam;
    })
    ->withoutOverlapping()
    ->runInBackground();
```

**Cara kerja:** cron menjalankan `schedule:run` setiap menit → Laravel cek apakah WIB sekarang = `jam_kirim` → jika ya, `notifikasi:kirim` dieksekusi.

### Setup Cron di Hostinger (hPanel)

1. Login **hPanel** → **Advanced** → **Cron Jobs**
2. Interval: **Every minute** (`* * * * *`)
3. Command:
   ```
   /opt/alt/php84/usr/bin/php /home/u454362045/domains/dokterkuklinik.com/public_html/apotik/artisan schedule:run >> /dev/null 2>&1
   ```

**Catatan:** `crontab` binary tidak tersedia via SSH di Hostinger shared hosting — harus via hPanel.

**PHP path Hostinger:** `/opt/alt/php84/usr/bin/php`

### Timezone

- **Server Hostinger**: UTC (`02:XX` = `09:XX WIB`)
- **Schedule logic**: selalu pakai `Carbon::now('Asia/Jakarta')` untuk komparasi jam WIB
- **Default jam_kirim**: `07:00:00` WIB

### Jalankan Manual (simulasi cron)

```bash
ssh -p 65002 -i ~/.ssh/id_ed25519 u454362045@153.92.8.132
/opt/alt/php84/usr/bin/php artisan schedule:run --verbose
# Output: "Running ['artisan' notifikasi:kirim] in background"

# Atau langsung tanpa schedule check:
/opt/alt/php84/usr/bin/php artisan notifikasi:kirim
/opt/alt/php84/usr/bin/php artisan notifikasi:kirim --dry-run
```

---

## Deploy ke Hostinger

```bash
# SSH
ssh -p 65002 -i ~/.ssh/id_ed25519 u454362045@153.92.8.132

# rsync single file
rsync -avz --checksum -e "ssh -p 65002 -i ~/.ssh/id_ed25519" \
  app/Livewire/SomeFile.php \
  u454362045@153.92.8.132:/home/u454362045/domains/dokterkuklinik.com/public_html/apotik/app/Livewire/

# rsync folder
rsync -avz --checksum -e "ssh -p 65002 -i ~/.ssh/id_ed25519" \
  app/ resources/ routes/ \
  u454362045@153.92.8.132:/home/u454362045/domains/dokterkuklinik.com/public_html/apotik/

# Migrate (jika ada migration baru)
ssh ... "/opt/alt/php84/usr/bin/php artisan migrate --force"

# Clear cache
ssh ... "/opt/alt/php84/usr/bin/php artisan optimize:clear"
```

**Live URL**: `https://apotik.dokterkuklinik.com`
**DB**: `u454362045_apotik` @ MySQL
**SSH**: port `65002`, key `~/.ssh/id_ed25519`

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- livewire/livewire (LIVEWIRE) - v4
- larastan/larastan (LARASTAN) - v3
- laravel/boost (BOOST) - v2
- laravel/breeze (BREEZE) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- phpunit/phpunit (PHPUNIT) - v11
- alpinejs (ALPINEJS) - v3
- tailwindcss (TAILWINDCSS) - v3

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app/Console/Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== livewire/core rules ===

# Livewire

- Livewire allow to build dynamic, reactive interfaces in PHP without writing JavaScript.
- You can use Alpine.js for client-side interactions instead of JavaScript frameworks.
- Keep state server-side so the UI reflects it. Validate and authorize in actions as you would in HTTP requests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This application uses PHPUnit for testing. All tests must be written as PHPUnit classes. Use `php artisan make:test --phpunit {name}` to create a new test.
- If you see a test using "Pest", convert it to PHPUnit.
- Every time a test has been updated, run that singular test.
- When the tests relating to your feature are passing, ask the user if they would like to also run the entire test suite to make sure everything is still passing.
- Tests should cover all happy paths, failure paths, and edge cases.
- You must not remove any tests or test files from the tests directory without approval. These are not temporary or helper files; these are core to the application.

## Running Tests

- Run the minimal number of tests, using an appropriate filter, before finalizing.
- To run all tests: `php artisan test --compact`.
- To run all tests in a file: `php artisan test --compact tests/Feature/ExampleTest.php`.
- To filter on a particular test name: `php artisan test --compact --filter=testName` (recommended after making a change to a related file).

</laravel-boost-guidelines>
