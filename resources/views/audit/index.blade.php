<x-app-layout>
    <x-slot name="title">Audit Trail</x-slot>

    <div style="margin-bottom:1.5rem;">
        <h1 class="font-heading" style="font-size:1.5rem;color:var(--ink);margin:0 0 .3rem;">Audit Trail</h1>
        <p style="color:var(--mut);font-size:.82rem;margin:0;">Log semua aktivitas pengguna dan perubahan data di sistem.</p>
    </div>

    <livewire:audit-log />
</x-app-layout>
