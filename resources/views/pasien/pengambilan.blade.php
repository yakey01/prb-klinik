<x-app-layout>
    <x-slot name="title">Pengambilan Obat PRB</x-slot>
    <div style="margin-bottom:1.5rem;">
        <div style="font-size:.72rem;color:var(--mut);margin-bottom:.3rem;">
            <a href="{{ route('pasien.index') }}" style="color:var(--mut);text-decoration:none;">Pasien</a>
            <span style="margin:0 .4rem;">›</span>
            <span style="color:var(--gold2);">Pengambilan Obat</span>
        </div>
        <h1 class="font-heading" style="font-size:1.6rem;color:var(--ink);margin:0 0 .3rem;">Catat Pengambilan Obat</h1>
        <p style="font-size:.82rem;color:var(--mut);margin:0;">Catat obat yang diambil pasien PRB, lengkapi checklist persyaratan klaim BPJS sebelum menyerahkan obat.</p>
    </div>
    <livewire:pengambilan-obat-form />
</x-app-layout>
