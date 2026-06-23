<?php

namespace App\Livewire;

use App\Models\Obat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Gabung Obat Duplikat — wizard fokus 1 grup/layar (2-panel).
 * Pindahkan SELURUH referensi obat_id (item_pengambilan, purchase_order_items, resep_pasien,
 * stok_keluar) dari duplikat → obat UTAMA, lalu hapus duplikat. Histori/transaksi tetap utuh.
 * Selaras dengan fitur serupa di SIM (dokterku_rme).
 */
class GabungObat extends Component
{
    public int $idx = 0;
    public int $mergedCount = 0;
    public ?int $primaryId = null;
    public bool $processing = false;

    private const FK_TABLES = ['item_pengambilan', 'purchase_order_items', 'resep_pasien', 'stok_keluar'];

    public function mount(): void
    {
        $this->syncPrimary();
    }

    private function norm(string $s): string
    {
        $s = strtolower(trim(preg_replace('/[^a-z0-9 ]/i', ' ', $s)));
        return preg_replace('/\s+/', ' ', $s);
    }

    private function numKey(string $s): string
    {
        preg_match_all('/\d+(?:[.,]\d+)?/', $s, $m);
        $a = array_map(fn ($x) => str_replace(',', '.', $x), $m[0]);
        sort($a);
        return implode('|', $a);
    }

    /** Hitung pemakaian (referensi) per obat_id di semua tabel anak. */
    private function usageMap(array $ids): array
    {
        $out = array_fill_keys($ids, 0);
        if (! $ids) return $out;
        foreach (self::FK_TABLES as $t) {
            if (! Schema::hasTable($t) || ! Schema::hasColumn($t, 'obat_id')) continue;
            $rows = DB::table($t)->select('obat_id', DB::raw('COUNT(*) c'))
                ->whereIn('obat_id', $ids)->groupBy('obat_id')->pluck('c', 'obat_id');
            foreach ($rows as $id => $c) $out[$id] = ($out[$id] ?? 0) + (int) $c;
        }
        return $out;
    }

    /** Grup obat berpotensi duplikat (nama ternormalisasi sama / mirip ≥88%, kekuatan WAJIB sama). */
    #[Computed(persist: false)]
    public function groups(): array
    {
        $meds = Obat::select('id', 'nama_obat', 'tipe_obat', 'satuan', 'bentuk_sediaan', 'is_active', 'stok_aktual')
            ->orderBy('nama_obat')->get()->values()->all();
        $alpha = fn (string $s): string => preg_replace('/[^a-z]/', '', strtolower($s));
        $used = [];
        $groups = [];
        for ($i = 0; $i < count($meds); $i++) {
            if (isset($used[$meds[$i]->id])) continue;
            $ni = $this->norm($meds[$i]->nama_obat); $ai = $alpha($meds[$i]->nama_obat); $ki = $this->numKey($meds[$i]->nama_obat);
            $g = [$meds[$i]];
            for ($j = $i + 1; $j < count($meds); $j++) {
                if (isset($used[$meds[$j]->id])) continue;
                $nj = $this->norm($meds[$j]->nama_obat); $aj = $alpha($meds[$j]->nama_obat); $kj = $this->numKey($meds[$j]->nama_obat);
                if ($ki !== $kj) continue;   // kekuatan/dosis beda → BUKAN duplikat (bahaya klinis)
                $pct = 0.0; similar_text($ai, $aj, $pct);
                if ($ai === $aj || $ni === $nj || $pct >= 88) { $g[] = $meds[$j]; $used[$meds[$j]->id] = true; }
            }
            if (count($g) > 1) { $used[$meds[$i]->id] = true; $groups[] = $g; }
        }

        $allIds = collect($groups)->flatten()->pluck('id')->all();
        $usage = $this->usageMap($allIds);

        return collect($groups)->map(function ($g) use ($usage) {
            $items = collect($g)->map(fn ($m) => [
                'id'        => $m->id,
                'name'      => $m->nama_obat,
                'tipe'      => $m->tipe_obat,
                'satuan'    => $m->satuan,
                'bentuk'    => $m->bentuk_sediaan,
                'is_active' => (bool) $m->is_active,
                'stok'      => (float) $m->stok_aktual,
                'usage'     => (int) ($usage[$m->id] ?? 0),
            ])->sortByDesc('usage')->values()->all();
            return [
                'label'     => $items[0]['name'],
                'count'     => count($items),
                'items'     => $items,
                'suggested' => $items[0]['id'],
            ];
        })->sortByDesc('count')->values()->all();
    }

    #[Computed(persist: false)]
    public function current(): ?array
    {
        return $this->groups[$this->idx] ?? null;
    }

    #[Computed(persist: false)]
    public function dups(): array
    {
        $g = $this->current;
        if (! $g) return [];
        return collect($g['items'])->filter(fn ($m) => $m['id'] !== $this->primaryId)->values()->all();
    }

    #[Computed(persist: false)]
    public function mixedUnit(): bool
    {
        $g = $this->current;
        if (! $g) return false;
        return collect($g['items'])->map(fn ($m) => strtolower(trim(($m['satuan'] ?? '') . '|' . ($m['bentuk'] ?? ''))))->unique()->count() > 1;
    }

    private function syncPrimary(): void
    {
        unset($this->groups, $this->current, $this->dups, $this->mixedUnit);
        $g = $this->current;
        $this->primaryId = $g['suggested'] ?? null;
    }

    public function skip(): void
    {
        unset($this->groups);
        if ($this->idx < count($this->groups)) $this->idx++;
        $this->syncPrimary();
    }

    public function restart(): void
    {
        $this->idx = 0;
        $this->syncPrimary();
    }

    public function merge(): void
    {
        $g = $this->current;
        if (! $g || ! $this->primaryId) return;
        $dupIds = collect($g['items'])->pluck('id')->filter(fn ($id) => $id !== $this->primaryId)->values()->all();
        if (! $dupIds) return;

        $this->processing = true;
        try {
            DB::transaction(function () use ($dupIds) {
                foreach (self::FK_TABLES as $t) {
                    if (Schema::hasTable($t) && Schema::hasColumn($t, 'obat_id')) {
                        DB::table($t)->whereIn('obat_id', $dupIds)->update(['obat_id' => $this->primaryId]);
                    }
                }
                $primary = Obat::find($this->primaryId);
                foreach (Obat::whereIn('id', $dupIds)->get() as $d) {
                    DB::table('activity_logs')->insert([
                        'user_id'    => Auth::id(),
                        'model_type' => Obat::class,
                        'model_id'   => $this->primaryId,
                        'action'     => 'obat.merge',
                        'description' => 'Gabung "' . $d->nama_obat . '" → "' . ($primary->nama_obat ?? '') . '"',
                        'new_values' => json_encode(['merged_from' => ['id' => $d->id, 'name' => $d->nama_obat], 'into' => ['id' => $this->primaryId]]),
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                    $d->delete();
                }
            });
            $this->mergedCount++;
            unset($this->groups);
            if ($this->idx > count($this->groups) - 1) $this->idx = max(0, count($this->groups) - 1);
            $this->syncPrimary();
            session()->flash('merge_ok', count($dupIds) . ' obat duplikat digabung.');
        } catch (\Throwable $e) {
            session()->flash('merge_err', 'Gagal menggabung: ' . (str_contains($e->getMessage(), 'Duplicate') ? 'bentrok stok/batch.' : 'kesalahan sistem.'));
        } finally {
            $this->processing = false;
        }
    }

    public function render()
    {
        return view('livewire.gabung-obat');
    }
}
