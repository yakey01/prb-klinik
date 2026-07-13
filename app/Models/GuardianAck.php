<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Konfirmasi manusia atas temuan Guardian AI (audit).
 * @see \App\Services\Guardian\GuardianEngine
 */
class GuardianAck extends Model
{
    protected $table = 'guardian_acknowledgements';

    protected $fillable = [
        'code', 'subject_type', 'subject_id', 'po_id',
        'fingerprint', 'status', 'catatan', 'oleh',
    ];

    /** Kunci unik temuan (kode + subjek). */
    public function key(): string
    {
        return $this->code . '|' . $this->subject_type . '|' . $this->subject_id;
    }
}
