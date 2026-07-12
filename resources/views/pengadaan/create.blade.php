<x-app-layout>
    <x-slot name="title">Pengadaan Baru</x-slot>

    <div style="display:flex; align-items:baseline; justify-content:space-between; margin-bottom:1.5rem; flex-wrap:wrap; gap:.75rem;">
        <div>
            <div class="font-label" style="font-size:.7rem; color:var(--mut); margin-bottom:.25rem;">Input</div>
            <h2 class="font-heading" style="font-size:1.5rem; color:var(--ink); margin:0;">Pengadaan Obat Baru</h2>
        </div>
        <a href="{{ route('riwayat.index') }}" class="btn-outline">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            Riwayat PO
        </a>
    </div>

    {{-- ALUR UTAMA: Pengajuan → disetujui manajer SIM → Input Faktur → PO --}}
    <div class="glass-card" style="padding:1.1rem 1.3rem;margin-bottom:1.25rem;border-color:rgba(63,207,142,.4);background:linear-gradient(120deg,rgba(63,207,142,.1),rgba(91,155,213,.06),transparent);">
        <div style="display:flex;align-items:center;gap:.9rem;flex-wrap:wrap;">
            <div style="width:42px;height:42px;border-radius:12px;flex-shrink:0;display:flex;align-items:center;justify-content:center;background:linear-gradient(160deg,rgba(63,207,142,.3),rgba(63,207,142,.08));border:1px solid rgba(63,207,142,.35);">
                <svg width="22" height="22" fill="none" stroke="var(--emer2)" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M9 15l2 2 4-4"/></svg>
            </div>
            <div style="flex:1;min-width:240px;">
                <div style="font-size:.62rem;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:var(--emer2);">Alur Utama Belanja</div>
                <div style="font-size:.84rem;color:var(--ink);font-weight:600;margin-top:.15rem;">Pengajuan Pengadaan → disetujui <span style="color:#5b9bd5;">manajer di SIM</span> → Input Faktur → PO</div>
                <div style="font-size:.72rem;color:var(--mut);margin-top:.25rem;line-height:1.5;">Standar izin belanja klinik. Saat barang datang, <strong style="color:var(--emer2);">Input Faktur</strong> menarik data dari pengajuan disetujui &amp; bisa disesuaikan bila qty/harga berbeda.</div>
            </div>
            <a href="{{ route('pengadaan.pengajuan') }}" class="btn-emerald" style="white-space:nowrap;display:inline-flex;align-items:center;gap:.5rem;padding:.65rem 1.2rem;border-radius:.7rem;background:linear-gradient(180deg,rgba(63,207,142,.9),rgba(63,207,142,.75));border:1px solid rgba(63,207,142,.5);color:#04150d;font-weight:800;font-size:.82rem;text-decoration:none;box-shadow:0 6px 18px rgba(63,207,142,.2);">Buka Pengajuan Pengadaan →</a>
        </div>
    </div>

    {{-- FORM MANUAL — hanya untuk KOREKSI / kondisi khusus (collapsed default) --}}
    <details style="margin-bottom:1rem;">
        <summary style="cursor:pointer;list-style:none;display:flex;align-items:center;gap:.6rem;padding:.75rem 1.1rem;border-radius:.8rem;background:rgba(217,164,65,.06);border:1px solid rgba(217,164,65,.28);color:var(--gold2);font-size:.8rem;font-weight:700;">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            Input Manual PO <span style="color:var(--mut);font-weight:500;">— hanya untuk koreksi / kondisi khusus (tanpa pengajuan)</span>
            <span style="margin-left:auto;font-size:.68rem;color:var(--mut2);">klik untuk buka ▾</span>
        </summary>
        <div style="margin-top:1rem;">
            <div style="font-size:.72rem;color:var(--gold2);background:rgba(217,164,65,.08);border:1px solid rgba(217,164,65,.25);border-radius:.6rem;padding:.6rem .9rem;margin-bottom:1rem;line-height:1.5;">
                ⚠ Form ini <strong>melewati persetujuan manajer</strong>. Gunakan hanya untuk koreksi data atau pembelian darurat yang sudah disepakati. Alur normal tetap lewat <strong>Pengajuan Pengadaan</strong> di atas.
            </div>
            <livewire:pengadaan-form />
        </div>
    </details>
</x-app-layout>
