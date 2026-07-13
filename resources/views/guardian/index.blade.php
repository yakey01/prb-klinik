<x-app-layout>
    <x-slot name="title">Guardian AI</x-slot>
    <div style="margin-bottom:1.25rem;">
        <div class="font-label" style="font-size:.7rem;color:var(--mut);margin-bottom:.25rem;">Kontrol Mutu · AI</div>
        <h2 class="font-heading" style="font-size:1.5rem;color:var(--ink);margin:0;">Pharmacy Guardian AI</h2>
        <div style="font-size:.78rem;color:var(--mut);margin-top:.3rem;">Deteksi otomatis ketidaksesuaian Riwayat PO ↔ Tagihan agar tidak tertukar — dengan konfirmasi manusia.</div>
    </div>
    <livewire:guardian-review />
</x-app-layout>
