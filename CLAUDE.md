# CLAUDE.md — PRB Klinik (Sistem Manajemen Obat Klinik)

## Ringkasan Proyek

Aplikasi Laravel 12 untuk manajemen obat BPJS/PRB (Program Rujuk Balik) klinik.
Fitur: manajemen pasien & resep, katalog obat, stok, pengadaan, kebutuhan obat kronis, laporan.

- **Path**: `/Users/yaya/Documents/prb-klinik`
- **Stack**: Laravel 12.61 · PHP 8.5 · Livewire 4.3.1 · Alpine.js · MySQL
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
cd /Users/yaya/Documents/prb-klinik
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
