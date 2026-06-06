<x-app-layout>
    <x-slot name="title">Tagihan</x-slot>
    <div style="margin-bottom:1.5rem;">
        <div class="font-label" style="font-size:.7rem;color:var(--mut);margin-bottom:.25rem;">Keuangan</div>
        <h2 class="font-heading" style="font-size:1.5rem;color:var(--ink);margin:0;">Manajemen Tagihan</h2>
        <div style="font-size:.78rem;color:var(--mut);margin-top:.3rem;">Tagihan per PO · Split Kronis & Non-Kronis · Tracking Pembayaran</div>
    </div>
    <livewire:tagihan-manager />
</x-app-layout>
