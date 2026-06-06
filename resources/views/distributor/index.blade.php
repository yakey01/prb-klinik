<x-app-layout>
    <x-slot name="title">Distributor / PBF</x-slot>

    <div style="display:flex; align-items:baseline; justify-content:space-between; margin-bottom:1.5rem;">
        <div>
            <div class="font-label" style="font-size:.7rem; color:var(--mut); margin-bottom:.25rem;">Master Data</div>
            <h2 class="font-heading" style="font-size:1.5rem; color:var(--ink); margin:0;">
                Distributor / PBF
            </h2>
        </div>
        <div style="font-size:.78rem; color:var(--mut2);">Pedagang Besar Farmasi yang bekerjasama</div>
    </div>

    <livewire:distributor-manager />
</x-app-layout>
