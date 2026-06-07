@php
    $s = $this->stats;
    $cfg = $s['cfg'];
    $waOk = $cfg->is_aktif_wa && ($cfg->wa_provider === 'local' || $cfg->wa_api_key);
    $tgOk = $cfg->is_aktif_telegram && $cfg->telegram_bot_token && $cfg->telegram_chat_id_staff;
@endphp

<div x-data="{}" @notif-toast.window="
    const el = document.createElement('div');
    el.className = 'toast toast-' + ($event.detail.type === 'error' ? 'error' : 'success');
    el.innerHTML = ($event.detail.type === 'error' ? '⚠️ ' : '✅ ') + $event.detail.message;
    document.getElementById('toast-container').appendChild(el);
    setTimeout(() => el.remove(), 4000);
">

    {{-- PAGE HEADER --}}
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.5rem;gap:1rem;flex-wrap:wrap;">
        <div>
            <div style="display:flex;align-items:center;gap:.7rem;margin-bottom:.25rem;">
                <h2 class="font-heading" style="font-size:1.5rem;color:var(--ink);margin:0;">Pusat Notifikasi Pasien</h2>
                @if($waOk || $tgOk)
                    <span class="badge" style="background:rgba(63,207,142,.12);color:var(--emer2);border:1px solid rgba(63,207,142,.25);gap:.35rem;">
                        <span style="width:6px;height:6px;border-radius:50%;background:var(--emer);animation:pulse 2s infinite;display:inline-block;"></span>
                        Aktif
                    </span>
                @else
                    <span class="badge badge-cek">Belum Dikonfigurasi</span>
                @endif
            </div>
            <p style="color:var(--mut);font-size:.82rem;margin:0;">Manajemen notifikasi WhatsApp & Telegram untuk jadwal ambil obat PRB pasien kronis</p>
        </div>
        <div style="display:flex;gap:.6rem;flex-wrap:wrap;align-items:center;">
            <button wire:click="kirimSemua" wire:loading.attr="disabled" class="btn-gold" style="font-size:.8rem;padding:.55rem 1.1rem;gap:.4rem;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                Kirim Semua (Hari Ini + H-1)
            </button>
            @if($tgOk)
            <button wire:click="kirimTelegramSummary" wire:loading.attr="disabled" class="btn-outline" style="font-size:.8rem;padding:.5rem 1rem;gap:.4rem;border-color:rgba(111,177,224,.3);color:var(--blue);">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                Summary ke Telegram
            </button>
            @endif
        </div>
    </div>

    {{-- GATEWAY STATUS BAR --}}
    <div style="display:flex;gap:.7rem;margin-bottom:1.35rem;flex-wrap:wrap;">
        <div style="display:flex;align-items:center;gap:.6rem;background:rgba({{ $waOk ? '63,207,142' : '232,100,90' }},.08);border:1px solid rgba({{ $waOk ? '63,207,142' : '232,100,90' }},.22);border-radius:.6rem;padding:.5rem 1rem;">
            <div style="width:8px;height:8px;border-radius:50%;background:{{ $waOk ? 'var(--emer2)' : 'var(--red2)' }};{{ $waOk ? 'animation:pulse 2.5s infinite;' : '' }}"></div>
            <span style="font-size:.75rem;font-weight:600;color:{{ $waOk ? 'var(--emer2)' : 'var(--red2)' }};">WhatsApp {{ $waOk ? '· Terhubung' : '· Belum Aktif' }}</span>
            @if($waOk)<span style="font-size:.68rem;color:var(--mut);">via {{ strtoupper($cfg->wa_provider ?? 'Fonnte') }}</span>@endif
        </div>
        <div style="display:flex;align-items:center;gap:.6rem;background:rgba({{ $tgOk ? '111,177,224' : '232,100,90' }},.08);border:1px solid rgba({{ $tgOk ? '111,177,224' : '232,100,90' }},.22);border-radius:.6rem;padding:.5rem 1rem;">
            <div style="width:8px;height:8px;border-radius:50%;background:{{ $tgOk ? 'var(--blue)' : 'var(--red2)' }};{{ $tgOk ? 'animation:pulse 3s infinite;' : '' }}"></div>
            <span style="font-size:.75rem;font-weight:600;color:{{ $tgOk ? 'var(--blue)' : 'var(--red2)' }};">Telegram {{ $tgOk ? '· Terhubung' : '· Belum Aktif' }}</span>
            @if($tgOk)<span style="font-size:.68rem;color:var(--mut);">Bot aktif · Chat {{ $cfg->telegram_chat_id_staff }}</span>@endif
        </div>
        <div style="margin-left:auto;display:flex;align-items:center;gap:.4rem;font-size:.72rem;color:var(--mut);">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            Jadwal kirim: {{ substr($cfg->jam_kirim ?? '08:00:00', 0, 5) }} WIB harian
        </div>
    </div>

    {{-- HERO STATS CARDS --}}
    <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:.85rem;margin-bottom:1.5rem;">

        <div style="background:rgba(217,164,65,.1);border:1px solid rgba(217,164,65,.28);border-radius:1rem;padding:1.1rem 1.2rem;">
            <div style="font-size:.57rem;text-transform:uppercase;letter-spacing:.1em;color:var(--mut);font-weight:700;margin-bottom:.5rem;">Hari Ini Jadwal</div>
            <div class="font-mono" style="font-size:1.8rem;font-weight:800;color:var(--gold2);line-height:1;">{{ $s['hariIniCount'] }}</div>
            <div style="font-size:.67rem;color:var(--mut);margin-top:.3rem;">Pasien wajib ambil</div>
        </div>

        <div style="background:rgba(111,177,224,.08);border:1px solid rgba(111,177,224,.22);border-radius:1rem;padding:1.1rem 1.2rem;">
            <div style="font-size:.57rem;text-transform:uppercase;letter-spacing:.1em;color:var(--mut);font-weight:700;margin-bottom:.5rem;">H-1 Besok</div>
            <div class="font-mono" style="font-size:1.8rem;font-weight:800;color:var(--blue);line-height:1;">{{ $s['besokCount'] }}</div>
            <div style="font-size:.67rem;color:var(--mut);margin-top:.3rem;">Jadwal besok</div>
        </div>

        <div style="background:rgba({{ $s['overdueCount'] > 0 ? '232,100,90' : '31,61,48' }},.1);border:1px solid rgba({{ $s['overdueCount'] > 0 ? '232,100,90' : '31,61,48' }},.28);border-radius:1rem;padding:1.1rem 1.2rem;">
            <div style="font-size:.57rem;text-transform:uppercase;letter-spacing:.1em;color:var(--mut);font-weight:700;margin-bottom:.5rem;">Overdue</div>
            <div class="font-mono" style="font-size:1.8rem;font-weight:800;color:{{ $s['overdueCount'] > 0 ? 'var(--red2)' : 'var(--mut2)' }};line-height:1;">{{ $s['overdueCount'] }}</div>
            <div style="font-size:.67rem;color:var(--mut);margin-top:.3rem;">Lewat jadwal</div>
        </div>

        <div style="background:rgba(63,207,142,.08);border:1px solid rgba(63,207,142,.22);border-radius:1rem;padding:1.1rem 1.2rem;">
            <div style="font-size:.57rem;text-transform:uppercase;letter-spacing:.1em;color:var(--mut);font-weight:700;margin-bottom:.5rem;">Terkirim Hari Ini</div>
            <div class="font-mono" style="font-size:1.8rem;font-weight:800;color:var(--emer2);line-height:1;">{{ $s['terkirimHariIni'] }}</div>
            <div style="font-size:.67rem;color:var(--mut);margin-top:.3rem;">WA + Telegram</div>
        </div>

        <div style="background:rgba({{ $s['gagalHariIni'] > 0 ? '232,100,90' : '17,36,28' }},.1);border:1px solid rgba({{ $s['gagalHariIni'] > 0 ? '232,100,90' : '31,61,48' }},.25);border-radius:1rem;padding:1.1rem 1.2rem;">
            <div style="font-size:.57rem;text-transform:uppercase;letter-spacing:.1em;color:var(--mut);font-weight:700;margin-bottom:.5rem;">Gagal Hari Ini</div>
            <div class="font-mono" style="font-size:1.8rem;font-weight:800;color:{{ $s['gagalHariIni'] > 0 ? 'var(--red2)' : 'var(--mut2)' }};line-height:1;">{{ $s['gagalHariIni'] }}</div>
            <div style="font-size:.67rem;color:var(--mut);margin-top:.3rem;">Perlu perhatian</div>
        </div>
    </div>

    {{-- TABS --}}
    <div style="display:flex;gap:.2rem;border-bottom:1px solid var(--line);margin-bottom:1.25rem;">
        @foreach([
            ['overview',   'Overview',     'M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z'],
            ['jadwal',     'Jadwal Ambil', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
            ['log',        'Log Notifikasi','M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
            ['pengaturan', 'Pengaturan',   'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0'],
        ] as [$key, $label, $path])
        <button wire:click="$set('tab','{{ $key }}')" style="display:flex;align-items:center;gap:.4rem;padding:.6rem 1rem;font-size:.8rem;font-weight:{{ $tab === $key ? '700' : '500' }};color:{{ $tab === $key ? 'var(--gold2)' : 'var(--mut)' }};border:none;background:none;cursor:pointer;border-bottom:2px solid {{ $tab === $key ? 'var(--gold)' : 'transparent' }};margin-bottom:-1px;transition:color .15s;">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="{{ $path }}"/></svg>
            {{ $label }}
            @if($key === 'jadwal' && ($s['hariIniCount'] + $s['overdueCount']) > 0)
                <span style="background:var(--red);color:#fff;border-radius:999px;font-size:.6rem;font-weight:700;padding:.1rem .45rem;min-width:18px;text-align:center;">{{ $s['hariIniCount'] + $s['overdueCount'] }}</span>
            @endif
        </button>
        @endforeach
    </div>

    {{-- TAB: OVERVIEW --}}
    @if($tab === 'overview')
    <div style="display:grid;grid-template-columns:1.5fr 1fr;gap:1rem;align-items:start;">

        {{-- Recent Activity --}}
        <div class="glass-card" style="overflow:hidden;">
            <div style="padding:.75rem 1.2rem;border-bottom:1px solid var(--line);background:rgba(0,0,0,.2);">
                <div style="font-size:.73rem;font-weight:700;color:var(--ink);">Aktivitas Notifikasi Terbaru</div>
                <div style="font-size:.61rem;color:var(--mut);">10 log terakhir</div>
            </div>
            @php
                $recent = \App\Models\NotifikasiLog::with('pasien')->orderByDesc('created_at')->limit(10)->get();
            @endphp
            @forelse($recent as $log)
            <div style="display:flex;align-items:center;gap:.85rem;padding:.7rem 1.2rem;border-bottom:1px solid rgba(31,61,48,.4);">
                <div style="width:28px;height:28px;border-radius:50%;background:rgba({{ $log->channel === 'wa' ? '63,207,142' : '111,177,224' }},.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    @if($log->channel === 'wa')
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="{{ $log->status === 'sent' ? '#3fcf8e' : ($log->status === 'failed' ? '#e8645a' : '#5f8071') }}"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    @else
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="{{ $log->status === 'sent' ? '#6fb1e0' : ($log->status === 'failed' ? '#e8645a' : '#5f8071') }}"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                    @endif
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:.79rem;font-weight:600;color:var(--ink);">{{ $log->pasien?->nama ?? '—' }}</div>
                    <div style="font-size:.68rem;color:var(--mut);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ Str::limit($log->pesan, 50) }}</div>
                </div>
                <div style="text-align:right;flex-shrink:0;">
                    <div>
                        @if($log->status === 'sent') <span style="font-size:.65rem;color:var(--emer2);font-weight:600;">Terkirim</span>
                        @elseif($log->status === 'failed') <span style="font-size:.65rem;color:var(--red2);font-weight:600;">Gagal</span>
                        @elseif($log->status === 'skipped') <span style="font-size:.65rem;color:var(--mut);font-weight:600;">Skip</span>
                        @else <span style="font-size:.65rem;color:var(--gold2);font-weight:600;">Pending</span>
                        @endif
                    </div>
                    <div style="font-size:.63rem;color:var(--mut2);">{{ $log->created_at->diffForHumans() }}</div>
                </div>
            </div>
            @empty
            <div style="padding:2.5rem;text-align:center;color:var(--mut);font-size:.82rem;">Belum ada log notifikasi</div>
            @endforelse
        </div>

        {{-- Quick Info --}}
        <div style="display:flex;flex-direction:column;gap:1rem;">
            {{-- Cara pakai --}}
            <div class="glass-card" style="padding:1.1rem 1.2rem;">
                <div style="font-size:.7rem;font-weight:700;color:var(--gold2);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.8rem;">⚡ Cara Kerja Sistem</div>
                <div style="font-size:.78rem;color:var(--mut2);line-height:1.7;">
                    <div style="display:flex;gap:.6rem;margin-bottom:.45rem;">
                        <span style="color:var(--gold2);font-weight:700;flex-shrink:0;">H-1</span>
                        <span>Notif WA dikirim 24 jam sebelum jadwal ambil obat</span>
                    </div>
                    <div style="display:flex;gap:.6rem;margin-bottom:.45rem;">
                        <span style="color:var(--emer2);font-weight:700;flex-shrink:0;">H+0</span>
                        <span>Pengingat pagi hari pada hari H jika belum ambil</span>
                    </div>
                    <div style="display:flex;gap:.6rem;margin-bottom:.45rem;">
                        <span style="color:var(--red2);font-weight:700;flex-shrink:0;">Overdue</span>
                        <span>Notif harian jika pasien belum konfirmasi ambil</span>
                    </div>
                    <div style="display:flex;gap:.6rem;">
                        <span style="color:var(--blue);font-weight:700;flex-shrink:0;">TG</span>
                        <span>Telegram summary harian ke apoteker/staff</span>
                    </div>
                </div>
            </div>

            {{-- Cron info --}}
            <div class="glass-card" style="padding:1.1rem 1.2rem;">
                <div style="font-size:.7rem;font-weight:700;color:var(--mut);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.75rem;">Otomatis via Cron</div>
                <div style="background:rgba(0,0,0,.3);border:1px solid var(--line);border-radius:.5rem;padding:.6rem .9rem;font-family:'JetBrains Mono',monospace;font-size:.7rem;color:var(--emer2);line-height:1.8;">
                    <div style="color:var(--mut);"># Tambahkan ke crontab:</div>
                    <div>* * * * * php /path/to/artisan schedule:run</div>
                </div>
                <div style="font-size:.68rem;color:var(--mut);margin-top:.6rem;">Atau jalankan manual:</div>
                <div style="background:rgba(0,0,0,.3);border:1px solid var(--line);border-radius:.5rem;padding:.45rem .9rem;font-family:'JetBrains Mono',monospace;font-size:.7rem;color:var(--gold2);margin-top:.3rem;">
                    php artisan notifikasi:kirim
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- TAB: JADWAL --}}
    @if($tab === 'jadwal')
    <div class="glass-card" style="overflow:hidden;">
        <div style="padding:.8rem 1.2rem;border-bottom:1px solid var(--line);display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
            <div style="display:flex;gap:.4rem;flex-wrap:wrap;">
                @foreach([
                    ['hari_ini','Hari Ini'],['besok','Besok'],
                    ['minggu','7 Hari'],['overdue','Overdue'],['semua','Semua'],
                ] as [$val,$lbl])
                <button wire:click="$set('filterJadwal','{{ $val }}')" style="font-size:.73rem;padding:.3rem .75rem;border-radius:.4rem;border:1px solid {{ $filterJadwal === $val ? 'var(--gold)' : 'var(--line2)' }};background:{{ $filterJadwal === $val ? 'rgba(217,164,65,.12)' : 'transparent' }};color:{{ $filterJadwal === $val ? 'var(--gold2)' : 'var(--mut)' }};cursor:pointer;transition:all .15s;">{{ $lbl }}</button>
                @endforeach
            </div>
            <div style="margin-left:auto;position:relative;">
                <input wire:model.live.debounce.300ms="searchJadwal" type="text" placeholder="Cari nama pasien..." class="form-input" style="padding:.4rem .8rem .4rem 2rem;font-size:.78rem;width:200px;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="position:absolute;left:.6rem;top:50%;transform:translateY(-50%);color:var(--mut);pointer-events:none;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            </div>
        </div>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:32%;">Pasien</th>
                        <th>Diagnosa</th>
                        <th>No. BPJS</th>
                        <th>Jadwal Ambil</th>
                        <th>Status</th>
                        <th>WA</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($this->jadwalList as $row)
                <tr>
                    <td>
                        <div style="font-weight:600;color:var(--ink);font-size:.83rem;">{{ $row['pasien']?->nama ?? '—' }}</div>
                        <div style="font-size:.68rem;color:var(--mut);">{{ $row['pasien']?->telepon ? '📱 '.$row['pasien']->telepon : '⚠️ Tidak ada nomor' }}</div>
                    </td>
                    <td><span style="font-size:.77rem;color:var(--mut2);">{{ $row['pasien']?->kategori_diagnosis ?? '—' }}</span></td>
                    <td><span class="font-mono" style="font-size:.73rem;color:var(--mut);">{{ $row['pasien']?->no_bpjs ?? '—' }}</span></td>
                    <td><span class="font-mono" style="font-size:.78rem;font-weight:600;color:var(--ink);">{{ $row['jadwal'] }}</span></td>
                    <td>
                        <span class="badge" style="font-size:.67rem;background:rgba({{ match($row['statusColor']) {
                            'emer2' => '63,207,142',
                            'red2'  => '232,100,90',
                            'gold2' => '217,164,65',
                            'blue'  => '111,177,224',
                            default => '95,128,113',
                        } }},.14);color:var(--{{ $row['statusColor'] }});border:1px solid rgba({{ match($row['statusColor']) {
                            'emer2' => '63,207,142',
                            'red2'  => '232,100,90',
                            'gold2' => '217,164,65',
                            'blue'  => '111,177,224',
                            default => '95,128,113',
                        } }},.3);">{{ $row['statusLabel'] }}</span>
                    </td>
                    <td>
                        @if($row['sudahNotif'])
                            <span style="font-size:.67rem;color:var(--emer2);">✅ Terkirim hari ini</span>
                        @else
                            <span style="font-size:.67rem;color:var(--mut);">—</span>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex;gap:.4rem;flex-wrap:nowrap;">
                            @if($row['status'] !== 'selesai' && $row['status'] !== 'batal')
                            <button wire:click="kirimNotifikasi({{ $row['id'] }})" wire:loading.attr="disabled"
                                style="font-size:.68rem;padding:.28rem .65rem;border-radius:.35rem;border:1px solid rgba(63,207,142,.25);background:rgba(63,207,142,.08);color:var(--emer2);cursor:pointer;white-space:nowrap;transition:background .15s;"
                                title="Kirim WA reminder">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor" style="vertical-align:middle;"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                WA
                            </button>
                            <button wire:click="konfirmasiSelesai({{ $row['id'] }})"
                                style="font-size:.68rem;padding:.28rem .65rem;border-radius:.35rem;border:1px solid rgba(217,164,65,.25);background:rgba(217,164,65,.08);color:var(--gold2);cursor:pointer;white-space:nowrap;">
                                ✓ Selesai
                            </button>
                            @else
                            <span style="font-size:.7rem;color:var(--mut);">{{ ucfirst($row['status']) }}</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;padding:3rem;color:var(--mut);">Tidak ada jadwal dalam rentang ini</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- TAB: LOG --}}
    @if($tab === 'log')
    <div class="glass-card" style="overflow:hidden;">
        <div style="padding:.8rem 1.2rem;border-bottom:1px solid var(--line);display:flex;align-items:center;gap:.7rem;flex-wrap:wrap;">
            <select wire:model.live="filterLogChannel" class="form-input" style="width:130px;padding:.35rem .7rem;font-size:.75rem;">
                <option value="">Semua Channel</option>
                <option value="wa">WhatsApp</option>
                <option value="telegram">Telegram</option>
            </select>
            <select wire:model.live="filterLogStatus" class="form-input" style="width:130px;padding:.35rem .7rem;font-size:.75rem;">
                <option value="">Semua Status</option>
                <option value="sent">Terkirim</option>
                <option value="failed">Gagal</option>
                <option value="pending">Pending</option>
                <option value="skipped">Skip</option>
            </select>
            <select wire:model.live="filterLogTipe" class="form-input" style="width:140px;padding:.35rem .7rem;font-size:.75rem;">
                <option value="">Semua Tipe</option>
                <option value="H1">H-1 Reminder</option>
                <option value="HARIAN">Harian</option>
                <option value="KONFIRMASI">Konfirmasi</option>
                <option value="TEST">Test</option>
                <option value="BROADCAST">Broadcast</option>
            </select>
            <select wire:model.live="filterLogHari" class="form-input" style="width:130px;padding:.35rem .7rem;font-size:.75rem;">
                <option value="1">Hari Ini</option>
                <option value="7" selected>7 Hari</option>
                <option value="30">30 Hari</option>
                <option value="90">90 Hari</option>
            </select>
            <span style="font-size:.72rem;color:var(--mut);margin-left:auto;">{{ $this->logList->count() }} log</span>
        </div>
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Pasien</th>
                        <th>Channel</th>
                        <th>Tipe</th>
                        <th>Nomor Tujuan</th>
                        <th>Status</th>
                        <th style="width:35%;">Pesan</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($this->logList as $log)
                <tr>
                    <td><span class="font-mono" style="font-size:.72rem;color:var(--mut);">{{ $log->created_at->format('d/m H:i') }}</span></td>
                    <td style="font-size:.8rem;font-weight:600;color:var(--ink);">{{ $log->pasien?->nama ?? '—' }}</td>
                    <td>
                        @if($log->channel === 'wa')
                        <span class="badge badge-real" style="font-size:.65rem;gap:.3rem;">
                            <svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            WA
                        </span>
                        @else
                        <span class="badge badge-po" style="font-size:.65rem;">TG</span>
                        @endif
                    </td>
                    <td>
                        <span style="font-size:.7rem;padding:.12rem .5rem;border-radius:.3rem;background:rgba(95,128,113,.12);color:var(--mut2);">{{ $log->tipe }}</span>
                    </td>
                    <td><span class="font-mono" style="font-size:.72rem;color:var(--mut);">{{ $log->nomor_tujuan }}</span></td>
                    <td>
                        @if($log->status === 'sent')    <span style="font-size:.72rem;font-weight:600;color:var(--emer2);">✅ Terkirim</span>
                        @elseif($log->status === 'failed')  <span style="font-size:.72rem;font-weight:600;color:var(--red2);" title="{{ $log->error_message }}">❌ Gagal</span>
                        @elseif($log->status === 'skipped') <span style="font-size:.72rem;color:var(--mut);">Skip</span>
                        @else <span style="font-size:.72rem;color:var(--gold2);">Pending</span>
                        @endif
                    </td>
                    <td style="font-size:.72rem;color:var(--mut2);max-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ Str::limit($log->pesan, 60) }}</td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;padding:2.5rem;color:var(--mut);">Tidak ada log dalam periode ini</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- TAB: PENGATURAN --}}
    @if($tab === 'pengaturan')
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.1rem;align-items:start;">

        {{-- WA Gateway --}}
        <div class="glass-card" style="overflow:hidden;">
            <div style="padding:.8rem 1.2rem;border-bottom:1px solid var(--line);background:rgba(63,207,142,.04);display:flex;align-items:center;gap:.7rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="#3fcf8e"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                <div>
                    <div style="font-size:.78rem;font-weight:700;color:var(--emer2);">WhatsApp Gateway</div>
                    <div style="font-size:.63rem;color:var(--mut);">Pilih provider WA untuk notifikasi pasien</div>
                </div>
                <label style="margin-left:auto;display:flex;align-items:center;gap:.5rem;cursor:pointer;">
                    <input type="checkbox" wire:model.live="setAktifWa" style="display:none;">
                    <div onclick="this.previousElementSibling.click()" style="width:38px;height:20px;border-radius:10px;background:{{ $setAktifWa ? 'var(--emer)' : 'var(--line2)' }};position:relative;cursor:pointer;transition:background .2s;">
                        <div style="position:absolute;top:3px;left:{{ $setAktifWa ? '21px' : '3px' }};width:14px;height:14px;border-radius:50%;background:#fff;transition:left .2s;box-shadow:0 1px 3px rgba(0,0,0,.3);"></div>
                    </div>
                    <span style="font-size:.72rem;color:var(--mut);">{{ $setAktifWa ? 'Aktif' : 'Nonaktif' }}</span>
                </label>
            </div>
            <div style="padding:1.1rem 1.2rem;display:flex;flex-direction:column;gap:.85rem;">

                {{-- Provider selector cards --}}
                <div>
                    <label class="form-label">Provider WA</label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.6rem;">
                        <label wire:click="$set('setWaProvider','local')" style="cursor:pointer;border:2px solid {{ $setWaProvider === 'local' ? 'var(--emer)' : 'var(--line)' }};border-radius:.6rem;padding:.75rem;background:{{ $setWaProvider === 'local' ? 'rgba(63,207,142,.08)' : 'rgba(255,255,255,.02)' }};transition:.2s;">
                            <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.3rem;">
                                <span style="font-size:1rem;">🆓</span>
                                <span style="font-size:.75rem;font-weight:700;color:{{ $setWaProvider === 'local' ? 'var(--emer2)' : 'var(--ink)' }};">WA Lokal (Gratis)</span>
                                @if($setWaProvider === 'local')
                                <span style="margin-left:auto;font-size:.6rem;background:rgba(63,207,142,.15);color:var(--emer2);padding:.1rem .4rem;border-radius:.3rem;font-weight:600;">AKTIF</span>
                                @endif
                            </div>
                            <p style="font-size:.65rem;color:var(--mut);margin:0;">whatsapp-web.js · Self-hosted · Gratis selamanya · Butuh Node.js</p>
                        </label>
                        <label wire:click="$set('setWaProvider','fonnte')" style="cursor:pointer;border:2px solid {{ $setWaProvider === 'fonnte' ? 'var(--gold)' : 'var(--line)' }};border-radius:.6rem;padding:.75rem;background:{{ $setWaProvider === 'fonnte' ? 'rgba(217,164,65,.06)' : 'rgba(255,255,255,.02)' }};transition:.2s;">
                            <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.3rem;">
                                <span style="font-size:1rem;">☁️</span>
                                <span style="font-size:.75rem;font-weight:700;color:{{ $setWaProvider === 'fonnte' ? 'var(--gold2)' : 'var(--ink)' }};">Fonnte</span>
                                @if($setWaProvider === 'fonnte')
                                <span style="margin-left:auto;font-size:.6rem;background:rgba(217,164,65,.15);color:var(--gold2);padding:.1rem .4rem;border-radius:.3rem;font-weight:600;">AKTIF</span>
                                @endif
                            </div>
                            <p style="font-size:.65rem;color:var(--mut);margin:0;">Cloud API · 1.000 pesan gratis/bulan · Perlu API key</p>
                        </label>
                    </div>
                </div>

                {{-- LOCAL: endpoint URL + instruksi --}}
                @if($setWaProvider === 'local')
                <div>
                    <label class="form-label">
                        URL Endpoint WA Service
                        @if(!$setWaEndpointUrl)
                        <span style="font-size:.63rem;color:var(--red);font-weight:400;margin-left:.4rem;">⚠ Wajib diisi untuk server live</span>
                        @endif
                    </label>
                    <input type="text" wire:model="setWaEndpointUrl" class="form-input" style="font-size:.82rem;"
                        placeholder="https://xxxx.ngrok-free.app  (atau http://localhost:3001 untuk lokal)">
                    <div style="font-size:.67rem;color:var(--mut);margin-top:.3rem;">
                        Untuk server live: expose WA service via <strong style="color:var(--emer2);">ngrok</strong> →
                        <span style="font-family:monospace;color:var(--emer2);">ngrok http 3001</span>
                        → salin HTTPS URL ke sini
                    </div>
                </div>

                <div style="border:1px solid rgba(63,207,142,.2);border-radius:.6rem;padding:.9rem;background:rgba(63,207,142,.04);">
                    <div style="font-size:.75rem;font-weight:600;color:var(--emer2);margin-bottom:.6rem;display:flex;align-items:center;gap:.4rem;">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        Cara Setup — Lokal + Server Live
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:.8rem;">
                        <div>
                            <div style="font-size:.68rem;color:var(--emer2);font-weight:600;margin-bottom:.4rem;">🖥 Di komputer lokal Anda:</div>
                            <ol style="font-size:.68rem;color:var(--mut2);margin:0;padding-left:1.1rem;line-height:2;">
                                <li>Jalankan WA service: <span style="font-family:monospace;color:var(--emer2);">node wa-service/server.js</span></li>
                                <li>Scan QR di: <span style="font-family:monospace;color:var(--emer2);">localhost:3001/qr</span></li>
                                <li>Install ngrok: <span style="font-family:monospace;color:var(--ink);">brew install ngrok</span></li>
                                <li>Expose: <span style="font-family:monospace;color:var(--gold2);">ngrok http 3001</span></li>
                                <li>Salin URL HTTPS dari ngrok (mis. <span style="font-family:monospace;color:var(--gold2);">https://abc.ngrok-free.app</span>)</li>
                                <li>Masukkan URL tersebut ke field di atas ↑</li>
                            </ol>
                        </div>
                        <div>
                            <div style="font-size:.68rem;color:var(--gold2);font-weight:600;margin-bottom:.4rem;">☁️ Alternatif: Simpan koneksi permanen:</div>
                            <ol style="font-size:.68rem;color:var(--mut2);margin:0;padding-left:1.1rem;line-height:2;">
                                <li>Daftar ngrok gratis: <span style="font-family:monospace;color:var(--gold2);">ngrok.com</span></li>
                                <li>Login: <span style="font-family:monospace;color:var(--ink);">ngrok config add-authtoken TOKEN</span></li>
                                <li>Static domain: gunakan ngrok free static domain</li>
                                <li>URL tidak berubah setiap restart</li>
                            </ol>
                            <div style="margin-top:.5rem;font-size:.66rem;color:var(--mut);padding:.4rem .6rem;background:rgba(0,0,0,.15);border-radius:.35rem;">
                                💡 Komputer harus <strong>menyala & terhubung internet</strong> agar notifikasi terkirim dari server live.
                            </div>
                        </div>
                    </div>
                    <div style="margin-top:.85rem;display:flex;gap:.6rem;align-items:center;flex-wrap:wrap;">
                        <button wire:click="cekStatusWaLokal" style="padding:.45rem .9rem;border-radius:.45rem;border:1px solid rgba(63,207,142,.35);background:rgba(63,207,142,.1);color:var(--emer2);cursor:pointer;font-size:.73rem;font-weight:600;">
                            ⚡ Cek Status Koneksi
                        </button>
                        @if($setWaEndpointUrl)
                        <a href="{{ $setWaEndpointUrl }}/qr" target="_blank" style="padding:.45rem .9rem;border-radius:.45rem;border:1px solid var(--line);background:rgba(255,255,255,.03);color:var(--mut2);text-decoration:none;font-size:.72rem;">
                            📱 Buka Halaman QR
                        </a>
                        @else
                        <a href="http://localhost:3001/qr" target="_blank" style="padding:.45rem .9rem;border-radius:.45rem;border:1px solid var(--line);background:rgba(255,255,255,.03);color:var(--mut2);text-decoration:none;font-size:.72rem;">
                            📱 Buka QR (Lokal)
                        </a>
                        @endif
                    </div>
                </div>
                <div>
                    <label class="form-label">Nomor WA Klinik (yang di-scan)</label>
                    <input type="text" wire:model="setWaSender" class="form-input" style="font-size:.82rem;" placeholder="08xxxxxxxxxx — nomor yang login di WA lokal">
                </div>
                @endif

                {{-- FONNTE: API key --}}
                @if($setWaProvider === 'fonnte')
                <div>
                    <label class="form-label">API Key / Token Fonnte</label>
                    <input type="password" wire:model="setWaApiKey" class="form-input" style="font-size:.82rem;" placeholder="Masukkan API key dari dashboard Fonnte">
                    <div style="font-size:.67rem;color:var(--mut);margin-top:.3rem;">Dapatkan di <span style="color:var(--gold2);">md.fonnte.com</span> → Perangkat → klik <strong>Token</strong></div>
                </div>
                <div>
                    <label class="form-label">Nomor WA Pengirim</label>
                    <input type="text" wire:model="setWaSender" class="form-input" style="font-size:.82rem;" placeholder="08155107450">
                </div>
                @endif

                <div>
                    <label class="form-label">Test Kirim WA</label>
                    <div style="display:flex;gap:.5rem;">
                        <input type="text" wire:model="testNomor" class="form-input" style="font-size:.8rem;" placeholder="08xxx nomor tujuan test">
                        <button wire:click="testKirimWa" wire:loading.attr="disabled" style="flex-shrink:0;padding:.55rem 1rem;border-radius:.5rem;border:1px solid rgba(63,207,142,.3);background:rgba(63,207,142,.1);color:var(--emer2);cursor:pointer;font-size:.78rem;white-space:nowrap;font-weight:600;">
                            <span wire:loading.remove wire:target="testKirimWa">Test WA</span>
                            <span wire:loading wire:target="testKirimWa">Mengirim...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Telegram Bot --}}
        <div class="glass-card" style="overflow:hidden;">
            <div style="padding:.8rem 1.2rem;border-bottom:1px solid var(--line);background:rgba(111,177,224,.04);display:flex;align-items:center;gap:.7rem;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="#6fb1e0"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                <div>
                    <div style="font-size:.78rem;font-weight:700;color:var(--blue);">Telegram Bot</div>
                    <div style="font-size:.63rem;color:var(--mut);">Notifikasi summary ke staff / apoteker</div>
                </div>
                <label style="margin-left:auto;display:flex;align-items:center;gap:.5rem;cursor:pointer;">
                    <input type="checkbox" wire:model.live="setAktifTg" style="display:none;">
                    <div onclick="this.previousElementSibling.click()" style="width:38px;height:20px;border-radius:10px;background:{{ $setAktifTg ? 'var(--blue)' : 'var(--line2)' }};position:relative;cursor:pointer;transition:background .2s;">
                        <div style="position:absolute;top:3px;left:{{ $setAktifTg ? '21px' : '3px' }};width:14px;height:14px;border-radius:50%;background:#fff;transition:left .2s;box-shadow:0 1px 3px rgba(0,0,0,.3);"></div>
                    </div>
                    <span style="font-size:.72rem;color:var(--mut);">{{ $setAktifTg ? 'Aktif' : 'Nonaktif' }}</span>
                </label>
            </div>
            <div style="padding:1.1rem 1.2rem;display:flex;flex-direction:column;gap:.85rem;">
                <div>
                    <label class="form-label">Bot Token</label>
                    <input type="password" wire:model="setTgToken" class="form-input" style="font-size:.82rem;" placeholder="1234567890:ABCDEFGhijklmnop...">
                    <div style="font-size:.67rem;color:var(--mut);margin-top:.3rem;">Buat bot di <span style="color:var(--blue);">@BotFather</span> → /newbot → salin token</div>
                </div>
                <div>
                    <label class="form-label">Chat ID Staff / Group</label>
                    <input type="text" wire:model="setTgChatId" class="form-input" style="font-size:.82rem;" placeholder="-100xxxxxxxxxx atau @channelname">
                    <div style="font-size:.67rem;color:var(--mut);margin-top:.3rem;">Gunakan <span style="color:var(--blue);">@userinfobot</span> untuk cek Chat ID Anda</div>
                </div>
                <div>
                    <label class="form-label">Test Kirim Telegram</label>
                    <button wire:click="testKirimTelegram" wire:loading.attr="disabled" style="width:100%;padding:.55rem;border-radius:.5rem;border:1px solid rgba(111,177,224,.3);background:rgba(111,177,224,.1);color:var(--blue);cursor:pointer;font-size:.78rem;font-weight:600;">
                        <span wire:loading.remove wire:target="testKirimTelegram">Kirim Test ke Telegram</span>
                        <span wire:loading wire:target="testKirimTelegram">Mengirim...</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Jadwal & Template --}}
        <div class="glass-card" style="overflow:hidden;grid-column:span 2;">
            <div style="padding:.8rem 1.2rem;border-bottom:1px solid var(--line);background:rgba(0,0,0,.2);">
                <div style="font-size:.78rem;font-weight:700;color:var(--ink);">Jadwal & Template Pesan</div>
                <div style="font-size:.63rem;color:var(--mut);">Variabel yang tersedia: {nama}, {tanggal}, {diagnosa}</div>
            </div>
            <div style="padding:1.1rem 1.2rem;display:grid;grid-template-columns:auto 1fr 1fr 1fr;gap:1rem;align-items:start;">
                <div style="padding-top:.1rem;">
                    <label class="form-label">Jam Kirim</label>
                    <input type="time" wire:model="setJamKirim" class="form-input" style="width:110px;font-size:.82rem;">
                </div>
                <div>
                    <label class="form-label">Template H-1 (Besok jadwal)</label>
                    <textarea wire:model="setTplH1" class="form-input" style="font-size:.75rem;resize:vertical;min-height:100px;line-height:1.5;" placeholder="Template notifikasi H-1..."></textarea>
                </div>
                <div>
                    <label class="form-label">Template Hari H (Hari ini jadwal)</label>
                    <textarea wire:model="setTplHarian" class="form-input" style="font-size:.75rem;resize:vertical;min-height:100px;line-height:1.5;" placeholder="Template hari jadwal..."></textarea>
                </div>
                <div>
                    <label class="form-label">Template Overdue</label>
                    <textarea wire:model="setTplOverdue" class="form-input" style="font-size:.75rem;resize:vertical;min-height:100px;line-height:1.5;" placeholder="Template overdue..."></textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- Save Button --}}
    <div style="margin-top:1.2rem;display:flex;justify-content:flex-end;gap:.75rem;">
        <button wire:click="simpanSetting" wire:loading.attr="disabled" class="btn-gold">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            Simpan Pengaturan
        </button>
    </div>
    @endif

    {{-- KONFIRMASI MODAL --}}
    @if($konfirmasiModal)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,.65);z-index:500;display:flex;align-items:center;justify-content:center;" wire:click.self="$set('konfirmasiModal',false)">
        <div class="glass-card" style="padding:1.75rem 2rem;max-width:380px;width:100%;margin:1rem;text-align:center;">
            <div style="font-size:2.5rem;margin-bottom:.75rem;">✅</div>
            <div style="font-size:1.05rem;font-weight:700;color:var(--ink);margin-bottom:.5rem;">Tandai Selesai?</div>
            <div style="font-size:.82rem;color:var(--mut);margin-bottom:1.5rem;">Konfirmasi bahwa pasien sudah mengambil obat. Status jadwal akan berubah menjadi <strong style="color:var(--emer2);">Selesai</strong>.</div>
            <div style="display:flex;gap:.75rem;justify-content:center;">
                <button wire:click="tandaiSelesai" class="btn-gold" style="padding:.65rem 1.5rem;">Konfirmasi Selesai</button>
                <button wire:click="$set('konfirmasiModal',false)" class="btn-outline">Batal</button>
            </div>
        </div>
    </div>
    @endif

</div>
