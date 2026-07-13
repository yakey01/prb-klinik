<?php

namespace App\Services\Guardian;

use Illuminate\Support\Collection;

/**
 * Hasil pemindaian Guardian AI: kumpulan temuan + agregasi risiko.
 */
class GuardianReport
{
    /** @param Finding[] $findings temuan AKTIF (belum dikonfirmasi/berbeda). */
    public function __construct(
        public array $findings = [],
        public int   $poDiperiksa = 0,
        public int   $itemDiperiksa = 0,
        public int   $dikonfirmasi = 0,   // jumlah temuan yg sudah di-ack (tersembunyi)
    ) {}

    /** @return Collection<int,Finding> */
    public function collect(): Collection
    {
        return collect($this->findings);
    }

    /** Hitungan per keparahan. */
    public function counts(): array
    {
        $c = ['kritis' => 0, 'tinggi' => 0, 'sedang' => 0, 'rendah' => 0];
        foreach ($this->findings as $f) $c[$f->severity] = ($c[$f->severity] ?? 0) + 1;
        $c['total'] = count($this->findings);
        return $c;
    }

    /** Skor risiko total (0..∞) — semakin tinggi semakin genting. */
    public function totalScore(): float
    {
        return round(array_sum(array_map(fn (Finding $f) => $f->score(), $this->findings)), 1);
    }

    /** Temuan dikelompokkan per faktur (PO), diurut keparahan tertinggi dulu. */
    public function groupedByPo(): Collection
    {
        return $this->collect()
            ->groupBy(fn (Finding $f) => $f->poId ?? 0)
            ->map(fn ($g) => $g->sortByDesc(fn (Finding $f) => $f->severityRank() * 100 + $f->confidence)->values())
            ->sortByDesc(fn ($g) => $g->max(fn (Finding $f) => $f->severityRank() * 1000 + $f->score()));
    }

    /** Peta risiko per PO: poId => ['level','count','score','top']. */
    public function riskByPo(): array
    {
        $out = [];
        foreach ($this->groupedByPo() as $poId => $g) {
            $score = round($g->sum(fn (Finding $f) => $f->score()), 1);
            $maxRank = $g->max(fn (Finding $f) => $f->severityRank());
            $out[(int) $poId] = [
                'level' => match (true) {
                    $maxRank >= 4 => 'kritis',
                    $maxRank == 3 => 'tinggi',
                    $maxRank == 2 => 'sedang',
                    default       => 'rendah',
                },
                'count' => $g->count(),
                'score' => $score,
                'top'   => $g->first()?->title,
            ];
        }
        return $out;
    }
}
