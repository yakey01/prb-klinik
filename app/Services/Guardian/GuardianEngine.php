<?php

namespace App\Services\Guardian;

use App\Models\GuardianAck;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * ╔═══════════════════════════════════════════════════════════════════╗
 * ║  PHARMACY GUARDIAN AI  ·  mesin deteksi anomali (pure PHP)         ║
 * ╠═══════════════════════════════════════════════════════════════════╣
 * ║  Explainable rule + statistical anomaly detection ala sistem       ║
 * ║  farmasi kelas dunia (median/MAD z-score) — TANPA API eksternal.   ║
 * ║                                                                     ║
 * ║  Fokus utama: REKONSILIASI Riwayat PO ↔ Tagihan agar tidak         ║
 * ║  tertukar/salah, ditambah 8 detektor pendukung. Setiap temuan      ║
 * ║  membawa bukti, keyakinan, dan rekomendasi tindakan, serta bisa    ║
 * ║  dikonfirmasi manusia (ack) dengan sidik jari kondisi.             ║
 * ╚═══════════════════════════════════════════════════════════════════╝
 */
class GuardianEngine
{
    /** Statistik harga/qty historis per obat (median + MAD). */
    private array $priceStats = [];
    private array $qtyStats   = [];

    /**
     * Pindai faktur & tagihan dalam WINDOW aktif, kembalikan laporan temuan AKTIF.
     * Statistik harga/qty tetap belajar dari SELURUH histori (lebih akurat),
     * namun temuan hanya untuk faktur baru (≤ windowDays) atau yang masih berutang.
     *
     * @param bool $includeAcked sertakan temuan yang sudah dikonfirmasi manusia
     * @param int  $windowDays   rentang faktur yang diperiksa (default 120 hari)
     */
    public function scan(bool $includeAcked = false, int $windowDays = 120): GuardianReport
    {
        // Statistik historis dari SELURUH data (query ringan, tak windowed).
        $this->buildHistoryFromDb();

        // Kolom item — 'tanggal_kadaluarsa' opsional (tak semua skema punya).
        $itemCols = ['id', 'purchase_order_id', 'obat_id', 'tipe_obat', 'jumlah_box', 'isi_per_box', 'harga_per_box', 'subtotal'];
        if (Schema::hasColumn('purchase_order_items', 'tanggal_kadaluarsa')) {
            $itemCols[] = 'tanggal_kadaluarsa';
        }

        $cut = now()->subDays($windowDays)->toDateString();
        $pos = PurchaseOrder::with([
            'items:' . implode(',', $itemCols),
            'items.obat:id,nama_obat,tipe_obat,harga_beli_per_unit,satuan',
            'tagihan:id,purchase_order_id,distributor_id,nomor_tagihan,tipe_obat,total_tagihan,jumlah_dibayar,status',
            'distributor:id,name',
        ])
            ->where(function ($q) use ($cut) {
                $q->whereDate('tanggal_po', '>=', $cut)
                    ->orWhereHas('tagihan', fn ($t) => $t->whereIn('status', ['belum_bayar', 'sebagian']));
            })
            ->orderByDesc('id')->get();

        $findings   = [];
        $itemCount  = 0;
        $invoiceMap = [];   // invoice(normalized) => [ ['po'=>id,'dist'=>id,'name'=>..], ... ]

        foreach ($pos as $po) {
            $itemCount += $po->items->count();
            // Kumpulkan peta invoice untuk deteksi dobel & reuse antar-PBF.
            $inv = $this->norm($po->nomor_invoice);
            if ($inv !== '') {
                $invoiceMap[$inv][] = ['po' => $po->id, 'dist' => $po->distributor_id, 'name' => $po->distributor?->name, 'tanggal' => $po->tanggal_po, 'total' => (float) $po->total_nilai];
            }

            $this->reconcilePoTagihan($po, $findings);   // ⭐ inti: PO ↔ Tagihan
            $this->detectTipeMismatch($po, $findings);
            $this->detectPriceAnomaly($po, $findings);
            $this->detectQtyOutlier($po, $findings);
            $this->detectExpiry($po, $findings);
            $this->detectOverpayment($po, $findings);
        }

        $this->detectDuplicateFaktur($invoiceMap, $pos, $findings);
        $this->detectInvoiceReuse($invoiceMap, $findings);

        // ── Filter berdasarkan konfirmasi manusia (ack) ──
        $acks = GuardianAck::get()->keyBy(fn ($a) => $a->key());
        $aktif = [];
        $dikonfirmasi = 0;
        foreach ($findings as $f) {
            $ack = $acks->get($f->key());
            if ($ack) {
                if ($ack->fingerprint === $f->fingerprint()) {
                    // Dikonfirmasi & kondisi TAK berubah → sembunyikan.
                    $f->ack = $ack->toArray();
                    $dikonfirmasi++;
                    if (! $includeAcked) continue;
                } else {
                    // Pernah dikonfirmasi TAPI kondisi berubah → munculkan lagi + tandai.
                    $f->berubahSejakAck = true;
                }
            }
            $aktif[] = $f;
        }

        return new GuardianReport($aktif, $pos->count(), $itemCount, $dikonfirmasi);
    }

