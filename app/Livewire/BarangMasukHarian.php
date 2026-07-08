<?php

namespace App\Livewire;

use App\Models\PurchaseOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Barang Masuk Harian — "memotret" Purchase Order (barang masuk) per hari.
 * Kalender bulanan: klik hari = pilih/batal, Shift+klik = rentang. Bisa kombinasi
 * tanggal lepas (5 & 7) + rentang (5–7). Di bawah: kartu agregat per hari.
 */
class BarangMasukHarian extends Component
{
    public int $year;
    public int $month;
    public array $selected = [];        // 'Y-m-d'
    public ?string $lastClicked = null;

    public function mount(): void
    {
        $this->year  = (int) now()->year;
        $this->month = (int) now()->month;
    }

    public function prevMonth(): void
    {
        $d = Carbon::create($this->year, $this->month, 1)->subMonth();
        $this->year = $d->year; $this->month = $d->month;
    }

    public function nextMonth(): void
    {
        $d = Carbon::create($this->year, $this->month, 1)->addMonth();
        $this->year = $d->year; $this->month = $d->month;
    }

    /**
     * Peta PO per hari di bulan tampil: ['Y-m-d' => ['count','beli','klaim','laba']].
     * Klaim = potensi penggantian/jual dari qty yang dibeli:
     *   kronis → unit × klaim_bpjs × faktor_jasa_farmasi ; non_kronis/bmhp → unit × harga_jual.
     * Laba = klaim − beli (untung/rugi pengadaan).
     */
    #[Computed]
    public function monthMap(): array
    {
        $start = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $end   = (clone $start)->endOfMonth();

        $rows = DB::table('purchase_order_items as poi')
            ->join('purchase_orders as po', 'poi.purchase_order_id', '=', 'po.id')
            ->join('obat as o', 'o.id', '=', 'poi.obat_id')
            ->whereBetween('po.tanggal_po', [$start->toDateString(), $end->toDateString()])
            ->selectRaw("
                DATE(po.tanggal_po) as d,
                COUNT(DISTINCT po.id) as c,
                COALESCE(SUM(poi.subtotal),0) as beli,
                COALESCE(SUM(
                    poi.jumlah_box * GREATEST(poi.isi_per_box,1) *
                    CASE WHEN COALESCE(poi.tipe_obat, o.tipe_obat) = 'kronis'
                         THEN COALESCE(o.klaim_bpjs_per_unit,0) * " . \App\Models\Obat::jfSql('o.faktor_jasa_farmasi') . "
                         ELSE COALESCE(o.harga_jual_per_unit,0) END
                ),0) as klaim
            ")
            ->groupBy('d')
            ->get();

        $out = [];
        foreach ($rows as $r) {
            $beli = (float) $r->beli; $klaim = (float) $r->klaim;
            $out[$r->d] = ['count' => (int) $r->c, 'beli' => $beli, 'klaim' => $klaim, 'laba' => $klaim - $beli, 'pay' => null];
        }

        // ── Rollup STATUS BAYAR per hari (dari tabel tagihan, via PO.tanggal_po) ──
        // Menyatukan semua tagihan (kronis + non_kronis) milik PO hari itu → satu status hari.
        $today = now()->toDateString();
        $pay = DB::table('tagihan as t')
            ->join('purchase_orders as po', 't.purchase_order_id', '=', 'po.id')
            ->whereBetween('po.tanggal_po', [$start->toDateString(), $end->toDateString()])
            ->selectRaw("
                DATE(po.tanggal_po) as d,
                COUNT(*) as bills,
                SUM(t.status = 'lunas') as lunas,
                SUM(t.status = 'sebagian') as sebagian,
                SUM(t.status <> 'lunas' AND t.tanggal_jatuh_tempo < ?) as overdue,
                COALESCE(SUM(t.total_tagihan),0) as tagih,
                COALESCE(SUM(t.jumlah_dibayar),0) as bayar
            ", [$today])
            ->groupBy('d')
            ->get();

        foreach ($pay as $p) {
            if (! isset($out[$p->d])) continue;                    // hanya hari yang punya PO
            $bills = (int) $p->bills; $lunas = (int) $p->lunas; $sebagian = (int) $p->sebagian; $overdue = (int) $p->overdue;
            $out[$p->d]['pay'] = [
                'status'   => self::rollupStatus($bills, $lunas, $sebagian, $overdue),
                'bills'    => $bills,
                'overdue'  => $overdue,
                'terutang' => max(0, (float) $p->tagih - (float) $p->bayar),
            ];
        }
        // Hari punya PO tapi tanpa tagihan → pay tetap null → di UI = "belum ada tagihan".
        return $out;
    }

    /** Status bayar 1 hari dari agregat tagihan. Prioritas: overdue > lunas-penuh > ada-progres > belum. */
    public static function rollupStatus(int $bills, int $lunas, int $sebagian, int $overdue): string
    {
        if ($bills === 0)          return 'none';
        if ($overdue > 0)          return 'overdue';
        if ($lunas === $bills)     return 'lunas';
        if ($lunas > 0 || $sebagian > 0) return 'sebagian';
        return 'belum';
    }

    /**
     * Meta visual status bayar 1 hari (dipakai kalender + panel kanan). $pay = monthMap[..]['pay'] atau null.
     * Channel warna SENGAJA beda dari laba/rugi (laba = tint latar + teks; bayar = titik sudut).
     */
    public static function payMeta(?array $pay): array
    {
        return match ($pay['status'] ?? 'none') {
            'lunas'    => ['s' => 'lunas',    'label' => 'Lunas',             'color' => 'var(--emer)',  'ring' => 'rgba(63,207,142,.55)',  'hollow' => false],
            'sebagian' => ['s' => 'sebagian', 'label' => 'Sebagian dibayar',  'color' => 'var(--gold2)', 'ring' => 'rgba(242,198,104,.55)', 'hollow' => false],
            'belum'    => ['s' => 'belum',    'label' => 'Belum dibayar',     'color' => 'var(--gold)',  'ring' => 'rgba(217,164,65,.55)',  'hollow' => false],
            'overdue'  => ['s' => 'overdue',  'label' => 'Jatuh tempo lewat', 'color' => 'var(--red2)',  'ring' => 'rgba(232,100,90,.6)',   'hollow' => false],
            default    => ['s' => 'none',     'label' => 'Belum ada tagihan', 'color' => 'var(--mut2)',  'ring' => 'var(--line3)',          'hollow' => true],
        };
    }

    /** Normalisasi faktor jasa farmasi → pengali. Delegasi ke satu sumber kebenaran. */
    public static function faktorMul($f): float
    {
        return \App\Models\Obat::jfMultiplier($f);
    }

    /**
     * Untung/rugi 1 PO. KLAIM = jumlah unit × klaim_bpjs × (1 + faktor jasa farmasi) untuk
     * kronis (mis. Amlodipin 144 × 100 = 14.400, + jasa 28% = 18.432). Non-kronis/BMHP →
     * harga_jual_per_unit × unit.
     */
    private function financePO(PurchaseOrder $po): array
    {
        $beli = 0.0; $klaim = 0.0;
        foreach ($po->items as $it) {
            $o     = $it->obat;
            $units = (float) $it->jumlah_box * max(1, (float) $it->isi_per_box);
            $beli += (float) $it->subtotal;
            $isKronis = (($it->tipe_obat ?? $o?->tipe_obat ?? 'kronis') === 'kronis');
            $perUnit  = $isKronis
                ? (float) ($o->klaim_bpjs_per_unit ?? 0) * self::faktorMul($o->faktor_jasa_farmasi)
                : (float) ($o->harga_jual_per_unit ?? 0);
            $klaim += $units * $perUnit;
        }
        return ['beli' => $beli, 'klaim' => $klaim, 'laba' => $klaim - $beli];
    }

    public function toggleDate(string $date, bool $shift = false): void
    {
        if ($shift && $this->lastClicked) {
            $a = min($this->lastClicked, $date);
            $b = max($this->lastClicked, $date);
            foreach (Carbon::parse($a)->daysUntil(Carbon::parse($b)) as $d) {
                $key = $d->toDateString();
                if (! in_array($key, $this->selected, true)) $this->selected[] = $key;
            }
        } else {
            $i = array_search($date, $this->selected, true);
            if ($i !== false) {
                unset($this->selected[$i]);
                $this->selected = array_values($this->selected);
            } else {
                $this->selected[] = $date;
            }
        }
        $this->lastClicked = $date;
        rsort($this->selected);
    }

    public function pilihSemuaBerPO(): void
    {
        $this->selected = array_keys(array_filter($this->monthMap, fn ($v) => $v['count'] > 0));
        rsort($this->selected);
    }

    public function resetPilihan(): void
    {
        $this->selected = [];
        $this->lastClicked = null;
    }

    public function preset(string $type): void
    {
        $this->selected = match ($type) {
            'hari_ini'  => [now()->toDateString()],
            'kemarin'   => [now()->subDay()->toDateString()],
            'tujuh'     => collect(range(0, 6))->map(fn ($i) => now()->subDays($i)->toDateString())->all(),
            'bulan_ini' => array_keys(array_filter($this->monthMap, fn ($v) => $v['count'] > 0)),
            default     => $this->selected,
        };
        rsort($this->selected);
        // pindah kalender ke bulan tanggal pertama terpilih
        if ($this->selected) {
            $d = Carbon::parse($this->selected[0]);
            $this->year = $d->year; $this->month = $d->month;
        }
    }

    /** PO untuk tanggal terpilih, dikelompokkan per hari (desc). */
    #[Computed]
    public function grouped(): array
    {
        if (empty($this->selected)) return [];

        $pos = PurchaseOrder::with(['distributor', 'items.obat', 'tagihan'])
            ->whereIn(DB::raw('DATE(tanggal_po)'), $this->selected)
            ->orderByDesc('tanggal_po')
            ->orderByDesc('id')
            ->get();

        $today = now()->toDateString();
        $out = [];
        foreach ($pos as $po) {
            $key = Carbon::parse($po->tanggal_po)->toDateString();
            $fin = $this->financePO($po);
            $out[$key]['rows'][]  = ['po' => $po, 'fin' => $fin];
            $out[$key]['beli']    = ($out[$key]['beli'] ?? 0) + $fin['beli'];
            $out[$key]['klaim']   = ($out[$key]['klaim'] ?? 0) + $fin['klaim'];
            $out[$key]['laba']    = ($out[$key]['laba'] ?? 0) + $fin['laba'];
            $out[$key]['count']   = ($out[$key]['count'] ?? 0) + 1;

            // Rollup status bayar hari (dari tagihan tiap PO)
            $pr = $out[$key]['pay'] ?? ['bills' => 0, 'lunas' => 0, 'sebagian' => 0, 'overdue' => 0, 'terutang' => 0.0];
            foreach ($po->tagihan as $t) {
                $pr['bills']++;
                if ($t->status === 'lunas')          $pr['lunas']++;
                elseif ($t->status === 'sebagian')   $pr['sebagian']++;
                if ($t->status !== 'lunas' && $t->tanggal_jatuh_tempo && $t->tanggal_jatuh_tempo->toDateString() < $today) $pr['overdue']++;
                $pr['terutang'] += max(0, (float) $t->total_tagihan - (float) $t->jumlah_dibayar);
            }
            $out[$key]['pay'] = $pr;
        }

        // Turunkan label status per hari
        foreach ($out as $k => $v) {
            $pr = $v['pay'] ?? ['bills' => 0, 'lunas' => 0, 'sebagian' => 0, 'overdue' => 0, 'terutang' => 0];
            $pr['status'] = self::rollupStatus((int) $pr['bills'], (int) $pr['lunas'], (int) $pr['sebagian'], (int) $pr['overdue']);
            $out[$k]['pay'] = $pr;
        }

        krsort($out);   // hari desc
        return $out;
    }

    /** Total OVERALL semua tanggal terpilih — beli vs klaim → untung/rugi gabungan. */
    #[Computed]
    public function summary(): array
    {
        $g     = $this->grouped;
        $beli  = array_sum(array_column($g, 'beli'));
        $klaim = array_sum(array_column($g, 'klaim'));
        $laba  = $klaim - $beli;
        return [
            'hari'   => count($g),
            'po'     => array_sum(array_column($g, 'count')),
            'beli'   => $beli,
            'klaim'  => $klaim,
            'laba'   => $laba,
            'margin' => $klaim > 0 ? round($laba / $klaim * 100, 1) : 0,
        ];
    }

    public function render()
    {
        return view('livewire.barang-masuk-harian');
    }
}
