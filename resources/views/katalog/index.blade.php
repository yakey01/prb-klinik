<x-app-layout>
    <x-slot name="title">Katalog Obat</x-slot>

    <div style="display:flex; align-items:baseline; justify-content:space-between; margin-bottom:1.5rem; flex-wrap:wrap; gap:.75rem;">
        <div>
            <div class="font-label" style="font-size:.7rem; color:var(--mut); margin-bottom:.25rem;">Manajemen</div>
            <h2 class="font-heading" style="font-size:1.5rem; color:var(--ink); margin:0;">Katalog Obat PRB</h2>
        </div>
        <div style="font-size:.78rem; color:var(--mut);">
            Referensi: <span style="color:var(--gold2);">KMK 730/2025</span> · <span style="color:var(--blue);">PMK 3/2023</span>
        </div>
    </div>

    <livewire:katalog-table />
</x-app-layout>
