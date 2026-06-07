<?php
namespace App\Livewire;

use App\Models\Diagnosis;
use App\Models\PersyaratanKlaim;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PersyaratanKlaimManager extends Component
{
    public string $filterDiagnosis = '';

    public bool   $showForm       = false;
    public ?int   $editId         = null;
    public string $diagnosis      = '';
    public string $nama_syarat    = '';
    public string $deskripsi      = '';
    public string $tipe           = 'dokumen';
    public int    $periode_bulan  = 1;
    public bool   $is_wajib       = true;
    public int    $urutan         = 0;

    public const TIPE_LABELS = ['lab' => 'Lab', 'dokumen' => 'Dokumen', 'pemeriksaan' => 'Pemeriksaan'];
    public const TIPE_COLORS = [
        'lab'          => 'rgba(111,177,224,.15);border-color:rgba(111,177,224,.3);color:var(--blue)',
        'dokumen'      => 'rgba(217,164,65,.12);border-color:rgba(217,164,65,.25);color:var(--gold2)',
        'pemeriksaan'  => 'rgba(63,207,142,.12);border-color:rgba(63,207,142,.25);color:var(--emer2)',
    ];

    #[Computed]
    public function diagnosisList(): \Illuminate\Support\Collection
    {
        $db = Diagnosis::aktif();
        $fallback = collect(['Diabetes','Hipertensi','Jantung','Dislipidemia','Asma & PPOK','Psikiatri','Imunosupresan','Gout','Lainnya']);
        return $db->isNotEmpty() ? $db : $fallback;
    }

    #[Computed]
    public function grouped(): \Illuminate\Support\Collection
    {
        $q = PersyaratanKlaim::orderBy('diagnosis')->orderBy('urutan')->orderBy('id');
        if ($this->filterDiagnosis) $q->where('diagnosis', $this->filterDiagnosis);
        return $q->get()->groupBy('diagnosis');
    }

    #[Computed]
    public function totalStats(): array
    {
        $all = PersyaratanKlaim::all();
        return [
            'total'    => $all->count(),
            'wajib'    => $all->where('is_wajib', true)->count(),
            'diagnosis' => $all->pluck('diagnosis')->unique()->count(),
        ];
    }

    public function openAdd(?string $diag = null): void
    {
        $this->reset(['editId','nama_syarat','deskripsi','urutan']);
        $this->diagnosis     = $diag ?? $this->filterDiagnosis ?? '';
        $this->tipe          = 'dokumen';
        $this->periode_bulan = 1;
        $this->is_wajib      = true;
        $this->showForm      = true;
    }

    public function openEdit(int $id): void
    {
        $p = PersyaratanKlaim::findOrFail($id);
        $this->editId        = $id;
        $this->diagnosis     = $p->diagnosis;
        $this->nama_syarat   = $p->nama_syarat;
        $this->deskripsi     = $p->deskripsi ?? '';
        $this->tipe          = $p->tipe;
        $this->periode_bulan = $p->periode_bulan;
        $this->is_wajib      = $p->is_wajib;
        $this->urutan        = $p->urutan;
        $this->showForm      = true;
    }

    public function save(): void
    {
        $this->validate([
            'diagnosis'     => 'required|max:100',
            'nama_syarat'   => 'required|min:3|max:200',
            'tipe'          => 'required|in:lab,dokumen,pemeriksaan',
            'periode_bulan' => 'required|integer|min:0|max:24',
            'urutan'        => 'required|integer|min:0',
        ]);

        $data = [
            'diagnosis'     => $this->diagnosis,
            'nama_syarat'   => trim($this->nama_syarat),
            'deskripsi'     => trim($this->deskripsi) ?: null,
            'tipe'          => $this->tipe,
            'periode_bulan' => $this->periode_bulan,
            'is_wajib'      => $this->is_wajib,
            'urutan'        => $this->urutan,
        ];

        if ($this->editId) {
            PersyaratanKlaim::findOrFail($this->editId)->update($data);
            $this->dispatch('toast', message: 'Persyaratan diperbarui.', type: 'success');
        } else {
            PersyaratanKlaim::create($data);
            $this->dispatch('toast', message: 'Persyaratan ditambahkan.', type: 'success');
        }

        $this->showForm = false;
        $this->reset(['editId','nama_syarat','deskripsi']);
    }

    public function delete(int $id): void
    {
        if (!auth()->user()?->isAdmin()) {
            $this->dispatch('toast', message: 'Hanya admin yang dapat menghapus persyaratan.', type: 'error');
            return;
        }
        PersyaratanKlaim::findOrFail($id)->delete();
        $this->dispatch('toast', message: 'Persyaratan dihapus.', type: 'success');
    }

    public function toggleAktif(int $id): void
    {
        $p = PersyaratanKlaim::findOrFail($id);
        $p->update(['is_aktif' => !$p->is_aktif]);
    }

    public function cancel(): void { $this->showForm = false; }

    public function render()
    {
        return view('livewire.persyaratan-klaim-manager');
    }
}
