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

    <livewire:pengadaan-form />
</x-app-layout>
