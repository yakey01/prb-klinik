<?php
namespace App\Livewire;

use App\Models\NotifikasiLog;
use App\Models\NotifikasiSetting;
use App\Models\PengambilanObat;
use App\Services\NotifikasiService;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Component;

class NotifikasiManager extends Component
{
    public string $tab = 'overview';

    // Jadwal filter
    public string $filterJadwal = 'minggu';  // hari_ini|besok|minggu|semua
    public string $searchJadwal = '';

    // Log filter
    public string $filterLogChannel = '';    // wa|telegram|''=semua
    public string $filterLogStatus  = '';    // sent|failed|pending|''=semua
    public string $filterLogTipe    = '';    // H1|HARIAN|KONFIRMASI|TEST|BROADCAST|''=semua
    public int    $filterLogHari    = 7;     // hari terakhir

    // Pengaturan form
    public string $setWaProvider     = 'fonnte';
    public string $setWaApiKey       = '';
    public string $setWaSender       = '';
    public string $setWaEndpointUrl  = '';   // URL endpoint WA lokal (support ngrok/custom)
    public string $setTgToken        = '';
    public string $setTgChatId       = '';
    public string $setJamKirim       = '08:00';
    public bool   $setAktifWa        = false;
    public bool   $setAktifTg        = false;
    public string $setTplH1          = '';
    public string $setTplHarian      = '';
    public string $setTplOverdue     = '';

    // Test send
    public string $testNomor    = '';
    public string $testPesan    = 'Test notifikasi dari Klinik Dokterku ✅';
    public string $testChannel  = 'wa';

    // UI state
    public ?string $toast = null;
    public string  $toastType = 'success';
    public bool    $konfirmasiModal = false;
    public ?int    $konfirmasiId    = null;

    public function mount(): void
    {
        $cfg = NotifikasiSetting::getSetting();
        $this->setWaProvider     = $cfg->wa_provider ?? 'fonnte';
        $this->setWaApiKey       = $cfg->wa_api_key ?? '';
        $this->setWaSender       = $cfg->wa_sender_number ?? '';
        $this->setWaEndpointUrl  = $cfg->wa_endpoint_url ?? '';
        $this->setTgToken        = $cfg->telegram_bot_token ?? '';
        $this->setTgChatId       = $cfg->telegram_chat_id_staff ?? '';
        $this->setJamKirim       = substr($cfg->jam_kirim ?? '08:00:00', 0, 5);
        $this->setAktifWa        = (bool) $cfg->is_aktif_wa;
        $this->setAktifTg        = (bool) $cfg->is_aktif_telegram;
        $this->setTplH1          = $cfg->template_h1 ?? '';
        $this->setTplHarian      = $cfg->template_harian ?? '';
        $this->setTplOverdue     = $cfg->template_overdue ?? '';
    }

    #[Computed]
    public function stats(): array
    {
        $hariIni  = today()->toDateString();
        $besok    = Carbon::tomorrow()->toDateString();
        $selesai  = ['selesai', 'batal'];

        $hariIniCount  = PengambilanObat::where('jadwal_berikutnya', $hariIni)->whereNotIn('status', $selesai)->count();
        $besokCount    = PengambilanObat::where('jadwal_berikutnya', $besok)->whereNotIn('status', $selesai)->count();
        $overdueCount  = PengambilanObat::where('jadwal_berikutnya', '<', $hariIni)->whereNotIn('status', $selesai)->whereNotNull('jadwal_berikutnya')->count();
        $terkirimHariIni = NotifikasiLog::today()->where('status', 'sent')->count();
        $gagalHariIni  = NotifikasiLog::today()->where('status', 'failed')->count();

        $cfg = NotifikasiSetting::getSetting();
        return compact('hariIniCount', 'besokCount', 'overdueCount', 'terkirimHariIni', 'gagalHariIni',
            'cfg');
    }

