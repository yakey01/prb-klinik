<?php

use App\Http\Controllers\Api\PrbManagerController;
use Illuminate\Support\Facades\Route;

Route::middleware('prb.api.auth')->prefix('prb')->group(function () {
    Route::get('/batch',               [PrbManagerController::class, 'batch']);
    Route::get('/summary',             [PrbManagerController::class, 'summary']);
    Route::get('/stok-kritis',         [PrbManagerController::class, 'stokKritis']);
    Route::get('/pasien-overdue',      [PrbManagerController::class, 'pasienOverdue']);
    Route::get('/keuangan',            [PrbManagerController::class, 'keuangan']);
    Route::get('/pengambilan-terbaru', [PrbManagerController::class, 'pengambilanTerbaru']);
    Route::get('/obat-defisit',        [PrbManagerController::class, 'obatDefisit']);
    Route::get('/prediksi-stok',       [PrbManagerController::class, 'prediksiStok']);

    // Bridge RME (katalog/stok/pasien)
    Route::get('/katalog',             [PrbManagerController::class, 'katalog']);
    Route::get('/katalog/stok',        [PrbManagerController::class, 'katalogStok']);
    Route::get('/pasien/cari',         [PrbManagerController::class, 'pasienCari']);
    Route::post('/resep/daftar',       [PrbManagerController::class, 'resepDaftar']);
    // Stok keluar generik dari SIM (umum & BPJS) — apotik = sumber tunggal stok
    Route::post('/stok/keluar',        [PrbManagerController::class, 'stokKeluar']);
});
