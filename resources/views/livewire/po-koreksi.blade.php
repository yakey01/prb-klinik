<div>
    @php $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.'); @endphp

    {{-- Tombol pemicu di-render oleh halaman Riwayat via dispatch; modal di sini --}}
    @if($show && $this->po)
    @php $po = $this->po; $r = $this->ringkas; @endphp
    <div style="position:fixed;inset:0;z-index:600;display:flex;align-items:flex-start;justify-content:center;padding:1.5rem;overflow-y:auto;">
        <div wire:click="tutup" style="position:fixed;inset:0;background:rgba(3,8,6,.82);backdrop-filter:blur(5px);"></div>
        <div class="glass-card" style="position:relative;width:100%;max-width:840px;padding:1.5rem;border:1px solid rgba(217,164,65,.4);box-shadow:0 30px 80px rgba(0,0,0,.7);">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1rem;">
                <div>
                    <div class="font-heading" style="font-size:1.05rem;color:var(--gold2);">✎ Koreksi Purchase Order #{{ $po->id }}</div>
                    <div style="font-size:.72rem;color:var(--mut);margin-top:.2rem;line-height:1.5;max-width:64ch;">
                        Perbaiki qty/harga/expiry saat barang atau harga faktur tidak sesuai. Koreksi <strong style="color:var(--gold2);">butuh persetujuan manajer di SIM</strong> — stok &amp; tagihan diselaraskan setelah disetujui.
                    </div>
                </div>
                <button wire:click="tutup" style="background:none;border:none;color:var(--mut);cursor:pointer;font-size:1.2rem;">✕</button>
            </div>

            {{-- Info + faktur --}}
            <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1rem;align-items:flex-end;">
                <div style="flex:1;min-width:200px;background:rgba(255,255,255,.04);border:1px solid var(--line);border-radius:.6rem;padding:.6rem .8rem;font-size:.74rem;">
                    <div style="display:flex;justify-content:space-between;"><span style="color:var(--mut);">Distributor</span><span>{{ $po->distributor->name ?? '—' }}</span></div>
                    <div style="display:flex;justify-content:space-between;margin-top:.2rem;"><span style="color:var(--mut);">Total lama</span><span class="font-mono">{{ $rp($r['lama']) }}</span></div>
                </div>
                <div style="min-width:170px;">
                    <label style="font-size:.6rem;color:var(--mut);text-transform:uppercase;">Nomor Faktur / Invoice</label>
                    <input wire:model="nomorFaktur" type="text" placeholder="mis. INV-2026-0123" style="width:100%;margin-top:.25rem;padding:.5rem .7rem;border-radius:.55rem;background:var(--card);border:1px solid var(--line2);color:var(--ink);font-size:.8rem;">
                </div>
                <div style="width:150px;">
                    <label style="font-size:.6rem;color:var(--mut);text-transform:uppercase;">Tanggal PO</label>
                    <input wire:model="tanggalPo" type="date" style="width:100%;margin-top:.25rem;padding:.5rem .4rem;border-radius:.55rem;background:var(--card);border:1px solid var(--line2);color:var(--ink);font-size:.8rem;">
                </div>
            </div>

            {{-- Peringatan bila tagihan sudah dibayar --}}
            @if($r['sudah_dibayar'] > 0)
            <div style="font-size:.72rem;color:var(--red2);background:rgba(232,100,90,.1);border:1px solid rgba(232,100,90,.3);border-radius:.5rem;padding:.5rem .8rem;margin-bottom:.8rem;">
                ⚠ Tagihan PO ini sudah dibayar {{ $rp($r['sudah_dibayar']) }}. Koreksi total akan menghitung ulang sisa/status pembayaran — pastikan sesuai bukti.
            </div>
            @endif

            {{-- Tabel item editable --}}
            <div style="overflow-x:auto;margin-bottom:.8rem;">
            <table style="width:100%;border-collapse:collapse;font-size:.74rem;min-width:680px;">
                <thead><tr style="color:var(--mut);">
                    <th style="text-align:left;padding:.3rem .4rem;font-size:.56rem;text-transform:uppercase;">Obat</th>
                    <th style="text-align:right;padding:.3rem .4rem;font-size:.56rem;text-transform:uppercase;">Box</th>
                    <th style="text-align:right;padding:.3rem .4rem;font-size:.56rem;text-transform:uppercase;">Isi/Box</th>
                    <th style="text-align:right;padding:.3rem .4rem;font-size:.56rem;text-transform:uppercase;">Harga/Box</th>
                    <th style="text-align:right;padding:.3rem .4rem;font-size:.56rem;text-transform:uppercase;">Kadaluarsa</th>
                    <th style="text-align:right;padding:.3rem .4rem;font-size:.56rem;text-transform:uppercase;">Subtotal</th>
                    <th></th>
                </tr></thead>
                <tbody>
                    @foreach($rows as $i => $row)
                    @php
                        $isK = ($row['tipe_obat'] ?? '') === 'kronis';
                        $bedaBox = (int)($row['jumlah_box']??0) !== (int)($row['ori_box']??0);
                        $bedaHarga = (float)($row['harga_per_box']??0) != (float)($row['ori_harga']??0);
                        $sub = (int)($row['jumlah_box']??0) * (float)($row['harga_per_box']??0);
                    @endphp
                    <tr wire:key="korr-{{ $i }}" style="border-top:1px solid rgba(31,61,48,.4);{{ ($row['hapus']??false) ? 'opacity:.45;' : '' }}">
                        <td style="padding:.3rem .4rem;">
                            @if(empty($row['item_id']))
                                {{-- ITEM BARU: pilih obat --}}
                                <select wire:model.live="rows.{{ $i }}.obat_id" @disabled($row['hapus']??false)
                                    style="width:210px;padding:.3rem;border-radius:.4rem;background:var(--card);border:1px solid var(--emer);color:var(--ink);font-size:.72rem;">
                                    <option value="0">— pilih obat —</option>
                                    @foreach($this->obatList as $o)
                                    <option value="{{ $o->id }}">{{ $o->nama_obat }}{{ $o->tipe_obat==='bmhp' ? ' (BMHP)' : '' }}</option>
                                    @endforeach
                                </select>
                                <div style="display:flex;align-items:center;gap:.3rem;margin-top:.15rem;">
                                    <span style="font-size:.52rem;font-weight:800;padding:.03rem .3rem;border-radius:999px;background:rgba(63,207,142,.16);border:1px solid rgba(63,207,142,.4);color:var(--emer2);">+ BARU</span>
                                    @if(!empty($row['nama_obat']))
                                    <span style="font-size:.56rem;font-weight:700;color:{{ $isK ? '#8fbdf5' : '#f2c14e' }};">{{ $isK ? 'KRONIS' : 'NON-KRONIS' }}</span>
                                    @endif
                                </div>
                            @else
                                <div style="color:var(--ink);">{{ $row['nama_obat'] }}</div>
                                <span style="font-size:.56rem;font-weight:700;color:{{ $isK ? '#8fbdf5' : '#f2c14e' }};">{{ $isK ? 'KRONIS' : 'NON-KRONIS' }}</span>
                            @endif
                        </td>
                        <td style="padding:.3rem .4rem;">
                            <input type="number" min="0" wire:model.live.debounce.400ms="rows.{{ $i }}.jumlah_box" @disabled($row['hapus']??false)
                                style="width:60px;padding:.3rem;border-radius:.4rem;background:var(--card);border:1px solid {{ $bedaBox ? 'rgba(217,164,65,.5)' : 'var(--line2)' }};color:{{ $bedaBox ? 'var(--gold2)' : 'var(--ink)' }};font-size:.74rem;text-align:right;">
                            @if($bedaBox)<div style="font-size:.54rem;color:var(--mut2);text-align:right;">dari {{ $row['ori_box'] }}</div>@endif
                        </td>
                        <td style="padding:.3rem .4rem;"><input type="number" min="1" wire:model.live.debounce.400ms="rows.{{ $i }}.isi_per_box" @disabled($row['hapus']??false) style="width:54px;padding:.3rem;border-radius:.4rem;background:var(--card);border:1px solid var(--line2);color:var(--ink);font-size:.74rem;text-align:right;"></td>
                        <td style="padding:.3rem .4rem;">
                            <input type="number" min="0" wire:model.live.debounce.400ms="rows.{{ $i }}.harga_per_box" @disabled($row['hapus']??false)
                                style="width:100px;padding:.3rem;border-radius:.4rem;background:var(--card);border:1px solid {{ $bedaHarga ? 'rgba(217,164,65,.5)' : 'var(--line2)' }};color:{{ $bedaHarga ? 'var(--gold2)' : 'var(--ink)' }};font-size:.74rem;text-align:right;">
                            @if($bedaHarga)<div style="font-size:.54rem;color:var(--mut2);text-align:right;">dari {{ $rp($row['ori_harga']) }}</div>@endif
                        </td>
                        <td style="padding:.3rem .4rem;"><input type="date" wire:model="rows.{{ $i }}.tanggal_kadaluarsa" @disabled($row['hapus']??false) style="width:130px;padding:.3rem;border-radius:.4rem;background:var(--card);border:1px solid var(--line2);color:var(--ink);font-size:.68rem;"></td>
                        <td class="font-mono" style="padding:.3rem .4rem;text-align:right;color:var(--red2);">{{ $rp($sub) }}</td>
                        <td style="padding:.3rem .4rem;text-align:center;">
                            @if($row['hapus']??false)
                            <button type="button" wire:click="$set('rows.{{ $i }}.hapus', false)" title="Batal hapus" style="background:none;border:none;color:var(--emer2);cursor:pointer;font-size:.7rem;">↺</button>
                            @else
                            <button type="button" wire:click="$set('rows.{{ $i }}.hapus', true)" title="Hapus item (stok ditarik kembali)" style="background:none;border:none;color:var(--red2);cursor:pointer;font-size:.9rem;">✕</button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>

            {{-- Tambah item obat baru (barang datang tapi belum tercatat di PO) --}}
            <div style="margin:-.4rem 0 .8rem;">
                <button type="button" wire:click="tambahItem"
                    style="display:inline-flex;align-items:center;gap:.35rem;font-size:.7rem;font-weight:700;padding:.35rem .75rem;border-radius:.5rem;cursor:pointer;background:rgba(63,207,142,.1);border:1px solid rgba(63,207,142,.35);color:var(--emer2);">
                    + Tambah Item Obat
                </button>
                <span style="font-size:.62rem;color:var(--mut);margin-left:.5rem;">untuk obat yang datang tapi belum ada di PO — stok & tagihan ikut disesuaikan setelah ACC manajer</span>
            </div>

            {{-- Ringkasan selisih --}}
            <div style="display:flex;flex-wrap:wrap;gap:.6rem;justify-content:flex-end;margin-bottom:.9rem;align-items:center;">
                <div style="text-align:right;"><div style="font-size:.56rem;color:var(--mut);text-transform:uppercase;">Total Lama</div><div class="font-mono" style="font-size:.85rem;color:var(--mut);">{{ $rp($r['lama']) }}</div></div>
                <div style="text-align:right;"><div style="font-size:.56rem;color:var(--mut);text-transform:uppercase;">Total Baru</div><div class="font-mono" style="font-size:.95rem;font-weight:800;color:var(--red2);">{{ $rp($r['baru']) }}</div></div>
                @if(abs($r['selisih']) >= 1)
                <div style="text-align:right;padding:.3rem .8rem;border-radius:.6rem;background:rgba(217,164,65,.1);border:1px solid rgba(217,164,65,.35);">
                    <div style="font-size:.56rem;color:var(--gold2);text-transform:uppercase;">Selisih</div>
                    <div class="font-mono" style="font-size:.9rem;font-weight:800;color:var(--gold2);">{{ ($r['selisih']>=0?'+':'−').$rp(abs($r['selisih'])) }}</div>
                </div>
                @endif
            </div>

            {{-- Alasan koreksi (wajib) --}}
            <div style="margin-bottom:1rem;">
                <label style="font-size:.62rem;color:var(--mut);text-transform:uppercase;">Alasan Koreksi <span style="color:var(--red2);">*</span></label>
                <input wire:model="alasan" type="text" placeholder="mis. barang kurang kirim 3 box, harga naik per faktur…" style="width:100%;margin-top:.25rem;padding:.5rem .7rem;border-radius:.55rem;background:var(--card);border:1px solid var(--line2);color:var(--ink);font-size:.8rem;">
                @error('alasan')<div style="color:var(--red2);font-size:.66rem;margin-top:.2rem;">{{ $message }}</div>@enderror
            </div>

            <div style="display:flex;gap:.6rem;justify-content:flex-end;">
                <button wire:click="tutup" style="padding:.6rem 1.1rem;border-radius:.6rem;background:transparent;border:1px solid var(--line2);color:var(--mut);cursor:pointer;font-size:.8rem;">Batal</button>
                <button wire:click="simpan" wire:confirm="Ajukan koreksi ini ke manajer SIM untuk disetujui? Stok & tagihan belum berubah sampai disetujui." style="padding:.6rem 1.3rem;border-radius:.6rem;background:linear-gradient(180deg,rgba(217,164,65,.95),rgba(217,164,65,.78));border:1px solid rgba(217,164,65,.5);color:#1a0e00;cursor:pointer;font-size:.8rem;font-weight:800;">📤 Ajukan Koreksi ke Manajer →</button>
            </div>
        </div>
    </div>
    @endif
</div>