    #[Computed]
    public function jadwalList()
    {
        $selesai = ['selesai', 'batal'];
        $q = PengambilanObat::with('pasien')
            ->whereNotNull('jadwal_berikutnya')
            ->orderBy('jadwal_berikutnya');

        switch ($this->filterJadwal) {
            case 'hari_ini':
                $q->where('jadwal_berikutnya', today()->toDateString())->whereNotIn('status', $selesai);
                break;
            case 'besok':
                $q->where('jadwal_berikutnya', Carbon::tomorrow()->toDateString())->whereNotIn('status', $selesai);
                break;
            case 'minggu':
                $q->whereBetween('jadwal_berikutnya', [today()->toDateString(), today()->addDays(7)->toDateString()])
                  ->whereNotIn('status', $selesai);
                break;
            case 'overdue':
                $q->where('jadwal_berikutnya', '<', today()->toDateString())->whereNotIn('status', $selesai);
                break;
            default:
                $q->whereBetween('jadwal_berikutnya', [today()->subDays(30)->toDateString(), today()->addDays(30)->toDateString()]);
        }

        if ($this->searchJadwal) {
            $s = $this->searchJadwal;
            $q->whereHas('pasien', fn ($p) => $p->where('nama', 'like', "%{$s}%")
                ->orWhere('no_bpjs', 'like', "%{$s}%"));
        }

        return $q->get()->map(function ($po) {
            $jadwal   = Carbon::parse($po->jadwal_berikutnya);
            $today    = today();
            $diffDays = $today->diffInDays($jadwal, false);

            if ($po->status === 'selesai' || $po->status === 'batal') {
                $statusLabel = $po->status === 'selesai' ? 'Selesai' : 'Batal';
                $statusColor = $po->status === 'selesai' ? 'emer2' : 'mut';
            } elseif ($diffDays < 0) {
                $statusLabel = 'Overdue ' . abs($diffDays) . 'h';
                $statusColor = 'red2';
            } elseif ($diffDays == 0) {
                $statusLabel = 'Hari Ini';
                $statusColor = 'gold2';
            } elseif ($diffDays == 1) {
                $statusLabel = 'Besok';
                $statusColor = 'blue';
            } else {
                $statusLabel = $diffDays . ' hari lagi';
                $statusColor = 'mut2';
            }

            $sudahNotif = NotifikasiLog::where('pengambilan_id', $po->id)
                ->where('status', 'sent')
                ->whereDate('created_at', today())
                ->exists();

            return [
                'id'           => $po->id,
                'pasien'       => $po->pasien,
                'jadwal'       => $jadwal->format('d M Y'),
                'jadwal_raw'   => $po->jadwal_berikutnya,
                'status'       => $po->status ?? 'menunggu',
                'statusLabel'  => $statusLabel,
                'statusColor'  => $statusColor,
                'sudahNotif'   => $sudahNotif,
                'diffDays'     => $diffDays,
            ];
        });
    }

    #[Computed]
    public function logList()
    {
        $q = NotifikasiLog::with('pasien')
            ->where('created_at', '>=', now()->subDays($this->filterLogHari))
            ->orderByDesc('created_at')
            ->limit(100);

        if ($this->filterLogChannel) $q->where('channel', $this->filterLogChannel);
        if ($this->filterLogStatus)  $q->where('status', $this->filterLogStatus);
        if ($this->filterLogTipe)    $q->where('tipe', $this->filterLogTipe);

        return $q->get();
    }

    public function kirimNotifikasi(int $pengambilanId): void
    {
        $po = PengambilanObat::with('pasien')->findOrFail($pengambilanId);
        if (!$po->pasien?->telepon) {
            $this->flash('Pasien tidak memiliki nomor telepon', 'error');
            return;
        }

        $svc = app(NotifikasiService::class);

        // Anti-spam: 1 WA per pasien per hari
        if ($svc->sudahKirimHariIni($po->id)) {
            $this->flash("Notifikasi sudah dikirim hari ini ke {$po->pasien->nama} — tidak dikirim ulang", 'error');
            return;
        }

        $cfg    = NotifikasiSetting::getSetting();
        $jadwal = Carbon::parse($po->jadwal_berikutnya);
        $diffH  = today()->diffInDays($jadwal, false);
        $tipe   = $diffH == 1 ? 'H1' : ($diffH < 0 ? 'OVERDUE' : 'HARIAN');
        $tplKey = $diffH < 0 ? 'template_overdue' : ($diffH == 1 ? 'template_h1' : 'template_harian');

        if ($diffH < 0 && $svc->sudahKirimOverdueMaxHari($po->id)) {
            $this->flash("Pengingat overdue {$po->pasien->nama} sudah mencapai batas 5 hari", 'error');
            return;
        }

        $pesan = $svc->buildPesanUntuk($cfg->{$tplKey} ?? '', $po->pasien, $jadwal);

        $result = $svc->kirimWa($po->pasien->telepon, $pesan, $po->pasien_id, $po->id, $tipe);
        $this->flash($result['ok'] ? "WA terkirim ke {$po->pasien->nama}" : "Gagal: {$result['msg']}", $result['ok'] ? 'success' : 'error');
    }

