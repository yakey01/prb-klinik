<x-app-layout>
    <x-slot name="title">Daftar Pasien PRB</x-slot>
    <div style="margin-bottom:1.5rem;display:flex;align-items:flex-end;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
        <div>
            <h1 class="font-heading" style="font-size:1.6rem;color:var(--ink);margin:0 0 .3rem;">Daftar Pasien <em style="color:var(--gold2);">PRB</em></h1>
            <p style="font-size:.82rem;color:var(--mut);margin:0;">Kelola data pasien Program Rujuk Balik — pantau jadwal, riwayat, dan persyaratan klaim.</p>
        </div>
        <div style="display:flex;gap:.5rem;">
            <a href="{{ route('pasien.pengambilan') }}" class="btn-gold" style="text-decoration:none;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 12h6m-3-3v6m-7 4h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Catat Pengambilan
            </a>
            <a href="{{ route('pasien.jadwal') }}" class="btn-outline" style="text-decoration:none;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Jadwal
            </a>
        </div>
    </div>
    <livewire:pasien-manager />
</x-app-layout>
