<div style="max-width:1080px;margin:0 auto;">
    @php
        $groups = $this->groups;
        $current = $this->current;
        $dups = $this->dups;
        $primary = $current ? collect($current['items'])->firstWhere('id', $this->primaryId) : null;
        $maxUsage = $current ? max(1, ...array_map(fn($m) => $m['usage'], $current['items'])) : 1;
        $allClear = count($groups) === 0;
        $reviewedAll = count($groups) > 0 && $this->idx >= count($groups);
        $tipeBadge = function ($t) {
            return match ($t) {
                'kronis' => ['l' => 'Kronis', 'c' => '#7c3aed', 'b' => 'rgba(124,58,237,0.1)'],
                'non_kronis' => ['l' => 'Non-Kronis', 'c' => '#0d9488', 'b' => 'rgba(13,148,136,0.1)'],
                default => null,
            };
        };
        // Ikon LUCIDE inline (stroke=currentColor ikut warna teks induk). Sepadan lucide-vue di SIM.
        $lucide = [
            'git-merge'    => '<circle cx="18" cy="18" r="3"/><circle cx="6" cy="6" r="3"/><path d="M6 21V9a9 9 0 0 0 9 9"/>',
            'circle-check' => '<path d="M21.801 10A10 10 0 1 1 17 3.335"/><path d="m9 11 3 3L22 4"/>',
            'check'        => '<path d="M20 6 9 17l-5-5"/>',
            'triangle-alert' => '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4"/><path d="M12 17h.01"/>',
            'sparkles'     => '<path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/><path d="M20 3v4"/><path d="M22 5h-4"/><path d="M4 17v2"/><path d="M5 18H3"/>',
            'rotate-ccw'   => '<path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/>',
            'pill'         => '<path d="m10.5 20.5 10-10a4.95 4.95 0 1 0-7-7l-10 10a4.95 4.95 0 1 0 7 7Z"/><path d="m8.5 8.5 7 7"/>',
            'star'         => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
            'arrow-right'  => '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
            'skip-forward' => '<polygon points="5 4 15 12 5 20 5 4"/><line x1="19" x2="19" y1="5" y2="19"/>',
        ];
        $ico = function (string $name, int $size = 16, string $extra = '') use ($lucide) {
            $p = $lucide[$name] ?? '';
            return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;' . $extra . '">' . $p . '</svg>';
        };
    @endphp

    {{-- flash --}}
    @if (session('merge_ok'))
        <div style="display:flex;align-items:center;gap:8px;background:rgba(22,163,74,0.1);border:1px solid rgba(22,163,74,0.3);color:#15803d;border-radius:12px;padding:10px 14px;margin-bottom:12px;font-size:.85rem;font-weight:700;">{!! $ico('circle-check', 16) !!}<span>{{ session('merge_ok') }}</span></div>
    @endif
    @if (session('merge_err'))
        <div style="display:flex;align-items:center;gap:8px;background:rgba(220,38,38,0.1);border:1px solid rgba(220,38,38,0.3);color:#dc2626;border-radius:12px;padding:10px 14px;margin-bottom:12px;font-size:.85rem;font-weight:700;">{!! $ico('triangle-alert', 16) !!}<span>{{ session('merge_err') }}</span></div>
    @endif

    {{-- Header glossy + progress --}}
    <div style="position:relative;overflow:hidden;border-radius:18px;padding:18px 22px;margin-bottom:16px;background:linear-gradient(135deg,#3b82f6,#2563eb 50%,#1d4ed8);box-shadow:0 12px 28px -10px rgba(37,99,235,0.5);">
        <div style="display:flex;align-items:center;gap:12px;">
            <span style="width:46px;height:46px;border-radius:14px;background:rgba(255,255,255,0.22);border:1px solid rgba(255,255,255,0.35);display:flex;align-items:center;justify-content:center;flex-shrink:0;color:#fff;">{!! $ico('git-merge', 22) !!}</span>
            <div style="flex:1;min-width:0;">
                <div style="font-size:1.15rem;font-weight:900;color:#fff;letter-spacing:-0.02em;">Gabung Obat Duplikat</div>
                <div style="font-size:.78rem;color:rgba(255,255,255,0.9);margin-top:1px;">
                    @if (!$allClear && !$reviewedAll) Grup <b>{{ $this->idx + 1 }}</b> dari <b>{{ count($groups) }}</b> · pilih obat utama lalu gabung
                    @else Tinjauan duplikat obat @endif
                </div>
            </div>
            @if ($this->mergedCount > 0)
                <span style="display:inline-flex;align-items:center;gap:5px;font-size:.72rem;font-weight:800;color:#fff;background:rgba(255,255,255,0.25);border:1px solid rgba(255,255,255,0.35);border-radius:20px;padding:4px 11px;white-space:nowrap;">{!! $ico('check', 13) !!} {{ $this->mergedCount }} digabung</span>
            @endif
        </div>
        @unless ($allClear)
            <div style="height:6px;border-radius:99px;background:rgba(255,255,255,0.25);margin-top:13px;overflow:hidden;">
                <div style="height:100%;border-radius:99px;background:#fff;transition:width .3s;width:{{ count($groups) ? round((($reviewedAll ? count($groups) : $this->idx) / max(1, count($groups))) * 100) : 100 }}%;"></div>
            </div>
        @endunless
    </div>

    {{-- Semua bersih --}}
    @if ($allClear)
        <div style="background:#fff;border:1px solid rgba(0,0,0,0.07);border-radius:18px;padding:48px 24px;text-align:center;box-shadow:0 4px 16px rgba(15,23,42,0.06);">
            <div style="color:#16a34a;margin-bottom:10px;display:flex;justify-content:center;">{!! $ico('circle-check', 44) !!}</div>
            <div style="font-size:1.05rem;font-weight:900;color:#0f172a;">Katalog obat bersih</div>
            <div style="font-size:.82rem;color:#64748b;margin-top:5px;">Tidak ada obat duplikat tersisa{{ $this->mergedCount ? ' · ' . $this->mergedCount . ' grup digabung sesi ini' : '' }}.</div>
        </div>

    {{-- Selesai ditinjau --}}
    @elseif ($reviewedAll)
        <div style="background:#fff;border:1px solid rgba(0,0,0,0.07);border-radius:18px;padding:42px 24px;text-align:center;box-shadow:0 4px 16px rgba(15,23,42,0.06);">
            <div style="color:#2563eb;margin-bottom:10px;display:flex;justify-content:center;">{!! $ico('sparkles', 34) !!}</div>
            <div style="font-size:.95rem;font-weight:800;color:#0f172a;">Selesai meninjau semua grup</div>
            <div style="font-size:.82rem;color:#64748b;margin-top:5px;">{{ count($groups) }} grup dilewati (belum digabung).</div>
            <button wire:click="restart" style="margin-top:16px;display:inline-flex;align-items:center;gap:7px;padding:11px 20px;border:1.5px solid rgba(37,99,235,0.3);background:rgba(37,99,235,0.06);border-radius:12px;color:#1d4ed8;font-size:.82rem;font-weight:800;cursor:pointer;">{!! $ico('rotate-ccw', 15) !!} Tinjau Lagi dari Awal</button>
        </div>

    {{-- WIZARD 2-PANEL --}}
    @elseif ($current)
        <div class="go-grid" style="display:grid;grid-template-columns:1fr 330px;gap:16px;align-items:start;">

            {{-- KIRI: daftar varian --}}
            <div style="background:#fff;border:1px solid rgba(0,0,0,0.07);border-radius:18px;overflow:hidden;box-shadow:0 4px 16px rgba(15,23,42,0.06);">
                <div style="display:flex;align-items:center;gap:9px;padding:13px 16px;border-bottom:1px solid rgba(0,0,0,0.06);background:#fafbff;">
                    <span style="display:inline-flex;align-items:center;gap:7px;font-size:.95rem;font-weight:900;color:#0f172a;"><span style="color:#2563eb;display:inline-flex;">{!! $ico('pill', 18) !!}</span>{{ $current['label'] }}</span>
                    <span style="font-size:.62rem;font-weight:800;color:#1d4ed8;background:rgba(37,99,235,0.1);border-radius:20px;padding:2px 9px;">{{ $current['count'] }} varian</span>
                    <span style="margin-left:auto;font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.04em;">Pilih utama</span>
                </div>
                <div style="padding:9px;">
                    @foreach ($current['items'] as $m)
                        @php $sel = $this->primaryId === $m['id']; $tb = $tipeBadge($m['tipe']); @endphp
                        <label wire:key="gabung-{{ $m['id'] }}" style="display:flex;align-items:center;gap:11px;padding:9px 12px;border-radius:11px;margin-bottom:5px;cursor:pointer;border:1.5px solid {{ $sel ? 'rgba(37,99,235,0.55)' : 'rgba(0,0,0,0.06)' }};background:{{ $sel ? 'rgba(37,99,235,0.06)' : '#fff' }};">
                            <input type="radio" wire:model.live="primaryId" value="{{ $m['id'] }}" style="accent-color:#2563eb;width:17px;height:17px;flex-shrink:0;" />
                            <div style="flex:1;min-width:0;">
                                <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                    <span style="font-size:.82rem;font-weight:700;color:#0f172a;">{{ $m['name'] }}</span>
                                    @if ($tb)<span style="font-size:.55rem;font-weight:800;border-radius:5px;padding:1px 6px;color:{{ $tb['c'] }};background:{{ $tb['b'] }};">{{ $tb['l'] }}</span>@endif
                                    @unless ($m['is_active'])<span style="font-size:.55rem;font-weight:800;color:#92400e;background:rgba(217,119,6,0.12);border-radius:5px;padding:1px 6px;">Nonaktif</span>@endunless
                                    @if ($sel)<span style="display:inline-flex;align-items:center;gap:3px;font-size:.55rem;font-weight:900;color:#1d4ed8;background:rgba(37,99,235,0.12);border-radius:5px;padding:1px 6px;">{!! $ico('star', 9) !!} UTAMA</span>@endif
                                </div>
                                <div style="display:flex;align-items:center;gap:8px;margin-top:4px;">
                                    <div style="flex:1;height:4px;border-radius:99px;background:rgba(0,0,0,0.06);overflow:hidden;max-width:160px;">
                                        <div style="height:100%;border-radius:99px;background:{{ $sel ? '#2563eb' : '#cbd5e1' }};width:{{ round(($m['usage'] / $maxUsage) * 100) }}%;"></div>
                                    </div>
                                    <span style="font-size:.66rem;color:#64748b;white-space:nowrap;">{{ $m['usage'] }}× · {{ $m['satuan'] ?: '-' }}</span>
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- KANAN: ringkasan + aksi (sticky) --}}
            <aside class="go-side" style="position:sticky;top:16px;">
                <div style="background:#fff;border:1px solid rgba(0,0,0,0.07);border-radius:18px;padding:16px;box-shadow:0 4px 16px rgba(15,23,42,0.06);">
                    <div style="font-size:.68rem;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:10px;">Ringkasan Gabung</div>
                    <div style="display:flex;align-items:center;gap:8px;padding:10px 12px;border-radius:11px;background:rgba(37,99,235,0.07);border:1px solid rgba(37,99,235,0.2);margin-bottom:11px;">
                        <span style="flex-shrink:0;color:#f59e0b;display:inline-flex;">{!! $ico('star', 16) !!}</span>
                        <div style="min-width:0;">
                            <div style="font-size:.55rem;font-weight:800;color:#1d4ed8;text-transform:uppercase;letter-spacing:0.04em;">Obat Utama (dipertahankan)</div>
                            <div style="font-size:.82rem;font-weight:800;color:#0f172a;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $primary['name'] ?? '-' }}</div>
                        </div>
                    </div>
                    <div style="font-size:.74rem;color:#475569;line-height:1.5;margin-bottom:8px;">
                        <b style="color:#dc2626;">{{ count($dups) }} obat</b> digabung ke utama (resep/transaksi dipindah, duplikat dihapus, histori aman).
                    </div>
                    @if (count($dups))
                        <div style="display:flex;flex-direction:column;gap:3px;margin-bottom:11px;max-height:120px;overflow-y:auto;">
                            @foreach ($dups as $d)
                                <div style="display:flex;align-items:center;gap:6px;font-size:.68rem;color:#94a3b8;">{!! $ico('arrow-right', 12) !!}<span style="text-decoration:line-through;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $d['name'] }}</span><span style="margin-left:auto;flex-shrink:0;">{{ $d['usage'] }}×</span></div>
                            @endforeach
                        </div>
                    @endif
                    @if ($this->mixedUnit)
                        <div style="display:flex;align-items:flex-start;gap:7px;font-size:.68rem;color:#b45309;background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.25);border-radius:10px;padding:9px 11px;margin-bottom:12px;line-height:1.45;"><span style="flex-shrink:0;margin-top:1px;display:inline-flex;">{!! $ico('triangle-alert', 14) !!}</span><span><b>Bentuk/satuan berbeda</b> — pastikan obat benar-benar sama.</span></div>
                    @endif
                    <button wire:click="merge" wire:loading.attr="disabled" @disabled(count($dups) === 0)
                        style="width:100%;display:flex;align-items:center;justify-content:center;gap:8px;padding:13px;border:none;border-radius:13px;font-size:.85rem;font-weight:800;cursor:pointer;color:#fff;background:linear-gradient(135deg,#2563eb,#1d4ed8);box-shadow:0 8px 20px -5px rgba(37,99,235,0.55);margin-bottom:9px;{{ count($dups) === 0 ? 'opacity:0.55;cursor:not-allowed;' : '' }}">
                        <span wire:loading.remove wire:target="merge" style="display:inline-flex;align-items:center;gap:7px;">{!! $ico('git-merge', 15) !!} Gabung {{ count($dups) }} & Lanjut {!! $ico('arrow-right', 14) !!}</span>
                        <span wire:loading wire:target="merge">Menggabung…</span>
                    </button>
                    <button wire:click="skip" wire:loading.attr="disabled" style="width:100%;display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:11px;border:1.5px solid rgba(0,0,0,0.12);background:#fff;border-radius:12px;color:#64748b;font-size:.78rem;font-weight:700;cursor:pointer;">{!! $ico('skip-forward', 14) !!} Lewati grup ini</button>
                </div>
            </aside>
        </div>
    @endif

    <style>
        @media (max-width: 820px) { .go-grid { grid-template-columns: 1fr !important; } .go-side { position: static !important; } }
    </style>
</div>