    public function konfirmasiSelesai(int $pengambilanId): void
    {
        $this->konfirmasiModal = true;
        $this->konfirmasiId    = $pengambilanId;
    }

    public function tandaiSelesai(): void
    {
        if (!$this->konfirmasiId) return;
        PengambilanObat::where('id', $this->konfirmasiId)->update(['status' => 'selesai']);
        $this->konfirmasiModal = false;
        $this->konfirmasiId    = null;
        unset($this->jadwalList, $this->stats);
        $this->flash('Pengambilan ditandai selesai ✅');
    }

    public function kirimSemua(): void
    {
        $hariIni = today()->toDateString();
        $besok   = Carbon::tomorrow()->toDateString();
        $selesai = ['selesai', 'batal'];
        $svc     = app(NotifikasiService::class);
        $cfg     = NotifikasiSetting::getSetting();
        $ok      = 0;
        $skip    = 0;

        // H-1 dan hari ini
        $list = PengambilanObat::with('pasien')
            ->whereIn('jadwal_berikutnya', [$hariIni, $besok])
            ->whereNotIn('status', $selesai)
            ->get();

        foreach ($list as $po) {
            if (!$po->pasien?->telepon) continue;
            if ($svc->sudahKirimHariIni($po->id)) { $skip++; continue; }

            $jadwal = Carbon::parse($po->jadwal_berikutnya);
            $tplKey = $jadwal->isToday() ? 'template_harian' : 'template_h1';
            $tipe   = $jadwal->isToday() ? 'HARIAN' : 'H1';
            $pesan  = $svc->buildPesanUntuk($cfg->{$tplKey} ?? '', $po->pasien, $jadwal);
            $result = $svc->kirimWa($po->pasien->telepon, $pesan, $po->pasien_id, $po->id, $tipe);
            if ($result['ok']) $ok++;
        }

        // Overdue — maks 5 hari pengingat per pasien
        $overdueList = PengambilanObat::with('pasien')
            ->where('jadwal_berikutnya', '<', $hariIni)
            ->whereNotIn('status', $selesai)
            ->whereNotNull('jadwal_berikutnya')
            ->get();

        foreach ($overdueList as $po) {
            if (!$po->pasien?->telepon) continue;
            if ($svc->sudahKirimHariIni($po->id)) { $skip++; continue; }
            if ($svc->sudahKirimOverdueMaxHari($po->id)) { $skip++; continue; }

            $jadwal = Carbon::parse($po->jadwal_berikutnya);
            $pesan  = $svc->buildPesanUntuk($cfg->template_overdue ?? '', $po->pasien, $jadwal);
            $result = $svc->kirimWa($po->pasien->telepon, $pesan, $po->pasien_id, $po->id, 'OVERDUE');
            if ($result['ok']) $ok++;
        }

        unset($this->stats, $this->jadwalList);
        $msg = "Kirim selesai: {$ok} terkirim";
        if ($skip > 0) $msg .= ", {$skip} skip";
        $this->flash($msg);
    }

