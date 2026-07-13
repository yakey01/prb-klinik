<x-app-layout>
    <x-slot name="title">Riwayat PO</x-slot>

    {{-- Modal koreksi PO (Livewire) — dipicu tombol Koreksi tiap kartu --}}
    <livewire:po-koreksi />
    <script>
        // Refresh halaman setelah koreksi tersimpan agar angka PO/subtotal ikut update.
        document.addEventListener('livewire:init', () => {
            Livewire.on('po-updated', () => setTimeout(() => window.location.reload(), 600));
        });
    </script>

    {{-- Pulihkan filter terakhir saat kembali ke /riwayat tanpa query (anti-hilang, sama spt kalkulator) --}}
    <script>
    (function () {
        var KEY = 'prb_riwayat_filter';
        var qs  = window.location.search;
        var hasFilter = /[?&](distributor_id|dari|sampai)=[^&]+/.test(qs);
        try {
            if (hasFilter) {
                localStorage.setItem(KEY, qs);
                if (sessionStorage.getItem('prb_riwayat_restored')) {
                    sessionStorage.removeItem('prb_riwayat_restored');
                    window.addEventListener('load', function () {
                        window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Filter terakhir dipulihkan', type: 'success' } }));
                    });
                }
            } else {
                var saved = localStorage.getItem(KEY);
                if (saved && saved.length > 1) {
                    sessionStorage.setItem('prb_riwayat_restored', '1');
                    window.location.replace(@json(route('riwayat.index')) + saved); // redirect sekali, no history
                }
            }
        } catch (e) {}
    })();
    </script>

    {{-- HEADER --}}
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:1.5rem; flex-wrap:wrap; gap:.75rem;">
        <div>
            <div class="font-label" style="font-size:.7rem; color:var(--mut); margin-bottom:.25rem;">Histori</div>
            <h2 class="font-heading" style="font-size:1.5rem; color:var(--ink); margin:0;">Riwayat Purchase Order</h2>
        </div>
        <div style="display:flex; gap:.75rem; align-items:center; flex-wrap:wrap;">
            <a href="{{ route('riwayat.export', request()->query()) }}" class="btn-outline" style="font-size:.78rem;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export CSV
            </a>
            <a href="{{ route('pengadaan.pengajuan') }}" class="btn-gold">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Pengadaan
            </a>
        </div>
    </div>

    {{-- FILTER FORM --}}
    <form method="GET" class="glass-card" style="padding:1.2rem 1.5rem; margin-bottom:1.5rem; display:flex; gap:.75rem; flex-wrap:wrap; align-items:flex-end;">
        <div>
            <label class="form-label">Distributor</label>
            <select name="distributor_id" class="form-input" style="min-width:200px;">
                <option value="">Semua Distributor</option>
                @foreach($distributors as $dist)
                <option value="{{ $dist->id }}" {{ request('distributor_id') == $dist->id ? 'selected' : '' }}>{{ $dist->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label">Dari Tanggal</label>
            <input type="date" name="dari" value="{{ request('dari') }}" class="form-input">
        </div>
        <div>
            <label class="form-label">Sampai Tanggal</label>
            <input type="date" name="sampai" value="{{ request('sampai') }}" class="form-input">
        </div>
        <button type="submit" class="btn-outline">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            Filter
        </button>
        @if(request()->hasAny(['distributor_id','dari','sampai']))
        <a href="{{ route('riwayat.index') }}" onclick="try{localStorage.removeItem('prb_riwayat_filter')}catch(e){}" class="btn-outline" style="color:var(--red);border-color:rgba(232,100,90,.3);">Reset</a>
        @endif
    </form>

    {{-- GUARDIAN AI BANNER --}}
    @php $gs = $guardianSummary ?? []; @endphp
    @if(($gs['total'] ?? 0) > 0)
    <a href="{{ route('guardian.index') }}" style="text-decoration:none;display:flex;align-items:center;gap:.85rem;flex-wrap:wrap;padding:.75rem 1.1rem;margin-bottom:1.25rem;border-radius:.7rem;border:1px solid {{ ($gs['kritis']??0)>0 ? 'rgba(232,100,90,.45)' : 'rgba(217,164,65,.4)' }};background:linear-gradient(100deg,{{ ($gs['kritis']??0)>0 ? 'rgba(232,100,90,.14)' : 'rgba(217,164,65,.12)' }},transparent 70%);">
        <div style="width:34px;height:34px;border-radius:.6rem;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#3fcf8e,#2b9d68);flex-shrink:0;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#04120c" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg></div>
        <div style="flex:1;min-width:200px;">
            <div style="font-size:.85rem;font-weight:700;color:var(--ink);">Guardian AI: {{ $gs['total'] }} temuan rekonsiliasi PO ↔ Tagihan</div>
            <div style="font-size:.72rem;color:var(--mut);margin-top:.1rem;">
                @if(($gs['kritis']??0)>0)<span style="color:var(--red2);font-weight:700;">🔴 {{ $gs['kritis'] }} kritis</span> · @endif
                @if(($gs['tinggi']??0)>0)<span style="color:#e6863c;font-weight:700;">🟠 {{ $gs['tinggi'] }} tinggi</span> · @endif
                cek agar tidak ada yang tertukar
            </div>
        </div>
        <span style="font-size:.75rem;font-weight:700;color:var(--emer2);display:inline-flex;align-items:center;gap:.3rem;flex-shrink:0;">Tinjau <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></span>
    </a>
    @endif

    {{-- LIST PO --}}
    @forelse($orders as $po)
    @php $risk = $guardianRisk[$po->id] ?? null; @endphp
    <div class="glass-card riwayat-po" id="po-{{ $po->id }}" data-po="{{ $po->id }}" style="margin-bottom:1rem; overflow:hidden;{{ $risk ? 'border-color:'.($risk['level']==='kritis'?'rgba(232,100,90,.45)':($risk['level']==='tinggi'?'rgba(230,134,60,.4)':'rgba(217,164,65,.35)')).';' : '' }}">
        <div style="display:flex; align-items:center; gap:1rem; padding:1.1rem 1.5rem; cursor:pointer;"
             onclick="riwayatToggle({{ $po->id }})">
            <div style="width:10px; height:10px; border-radius:50%; background:var(--emer); flex-shrink:0; box-shadow:0 0 6px var(--emer);"></div>
            <div style="flex:1;">
                <div style="display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
                    <span class="font-mono" style="font-size:.8rem; color:var(--gold2);">{{ $po->tanggal_po->format('d M Y') }}</span>
                    <span style="font-weight:600; color:var(--ink);">{{ $po->distributor->name }}</span>
                    @if($po->nomor_invoice)
                    <span style="color:var(--mut); font-size:.78rem;">#{{ $po->nomor_invoice }}</span>
                    @endif
                    <span style="background:rgba(111,177,224,.1); border:1px solid rgba(111,177,224,.2); border-radius:999px; padding:.1rem .5rem; font-size:.7rem; color:var(--blue);">
                        {{ $po->items->count() }} item
                    </span>
                    @if($risk)
                    @php $rl = $risk['level']; $rc = $rl==='kritis'?'var(--red2)':($rl==='tinggi'?'#e6863c':($rl==='sedang'?'var(--gold2)':'var(--blue)')); $ri = $rl==='kritis'?'🔴':($rl==='tinggi'?'🟠':($rl==='sedang'?'🟡':'🔵')); @endphp
                    <a href="{{ route('guardian.index') }}" onclick="event.stopPropagation();" title="{{ $risk['top'] }}" style="text-decoration:none;background:rgba(63,207,142,.06);border:1px solid {{ $rc }};border-radius:999px;padding:.1rem .55rem;font-size:.68rem;font-weight:700;color:{{ $rc }};display:inline-flex;align-items:center;gap:.25rem;">
                        🛡 {{ $ri }} {{ $risk['count'] }} temuan AI
                    </a>
                    @endif
                    @if(isset($po->status_bayar))
                    <span class="badge badge-{{ $po->status_bayar==='lunas'?'laba':($po->status_bayar==='sebagian'?'cek':'rugi') }}" style="font-size:.68rem;">
                        {{ ucfirst($po->status_bayar) }}
                    </span>
                    @endif
                </div>
            </div>
            <div style="text-align:right; flex-shrink:0;">
                <div class="font-mono" style="font-size:1.1rem; font-weight:700; color:var(--emer2);">
                    Rp {{ number_format($po->total_nilai,0,',','.') }}
                </div>
                @if(isset($po->status_bayar) && $po->status_bayar !== 'lunas')
                <div style="font-size:.7rem;color:var(--mut);">Sisa: Rp {{ number_format($po->sisa_tagihan,0,',','.') }}</div>
                @endif
            </div>
            <svg data-chev width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="transition:transform .2s; color:var(--mut); flex-shrink:0;"><polyline points="6 9 12 15 18 9"/></svg>
        </div>

        {{-- EXPANDED DETAIL --}}
        <div data-detail style="display:none; border-top:1px solid var(--line); padding:1rem 1.5rem;">
            <div style="overflow-x:auto; margin-bottom:.75rem;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width:80px;">Jenis</th>
                            <th>Obat</th>
                            <th style="text-align:right;">Box</th>
                            <th style="text-align:right;">Isi/Box</th>
                            <th style="text-align:right;">Harga/Box</th>
                            <th style="text-align:right;">Harga/Unit</th>
                            <th style="text-align:right;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($po->items as $item)
                        @php $isBpjs = ($item->tipe_obat ?? 'kronis') === 'kronis'; @endphp
                        <tr style="border-left:3px solid {{ $isBpjs ? 'rgba(63,207,142,.35)' : 'rgba(111,177,224,.35)' }};">
                            <td>
                                <span style="display:inline-block;padding:.2rem .55rem;border-radius:.35rem;font-size:.68rem;font-weight:700;letter-spacing:.04em;
                                    {{ $isBpjs
                                        ? 'background:rgba(63,207,142,.12);border:1px solid rgba(63,207,142,.3);color:var(--emer2);'
                                        : 'background:rgba(111,177,224,.12);border:1px solid rgba(111,177,224,.3);color:var(--blue);' }}">
                                    {{ $isBpjs ? 'Kronis' : 'Non-Kronis' }}
                                </span>
                            </td>
                            <td>{{ $item->obat->nama_obat ?? '-' }}</td>
                            <td class="font-mono" style="text-align:right;">{{ $item->jumlah_box }}</td>
                            <td class="font-mono" style="text-align:right;">{{ $item->isi_per_box }}</td>
                            <td class="font-mono" style="text-align:right;">Rp {{ number_format($item->harga_per_box,0,',','.') }}</td>
                            <td class="font-mono" style="text-align:right; color:var(--gold2);">Rp {{ number_format($item->harga_per_unit,0,',','.') }}</td>
                            <td class="font-mono" style="text-align:right; font-weight:600;">Rp {{ number_format($item->subtotal,0,',','.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    @php
                        $bpjsTotal    = $po->items->where('tipe_obat','kronis')->sum('subtotal');
                        $umumTotal    = $po->items->where('tipe_obat','non_kronis')->sum('subtotal');
                    @endphp
                    @if($bpjsTotal > 0 && $umumTotal > 0)
                    <tfoot>
                        <tr>
                            <td colspan="6" style="text-align:right;font-size:.72rem;color:var(--mut);padding:.5rem .75rem;">
                                <span style="color:var(--emer2);margin-right:1.5rem;">Kronis: Rp {{ number_format($bpjsTotal,0,',','.') }}</span>
                                <span style="color:var(--blue);">Non-Kronis: Rp {{ number_format($umumTotal,0,',','.') }}</span>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>

            @if($po->catatan)
            <div style="font-size:.78rem; color:var(--mut); margin-bottom:.75rem;">Catatan: {{ $po->catatan }}</div>
            @endif

            <div style="display:flex; justify-content:flex-end; gap:.6rem; align-items:center;">
                @php $kp = $po->koreksiPending(); @endphp
                @if($kp)
                <span style="display:inline-flex;align-items:center;gap:.4rem;padding:.5rem .9rem;border-radius:.5rem;background:rgba(217,164,65,.1);border:1px solid rgba(217,164,65,.35);color:var(--gold2);font-size:.76rem;font-weight:700;"
                    title="Usulan koreksi diajukan {{ $kp->created_at->diffForHumans() }} oleh {{ $kp->pemohon_nama }} — menunggu ACC manajer di SIM">
                    ⏳ Koreksi menunggu persetujuan manajer SIM
                </span>
                @else
                <button type="button" onclick="Livewire.dispatch('koreksi-po', { poId: {{ $po->id }} })"
                    style="display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1rem;border-radius:.5rem;background:rgba(217,164,65,.12);border:1px solid rgba(217,164,65,.4);color:var(--gold2);font-size:.8rem;font-weight:700;cursor:pointer;"
                    title="Ajukan perbaikan qty/harga bila barang/faktur tidak sesuai — perlu persetujuan manajer">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4z"/></svg>
                    Koreksi PO
                </button>
                @endif
                <form method="POST" action="{{ route('riwayat.destroy', $po) }}"
                      onsubmit="return confirm('Hapus PO ini? Data tidak dapat dikembalikan.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-danger">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                        Hapus PO
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="glass-card" style="padding:3rem; text-align:center; color:var(--mut);">
        <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24" style="margin:0 auto 1rem;color:var(--line2);"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
        <div style="font-size:1rem; margin-bottom:.5rem;">Belum ada riwayat PO</div>
        <a href="{{ route('pengadaan.pengajuan') }}" style="color:var(--gold2); font-size:.85rem;">Buat pengadaan pertama <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg></a>
    </div>
    @endforelse

    {{-- PAGINATION --}}
    @if($orders->hasPages())
    <div style="margin-top:1.5rem; display:flex; justify-content:center;">
        {{ $orders->links() }}
    </div>
    @endif

    {{-- Accordion vanilla-JS: ingat PO detail yang terbuka antar-navigasi (anti-nutup saat balik) --}}
    <script>
    (function () {
        var KEY = 'prb_riwayat_open';
        function getOpen() { try { return JSON.parse(localStorage.getItem(KEY) || '[]'); } catch (e) { return []; } }
        function setOpen(a) { try { localStorage.setItem(KEY, JSON.stringify(a)); } catch (e) {} }
        function apply(card, open) {
            var d = card.querySelector('[data-detail]');
            var ch = card.querySelector('[data-chev]');
            if (d)  d.style.display = open ? '' : 'none';
            if (ch) ch.style.transform = open ? 'rotate(180deg)' : '';
        }
        // Toggle dipanggil dari onclick header.
        window.riwayatToggle = function (id) {
            var card = document.querySelector('.riwayat-po[data-po="' + id + '"]');
            if (!card) return;
            var open = getOpen();
            var i = open.indexOf(id);
            var nowOpen = i === -1;
            if (nowOpen) open.push(id); else open.splice(i, 1);
            setOpen(open);
            apply(card, nowOpen);
        };
        // Restore saat load: buka kembali detail yang sebelumnya terbuka.
        function restore() {
            var open = getOpen();
            document.querySelectorAll('.riwayat-po').forEach(function (card) {
                apply(card, open.indexOf(parseInt(card.dataset.po, 10)) !== -1);
            });
        }
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', restore);
        else restore();
    })();
    </script>
</x-app-layout>
