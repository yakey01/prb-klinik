<?php

namespace App\Services\Guardian;

/**
 * Satu temuan anomali dari Guardian AI — explainable & dapat dikonfirmasi.
 *
 * Setiap temuan menempel pada satu FAKTUR (PO) agar mudah dikelompokkan,
 * membawa bukti (evidence) yang bisa dibaca manusia, tingkat keparahan,
 * dan tingkat keyakinan (confidence) ala sistem deteksi kelas dunia.
 */
class Finding
{
    /** @var array<string,mixed> Ack aktif jika sudah dikonfirmasi manusia. */
    public ?array $ack = null;

    /** True bila kondisi berubah sejak dikonfirmasi (sidik jari tak cocok). */
    public bool $berubahSejakAck = false;

    public function __construct(
        public string $code,          // DUP_FAKTUR, TIPE_MISMATCH, ...
        public string $category,      // Duplikasi, Tipe, Harga, Integritas, ...
        public string $severity,      // kritis | tinggi | sedang | rendah
        public int    $confidence,    // 0-100
        public string $subjectType,   // po | tagihan
        public int    $subjectId,
        public ?int   $poId,
        public string $title,
        public string $detail,
        public array  $evidence = [], // label => nilai (string, human-readable)
        public string $recommendation = '',
        public array  $refs = [],     // id terkait (mis. PO kembar)
    ) {}

    /** Bobot risiko per keparahan (untuk skor agregat). */
    public function weight(): int
    {
        return match ($this->severity) {
            'kritis' => 100,
            'tinggi' => 60,
            'sedang' => 30,
            default  => 12,
        };
    }

    /** Kontribusi skor = bobot × keyakinan. */
    public function score(): float
    {
        return $this->weight() * ($this->confidence / 100);
    }

    /** Kunci stabil temuan (kode + subjek). */
    public function key(): string
    {
        return $this->code . '|' . $this->subjectType . '|' . $this->subjectId;
    }

    /**
     * Sidik jari kondisi — hash dari kode, subjek & nilai bukti material.
     * Bila salah satu berubah, temuan yang sudah dikonfirmasi akan muncul lagi.
     */
    public function fingerprint(): string
    {
        return substr(hash('sha256', $this->key() . '|' . json_encode($this->evidence)), 0, 64);
    }

    public function severityRank(): int
    {
        return match ($this->severity) {
            'kritis' => 4, 'tinggi' => 3, 'sedang' => 2, default => 1,
        };
    }
}
