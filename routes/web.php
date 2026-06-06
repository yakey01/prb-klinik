<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ObatController;
use App\Http\Controllers\PengadaanController;
use App\Http\Controllers\RiwayatController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware(['auth', 'throttle:60,1'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/katalog', [ObatController::class, 'index'])->name('katalog.index');

    Route::get('/pengadaan/baru', [PengadaanController::class, 'create'])->name('pengadaan.create');
    Route::post('/pengadaan', [PengadaanController::class, 'store'])->name('pengadaan.store');
    Route::get('/pengadaan/kebutuhan', fn () => view('pengadaan.kebutuhan'))->name('pengadaan.kebutuhan');

    Route::get('/riwayat', [RiwayatController::class, 'index'])->name('riwayat.index');
    Route::get('/riwayat/export', [RiwayatController::class, 'exportCsv'])->name('riwayat.export');
    Route::delete('/riwayat/{purchaseOrder}', [RiwayatController::class, 'destroy'])->name('riwayat.destroy');

    Route::get('/distributor', fn () => view('distributor.index'))->name('distributor.index');

    Route::get('/stok',         fn () => view('stok.index'))->name('stok.index');
    Route::get('/stok-keluar',  fn () => view('stok-keluar.index'))->name('stok-keluar.index');
    Route::get('/laporan',      fn () => view('laporan.index'))->name('laporan.index');
    Route::get('/tagihan',      fn () => view('keuangan.tagihan'))->name('tagihan.index');
    Route::get('/rekonsiliasi', fn () => view('rekonsiliasi.index'))->name('rekonsiliasi.index');
    Route::get('/audit',        fn () => view('audit.index'))->name('audit.index');

    Route::get('/pasien',             fn () => view('pasien.index'))->name('pasien.index');
    Route::get('/pasien/pengambilan', fn () => view('pasien.pengambilan'))->name('pasien.pengambilan');
    Route::get('/pasien/jadwal',      fn () => view('pasien.jadwal'))->name('pasien.jadwal');
    Route::get('/persyaratan-klaim',  fn () => view('persyaratan-klaim.index'))->name('persyaratan-klaim.index');
    Route::get('/notifikasi', fn () => view('notifikasi.index'))->name('notifikasi.index');

    Route::get('/diagnosis', fn () => view('diagnosis.index'))->name('diagnosis.index');
    Route::get('/users',     fn () => view('users.index'))->name('users.index');
    Route::get('/pengadaan', fn () => redirect()->route('pengadaan.create'))->name('pengadaan.index');

    Route::get('/pengaturan', fn () => view('pengaturan.index'))->name('pengaturan.index');

    Route::get('/deploy', fn () => view('deploy.index'))->name('deploy.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
