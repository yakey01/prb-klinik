<div>
    {{-- Alert Summary --}}
    @php $alert = $this->alertSummary; @endphp
    @if($alert['habis'] > 0 || $alert['kritis'] > 0 || $alert['kadaluarsa'] > 0)
    <div style="display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:1.25rem;">
        @if($alert['habis'] > 0)
        <div style="background:rgba(232,100,90,.1);border:1px solid rgba(232,100,90,.25);border-radius:.6rem;padding:.65rem 1rem;display:flex;align-items:center;gap:.5rem;">
            <svg width="14" height="14" fill="none" stroke="var(--red2)" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span style="font-size:.78rem;color:var(--red2);"><strong>{{ $alert['habis'] }}</strong> obat stok habis</span>
        </div>
        @endif
        @if($alert['kritis'] > 0)
        <div style="background:rgba(217,164,65,.1);border:1px solid rgba(217,164,65,.25);border-radius:.6rem;padding:.65rem 1rem;display:flex;align-items:center;gap:.5rem;">
            <svg width="14" height="14" fill="none" stroke="var(--gold2)" stroke-width="2" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <span style="font-size:.78rem;color:var(--gold2);"><strong>{{ $alert['kritis'] }}</strong> obat stok kritis</span>
        </div>
        @endif
        @if($alert['kadaluarsa'] > 0)
        <div style="background:rgba(111,177,224,.1);border:1px solid rgba(111,177,224,.25);border-radius:.6rem;padding:.65rem 1rem;display:flex;align-items:center;gap:.5rem;">
            <svg width="14" height="14" fill="none" stroke="var(--blue)" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <span style="font-size:.78rem;color:var(--blue);"><strong>{{ $alert['kadaluarsa'] }}</strong> obat kadaluarsa/segera</span>
        </div>
        @endif
    </div>
    @endif

    {{-- Filter + Search --}}
    <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;margin-bottom:1.2rem;">
        <div style="position:relative;min-width:200px;">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="position:absolute;left:.75rem;top:50%;transform:translateY(-50%);color:var(--mut);"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari obat..." class="form-input" style="padding-left:2.2rem;">
        </div>
        @foreach(['semua'=>'Semua','habis'=>'Habis','kritis'=>'Kritis','aman'=>'Aman','kadaluarsa'=>'Kadaluarsa/Segera'] as $val => $lbl)
        <button wire:click="$set('filterStok','{{ $val }}')"
            style="padding:.3rem .8rem;border-radius:999px;font-size:.72rem;cursor:pointer;border:1px solid;transition:all .2s;
                {{ $filterStok===$val ? 'background:var(--gold);border-color:var(--gold);color:#1a0e00;font-weight:700;' : 'background:transparent;border-color:var(--line2);color:var(--mut);' }}">
            {{ $lbl }}
        </button>
        @endforeach
    </div>

    <div class="glass-card" style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th wire:click="sortBy('nama_obat')" style="min-width:160px;">Obat {{ $sortBy==='nama_obat' ? ($sortDir==='asc'?'↑':'↓') : '' }}</th>
                    <th>Kategori</th>
                    <th style="text-align:right;">Stok Aktual ✎</th>
                    <th style="text-align:right;">Stok Min ✎</th>
                    <th style="text-align:center;">Status Stok</th>
                    <th style="text-align:center;">Kadaluarsa ✎</th>
                    <th style="text-align:center;">Status Exp</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->obatList as $obat)
                <tr>
                    <td>
                        <div style="font-weight:500;">{{ $obat->nama_obat }}</div>
                        @if($obat->kode_obat)<div style="font-size:.68rem;color:var(--mut2);font-family:monospace;">{{ $obat->kode_obat }}</div>@endif
                    </td>
                    <td style="font-size:.77rem;color:var(--mut);">{{ $obat->kategori_diagnosis }}</td>
                    <td style="text-align:right;">
                        <input type="number" value="{{ $obat->stok_aktual }}" min="0"
                            wire:change="updateStok({{ $obat->id }}, $event.target.value)"
                            class="font-mono"
                            style="width:70px;text-align:right;background:rgba(63,207,142,.07);border:1px solid rgba(63,207,142,.2);border-radius:.3rem;padding:.18rem .35rem;font-size:.82rem;color:{{ $obat->stok_aktual <= 0 ? 'var(--red2)' : ($obat->stok_aktual <= $obat->stok_minimum ? 'var(--gold2)' : 'var(--emer2)') }};">
                    </td>
                    <td style="text-align:right;">
                        <input type="number" value="{{ $obat->stok_minimum }}" min="0"
                            wire:change="updateMinimum({{ $obat->id }}, $event.target.value)"
                            class="font-mono"
                            style="width:60px;text-align:right;background:rgba(217,164,65,.07);border:1px solid rgba(217,164,65,.2);border-radius:.3rem;padding:.18rem .35rem;font-size:.82rem;color:var(--mut);">
                    </td>
                    <td style="text-align:center;">
                        @php $st = $obat->stok_status; @endphp
                        <span class="badge badge-{{ $st==='aman'?'laba':($st==='kritis'?'cek':'rugi') }}" style="font-size:.7rem;">
                            {{ ucfirst($st) }}
                        </span>
                    </td>
                    <td style="text-align:center;">
                        <input type="date" value="{{ $obat->tanggal_kadaluarsa?->format('Y-m-d') }}"
                            wire:change="updateKadaluarsa({{ $obat->id }}, $event.target.value)"
                            style="background:var(--panel);border:1px solid var(--line);color:var(--ink);border-radius:.3rem;padding:.18rem .4rem;font-size:.75rem;font-family:monospace;max-width:120px;">
                    </td>
                    <td style="text-align:center;">
                        @php $ks = $obat->kadaluarsa_status; @endphp
                        @if($ks)
                        <span class="badge badge-{{ $ks==='aman'?'laba':($ks==='perhatian'?'cek':($ks==='segera'?'est':'rugi')) }}" style="font-size:.68rem;">
                            {{ ucfirst($ks) }}
                        </span>
                        @else
                        <span style="font-size:.72rem;color:var(--mut2);">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--mut);">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:.6rem;font-size:.73rem;color:var(--mut2);">{{ $this->obatList->count() }} obat · ✎ Kolom Stok Aktual, Stok Minimum, dan Kadaluarsa dapat diedit langsung</div>
</div>
