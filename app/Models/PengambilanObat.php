<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class PengambilanObat extends Model
{
    use SoftDeletes;

    protected $table = 'pengambilan_obat';

    protected $fillable = [
        'pasien_id', 'tanggal_pengambilan', 'jadwal_berikutnya',
        'status', 'total_item', 'dicatat_oleh', 'catatan',
        'checklist_json', 'persyaratan_ok',
        'ref_rme', 'sumber_resep',
    ];

    protected $casts = [
        'tanggal_pengambilan' => 'date',
        'jadwal_berikutnya' => 'date',
        'checklist_json' => 'array',
        'persyaratan_ok' => 'boolean',
    ];

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class, 'pasien_id');
    }

    /**
     * Pasien JATUH TEMPO ambil obat: pengambilan TERAKHIR tiap pasien yang
     * jadwal_berikutnya-nya <= hari ini (belum ambil sejak jadwal), pasien aktif.
     * Diurut jadwal terlama dulu (paling telat di atas). Sumber tunggal untuk
     * papan "Ambil Obat Hari Ini" (form) & kartu akuntabilitas (dashboard).
     *
     * @return Collection<int, self>
     */
    public static function jatuhTempo(): Collection
    {
        $latestIds = static::selectRaw('MAX(id) as id')
            ->whereNotNull('jadwal_berikutnya')
            ->groupBy('pasien_id')
            ->pluck('id');

        return static::with('pasien:id,nama,no_bpjs,kategori_diagnosis,is_aktif')
            ->whereIn('id', $latestIds)
            ->whereDate('jadwal_berikutnya', '<=', now()->toDateString())
            ->get()
            ->filter(fn ($p) => $p->pasien && $p->pasien->is_aktif)
            ->sortBy('jadwal_berikutnya')
            ->values();
    }

    public function items(): HasMany
    {
        return $this->hasMany(ItemPengambilan::class, 'pengambilan_obat_id');
    }

    public function pencatat(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }
}
