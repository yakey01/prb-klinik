<x-app-layout>
    <x-slot name="title">Manajemen Stok</x-slot>

    <div style="margin-bottom:1.5rem;">
        <h1 class="font-heading" style="font-size:1.5rem;color:var(--ink);margin:0 0 .3rem;">Manajemen Stok Obat</h1>
        <p style="color:var(--mut);font-size:.82rem;margin:0;">Monitor stok aktual, stok minimum, dan kadaluarsa semua obat PRB.</p>
    </div>

    <livewire:stok-table />
</x-app-layout>
