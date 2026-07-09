<?php
namespace App\Livewire;

use App\Models\RekonsiliasiiBpjs;
use App\Models\Obat;
use App\Models\ItemPengambilan;
use App\Models\PengambilanObat;
use App\Models\ActivityLog;
use Livewire\Component;
use Livewire\Attributes\Computed;

class RekonsiliasiBpjs extends Component
{
    public bool   $showForm           = false;
    public ?int   $editId             = null;
    public int    $bulan;
    public int    $tahun;
    public float  $tagihan_diajukan   = 0;
    public float  $tagihan_dibayar    = 0;
    public string $status             = 'draft';
    public string $catatan            = '';
    public string $tanggal_pengajuan  = '';
    public string $tanggal_pembayaran = '';

    public function mount(): void
    {
        $this->bulan = now()->month;
        $this->tahun = now()->year;
    }

    #[Computed]
    public function records()
    {
        $recs = RekonsiliasiiBpjs::orderByDesc('tahun')->orderByDesc('bulan')->get();

        // Compute proyeksi dinamis dari item_pengambilan per periode (1 query)
        $proyeksiMap = \DB::table('item_pengambilan as ip')
            ->join('pengambilan_obat as po', 'ip.pengambilan_obat_id', '=', 'po.id')
            ->join('obat as o', 'ip.obat_id', '=', 'o.id')
            ->whereIn(\DB::raw('CONCAT(YEAR(po.tanggal_pengambilan),"-",MONTH(po.tanggal_pengambilan))'),
                $recs->map(fn($r) => "{$r->tahun}-{$r->bulan}")->toArray()
            )
            ->where('po.status', 'selesai')
            ->where('o.tipe_obat', 'kronis')
            ->select(
                \DB::raw('YEAR(po.tanggal_pengambilan) as tahun'),
                \DB::raw('MONTH(po.tanggal_pengambilan) as bulan'),
                \DB::raw('SUM(ip.jumlah_unit * ip.harga_klaim_bpjs_snapshot * ' . \App\Models\Obat::jfSql('ip.faktor_jasa_farmasi_snapshot') . ') as proyeksi_aktual')
            )
            ->groupBy('tahun', 'bulan')
            ->get()
            ->keyBy(fn($r) => "{$r->tahun}-{$r->bulan}");

        return $recs->map(function ($rec) use ($proyeksiMap) {
            $key = "{$rec->tahun}-{$rec->bulan}";
            $rec->proyeksi_aktual = isset($proyeksiMap[$key])
                ? (float) $proyeksiMap[$key]->proyeksi_aktual
                : 0.0;
            return $rec;
        });
    }

    // Proyeksi dihitung dari AKTUAL obat yang diserahkan ke pasien bulan ini
    // (bukan dari formula hardcoded obat.unit_per_bulan)
    public function getProyeksiProperty(): float
    {
        return $this->hitungProyeksi($this->bulan, $this->tahun);
    }

    public static function hitungProyeksi(int $bulan, int $tahun): float
    {
        // Σ item_pengambilan.jumlah_unit × klaim_bpjs_snapshot × faktor_snapshot
        // dikelompokkan berdasarkan bulan pengambilan
        $total = \DB::table('item_pengambilan as ip')
            ->join('pengambilan_obat as po', 'ip.pengambilan_obat_id', '=', 'po.id')
            ->join('obat as o', 'ip.obat_id', '=', 'o.id')
            ->whereBetween('po.tanggal_pengambilan', \App\Support\Periode::bulan($tahun, $bulan))
            ->whereIn('po.status', ['selesai'])
            ->where('o.tipe_obat', 'kronis')
            ->sum(\DB::raw('ip.jumlah_unit * ip.harga_klaim_bpjs_snapshot * ' . \App\Models\Obat::jfSql('ip.faktor_jasa_farmasi_snapshot')));

        return (float) $total;
    }

    public function openAdd(): void
    {
        $this->resetForm();
        $this->tagihan_diajukan = $this->proyeksi; // prefill dari proyeksi aktual
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $r = RekonsiliasiiBpjs::findOrFail($id);
        $this->editId              = $id;
        $this->bulan               = $r->bulan;
        $this->tahun               = $r->tahun;
        $this->tagihan_diajukan    = (float) $r->tagihan_diajukan;
        $this->tagihan_dibayar     = (float) $r->tagihan_dibayar;
        $this->status              = $r->status;
        $this->catatan             = $r->catatan ?? '';
        $this->tanggal_pengajuan   = $r->tanggal_pengajuan?->format('Y-m-d') ?? '';
        $this->tanggal_pembayaran  = $r->tanggal_pembayaran?->format('Y-m-d') ?? '';
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'bulan'            => 'required|integer|min:1|max:12',
            'tahun'            => 'required|integer|min:2020|max:2099',
            'tagihan_diajukan' => 'required|numeric|min:0',
            'tagihan_dibayar'  => 'required|numeric|min:0',
            'status'           => 'required|in:draft,diajukan,dibayar,selisih',
        ]);

        $data = [
            'bulan'               => $this->bulan,
            'tahun'               => $this->tahun,
            'proyeksi_pendapatan' => $this->proyeksi,
            'tagihan_diajukan'    => $this->tagihan_diajukan,
            'tagihan_dibayar'     => $this->tagihan_dibayar,
            'status'              => $this->status,
            'catatan'             => $this->catatan ?: null,
            'tanggal_pengajuan'   => $this->tanggal_pengajuan ?: null,
            'tanggal_pembayaran'  => $this->tanggal_pembayaran ?: null,
        ];

        if ($this->editId) {
            RekonsiliasiiBpjs::findOrFail($this->editId)->update($data);
            ActivityLog::record('updated','Rekonsiliasi BPJS diperbarui','RekonsiliasBpjs',$this->editId);
            $this->dispatch('toast', message: 'Rekonsiliasi BPJS disimpan.', type: 'success');
        } else {
            $rec = RekonsiliasiiBpjs::create($data);
            ActivityLog::record('created','Rekonsiliasi BPJS ditambah','RekonsiliasBpjs',$rec->id);
            $this->dispatch('toast', message: 'Rekonsiliasi BPJS ditambahkan.', type: 'success');
        }
        $this->cancel();
    }

    public function delete(int $id): void
    {
        if (!auth()->user()?->canEdit()) {
            $this->dispatch('toast', message: 'Tidak memiliki izin untuk menghapus data.', type: 'error');
            return;
        }
        ActivityLog::record('deleted','Rekonsiliasi BPJS dihapus','RekonsiliasBpjs',$id);
        RekonsiliasiiBpjs::findOrFail($id)->delete();
        $this->dispatch('toast', message: 'Data dihapus.', type: 'success');
    }

    public function cancel(): void { $this->showForm = false; $this->resetForm(); }

    private function resetForm(): void
    {
        $this->editId             = null;
        $this->bulan              = now()->month;
        $this->tahun              = now()->year;
        $this->tagihan_diajukan   = 0;
        $this->tagihan_dibayar    = 0;
        $this->status             = 'draft';
        $this->catatan            = '';
        $this->tanggal_pengajuan  = '';
        $this->tanggal_pembayaran = '';
        $this->resetValidation();
    }

    public function render() { return view('livewire.rekonsiliasi-bpjs'); }
}