    /** Ringkasan ringan (di-cache) untuk banner halaman lain. */
    public function summary(int $ttl = 120): array
    {
        return Cache::remember('guardian:summary', $ttl, function () {
            $r = $this->scan();
            return $r->counts() + ['score' => $r->totalScore()];
        });
    }

    /** Peta risiko + hitungan (di-cache) untuk badge per-PO di Riwayat. */
    public function riskMap(int $ttl = 120): array
    {
        return Cache::remember('guardian:riskmap', $ttl, function () {
            $r = $this->scan();
            return ['risk' => $r->riskByPo(), 'counts' => $r->counts()];
        });
    }

    /** Detail temuan per-PO (untuk popover penjelasan AI di header faktur). */
    public function poFindings(int $ttl = 120): array
    {
        return Cache::remember('guardian:pofindings', $ttl, function () {
            $r = $this->scan();
            $risk = $r->riskByPo();
            $out = [];
            foreach ($r->groupedByPo() as $poId => $g) {
                $poId = (int) $poId;
                $out[$poId] = [
                    'level' => $risk[$poId]['level'] ?? 'rendah',
                    'count' => $g->count(),
                    'findings' => $g->map(fn (Finding $f) => [
                        'code'           => $f->code,
                        'category'       => $f->category,
                        'severity'       => $f->severity,
                        'confidence'     => $f->confidence,
                        'title'          => $f->title,
                        'detail'         => $f->detail,
                        'evidence'       => $f->evidence,
                        'recommendation' => $f->recommendation,
                    ])->all(),
                ];
            }
            return $out;
        });
    }

    public static function bustCache(): void
    {
        Cache::forget('guardian:summary');
        Cache::forget('guardian:riskmap');
        Cache::forget('guardian:pofindings');
    }

