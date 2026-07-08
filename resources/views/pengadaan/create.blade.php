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

    {{-- Gerbang belanja: arahkan ke alur pengajuan yang disetujui manajer --}}
    <div class="glass-card" style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;padding:.75rem 1.1rem;margin-bottom:1.25rem;border-color:rgba(91,155,213,.35);background:linear-gradient(90deg,rgba(91,155,213,.1),transparent);">
        <svg width="18" height="18" fill="none" stroke="#5b9bd5" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M9 15l2 2 4-4"/></svg>
        <div style="flex:1;min-width:220px;font-size:.78rem;color:var(--mut);">
            <strong style="color:var(--ink);">Belanja butuh persetujuan.</strong> Alur standar: buat <strong style="color:#5b9bd5;">Pengajuan Pengadaan</strong> → disetujui manajer → realisasikan jadi PO otomatis. Form manual di bawah untuk input langsung/koreksi.
        </div>
        <a href="{{ route('pengadaan.pengajuan') }}" class="btn-outline" style="white-space:nowrap;">Buka Pengajuan →</a>
    </div>

    <livewire:pengadaan-form />
</x-app-layout>
