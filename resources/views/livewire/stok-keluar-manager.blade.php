<div>
    {{-- ── PAGE HEADER ─────────────────────────────────────────────────────── --}}
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
        <div>
            <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.35rem;">
                <h2 class="font-heading" style="font-size:1.15rem;color:var(--ink);margin:0;">Stok Keluar Obat</h2>
                <span style="background:rgba(63,207,142,.1);border:1px solid rgba(63,207,142,.22);color:var(--emer2);font-size:.62rem;font-weight:700;padding:.15rem .5rem;border-radius:2rem;letter-spacing:.06em;text-transform:uppercase;">Kronis</span>
                <span style="background:rgba(111,177,224,.1);border:1px solid rgba(111,177,224,.22);color:var(--blue);font-size:.62rem;font-weight:700;padding:.15rem .5rem;border-radius:2rem;letter-spacing:.06em;text-transform:uppercase;">Non-Kronis</span>
            </div>
            <p style="font-size:.73rem;color:var(--mut);margin:0;">Rekam pengeluaran obat ke pasien — kronis otomatis dari penyerahan, non-kronis dicatat manual</p>
        </div>
        <div style="display:flex;gap:.6rem;align-items:center;flex-wrap:wrap;">
            <input wire:model.live="search" type="text" placeholder="Cari obat / pasien..." class="form-input" style="max-width:210px;font-size:.8rem;">
            <input wire:model.live="filterBulan" type="month" class="form-input font-mono" style="max-width:150px;font-size:.8rem;">
            @if($activeTab === 'non_kronis')
            <button wire:click="openAdd" class="btn-gold" style="white-space:nowrap;">+ Catat Keluar</button>
            @endif
        </div>
    </div>

    {{-- ── KPI SUMMARY CARDS ────────────────────────────────────────────────── --}}
    @php $s = $this->summary; @endphp
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(155px,1fr));gap:.75rem;margin-bottom:1.5rem;">
        <div style="background:linear-gradient(135deg,rgba(63,207,142,.08),rgba(63,207,142,.03));border:1px solid rgba(63,207,142,.18);border-radius:.85rem;padding:1rem 1.1rem;position:relative;overflow:hidden;">
            <div style="position:absolute;top:-.4rem;right:-.4rem;font-size:2.5rem;opacity:.06;">↑</div>
            <div style="font-size:.6rem;color:var(--mut);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.4rem;font-weight:600;">Pendapatan</div>
            <div class="font-mono" style="font-size:.95rem;font-weight:700;color:var(--emer2);">Rp {{ number_format($s['total_pendapatan'],0,',','.') }}</div>
            <div style="font-size:.65rem;color:var(--mut);margin-top:.2rem;">{{ number_format($s['jumlah_transaksi'],0,',','.') }} transaksi</div>
        </div>
        <div style="background:linear-gradient(135deg,rgba(232,100,90,.07),rgba(232,100,90,.02));border:1px solid rgba(232,100,90,.14);border-radius:.85rem;padding:1rem 1.1rem;position:relative;overflow:hidden;">
            <div style="position:absolute;top:-.4rem;right:-.4rem;font-size:2.5rem;opacity:.06;">↓</div>
            <div style="font-size:.6rem;color:var(--mut);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.4rem;font-weight:600;">Biaya HPP</div>
            <div class="font-mono" style="font-size:.95rem;font-weight:700;color:var(--mut2);">Rp {{ number_format($s['total_biaya'],0,',','.') }}</div>
            <div style="font-size:.65rem;color:var(--mut);margin-top:.2rem;">harga pokok penjualan</div>
        </div>
        <div style="background:linear-gradient(135deg,rgba(217,164,65,.08),rgba(217,164,65,.03));border:1px solid rgba(217,164,65,.16);border-radius:.85rem;padding:1rem 1.1rem;position:relative;overflow:hidden;">
            <div style="position:absolute;top:-.4rem;right:-.4rem;font-size:2.5rem;opacity:.06;">=</div>
            <div style="font-size:.6rem;color:var(--mut);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.4rem;font-weight:600;">Laba Kotor</div>
            <div class="font-mono" style="font-size:.95rem;font-weight:700;color:{{ $s['total_laba'] >= 0 ? 'var(--gold2)' : 'var(--red2)' }};">
                {{ $s['total_laba'] >= 0 ? '+' : '' }}Rp {{ number_format($s['total_laba'],0,',','.') }}
            </div>
            @if($s['total_pendapatan'] > 0)
            <div style="font-size:.65rem;color:var(--mut);margin-top:.2rem;">
                margin {{ round(($s['total_laba'] / $s['total_pendapatan']) * 100, 1) }}%
            </div>
            @endif
        </div>
        <div style="background:linear-gradient(135deg,rgba(111,177,224,.08),rgba(111,177,224,.02));border:1px solid rgba(111,177,224,.14);border-radius:.85rem;padding:1rem 1.1rem;position:relative;overflow:hidden;">
            <div style="position:absolute;top:-.4rem;right:-.4rem;font-size:2.5rem;opacity:.06;">⬡</div>
            <div style="font-size:.6rem;color:var(--mut);text-transform:uppercase;letter-spacing:.08em;margin-bottom:.4rem;font-weight:600;">Total Unit</div>
            <div class="font-mono" style="font-size:.95rem;font-weight:700;color:var(--blue);">{{ number_format($s['total_item'],0,',','.') }}</div>
            <div style="font-size:.65rem;color:var(--mut);margin-top:.2rem;">unit obat keluar</div>
        </div>
    </div>

    {{-- ── TAB BAR ──────────────────────────────────────────────────────────── --}}
    @php $counts = $this->tabCounts; @endphp
    <div style="display:flex;gap:0;border-bottom:2px solid rgba(255,255,255,.07);margin-bottom:1.25rem;">
        <button wire:click="setTab('kronis')"
            style="padding:.65rem 1.4rem;border:none;border-bottom:2px solid {{ $activeTab==='kronis' ? 'var(--emer2)' : 'transparent' }};margin-bottom:-2px;background:transparent;cursor:pointer;display:flex;align-items:center;gap:.5rem;transition:all .18s;color:{{ $activeTab==='kronis' ? 'var(--emer2)' : 'var(--mut)' }};">
            <span style="font-size:.8rem;font-weight:{{ $activeTab==='kronis' ? '700' : '500' }};">KRONIS</span>
            <span style="background:{{ $activeTab==='kronis' ? 'rgba(63,207,142,.15)' : 'rgba(255,255,255,.05)' }};color:{{ $activeTab==='kronis' ? 'var(--emer2)' : 'var(--mut)' }};font-size:.6rem;font-weight:700;padding:.1rem .45rem;border-radius:2rem;font-family:monospace;">{{ $counts['kronis'] }}</span>
            <span style="font-size:.62rem;color:var(--mut);font-weight:400;">auto</span>
        </button>
        <button wire:click="setTab('non_kronis')"
            style="padding:.65rem 1.4rem;border:none;border-bottom:2px solid {{ $activeTab==='non_kronis' ? 'var(--blue)' : 'transparent' }};margin-bottom:-2px;background:transparent;cursor:pointer;display:flex;align-items:center;gap:.5rem;transition:all .18s;color:{{ $activeTab==='non_kronis' ? 'var(--blue)' : 'var(--mut)' }};">
            <span style="font-size:.8rem;font-weight:{{ $activeTab==='non_kronis' ? '700' : '500' }};">NON KRONIS</span>
            <span style="background:{{ $activeTab==='non_kronis' ? 'rgba(111,177,224,.15)' : 'rgba(255,255,255,.05)' }};color:{{ $activeTab==='non_kronis' ? 'var(--blue)' : 'var(--mut)' }};font-size:.6rem;font-weight:700;padding:.1rem .45rem;border-radius:2rem;font-family:monospace;">{{ $counts['non_kronis'] }}</span>
            <span style="font-size:.62rem;color:var(--mut);font-weight:400;">manual</span>
        </button>
    </div>

    {{-- ════════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB: KRONIS (Auto-populated from pengambilan obat)                      --}}
    {{-- ════════════════════════════════════════════════════════════════════════ --}}
    @if($activeTab === 'kronis')

    {{-- Info Banner --}}
    <div style="background:linear-gradient(135deg,rgba(63,207,142,.08),rgba(63,207,142,.03));border:1px solid rgba(63,207,142,.18);border-radius:.75rem;padding:.85rem 1.1rem;margin-bottom:1.1rem;display:flex;align-items:flex-start;gap:.75rem;">
        <div style="font-size:1rem;line-height:1;margin-top:.05rem;opacity:.7;">⚡</div>
        <div>
            <div style="font-size:.78rem;font-weight:700;color:var(--emer2);margin-bottom:.2rem;">Otomatis dari Penyerahan Obat Kronis</div>
            <div style="font-size:.72rem;color:var(--mut);line-height:1.5;">
                Setiap kali obat kronis diserahkan ke pasien di halaman <strong style="color:var(--mut2);">Pengambilan Obat</strong>, stok keluar dan pengurangan stok aktual dicatat otomatis di sini.
                Data ini <em>read-only</em> — tidak dapat diedit atau dihapus secara manual.
            </div>
        </div>
    </div>

    {{-- Kronis Table --}}
    <div class="glass-card" style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="min-width:90px;">Tanggal</th>
                    <th style="min-width:150px;">Pasien</th>
                    <th style="min-width:180px;">Nama Obat</th>
                    <th style="text-align:right;min-width:80px;">Jumlah</th>
                    <th style="text-align:right;min-width:100px;">HPP/Unit</th>
                    <th style="text-align:right;min-width:110px;">Klaim/Unit</th>
                    <th style="text-align:right;min-width:100px;">Total HPP</th>
                    <th style="text-align:right;min-width:110px;">Total Klaim</th>
                    <th style="text-align:right;min-width:90px;">Laba</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->records as $sk)
                @php
                    $laba    = $sk->laba;
                    $margin  = $sk->total_pendapatan > 0 ? round(($laba / $sk->total_pendapatan) * 100, 1) : 0;
                @endphp
                <tr>
                    <td class="font-mono" style="font-size:.77rem;color:var(--mut2);">{{ $sk->tanggal_keluar->format('d/m/Y') }}</td>
                    <td>
                        @if($sk->pasien)
                        <div style="font-size:.82rem;font-weight:600;color:var(--ink);">{{ $sk->pasien->nama }}</div>
                        <div style="font-size:.65rem;color:var(--mut);">{{ $sk->pasien->no_bpjs ?? '—' }}</div>
                        @else
                        <span style="color:var(--mut2);font-size:.77rem;">—</span>
                        @endif
                    </td>
                    <td>
                        <div style="font-size:.83rem;font-weight:600;color:var(--ink);">{{ $sk->obat->nama_obat ?? '—' }}</div>
                        <div style="font-size:.65rem;color:var(--mut);">{{ $sk->obat->kategori_diagnosis ?? '' }}</div>
                    </td>
                    <td class="font-mono" style="text-align:right;font-size:.82rem;">
                        {{ number_format($sk->jumlah_unit,0,',','.') }}
                        <span style="color:var(--mut);font-size:.7rem;">{{ $sk->satuan }}</span>
                    </td>
                    <td class="font-mono" style="text-align:right;font-size:.78rem;color:var(--mut2);">{{ number_format($sk->harga_beli_snapshot,0,',','.') }}</td>
                    <td class="font-mono" style="text-align:right;font-size:.78rem;color:var(--emer2);">{{ number_format($sk->harga_jual_per_unit,0,',','.') }}</td>
                    <td class="font-mono" style="text-align:right;font-size:.8rem;color:var(--mut2);">{{ number_format($sk->total_biaya,0,',','.') }}</td>
                    <td class="font-mono" style="text-align:right;font-weight:600;color:var(--emer2);">{{ number_format($sk->total_pendapatan,0,',','.') }}</td>
                    <td class="font-mono" style="text-align:right;font-weight:700;">
                        <div style="color:{{ $laba >= 0 ? 'var(--emer2)' : 'var(--red2)' }};">
                            {{ $laba >= 0 ? '+' : '' }}{{ number_format($laba,0,',','.') }}
                        </div>
                        <div style="font-size:.62rem;color:var(--mut);margin-top:.1rem;">{{ $margin }}%</div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center;padding:3rem 1rem;">
                        <div style="font-size:2.5rem;margin-bottom:.6rem;opacity:.3;">💊</div>
                        <div style="font-size:.85rem;color:var(--mut);font-weight:600;margin-bottom:.3rem;">Belum ada stok keluar kronis</div>
                        <div style="font-size:.72rem;color:var(--mut);">
                            Stok keluar akan muncul otomatis saat obat diserahkan ke pasien di halaman Pengambilan Obat.
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Count bar --}}
    @if($this->records->isNotEmpty())
    <div style="margin-top:.6rem;font-size:.72rem;color:var(--mut);display:flex;align-items:center;gap:.5rem;">
        <span style="width:.5rem;height:.5rem;border-radius:50%;background:var(--emer2);display:inline-block;"></span>
        {{ $this->records->count() }} entri kronis
        @if($filterBulan) · {{ \Carbon\Carbon::createFromFormat('Y-m',$filterBulan)->translatedFormat('F Y') }} @endif
    </div>
    @endif

    @endif {{-- end kronis tab --}}


    {{-- ════════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB: NON KRONIS (Manual entry)                                          --}}
    {{-- ════════════════════════════════════════════════════════════════════════ --}}
    @if($activeTab === 'non_kronis')

    {{-- Manual Entry Form --}}
    @if($showForm)
    <div class="glass-card" style="padding:1.25rem;margin-bottom:1.25rem;border-color:rgba(111,177,224,.3);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.1rem;">
            <div style="display:flex;align-items:center;gap:.6rem;">
                <div style="width:2rem;height:2rem;border-radius:.5rem;background:rgba(111,177,224,.12);border:1px solid rgba(111,177,224,.25);display:flex;align-items:center;justify-content:center;font-size:.85rem;">
                    {{ $editId ? '✏️' : '📤' }}
                </div>
                <div>
                    <div class="font-heading" style="font-size:.9rem;color:var(--blue);">{{ $editId ? 'Edit Entri Stok Keluar' : 'Catat Stok Keluar Non-Kronis' }}</div>
                    <div style="font-size:.65rem;color:var(--mut);">Pengeluaran obat non-kronis / swamedikasi</div>
                </div>
            </div>
            <button wire:click="cancel" style="background:none;border:none;color:var(--mut);cursor:pointer;font-size:1.1rem;width:1.8rem;height:1.8rem;display:flex;align-items:center;justify-content:center;border-radius:.3rem;" title="Tutup">✕</button>
        </div>
        <form wire:submit="save">
            <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:.75rem;margin-bottom:.75rem;">
                <div>
                    <label class="form-label">Nama Obat <span style="color:var(--red2);">*</span></label>
                    <select wire:model.live="obat_id" class="form-input">
                        <option value="0">— Pilih Obat Non-Kronis —</option>
                        @foreach($this->obatList as $o)
                        <option value="{{ $o->id }}">{{ $o->nama_obat }}</option>
                        @endforeach
                    </select>
                    @error('obat_id')<div style="color:var(--red2);font-size:.68rem;margin-top:.2rem;">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label">Tanggal Keluar <span style="color:var(--red2);">*</span></label>
                    <input wire:model="tanggal_keluar" type="date" class="form-input">
                    @error('tanggal_keluar')<div style="color:var(--red2);font-size:.68rem;margin-top:.2rem;">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label">Jumlah Unit <span style="color:var(--red2);">*</span></label>
                    <input wire:model="jumlah_unit" type="number" min="1" class="form-input font-mono">
                    @error('jumlah_unit')<div style="color:var(--red2);font-size:.68rem;margin-top:.2rem;">{{ $message }}</div>@enderror
                </div>
                <div>
                    <label class="form-label">Satuan</label>
                    <input wire:model="satuan" type="text" placeholder="tablet" class="form-input">
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 2fr;gap:.75rem;margin-bottom:1rem;">
                <div>
                    <label class="form-label">Harga Jual / Unit (Rp) <span style="color:var(--red2);">*</span></label>
                    <input wire:model="harga_jual_per_unit" type="number" min="0" step="100" class="form-input font-mono">
                    @error('harga_jual_per_unit')<div style="color:var(--red2);font-size:.68rem;margin-top:.2rem;">{{ $message }}</div>@enderror
                    @if($harga_jual_per_unit > 0 && $jumlah_unit > 0)
                    <div style="font-size:.68rem;color:var(--emer2);margin-top:.25rem;font-family:monospace;">
                        Total: Rp {{ number_format($harga_jual_per_unit * $jumlah_unit, 0, ',', '.') }}
                    </div>
                    @endif
                </div>
                <div>
                    <label class="form-label">Keterangan</label>
                    <input wire:model="keterangan" type="text" placeholder="No. resep / kode pasien / keterangan..." class="form-input">
                </div>
            </div>
            <div style="display:flex;gap:.5rem;padding-top:.75rem;border-top:1px solid rgba(255,255,255,.06);">
                <button type="submit" class="btn-gold">{{ $editId ? 'Perbarui' : 'Simpan' }}</button>
                <button type="button" wire:click="cancel" class="btn-outline">Batal</button>
            </div>
        </form>
    </div>
    @endif

    {{-- Info banner --}}
    @if(!$showForm)
    <div style="background:rgba(111,177,224,.05);border:1px solid rgba(111,177,224,.14);border-radius:.65rem;padding:.75rem 1rem;margin-bottom:1.1rem;display:flex;align-items:center;gap:.75rem;">
        <div style="font-size:.9rem;opacity:.6;">📝</div>
        <div style="font-size:.72rem;color:var(--mut);line-height:1.5;">
            Catat pengeluaran obat <strong style="color:var(--mut2);">non-kronis</strong> / swamedikasi secara manual.
            Stok aktual akan berkurang otomatis saat menyimpan dan dikembalikan saat dihapus.
        </div>
    </div>
    @endif

    {{-- Non-Kronis Table --}}
    <div class="glass-card" style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="min-width:90px;">Tanggal</th>
                    <th style="min-width:180px;">Nama Obat</th>
                    <th style="text-align:right;min-width:80px;">Jumlah</th>
                    <th style="text-align:right;min-width:110px;">Harga Jual/Unit</th>
                    <th style="text-align:right;min-width:110px;">Total Pend.</th>
                    <th style="text-align:right;min-width:100px;">Total Biaya</th>
                    <th style="text-align:right;min-width:90px;">Laba</th>
                    <th style="min-width:140px;">Keterangan</th>
                    <th style="text-align:center;min-width:100px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->records as $sk)
                @php $laba = $sk->laba; @endphp
                <tr>
                    <td class="font-mono" style="font-size:.77rem;color:var(--mut2);">{{ $sk->tanggal_keluar->format('d/m/Y') }}</td>
                    <td>
                        <div style="font-size:.83rem;font-weight:600;color:var(--ink);">{{ $sk->obat->nama_obat ?? '—' }}</div>
                        <div style="font-size:.65rem;color:var(--mut);">non-kronis</div>
                    </td>
                    <td class="font-mono" style="text-align:right;font-size:.82rem;">
                        {{ number_format($sk->jumlah_unit,0,',','.') }}
                        <span style="color:var(--mut);font-size:.7rem;">{{ $sk->satuan }}</span>
                    </td>
                    <td class="font-mono" style="text-align:right;font-size:.78rem;">{{ number_format($sk->harga_jual_per_unit,0,',','.') }}</td>
                    <td class="font-mono" style="text-align:right;font-weight:600;color:var(--emer2);">{{ number_format($sk->total_pendapatan,0,',','.') }}</td>
                    <td class="font-mono" style="text-align:right;font-size:.78rem;color:var(--mut2);">{{ number_format($sk->total_biaya,0,',','.') }}</td>
                    <td class="font-mono" style="text-align:right;font-weight:700;color:{{ $laba >= 0 ? 'var(--emer2)' : 'var(--red2)' }};">
                        {{ $laba >= 0 ? '+' : '' }}{{ number_format($laba,0,',','.') }}
                    </td>
                    <td style="font-size:.75rem;color:var(--mut2);">{{ $sk->keterangan ?? '—' }}</td>
                    <td style="text-align:center;">
                        <div style="display:flex;gap:.3rem;justify-content:center;">
                            <button wire:click="openEdit({{ $sk->id }})" style="background:rgba(217,164,65,.1);border:1px solid rgba(217,164,65,.2);color:var(--gold2);border-radius:.3rem;padding:.2rem .55rem;cursor:pointer;font-size:.7rem;font-weight:600;">Edit</button>
                            <button wire:click="delete({{ $sk->id }})" wire:confirm="Hapus data ini? Stok akan dikembalikan." class="btn-danger" style="padding:.2rem .55rem;font-size:.7rem;">Hapus</button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center;padding:3rem 1rem;">
                        <div style="font-size:2.5rem;margin-bottom:.6rem;opacity:.3;">📦</div>
                        <div style="font-size:.85rem;color:var(--mut);font-weight:600;margin-bottom:.3rem;">Belum ada pencatatan non-kronis</div>
                        <div style="font-size:.72rem;color:var(--mut);margin-bottom:1rem;">
                            Gunakan tombol <strong>+ Catat Keluar</strong> di atas untuk menambah data.
                        </div>
                        <button wire:click="openAdd" style="background:rgba(111,177,224,.1);border:1px solid rgba(111,177,224,.25);color:var(--blue);border-radius:.4rem;padding:.4rem 1rem;cursor:pointer;font-size:.75rem;font-weight:600;">+ Catat Keluar Pertama</button>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($this->records->isNotEmpty())
    <div style="margin-top:.6rem;font-size:.72rem;color:var(--mut);display:flex;align-items:center;gap:.5rem;">
        <span style="width:.5rem;height:.5rem;border-radius:50%;background:var(--blue);display:inline-block;"></span>
        {{ $this->records->count() }} entri non-kronis
        @if($filterBulan) · {{ \Carbon\Carbon::createFromFormat('Y-m',$filterBulan)->translatedFormat('F Y') }} @endif
    </div>
    @endif

    @endif {{-- end non_kronis tab --}}
</div>
