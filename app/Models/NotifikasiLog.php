<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotifikasiLog extends Model
{
    protected $table = 'notifikasi_log';
    protected $fillable = [
        'pasien_id', 'pengambilan_id', 'channel',
        'nomor_tujuan', 'pesan', 'status', 'tipe',
        'sent_at', 'error_message',
    ];
    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class, 'pasien_id');
    }

    public function pengambilan(): BelongsTo
    {
        return $this->belongsTo(PengambilanObat::class, 'pengambilan_id');
    }

    public function scopeSent($q)    { return $q->where('status', 'sent'); }
    public function scopeFailed($q)  { return $q->where('status', 'failed'); }
    public function scopeToday($q)   { return $q->whereDate('created_at', today()); }
}