    public function kirimTelegramSummary(): void
    {
        $svc   = app(NotifikasiService::class);
        $hariIni = today()->toDateString();
        $selesai = ['selesai', 'batal'];

        $todayCount   = PengambilanObat::where('jadwal_berikutnya', $hariIni)->whereNotIn('status', $selesai)->count();
        $besokCount   = PengambilanObat::where('jadwal_berikutnya', Carbon::tomorrow()->toDateString())->whereNotIn('status', $selesai)->count();
        $overdueCount = PengambilanObat::where('jadwal_berikutnya', '<', $hariIni)->whereNotIn('status', $selesai)->whereNotNull('jadwal_berikutnya')->count();

        $tgl = now()->format('d/m/Y H:i');
        $msg = "📊 <b>Klinik Dokterku — Update Notifikasi PRB</b>\n<i>{$tgl}</i>\n\n" .
            "🕐 Hari ini jadwal ambil: <b>{$todayCount}</b>\n" .
            "📅 Besok: <b>{$besokCount}</b>\n" .
            "⚠️ Overdue: <b>{$overdueCount}</b>\n\n" .
            "<i>Dikirim manual dari dashboard</i>";

        $result = $svc->kirimTelegram($msg, null, null, 'BROADCAST');
        unset($this->stats);
        $this->flash($result['ok'] ? 'Summary terkirim ke Telegram ✅' : "Telegram gagal: {$result['msg']}", $result['ok'] ? 'success' : 'error');
    }

    public function testKirim(): void
    {
        $svc = app(NotifikasiService::class);
        if ($this->testChannel === 'wa') {
            if (!$this->testNomor) { $this->flash('Masukkan nomor tujuan', 'error'); return; }
            $result = $svc->kirimWa($this->testNomor, $this->testPesan, null, null, 'TEST');
        } else {
            $result = $svc->kirimTelegram($this->testPesan, null, null, 'TEST');
        }
        unset($this->stats, $this->logList);
        $this->flash($result['ok'] ? "Test berhasil terkirim ✅" : "Gagal: {$result['msg']}", $result['ok'] ? 'success' : 'error');
    }

    public function testKirimWa(): void
    {
        $this->testChannel = 'wa';
        $this->testKirim();
    }

    public function testKirimTelegram(): void
    {
        $this->testChannel = 'telegram';
        $this->testKirim();
    }

    public function cekStatusWaLokal(): void
    {
        $svc = app(NotifikasiService::class);
        $status = $svc->statusWaLokal();

        if (!$status['ok']) {
            $this->flash('WA Service belum jalan. Jalankan: cd wa-service && node server.js', 'error');
            return;
        }
        if ($status['ready'] ?? false) {
            $nomor = $status['nomor'] ?? '-';
            $nama  = $status['nama'] ?? '-';
            $sent  = $status['messages_sent'] ?? 0;
            $this->flash("WA Lokal terhubung ✅ ({$nama} / {$nomor}) | {$sent} pesan terkirim");
        } else {
            $this->flash('WA Service berjalan tapi belum scan QR. Buka http://localhost:3001/qr', 'error');
        }
    }

    public function simpanSetting(): void
    {
        $cfg = NotifikasiSetting::getSetting();
        $cfg->update([
            'wa_provider'            => $this->setWaProvider,
            'wa_api_key'             => $this->setWaApiKey ?: null,
            'wa_sender_number'       => $this->setWaSender ?: null,
            'wa_endpoint_url'        => $this->setWaEndpointUrl ?: null,
            'telegram_bot_token'     => $this->setTgToken ?: null,
            'telegram_chat_id_staff' => $this->setTgChatId ?: null,
            'jam_kirim'              => $this->setJamKirim . ':00',
            'is_aktif_wa'            => $this->setAktifWa,
            'is_aktif_telegram'      => $this->setAktifTg,
            'template_h1'            => $this->setTplH1 ?: null,
            'template_harian'        => $this->setTplHarian ?: null,
            'template_overdue'       => $this->setTplOverdue ?: null,
        ]);
        unset($this->stats);
        $this->flash('Pengaturan tersimpan ✅');
    }

    private function flash(string $msg, string $type = 'success'): void
    {
        $this->toast     = $msg;
        $this->toastType = $type;
        $this->dispatch('notif-toast', message: $msg, type: $type);
    }

    public function render()
    {
        return view('livewire.notifikasi-manager');
    }
}