    // ═══════════════════════════════════════════════════════════════════
    //  ⭐ DETEKTOR INTI — REKONSILIASI PO ↔ TAGIHAN
    // ═══════════════════════════════════════════════════════════════════
    private function reconcilePoTagihan(PurchaseOrder $po, array &$out): void
    {
        $items   = $po->items;
        $tagihan = $po->tagihan;

        // Subtotal barang per tipe (sumber kebenaran = isi PO).
        $itemPerTipe = ['kronis' => 0.0, 'non_kronis' => 0.0];
        foreach ($items as $it) {
            $tipe = $it->tipe_obat === 'kronis' ? 'kronis' : 'non_kronis';
            $itemPerTipe[$tipe] += (float) $it->subtotal;
        }
        $tagPerTipe = ['kronis' => 0.0, 'non_kronis' => 0.0];
        foreach ($tagihan as $t) {
            $tipe = $t->tipe_obat === 'kronis' ? 'kronis' : 'non_kronis';
            $tagPerTipe[$tipe] += (float) $t->total_tagihan;
        }

        // 1) Tagihan yatim: ada tagihan tipe X tapi PO tak punya barang tipe X (TERTUKAR).
        foreach (['kronis', 'non_kronis'] as $tipe) {
            if ($tagPerTipe[$tipe] > 0 && $itemPerTipe[$tipe] <= 0 && $items->count() > 0) {
                $lbl = $tipe === 'kronis' ? 'Kronis' : 'Non-Kronis';
                $out[] = new Finding(
                    'RECON_ORPHAN', 'Rekonsiliasi', 'kritis', 96, 'po', $po->id, $po->id,
                    "Tagihan {$lbl} tanpa barang {$lbl} di PO",
                    "Faktur PO #{$po->id} memiliki tagihan {$lbl} Rp " . $this->rp($tagPerTipe[$tipe]) . " padahal daftar barang PO tidak mengandung obat {$lbl}. Kemungkinan tipe tagihan tertukar atau tagihan salah faktur.",
                    ['Tagihan ' . $lbl => 'Rp ' . $this->rp($tagPerTipe[$tipe]), 'Barang ' . $lbl . ' di PO' => 'Rp 0'],
                    "Periksa: apakah tagihan {$lbl} ini seharusnya milik faktur lain, atau tipe barang di PO salah entry."
                );
            }
        }

        // 2) Tagihan hilang: PO punya barang tipe X tapi tak ada tagihan tipe X.
        foreach (['kronis', 'non_kronis'] as $tipe) {
            if ($itemPerTipe[$tipe] > 0 && $tagPerTipe[$tipe] <= 0) {
                $lbl = $tipe === 'kronis' ? 'Kronis' : 'Non-Kronis';
                $out[] = new Finding(
                    'RECON_MISSING', 'Rekonsiliasi', 'tinggi', 90, 'po', $po->id, $po->id,
                    "Tagihan {$lbl} belum terbentuk",
                    "PO #{$po->id} berisi barang {$lbl} senilai Rp " . $this->rp($itemPerTipe[$tipe]) . " tetapi belum ada tagihan {$lbl}. Utang ke PBF berpotensi tidak tercatat.",
                    ['Barang ' . $lbl => 'Rp ' . $this->rp($itemPerTipe[$tipe]), 'Tagihan ' . $lbl => 'belum ada'],
                    "Buat tagihan {$lbl} untuk faktur ini agar utang distributor tercatat lengkap."
                );
            }
        }

        // 3) Selisih nilai per tipe: tagihan ≠ subtotal barang tipe sama.
        foreach (['kronis', 'non_kronis'] as $tipe) {
            $a = $itemPerTipe[$tipe];
            $b = $tagPerTipe[$tipe];
            if ($a > 0 && $b > 0) {
                $selisih = abs($a - $b);
                $tol = max(1.0, $a * 0.005);   // toleransi 0.5% (pembulatan)
                if ($selisih > $tol) {
                    $lbl = $tipe === 'kronis' ? 'Kronis' : 'Non-Kronis';
                    $out[] = new Finding(
                        'RECON_SELISIH', 'Rekonsiliasi', 'kritis', 94, 'po', $po->id, $po->id,
                        "Nilai tagihan {$lbl} ≠ barang PO",
                        "Tagihan {$lbl} Rp " . $this->rp($b) . " tidak sama dengan total barang {$lbl} di PO Rp " . $this->rp($a) . " (selisih Rp " . $this->rp($selisih) . "). Indikasi salah input nominal / barang tertukar.",
                        ['Total barang ' . $lbl => 'Rp ' . $this->rp($a), 'Nilai tagihan ' . $lbl => 'Rp ' . $this->rp($b), 'Selisih' => 'Rp ' . $this->rp($selisih)],
                        "Samakan nilai tagihan dengan total barang PO, atau koreksi item PO bila harga/qty salah."
                    );
                }
            }
        }

        // 4) Total faktur: Σ tagihan ≠ PO.total_nilai.
        $sumTag = array_sum($tagPerTipe);
        $poTotal = (float) $po->total_nilai;
        if ($sumTag > 0 && $poTotal > 0) {
            $selisih = abs($sumTag - $poTotal);
            if ($selisih > max(1.0, $poTotal * 0.005)) {
                $out[] = new Finding(
                    'RECON_TOTAL', 'Rekonsiliasi', 'tinggi', 88, 'po', $po->id, $po->id,
                    "Total tagihan ≠ nilai faktur PO",
                    "Jumlah semua tagihan Rp " . $this->rp($sumTag) . " berbeda dari nilai PO Rp " . $this->rp($poTotal) . " (selisih Rp " . $this->rp($selisih) . ").",
                    ['Nilai PO' => 'Rp ' . $this->rp($poTotal), 'Σ Tagihan' => 'Rp ' . $this->rp($sumTag), 'Selisih' => 'Rp ' . $this->rp($selisih)],
                    "Cek apakah ada barang/tagihan yang belum tercatat atau nilai PO perlu dikoreksi."
                );
            }
        }

        // 5) PBF tertukar: distributor tagihan ≠ distributor PO.
        foreach ($tagihan as $t) {
            if ($t->distributor_id && $po->distributor_id && $t->distributor_id !== $po->distributor_id) {
                $out[] = new Finding(
                    'RECON_PBF', 'Rekonsiliasi', 'tinggi', 92, 'tagihan', $t->id, $po->id,
                    "PBF tagihan berbeda dgn PO",
                    "Tagihan {$t->nomor_tagihan} tertaut PBF berbeda dari faktur PO #{$po->id}. Kemungkinan distributor tertukar.",
                    ['PBF di PO' => $po->distributor?->name ?? ('#' . $po->distributor_id), 'PBF di Tagihan' => '#' . $t->distributor_id, 'Tagihan' => $t->nomor_tagihan],
                    "Perbaiki distributor tagihan agar sesuai faktur PO."
                );
            }
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    //  DETEKTOR PENDUKUNG
    // ═══════════════════════════════════════════════════════════════════

    /** Tipe barang di PO ≠ tipe master obat (kronis/non tertukar). */
    private function detectTipeMismatch(PurchaseOrder $po, array &$out): void
    {
        foreach ($po->items as $it) {
            $master = $it->obat?->tipe_obat;
            if (! $master || ! $it->tipe_obat) continue;
            // BMHP diadakan & ditagih sebagai non-kronis — bukan ketidaksesuaian.
            if ($master === 'bmhp') $master = 'non_kronis';
            if ($master !== $it->tipe_obat) {
                $mLbl = $master === 'kronis' ? 'Kronis' : 'Non-Kronis';
                $iLbl = $it->tipe_obat === 'kronis' ? 'Kronis' : 'Non-Kronis';
                $out[] = new Finding(
                    'TIPE_MISMATCH', 'Tipe', 'tinggi', 86, 'po', $po->id, $po->id,
                    "Tipe obat tertukar: {$it->obat->nama_obat}",
                    "Di PO #{$po->id}, obat {$it->obat->nama_obat} dientry sebagai {$iLbl}, padahal di katalog obat ini {$mLbl}. Mempengaruhi pemisahan klaim BPJS.",
                    ['Obat' => $it->obat->nama_obat, 'Tipe di katalog' => $mLbl, 'Tipe di PO' => $iLbl],
                    "Samakan tipe item PO dengan katalog, atau perbaiki katalog bila tipe master salah."
                );
            }
        }
    }

    /** Harga beli janggal vs median historis obat (MAD z-score). */
    private function detectPriceAnomaly(PurchaseOrder $po, array &$out): void
    {
        foreach ($po->items as $it) {
            if (! $it->obat_id || (int) $it->isi_per_box <= 0) continue;
            $unit = (float) $it->harga_per_box / (int) $it->isi_per_box;
            if ($unit <= 0) continue;

            $st = $this->priceStats[$it->obat_id] ?? null;
            $median = $st['median'] ?? ((float) ($it->obat?->harga_beli_per_unit ?? 0));
            $mad    = $st['mad'] ?? 0.0;
            $n      = $st['n'] ?? 0;
            if ($median <= 0) continue;

            $ratio = $unit / $median;
            // Kemungkinan kelebihan/kurang digit (≈10×) — sangat kuat.
            if ($ratio >= 8 || $ratio <= 0.125) {
                $out[] = new Finding(
                    'PRICE_DIGIT', 'Harga', 'tinggi', 90, 'po', $po->id, $po->id,
                    "Harga janggal (kemungkinan salah digit): {$it->obat->nama_obat}",
                    "Harga satuan Rp " . $this->rp($unit) . " ≈ " . number_format($ratio, 1) . "× dari biasanya (Rp " . $this->rp($median) . "). Kemungkinan kelebihan/kurang angka nol.",
                    ['Obat' => $it->obat->nama_obat, 'Harga input/unit' => 'Rp ' . $this->rp($unit), 'Median historis' => 'Rp ' . $this->rp($median), 'Rasio' => number_format($ratio, 1) . '×'],
                    "Cek faktur asli PBF; koreksi harga bila salah ketik."
                );
                continue;
            }
            // Outlier statistik (butuh cukup sampel & MAD).
            if ($n >= 4 && $mad > 0) {
                $z = 0.6745 * ($unit - $median) / $mad;   // robust z-score
                $dev = abs($unit - $median) / $median;
                if (abs($z) >= 3.5 && $dev >= 0.25) {
                    $arah = $unit > $median ? 'lebih tinggi' : 'lebih rendah';
                    $out[] = new Finding(
                        'PRICE_OUTLIER', 'Harga', 'sedang', min(85, 55 + (int) round(abs($z) * 4)), 'po', $po->id, $po->id,
                        "Harga di luar kebiasaan: {$it->obat->nama_obat}",
                        "Harga satuan Rp " . $this->rp($unit) . " {$arah} " . number_format($dev * 100, 0) . "% dari median historis Rp " . $this->rp($median) . " (dari {$n} pembelian).",
                        ['Obat' => $it->obat->nama_obat, 'Harga input/unit' => 'Rp ' . $this->rp($unit), 'Median historis' => 'Rp ' . $this->rp($median), 'Deviasi' => number_format($dev * 100, 0) . '%'],
                        "Konfirmasi apakah harga naik/turun wajar (nego/kontrak) atau salah input."
                    );
                }
            }
        }
    }

    /** Kuantitas jauh di atas kebiasaan (kemungkinan salah ketik). */
    private function detectQtyOutlier(PurchaseOrder $po, array &$out): void
    {
        foreach ($po->items as $it) {
            if (! $it->obat_id) continue;
            $unit = (int) $it->jumlah_box * max(1, (int) $it->isi_per_box);
            $st = $this->qtyStats[$it->obat_id] ?? null;
            if (! $st || ($st['n'] ?? 0) < 4 || ($st['median'] ?? 0) <= 0) continue;
            if ($unit >= 5 * $st['median'] && $unit >= 500) {
                $out[] = new Finding(
                    'QTY_OUTLIER', 'Kuantitas', 'sedang', 62, 'po', $po->id, $po->id,
                    "Jumlah tak biasa: {$it->obat->nama_obat}",
                    "Qty {$unit} {$it->obat->satuan} ≈ " . number_format($unit / $st['median'], 1) . "× dari biasanya (median {$st['median']}). Pastikan bukan salah ketik box/isi.",
                    ['Obat' => $it->obat->nama_obat, 'Qty PO' => $unit . ' ' . $it->obat->satuan, 'Median historis' => (int) $st['median'] . ' ' . $it->obat->satuan],
                    "Verifikasi jumlah box × isi pada faktur asli."
                );
            }
        }
    }

    /** Barang kedaluwarsa / mendekati exp saat diterima. */
    private function detectExpiry(PurchaseOrder $po, array &$out): void
    {
        $today = now()->startOfDay();
        foreach ($po->items as $it) {
            if (! $it->tanggal_kadaluarsa) continue;
            try { $exp = \Carbon\Carbon::parse($it->tanggal_kadaluarsa)->startOfDay(); } catch (\Throwable $e) { continue; }
            $hari = $today->diffInDays($exp, false);
            if ($hari < 0) {
                $out[] = new Finding(
                    'EXP_PAST', 'Kedaluwarsa', 'kritis', 97, 'po', $po->id, $po->id,
                    "Barang kedaluwarsa: {$it->obat?->nama_obat}",
                    "Item {$it->obat?->nama_obat} kedaluwarsa " . abs($hari) . " hari lalu (exp {$exp->format('d M Y')}). Tidak layak diterima/dijual.",
                    ['Obat' => $it->obat?->nama_obat, 'Kedaluwarsa' => $exp->format('d M Y'), 'Status' => abs($hari) . ' hari lewat'],
                    "Retur/musnahkan sesuai SOP; jangan masukkan ke stok jual."
                );
            } elseif ($hari <= 90) {
                $out[] = new Finding(
                    'EXP_NEAR', 'Kedaluwarsa', 'sedang', 88, 'po', $po->id, $po->id,
                    "Mendekati kedaluwarsa: {$it->obat?->nama_obat}",
                    "Item {$it->obat?->nama_obat} exp {$exp->format('d M Y')} (~{$hari} hari lagi). Prioritaskan penjualan (FEFO) atau nego retur.",
                    ['Obat' => $it->obat?->nama_obat, 'Kedaluwarsa' => $exp->format('d M Y'), 'Sisa' => $hari . ' hari'],
                    "Terapkan FEFO; pertimbangkan retur bila risiko tidak terjual."
                );
            }
        }
    }

    /** Pembayaran melebihi nilai tagihan. */
    private function detectOverpayment(PurchaseOrder $po, array &$out): void
    {
        foreach ($po->tagihan as $t) {
            $lebih = (float) $t->jumlah_dibayar - (float) $t->total_tagihan;
            if ($lebih > 1) {
                $out[] = new Finding(
                    'OVERPAY', 'Pembayaran', 'tinggi', 95, 'tagihan', $t->id, $po->id,
                    "Pembayaran melebihi tagihan",
                    "Tagihan {$t->nomor_tagihan} sudah dibayar Rp " . $this->rp($t->jumlah_dibayar) . " melebihi nilai tagihan Rp " . $this->rp($t->total_tagihan) . " (lebih Rp " . $this->rp($lebih) . ").",
                    ['Tagihan' => $t->nomor_tagihan, 'Nilai tagihan' => 'Rp ' . $this->rp($t->total_tagihan), 'Dibayar' => 'Rp ' . $this->rp($t->jumlah_dibayar), 'Kelebihan' => 'Rp ' . $this->rp($lebih)],
                    "Cek pembayaran dobel; batalkan salah satu atau catat sebagai deposit/retur."
                );
            }
        }
    }

    /** Faktur kembar (entry dobel): invoice sama / tanggal+nominal sama. */
    private function detectDuplicateFaktur(array $invoiceMap, $pos, array &$out): void
    {
        // a) Invoice sama pada PBF sama.
        foreach ($invoiceMap as $inv => $list) {
            $byDist = [];
            foreach ($list as $row) $byDist[$row['dist']][] = $row;
            foreach ($byDist as $rows) {
                if (count($rows) < 2) continue;
                $ids = array_map(fn ($r) => $r['po'], $rows);
                foreach ($rows as $r) {
                    $lain = array_values(array_diff($ids, [$r['po']]));
                    $out[] = new Finding(
                        'DUP_FAKTUR', 'Duplikasi', 'tinggi', 95, 'po', $r['po'], $r['po'],
                        "Faktur dobel (invoice sama)",
                        "PBF {$r['name']} punya invoice #{$inv} pada beberapa PO: " . implode(', ', array_map(fn ($x) => "PO #$x", $ids)) . ". Kemungkinan entry dobel.",
                        ['No. Invoice' => $inv, 'PBF' => $r['name'], 'PO kembar' => implode(', ', array_map(fn ($x) => "#$x", $ids))],
                        "Bandingkan isinya; hapus faktur yang keliru (stok akan dikembalikan).",
                        $lain
                    );
                }
            }
        }
        // b) Presisi (bukan pairwise): PBF + tanggal SAMA + nominal SAMA (dibulatkan).
        //    Sangat kecil kemungkinan beli nominal identik di hari sama tanpa dobel.
        //    Lewati PO yang sudah kena DUP_FAKTUR (invoice sama) agar tak dobel-flag.
        $sudah = [];
        foreach ($invoiceMap as $list) {
            $byDist = [];
            foreach ($list as $r) $byDist[$r['dist']][] = $r['po'];
            foreach ($byDist as $poids) if (count($poids) > 1) foreach ($poids as $id) $sudah[$id] = true;
        }
        $groups = [];
        foreach ($pos as $po) {
            $t = (float) $po->total_nilai;
            if ($t <= 0 || ! $po->tanggal_po) continue;
            $key = $po->distributor_id . '|' . $po->tanggal_po->format('Y-m-d') . '|' . round($t);
            $groups[$key][] = $po;
        }
        foreach ($groups as $rows) {
            if (count($rows) < 2) continue;
            $ids = array_map(fn ($p) => $p->id, $rows);
            foreach ($rows as $p) {
                if (isset($sudah[$p->id])) continue;
                $lain = array_values(array_diff($ids, [$p->id]));
                $out[] = new Finding(
                    'DUP_SAMEDAY', 'Duplikasi', 'sedang', 74, 'po', $p->id, $p->id,
                    "Mungkin faktur dobel (hari & nominal sama)",
                    "PO #{$p->id} punya PBF, tanggal, dan nominal identik dengan " . implode(', ', array_map(fn ($x) => "PO #$x", $lain)) . " (Rp " . $this->rp((float) $p->total_nilai) . " · " . $p->tanggal_po->format('d M Y') . "). Kemungkinan entry dobel.",
                    ['PBF' => $p->distributor?->name, 'Tanggal' => $p->tanggal_po->format('d M Y'), 'Nominal' => 'Rp ' . $this->rp((float) $p->total_nilai), 'PO kembar' => implode(', ', array_map(fn ($x) => "#$x", $lain))],
                    "Bandingkan isinya; hapus salah satu bila memang pembelian dobel.",
                    $lain
                );
            }
        }
    }

    /** Invoice sama dipakai lintas PBF berbeda (tertukar). */
    private function detectInvoiceReuse(array $invoiceMap, array &$out): void
    {
        foreach ($invoiceMap as $inv => $list) {
            $dists = array_unique(array_map(fn ($r) => $r['dist'], $list));
            if (count($dists) < 2) continue;
            foreach ($list as $r) {
                $out[] = new Finding(
                    'INVOICE_REUSE', 'Invoice', 'tinggi', 84, 'po', $r['po'], $r['po'],
                    "No. invoice dipakai lintas PBF",
                    "Invoice #{$inv} muncul pada lebih dari satu distributor. Nomor faktur seharusnya unik per PBF — kemungkinan tertukar.",
                    ['No. Invoice' => $inv, 'Jumlah PBF' => (string) count($dists), 'PBF ini' => $r['name']],
                    "Cek nomor faktur asli; perbaiki invoice yang salah tempel."
                );
            }
        }
    }

    // ── Statistik historis (dari SELURUH data — query ringan) ─────────
    private function buildHistoryFromDb(): void
    {
        $prices = []; $qtys = [];
        \App\Models\PurchaseOrderItem::select('obat_id', 'jumlah_box', 'isi_per_box', 'harga_per_box')
            ->whereNotNull('obat_id')
            ->orderBy('id')
            ->chunk(2000, function ($rows) use (&$prices, &$qtys) {
                foreach ($rows as $it) {
                    if ((int) $it->isi_per_box > 0 && (float) $it->harga_per_box > 0) {
                        $prices[$it->obat_id][] = (float) $it->harga_per_box / (int) $it->isi_per_box;
                    }
                    $qtys[$it->obat_id][] = (int) $it->jumlah_box * max(1, (int) $it->isi_per_box);
                }
            });
        foreach ($prices as $oid => $arr) {
            $m = $this->median($arr);
            $this->priceStats[$oid] = ['median' => $m, 'mad' => $this->mad($arr, $m), 'n' => count($arr)];
        }
        foreach ($qtys as $oid => $arr) {
            $this->qtyStats[$oid] = ['median' => $this->median($arr), 'n' => count($arr)];
        }
    }

    private function median(array $a): float
    {
        if (! $a) return 0.0;
        sort($a); $n = count($a); $mid = intdiv($n, 2);
        return $n % 2 ? (float) $a[$mid] : ($a[$mid - 1] + $a[$mid]) / 2;
    }

    /** Median Absolute Deviation (robust terhadap outlier). */
    private function mad(array $a, float $median): float
    {
        if (! $a) return 0.0;
        $dev = array_map(fn ($x) => abs($x - $median), $a);
        return $this->median($dev);
    }

    private function norm(?string $s): string
    {
        return strtolower(trim((string) $s));
    }

    private function rp(float $n): string
    {
        return number_format($n, 0, ',', '.');
    }
}
