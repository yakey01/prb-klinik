<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Runs BEFORE the NotifikasiSetting model's 'encrypted' cast is used.
    // Detects plaintext wa_api_key/telegram_bot_token and encrypts them.
    // Already-encrypted values (from prior runs) decode fine and are skipped.
    public function up(): void
    {
        foreach (DB::table('notifikasi_settings')->get() as $row) {
            $updates = [];
            foreach (['wa_api_key', 'telegram_bot_token'] as $col) {
                $val = $row->{$col};
                if ($val === null || $val === '') continue;
                try {
                    Crypt::decryptString($val); // already encrypted — skip
                } catch (\Illuminate\Contracts\Encryption\DecryptException) {
                    $updates[$col] = Crypt::encryptString($val);
                }
            }
            if ($updates) {
                DB::table('notifikasi_settings')->where('id', $row->id)->update($updates);
            }
        }
    }

    public function down(): void
    {
        // No-op — cannot safely reverse encryption
    }
};
