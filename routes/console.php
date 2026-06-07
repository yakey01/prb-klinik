<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Kirim notifikasi PRB — jam_kirim dari DB (WIB/Asia/Jakarta), cron tiap menit
Schedule::command('notifikasi:kirim')
    ->everyMinute()
    ->when(function () {
        try {
            $cfg     = \App\Models\NotifikasiSetting::getSetting();
            $jam     = substr($cfg->jam_kirim ?? '07:00:00', 0, 5); // "07:00"
            $nowWib  = \Carbon\Carbon::now('Asia/Jakarta')->format('H:i');
            return $nowWib === $jam;
        } catch (\Throwable $e) {
            return false;
        }
    })
    ->withoutOverlapping()
    ->runInBackground();
