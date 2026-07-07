<div style="display:flex; height:calc(100vh - 120px); gap:0; overflow:hidden; border-radius:.75rem; border:1px solid var(--line);">

    {{-- Sidebar --}}
    <div style="width:300px; flex-shrink:0; background:var(--panel); border-right:1px solid var(--line); overflow-y:auto; display:flex; flex-direction:column;">
        <div style="padding:1rem 1.25rem; border-bottom:1px solid var(--line); display:flex; align-items:center; justify-content:space-between;">
            <div style="font-weight:700; font-size:.95rem;">Kurir Aktif</div>
            <span style="background:rgba(63,207,142,.12); color:var(--emer); border-radius:999px; padding:.1rem .55rem; font-size:.75rem; font-weight:600;">
                {{ $this->aktifList->count() }}
            </span>
        </div>

        @forelse($this->aktifList as $v)
        <button wire:click="selectVisite({{ $v->id }})"
                style="width:100%; text-align:left; background:{{ $selectedVisiteId === $v->id ? 'rgba(255,255,255,.06)' : 'transparent' }}; border:none; border-bottom:1px solid var(--line); padding:.9rem 1.25rem; cursor:pointer; transition:background .15s;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:.3rem;">
                <span style="font-weight:600; color:var(--ink); font-size:.9rem;">{{ $v->kurir?->name }}</span>
                <span style="color:{{ $v->statusColor() }}; font-size:.72rem; font-weight:600;">{{ $v->statusLabel() }}</span>
            </div>
            <div style="font-size:.78rem; color:var(--mut);">{{ $v->pasien?->nama }}</div>
            <div style="font-size:.75rem; color:var(--mut2); margin-top:.1rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $v->alamat_tujuan }}</div>
        </button>
        @empty
        <div style="padding:2rem 1.25rem; text-align:center; color:var(--mut); font-size:.88rem;">
            Tidak ada kurir sedang bertugas.
        </div>
        @endforelse

        <div style="margin-top:auto; padding:.75rem 1.25rem; border-top:1px solid var(--line);">
            <a href="{{ route('visite.index') }}"
               style="display:block; text-align:center; background:rgba(255,255,255,.05); border:1px solid var(--line2); color:var(--mut); border-radius:.5rem; padding:.6rem; font-size:.82rem; text-decoration:none;">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg> Kembali ke Daftar
            </a>
        </div>
    </div>

    {{-- Map container --}}
    <div style="flex:1; position:relative;"
         x-data="{
             map: null,
             markers: {},
             polylines: {},
             destMarkers: {},
             pollTimer: null,
             selectedId: @entangle('selectedVisiteId'),

             init() {
                 this.$nextTick(() => {
                     this.map = L.map(this.$refs.mapEl, { zoomControl: true }).setView([-7.816, 112.016], 13);
                     L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                         attribution: '© <a href=\"https://openstreetmap.org\">OpenStreetMap</a>',
                         maxZoom: 19,
                     }).addTo(this.map);
                     this.startPolling();
                 });
             },

             startPolling() {
                 this.pollAll();
                 this.pollTimer = setInterval(() => this.pollAll(), 5000);
             },

             async pollAll() {
                 try {
                     const resp = await fetch('/api/visite/active-list', {
                         headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
                     });
                     if (!resp.ok) return;
                     const visites = await resp.json();
                     visites.forEach(v => this.updateMarker(v));
                 } catch {}
             },

             updateMarker(v) {
                 if (!v.lat || !v.lng) return;
                 const latlng = [v.lat, v.lng];
                 const icon   = this.makeIcon(v.status);

                 if (this.markers[v.id]) {
                     this.markers[v.id].setLatLng(latlng).setIcon(icon);
                     this.markers[v.id].setPopupContent(this.makePopup(v));
                 } else {
                     this.markers[v.id] = L.marker(latlng, { icon })
                         .addTo(this.map)
                         .bindPopup(this.makePopup(v));
                 }

                 // Destination marker (static pin)
                 if (v.lat_tujuan && v.lng_tujuan && !this.destMarkers[v.id]) {
                     const destIcon = L.divIcon({
                         html: '<div style=\"width:14px;height:14px;border-radius:50%;background:#6fb1e0;border:3px solid white;box-shadow:0 1px 6px rgba(0,0,0,.4);\"></div>',
                         iconSize: [14, 14], className: '',
                     });
                     this.destMarkers[v.id] = L.marker([v.lat_tujuan, v.lng_tujuan], { icon: destIcon })
                         .addTo(this.map)
                         .bindPopup('<b>Tujuan</b><br>' + v.pasien_nama);
                 }

                 // Append to live trail polyline
                 if (!this.polylines[v.id]) {
                     this.polylines[v.id] = { points: [], line: null };
                 }
                 this.polylines[v.id].points.push(latlng);
                 if (this.polylines[v.id].line) {
                     this.polylines[v.id].line.setLatLngs(this.polylines[v.id].points);
                 } else {
                     this.polylines[v.id].line = L.polyline(
                         this.polylines[v.id].points,
                         { color: '#d9a441', weight: 3, opacity: .7, dashArray: '6 4' }
                     ).addTo(this.map);
                 }

                 // Pan to selected
                 if (this.selectedId == v.id) {
                     this.map.setView(latlng, Math.max(this.map.getZoom(), 15));
                 }
             },

             makeIcon(status) {
                 const colors = { dalam_perjalanan: '#d9a441', sampai: '#3fcf8e', ditugaskan: '#6fb1e0' };
                 const c = colors[status] || '#8fae9f';
                 return L.divIcon({
                     html: `<div style='background:${c};width:16px;height:16px;border-radius:50%;border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,.5);'></div>`,
                     iconSize: [16, 16], className: '',
                 });
             },

             makePopup(v) {
                 return `<div style='font-family:sans-serif;min-width:150px;'>
                     <b style='color:#0a1410;'>${v.karyawan_nama}</b><br>
                     <span style='color:#444;font-size:.85em;'>${v.pasien_nama}</span><br>
                     <span style='font-size:.8em;color:#666;'>${v.status_label}</span>
                     ${v.updated_at ? '<br><span style=\"font-size:.75em;color:#888;\">Update: '+new Date(v.updated_at).toLocaleTimeString('id-ID')+'</span>' : ''}
                 </div>`;
             },

             destroy() {
                 if (this.pollTimer) clearInterval(this.pollTimer);
             }
         }"
         x-init="init()"
         @navigate.away="destroy()"
    >
        <div x-ref="mapEl" style="width:100%; height:100%;"></div>

        {{-- Legend --}}
        <div style="position:absolute; bottom:1rem; right:1rem; background:rgba(17,36,28,.9); border:1px solid var(--line); border-radius:.5rem; padding:.6rem .85rem; font-size:.75rem; z-index:1000;">
            <div style="display:flex; align-items:center; gap:.5rem; margin-bottom:.3rem;">
                <span style="width:10px;height:10px;border-radius:50%;background:#d9a441;display:inline-block;"></span>
                <span style="color:var(--mut);">Dalam Perjalanan</span>
            </div>
            <div style="display:flex; align-items:center; gap:.5rem; margin-bottom:.3rem;">
                <span style="width:10px;height:10px;border-radius:50%;background:#3fcf8e;display:inline-block;"></span>
                <span style="color:var(--mut);">Sudah Sampai</span>
            </div>
            <div style="display:flex; align-items:center; gap:.5rem;">
                <span style="width:10px;height:10px;border-radius:50%;background:#6fb1e0;display:inline-block;"></span>
                <span style="color:var(--mut);">Tujuan</span>
            </div>
        </div>
    </div>
</div>
