<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HomeVisite extends Model
{
    use SoftDeletes;

    protected $table = 'home_visite';

    protected $fillable = [
        'pasien_id', 'assigned_to', 'assigned_by', 'pengambilan_obat_id',
        'tanggal_visite', 'alamat_tujuan', 'lat_tujuan', 'lng_tujuan',
        'status', 'catatan_admin', 'catatan_karyawan',
        'started_at', 'arrived_at', 'completed_at', 'foto_bukti',
    ];

    protected $casts = [
        'tanggal_visite' => 'date',
        'started_at'     => 'datetime',
        'arrived_at'     => 'datetime',
        'completed_at'   => 'datetime',
        'lat_tujuan'     => 'float',
        'lng_tujuan'     => 'float',
    ];

    // Relationships
    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class);
    }

    public function kurir(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function pengambilanObat(): BelongsTo
    {
        return $this->belongsTo(PengambilanObat::class);
    }

    public function tracks(): HasMany
    {
        return $this->hasMany(HomeVisiteTrack::class)->orderBy('recorded_at');
    }

    public function latestTrack(): HasMany
    {
        return $this->hasMany(HomeVisiteTrack::class)->latest('recorded_at')->limit(1);
    }

    // Scopes
    public function scopeAktif($query)
    {
        return $query->whereIn('status', ['ditugaskan', 'dalam_perjalanan', 'sampai']);
    }

    public function scopeHariIni($query)
    {
        return $query->whereDate('tanggal_visite', today());
    }

    public function scopeDalamPerjalanan($query)
    {
        return $query->where('status', 'dalam_perjalanan');
    }

    public function scopeByKurir($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    // Status helpers
    public function canStart(): bool  { return $this->status === 'ditugaskan'; }
    public function canArrive(): bool { return $this->status === 'dalam_perjalanan'; }
    public function canFinish(): bool { return $this->status === 'sampai'; }
    public function isOngoing(): bool { return in_array($this->status, ['dalam_perjalanan', 'sampai']); }
    public function isDone(): bool    { return in_array($this->status, ['selesai', 'dibatalkan']); }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'ditugaskan'      => 'Ditugaskan',
            'dalam_perjalanan'=> 'Dalam Perjalanan',
            'sampai'          => 'Sudah Sampai',
            'selesai'         => 'Selesai',
            'dibatalkan'      => 'Dibatalkan',
            default           => $this->status,
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'ditugaskan'      => 'var(--blue)',
            'dalam_perjalanan'=> 'var(--gold)',
            'sampai'          => 'var(--emer)',
            'selesai'         => 'var(--mut)',
            'dibatalkan'      => 'var(--red)',
            default           => 'var(--mut)',
        };
    }

    public function googleMapsUrl(): string
    {
        if ($this->lat_tujuan && $this->lng_tujuan) {
            return "https://maps.google.com/?q={$this->lat_tujuan},{$this->lng_tujuan}";
        }
        return 'https://maps.google.com/?q=' . urlencode($this->alamat_tujuan);
    }
}
