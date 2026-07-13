<?php

namespace App\Livewire;

use App\Models\GuardianAck;
use App\Services\Guardian\GuardianEngine;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Pusat Tinjau Pharmacy Guardian AI — menampilkan temuan anomali PO ↔ Tagihan,
 * dikelompokkan per faktur, dengan alur konfirmasi (aman / diperbaiki).
 */
class GuardianReview extends Component
{
    public string $filterSeverity = 'semua';   // semua|kritis|tinggi|sedang|rendah
    public string $filterKategori = 'semua';   // semua|Rekonsiliasi|Tipe|Harga|...
    public string $search = '';
    public bool   $showConfirmed = false;

    #[Computed]
    public function report()
    {
        return app(GuardianEngine::class)->scan($this->showConfirmed);
    }

    #[Computed]
    public function grouped()
    {
        $q = trim(mb_strtolower($this->search));
        return $this->report->groupedByPo()->map(function ($g) use ($q) {
            return $g->filter(function ($f) use ($q) {
                if ($this->filterSeverity !== 'semua' && $f->severity !== $this->filterSeverity) return false;
                if ($this->filterKategori !== 'semua' && $f->category !== $this->filterKategori) return false;
                if ($q !== '') {
                    $hay = mb_strtolower($f->title . ' ' . $f->detail . ' ' . implode(' ', $f->evidence) . ' PO#' . $f->poId);
                    if (! str_contains($hay, $q)) return false;
                }
                return true;
            })->values();
        })->filter(fn ($g) => $g->count() > 0);
    }

    #[Computed]
    public function kategoriList(): array
    {
        return $this->report->collect()->pluck('category')->unique()->sort()->values()->all();
    }

    private function simpanAck(string $code, string $subjectType, int $subjectId, ?int $poId, string $fingerprint, string $status): void
    {
        GuardianAck::updateOrCreate(
            ['code' => $code, 'subject_type' => $subjectType, 'subject_id' => $subjectId],
            ['po_id' => $poId, 'fingerprint' => $fingerprint, 'status' => $status, 'oleh' => Auth::user()?->name]
        );
        GuardianEngine::bustCache();
        unset($this->report, $this->grouped);
    }

    public function tandaiAman(string $code, string $subjectType, int $subjectId, ?int $poId, string $fingerprint): void
    {
        $this->simpanAck($code, $subjectType, $subjectId, $poId, $fingerprint, 'confirmed_ok');
        $this->dispatch('toast', type: 'success', message: 'Temuan dikonfirmasi aman & disembunyikan.');
    }

    public function tandaiDiperbaikI(string $code, string $subjectType, int $subjectId, ?int $poId, string $fingerprint): void
    {
        $this->simpanAck($code, $subjectType, $subjectId, $poId, $fingerprint, 'resolved');
        $this->dispatch('toast', type: 'success', message: 'Temuan ditandai sudah diperbaiki.');
    }

    public function bukaKembali(string $code, string $subjectType, int $subjectId): void
    {
        GuardianAck::where('code', $code)
            ->where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
            ->delete();
        GuardianEngine::bustCache();
        unset($this->report, $this->grouped);
        $this->dispatch('toast', type: 'info', message: 'Temuan dibuka kembali untuk ditinjau.');
    }

    public function render()
    {
        return view('livewire.guardian-review');
    }
}
