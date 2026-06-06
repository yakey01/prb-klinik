# Changelog — PRB Klinik

Semua perubahan signifikan pada sistem ini dicatat di file ini.
Format mengikuti [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

---

## [1.0.0] — 2025-06-06

### Rilis Pertama — Production di apotik.dokterkuklinik.com

#### Ditambahkan
- Dashboard dengan KPI: laba kotor, stok kritis, alert kedaluwarsa
- Katalog Obat: 62+ obat PRB, kalkulasi laba dari `resep_pasien` aktif (real-time)
- Pengadaan: form PO ke distributor, riwayat PO, kebutuhan obat kronis
- Inventori: stok masuk/keluar, stok-keluar, alert minimum
- Pasien: data pasien PRB, resep aktif, jadwal pengambilan
- Laporan bulanan BPJS, rekonsiliasi, tagihan, persyaratan klaim
- Manajemen Diagnosis PRB (DM, Hipertensi, Jantung, dll)
- Manajemen User dengan role: admin, apoteker, viewer
- Audit Log: riwayat semua aktivitas pengguna
- Notifikasi sistem
- Deploy Panel: rsync ke Hostinger via WebSocket realtime, auto-reconnect
- WA Service: notifikasi WhatsApp (Node.js)
- SSH key auth ke Hostinger (port 65002)

#### Teknis
- Laravel 12 + PHP 8.4 + Livewire 4 + Alpine.js
- MariaDB 11 di Hostinger shared hosting
- KatalogTable: `jumlah_pasien` & `unit_per_bulan` dihitung live dari `resep_pasien`
- Deploy pipeline: rsync → composer install → migrate → optimize → permissions
- WebSocket deploy server (ws-deploy) dengan auto-reconnect frontend

---

*Untuk menambah entri: tulis di bawah `## [Unreleased]` lalu pindah ke versi saat release.*
