<?php
namespace App\Livewire;

use App\Models\ActivityLog;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

class AuditLog extends Component
{
    use WithPagination;

    public string $search     = '';
    public string $filterAction = 'semua';

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterAction(): void { $this->resetPage(); }

    #[Computed]
    public function logs()
    {
        $query = ActivityLog::with('user')->latest();

        if ($this->search) {
            $query->where('description', 'like', '%'.$this->search.'%');
        }
        if ($this->filterAction !== 'semua') {
            $query->where('action', $this->filterAction);
        }

        return $query->paginate(25);
    }

    public function getActionIconProperty(): array
    {
        return [
            'created' => '✚',
            'updated' => '✎',
            'deleted' => '✕',
            'login'   => '→',
            'export'  => '↓',
            'import'  => '↑',
        ];
    }

    public function getActionColorProperty(): array
    {
        return [
            'created' => 'var(--emer2)',
            'updated' => 'var(--gold2)',
            'deleted' => 'var(--red2)',
            'login'   => 'var(--blue)',
            'export'  => 'var(--mut)',
            'import'  => 'var(--blue)',
        ];
    }

    public function render() { return view('livewire.audit-log'); }
}
