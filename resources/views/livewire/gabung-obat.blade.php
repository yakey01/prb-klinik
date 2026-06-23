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
    @endphp

    {{-- flash --}}
    @if (session('merge_ok'))
        <div style="background:rgba(22,163,74,0.1);border:1px solid rgba(22,163,74,0.3);color:#15803d;border-radius:12px;padding:10px 14px;margin-bottom:12px;font-size:.85rem;font-weight:700;">✓ {{ session('merge_ok') }}</div>
    @endif
    @if (session('merge_err'))
        <div style="background:rgba(220,38,38,0.1);border:1px solid rgba(220,38,38,0.3);color:#dc2626;border-radius:12px;padding:10px 14px;margin-bottom:12px;font-size:.85rem;font-weight:700;">⚠ {{ session('merge_err') }}</div>
    @endif

    {{-- Header glossy + progress --}}
    <div style="position:relative;overflow:hidden;border-radius:18px;padding:18px 22px;margin-bottom:16px;background:linear-gradient(135deg,#3b82f6,#2563eb 50%,#1d4ed8);box-shadow:0 12px 28px -10px rgba(37,99,235,0.5);">
        <div style="display:flex;align-items:center;gap:12px;">
            <span style="width:46px;height:46px;border-radius:14px;background:rgba(255,255,255,0.22);border:1px solid rgba(255,255,255,0.35);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:22px;">⤵</span>
            <div style="flex:1;min-width:0;">
                <div style="font-size:1.15rem;font-weight:900;color:#fff;letter-spacing:-0.02em;">Gabung Obat Duplikat</div>
                <div style="font-size:.78rem;color:rgba(255,255,255,0.9);margin-top:1px;">
                    @if (!$allClear && !$reviewedAll) Grup <b>{{ $this->idx + 1 }}</b> dari <b>{{ count($groups) }}</b> · pilih obat utama lalu gabung
                    @else Tinjauan duplikat obat @endif
                </div>
            </div>
            @if ($this->mergedCount > 0)
                <span style="font-size:.72rem;font-weight:800;color:#fff;background:rgba(255,255,255,0.25);border:1px solid rgba(255,255,255,0.35);border-radius:20px;padding:4px 11px;white-space:nowrap;">✓ {{ $this->mergedCount }} digabung</span>
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
            <div style="font-size:42px;margin-bottom:8px;">🎉</div>
            <div style="font-size:1.05rem;font-weight:900;color:#0f172a;">Katalog obat bersih</div>
            <div style="font-size:.82rem;color:#64748b;margin-top:5px;">Tidak ada obat duplikat tersisa{{ $this->mergedCount ? ' · ' . $this->mergedCount . ' grup digabung sesi ini' : '' }}.</div>
        </div>

    {{-- Selesai ditinjau --}}
    @elseif ($reviewedAll)
        <div style="background:#fff;border:1px solid rgba(0,0,0,0.07);border-radius:18px;padding:42px 24px;text-align:center;box-shadow:0 4px 16px rgba(15,23,42,0.06);">
            <div style="font-size:36px;margin-bottom:8px;">✨</div>
            <div style="font-size:.95rem;font-weight:800;color:#0f172a;">Selesai meninjau semua grup</div>
            <div style="font-size:.82rem;color:#64748b;margin-top:5px;">{{ count($groups) }} grup dilewati (belum digabung).</div>
            <button wire:click="restart" style="margin-top:16px;padding:11px 20px;border:1.5px solid rgba(37,99,235,0.3);background:rgba(37,99,235,0.06);border-radius:12px;color:#1d4ed8;font-size:.82rem;font-weight:800;cursor:pointer;">↺ Tinjau Lagi dari Awal</button>
        </div>

    {{-- WIZARD 2-PANEL --}}
    @elseif ($current)
        <div class="go-grid" style="display:grid;grid-template-columns:1fr 330px;gap:16px;align-items:start;">

            {{-- KIRI: daftar varian --}}
            <div style="background:#fff;border:1px solid rgba(0,0,0,0.07);border-radius:18px;overflow:hidden;box-shadow:0 4px 16px rgba(15,23,42,0.06);">
                <div style="display:flex;align-items:center;gap:9px;padding:13px 16px;border-bottom:1px solid rgba(0,0,0,0.06);background:#fafbff;">
                    <span style="font-size:.95rem;font-weight:900;color:#0f172a;">💊 {{ $current['label'] }}</span>
                    <span style="font-size:.62rem;font-weight:800;color:#1d4ed8;background:rgba(37,99,235,0.1);border-radius:20px;padding:2px 9px;">{{ $current['count'] }} varian</span>
                    <span style="margin-left:auto;font-size:.6rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.04em;">Pilih utama</span>
                </div>
                <div style="padding:9px;">
                    @foreach ($current['items'] as $m)
                        @php $sel = $this->primaryId === $m['id']; $tb = $tipeBadge($m['tipe']); @endphp
                        <label style="display:flex;align-items:center;gap:11px;padding:9px 12px;border-radius:11px;margin-bottom:5px;cursor:pointer;border:1.5px solid {{ $sel ? 'rgba(37,99,235,0.55)' : 'rgba(0,0,0,0.06)' }};background:{{ $sel ? 'rgba(37,99,235,0.06)' : '#fff' }};">
                            <input type="radio" wire:model.live="primaryId" value="{{ $m['id'] }}" style="accent-color:#2563eb;width:17px;height:17px;flex-shrink:0;" />
                            <div style="flex:1;min-width:0;">
                                <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                                    <span style="font-size:.82rem;font-weight:700;color:#0f172a;">{{ $m['name'] }}</span>
                                    @if ($tb)<span style="font-size:.55rem;font-weight:800;border-radius:5px;padding:1px 6px;color:{{ $tb['c'] }};background:{{ $tb['b'] }};">{{ $tb['l'] }}</span>@endif
                                    @unless ($m['is_active'])<span style="font-size:.55rem;font-weight:800;color:#92400e;background:rgba(217,119,6,0.12);border-radius:5px;padding:1px 6px;">Nonaktif</span>@endunless
                                    @if ($sel)<span style="font-size:.55rem;font-weight:900;color:#1d4ed8;background:rgba(37,99,235,0.12);border-radius:5px;padding:1px 6px;">★ UTAMA</span>@endif
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
                        <span style="font-size:15px;flex-shrink:0;">★</span>
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
                                <div style="display:flex;align-items:center;gap:6px;font-size:.68rem;color:#94a3b8;">→ <span style="text-decoration:line-through;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ $d['name'] }}</span><span style="margin-left:auto;flex-shrink:0;">{{ $d['usage'] }}×</span></div>
                            @endforeach
                        </div>
                    @endif
                    @if ($this->mixedUnit)
                        <div style="font-size:.68rem;color:#b45309;background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.25);border-radius:10px;padding:9px 11px;margin-bottom:12px;line-height:1.45;">⚠ <b>Bentuk/satuan berbeda</b> — pastikan obat benar-benar sama.</div>
                    @endif
                    <button wire:click="merge" wire:loading.attr="disabled" @disabled(count($dups) === 0)
                        style="width:100%;display:flex;align-items:center;justify-content:center;gap:8px;padding:13px;border:none;border-radius:13px;font-size:.85rem;font-weight:800;cursor:pointer;color:#fff;background:linear-gradient(135deg,#2563eb,#1d4ed8);box-shadow:0 8px 20px -5px rgba(37,99,235,0.55);margin-bottom:9px;{{ count($dups) === 0 ? 'opacity:0.55;cursor:not-allowed;' : '' }}">
                        <span wire:loading.remove wire:target="merge">⤵ Gabung {{ count($dups) }} & Lanjut →</span>
                        <span wire:loading wire:target="merge">Menggabung…</span>
                    </button>
                    <button wire:click="skip" wire:loading.attr="disabled" style="width:100%;padding:11px;border:1.5px solid rgba(0,0,0,0.12);background:#fff;border-radius:12px;color:#64748b;font-size:.78rem;font-weight:700;cursor:pointer;">⏭ Lewati grup ini</button>
                </div>
            </aside>
        </div>
    @endif

    <style>
        @media (max-width: 820px) { .go-grid { grid-template-columns: 1fr !important; } .go-side { position: static !important; } }
    </style>
</div>
