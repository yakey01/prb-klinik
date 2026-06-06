<x-app-layout>
    <x-slot name="title">Jadwal Pasien PRB</x-slot>
    <div style="margin-bottom:1.5rem;">
        <div style="font-size:.72rem;color:var(--mut);margin-bottom:.3rem;">
            <a href="{{ route('pasien.index') }}" style="color:var(--mut);text-decoration:none;">Pasien</a>
            <span style="margin:0 .4rem;">›</span>
            <span style="color:var(--gold2);">Jadwal & Reminder</span>
        </div>
        <h1 class="font-heading" style="font-size:1.6rem;color:var(--ink);margin:0 0 .3rem;">Jadwal Pengambilan Obat</h1>
        <p style="font-size:.82rem;color:var(--mut);margin:0;">Pantau jadwal pengambilan obat pasien, tandai yang terlambat, dan kirim pengingat.</p>
    </div>
    <livewire:pasien-jadwal />
</x-app-layout>
