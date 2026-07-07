<x-app-layout>
    <x-slot name="header">
        <h2 style="font-size:1.1rem; font-weight:600; color:var(--ink);">Riwayat Visite #{{ $visite->id }}</h2>
    </x-slot>

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @endpush

    <div style="max-width:1000px; margin:0 auto; padding:1.5rem;">

        {{-- Info card --}}
        <div class="glass-card" style="padding:1.25rem; margin-bottom:1.25rem; display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:1rem;">
            <div>
                <div style="font-size:.72rem; color:var(--mut); text-transform:uppercase; letter-spacing:.06em; margin-bottom:.25rem;">Pasien</div>
                <div style="font-weight:600;">{{ $visite->pasien?->nama }}</div>
            </div>
            <div>
                <div style="font-size:.72rem; color:var(--mut); text-transform:uppercase; letter-spacing:.06em; margin-bottom:.25rem;">Karyawan</div>
                <div style="font-weight:600;">{{ $visite->kurir?->name }}</div>
            </div>
            <div>
                <div style="font-size:.72rem; color:var(--mut); text-transform:uppercase; letter-spacing:.06em; margin-bottom:.25rem;">Tanggal</div>
                <div style="font-weight:600;">{{ $visite->tanggal_visite->format('d M Y') }}</div>
            </div>
            <div>
                <div style="font-size:.72rem; color:var(--mut); text-transform:uppercase; letter-spacing:.06em; margin-bottom:.25rem;">Status</div>
                <span style="background:{{ $visite->statusColor() }}22; color:{{ $visite->statusColor() }}; border:1px solid {{ $visite->statusColor() }}44; border-radius:999px; padding:.2rem .7rem; font-size:.8rem; font-weight:600;">
                    {{ $visite->statusLabel() }}
                </span>
            </div>
            @if($visite->started_at)
            <div>
                <div style="font-size:.72rem; color:var(--mut); text-transform:uppercase; letter-spacing:.06em; margin-bottom:.25rem;">Mulai</div>
                <div style="font-weight:600;">{{ $visite->started_at->format('H:i') }}</div>
            </div>
            @endif
            @if($visite->completed_at)
            <div>
                <div style="font-size:.72rem; color:var(--mut); text-transform:uppercase; letter-spacing:.06em; margin-bottom:.25rem;">Selesai</div>
                <div style="font-weight:600;">{{ $visite->completed_at->format('H:i') }}</div>
            </div>
            <div>
                <div style="font-size:.72rem; color:var(--mut); text-transform:uppercase; letter-spacing:.06em; margin-bottom:.25rem;">Durasi</div>
                <div style="font-weight:600;">{{ $visite->started_at->diffForHumans($visite->completed_at, true) }}</div>
            </div>
            @endif
            <div>
                <div style="font-size:.72rem; color:var(--mut); text-transform:uppercase; letter-spacing:.06em; margin-bottom:.25rem;">Total Titik GPS</div>
                <div style="font-weight:600;">{{ $tracks->count() }}</div>
            </div>
            @if($totalJarak > 0)
            <div>
                <div style="font-size:.72rem; color:var(--mut); text-transform:uppercase; letter-spacing:.06em; margin-bottom:.25rem;">Jarak Tempuh</div>
                <div style="font-weight:600;">{{ number_format($totalJarak / 1000, 2) }} km</div>
            </div>
            @endif
        </div>

        {{-- Map --}}
        @if($tracks->isNotEmpty())
        <div class="glass-card" style="overflow:hidden; margin-bottom:1.25rem;">
            <div id="riwayat-map" style="height:400px; width:100%;"></div>
        </div>
        @endif

        {{-- Catatan --}}
        @if($visite->catatan_karyawan)
        <div class="glass-card" style="padding:1rem 1.25rem; margin-bottom:1.25rem;">
            <div style="font-size:.72rem; color:var(--mut); text-transform:uppercase; letter-spacing:.06em; margin-bottom:.4rem;">Catatan Karyawan</div>
            <div style="font-size:.88rem; color:var(--ink);">{{ $visite->catatan_karyawan }}</div>
        </div>
        @endif

        {{-- Tombol kembali --}}
        <a href="{{ route('visite.index') }}"
           style="display:inline-flex; align-items:center; gap:.4rem; background:rgba(255,255,255,.05); border:1px solid var(--line2); color:var(--mut); border-radius:.5rem; padding:.55rem 1rem; font-size:.85rem; text-decoration:none;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg> Kembali ke Daftar
        </a>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @if($tracks->isNotEmpty())
    <script>
    (function() {
        const tracks = @json($tracks->map(fn($t) => [$t->latitude, $t->longitude]));
        const dest   = @json($visite->lat_tujuan && $visite->lng_tujuan ? [$visite->lat_tujuan, $visite->lng_tujuan] : null);

        const map = L.map('riwayat-map').setView(tracks[0], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap', maxZoom: 19
        }).addTo(map);

        // Route polyline
        const polyline = L.polyline(tracks, { color: '#d9a441', weight: 4, opacity: .85 }).addTo(map);
        map.fitBounds(polyline.getBounds(), { padding: [30, 30] });

        // Start marker
        const startIcon = L.divIcon({
            html: '<div style="background:#3fcf8e;width:14px;height:14px;border-radius:50%;border:3px solid white;box-shadow:0 2px 6px rgba(0,0,0,.4);"></div>',
            iconSize: [14, 14], className: '',
        });
        L.marker(tracks[0], { icon: startIcon }).addTo(map).bindPopup('Mulai Perjalanan');

        // End marker
        if (tracks.length > 1) {
            const endIcon = L.divIcon({
                html: '<div style="background:#e8645a;width:14px;height:14px;border-radius:50%;border:3px solid white;box-shadow:0 2px 6px rgba(0,0,0,.4);"></div>',
                iconSize: [14, 14], className: '',
            });
            L.marker(tracks[tracks.length - 1], { icon: endIcon }).addTo(map).bindPopup('Posisi Terakhir');
        }

        // Destination marker
        if (dest) {
            const destIcon = L.divIcon({
                html: '<div style="background:#6fb1e0;width:14px;height:14px;border-radius:50%;border:3px solid white;box-shadow:0 2px 6px rgba(0,0,0,.4);"></div>',
                iconSize: [14, 14], className: '',
            });
            L.marker(dest, { icon: destIcon }).addTo(map).bindPopup('Tujuan: {{ addslashes($visite->alamat_tujuan) }}');
        }
    })();
    </script>
    @endif
    @endpush
</x-app-layout>
