<?php
namespace App\Livewire;

use App\Models\RekonsiliasiiBpjs;
use App\Models\Obat;
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
        return RekonsiliasiiBpjs::orderByDesc('tahun')->orderByDesc('bulan')->get();
    }

    public function getProyeksiProperty(): float
    {
        return (float) Obat::where('is_active', true)->get()->sum('pendapatan_bulan');
    }

    public function openAdd(): void
    {
        $this->resetForm();
        $this->tagihan_diajukan = $this->proyeksi;
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
