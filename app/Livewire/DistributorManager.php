<?php

namespace App\Livewire;

use App\Models\Distributor;
use Livewire\Attributes\Computed;
use Livewire\Component;

class DistributorManager extends Component
{
    public bool $showForm = false;
    public ?int $editId   = null;

    public string $name    = '';
    public string $phone   = '';
    public string $address = '';

    #[Computed]
    public function distributors()
    {
        return Distributor::orderBy('name')->get();
    }

    public function openAdd(): void
    {
        $this->reset(['name', 'phone', 'address', 'editId']);
        $this->showForm = true;
    }

    public function openEdit(int $id): void
    {
        $d = Distributor::findOrFail($id);
        $this->editId  = $id;
        $this->name    = $d->name;
        $this->phone   = $d->phone   ?? '';
        $this->address = $d->address ?? '';
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate([
            'name'    => 'required|min:3|max:120',
            'phone'   => 'nullable|max:30',
            'address' => 'nullable|max:255',
        ]);

        if ($this->editId) {
            Distributor::findOrFail($this->editId)->update([
                'name'    => $this->name,
                'phone'   => $this->phone  ?: null,
                'address' => $this->address ?: null,
            ]);
            $this->dispatch('toast', message: 'Distributor diperbarui.', type: 'success');
        } else {
            Distributor::create([
                'name'    => $this->name,
                'phone'   => $this->phone  ?: null,
                'address' => $this->address ?: null,
            ]);
            $this->dispatch('toast', message: 'Distributor baru ditambahkan!', type: 'success');
        }

        $this->reset(['name', 'phone', 'address', 'editId', 'showForm']);
    }

    public function toggleActive(int $id): void
    {
        $d = Distributor::findOrFail($id);
        $d->update(['is_active' => !$d->is_active]);
        $this->dispatch('toast', message: $d->name . ($d->is_active ? ' diaktifkan.' : ' dinonaktifkan.'), type: 'success');
    }

    public function cancel(): void
    {
        $this->reset(['name', 'phone', 'address', 'editId', 'showForm']);
    }

    public function render()
    {
        return view('livewire.distributor-manager');
    }
}
