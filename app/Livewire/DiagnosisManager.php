<?php
namespace App\Livewire;

use App\Models\Diagnosis;
use App\Models\ActivityLog;
use Livewire\Component;
use Livewire\Attributes\Computed;

class DiagnosisManager extends Component
{
    public bool   $showForm   = false;
    public ?int   $editId     = null;
    public string $nama       = '';
    public string $warna      = '#6fb1e0';
    public bool   $is_active  = true;
    public int    $sort_order = 0;

    #[Computed]
    public function diagnoses()
    {
        return Diagnosis::orderBy('sort_order')->orderBy('nama')->get();
    }

    public function openAdd(): void
    {
        $this->resetForm();
        $this->sort_order = Diagnosis::max('sort_order') + 1;
        $this->showForm   = true;
    }

    public function openEdit(int $id): void
    {
        $d               = Diagnosis::findOrFail($id);
        $this->editId    = $id;
        $this->nama      = $d->nama;
        $this->warna     = $d->warna;
        $this->is_active = $d->is_active;
        $this->sort_order= $d->sort_order;
        $this->showForm  = true;
    }

    public function save(): void
    {
        $this->validate([
            'nama'      => 'required|min:2|max:100',
            'warna'     => 'required|regex:/^#[0-9A-Fa-f]{6}$/',
            'sort_order'=> 'required|integer|min:0',
        ]);

        $data = [
            'nama'       => trim($this->nama),
            'warna'      => $this->warna,
            'is_active'  => $this->is_active,
            'sort_order' => $this->sort_order,
        ];

        if ($this->editId) {
            $old = Diagnosis::find($this->editId)->toArray();
            Diagnosis::findOrFail($this->editId)->update($data);
            ActivityLog::record('updated','Diagnosis diperbarui: '.$this->nama,'Diagnosis',$this->editId,$old,$data);
            $this->dispatch('toast', message: 'Diagnosis "'.$this->nama.'" diperbarui.', type: 'success');
        } else {
            $d = Diagnosis::create($data);
            ActivityLog::record('created','Diagnosis ditambah: '.$this->nama,'Diagnosis',$d->id,null,$data);
            $this->dispatch('toast', message: 'Diagnosis "'.$this->nama.'" ditambahkan.', type: 'success');
        }
        $this->cancel();
    }

    public function toggleActive(int $id): void
    {
        $d  = Diagnosis::findOrFail($id);
        $new = !$d->is_active;
        $d->update(['is_active' => $new]);
        $status = $new ? 'diaktifkan' : 'dinonaktifkan';
        ActivityLog::record('updated',"Diagnosis $status: {$d->nama}",'Diagnosis',$id);
        $this->dispatch('toast', message: "Diagnosis \"{$d->nama}\" {$status}.", type: 'success');
    }

    public function delete(int $id): void
    {
        if (!auth()->user()?->isAdmin()) {
            $this->dispatch('toast', message: 'Hanya admin yang dapat menghapus diagnosis.', type: 'error');
            return;
        }
        $d = Diagnosis::findOrFail($id);
        if ($d->nama === 'Lainnya') {
            $this->dispatch('toast', message: 'Diagnosis "Lainnya" tidak dapat dihapus.', type: 'error');
            return;
        }
        $nama = $d->nama;
        ActivityLog::record('deleted',"Diagnosis dihapus: {$nama}",'Diagnosis',$id);
        $d->delete();
        $this->dispatch('toast', message: "\"$nama\" dihapus.", type: 'success');
    }

    public function cancel(): void { $this->showForm = false; $this->resetForm(); }

    private function resetForm(): void
    {
        $this->editId    = null;
        $this->nama      = '';
        $this->warna     = '#6fb1e0';
        $this->is_active = true;
        $this->sort_order= 0;
        $this->resetValidation();
    }

    public function render() { return view('livewire.diagnosis-manager'); }
}
