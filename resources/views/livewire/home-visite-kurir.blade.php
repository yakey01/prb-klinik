<div
    x-data="{
        visiteId: null,
        tracking: false,
        watchId: null,
        lat: null,
        lng: null,
        accuracy: null,
        battery: null,
        errorMsg: null,
        offlineQueue: [],
        isOnline: navigator.onLine,

        init() {
            window.addEventListener('online',  () => { this.isOnline = true; this.drainQueue(); });
            window.addEventListener('offline', () => { this.isOnline = false; });
        },

        startTracking(id) {
            this.visiteId = id;
            this.tracking = true;
            this.errorMsg = null;
            this.loadQueue(id);

            if ('getBattery' in navigator) {
                navigator.getBattery().then(b => { this.battery = Math.round(b.level * 100); });
            }

            if (!navigator.geolocation) {
                this.errorMsg = 'Perangkat tidak mendukung GPS';
                return;
            }

            this.watchId = navigator.geolocation.watchPosition(
                pos => this.onPosition(pos),
                err => this.onError(err),
                { enableHighAccuracy: true, timeout: 15000, maximumAge: 5000 }
            );
        },

        stopTracking() {
            if (this.watchId !== null) {
                navigator.geolocation.clearWatch(this.watchId);
                this.watchId = null;
            }
            this.tracking = false;
            this.visiteId = null;
        },

        onPosition(pos) {
            this.lat      = pos.coords.latitude;
            this.lng      = pos.coords.longitude;
            this.accuracy = Math.round(pos.coords.accuracy);
            const point = {
                latitude:      pos.coords.latitude,
                longitude:     pos.coords.longitude,
                accuracy:      pos.coords.accuracy,
                speed:         pos.coords.speed,
                battery_level: this.battery,
                recorded_at:   new Date(pos.timestamp).toISOString(),
            };
            this.sendPoint(point);
        },

        async sendPoint(point) {
            await this.drainQueue();
            try {
                await this.postPoint(point);
            } catch {
                this.offlineQueue.push(point);
                if (this.offlineQueue.length > 50) this.offlineQueue.shift();
                this.saveQueue();
            }
        },

        async drainQueue() {
            while (this.offlineQueue.length > 0 && this.isOnline) {
                const p = this.offlineQueue[0];
                try {
                    await this.postPoint(p);
                    this.offlineQueue.shift();
                    this.saveQueue();
                } catch { break; }
            }
        },

        async postPoint(point) {
            const resp = await fetch(`/api/visite/${this.visiteId}/track`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: JSON.stringify(point),
            });
            if (!resp.ok) throw new Error('HTTP ' + resp.status);
        },

        saveQueue() {
            if (this.visiteId) {
                localStorage.setItem('visite_q_' + this.visiteId, JSON.stringify(this.offlineQueue));
            }
        },

        loadQueue(id) {
            const raw = localStorage.getItem('visite_q_' + id);
            if (raw) { try { this.offlineQueue = JSON.parse(raw); } catch {} }
        },

        onError(err) {
            this.errorMsg = { 1:'Izin GPS ditolak', 2:'GPS tidak tersedia', 3:'Timeout GPS' }[err.code] || 'Error GPS';
        },

        accuracyColor() {
            if (!this.accuracy) return 'var(--mut)';
            if (this.accuracy <= 10) return 'var(--emer)';
            if (this.accuracy <= 30) return 'var(--gold)';
            return 'var(--red)';
        }
    }"
    @visite-started.window="startTracking($event.detail.visiteId)"
    @visite-selesai.window="stopTracking()"
>

    {{-- Offline banner --}}
    <div x-show="!isOnline" x-cloak
         style="background:rgba(232,100,90,.15); border:1px solid rgba(232,100,90,.35); border-radius:.6rem; padding:.6rem 1rem; margin-bottom:1rem; font-size:.85rem; color:var(--red2); display:flex; align-items:center; gap:.5rem;">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 1l22 22M16.72 11.06A10.94 10.94 0 0119 12.55M5 12.55a10.94 10.94 0 015.17-2.39M10.71 5.05A16 16 0 0122.56 9M1.42 9a15.91 15.91 0 014.7-2.88M8.53 16.11a6 6 0 016.95 0M12 20h.01"/></svg>
        Tidak ada koneksi — titik GPS disimpan lokal, akan dikirim saat online
    </div>

    {{-- GPS status bar --}}
    <div x-show="tracking" x-cloak
         style="background:rgba(63,207,142,.08); border:1px solid rgba(63,207,142,.2); border-radius:.6rem; padding:.55rem 1rem; margin-bottom:1rem; font-size:.82rem; display:flex; align-items:center; gap:.6rem;">
        <span style="width:8px; height:8px; border-radius:50%; background:var(--emer); animation:pulse 1.5s infinite; flex-shrink:0;"></span>
        <span style="color:var(--emer);">GPS Aktif</span>
        <span x-show="accuracy" style="color:var(--mut);">·</span>
        <span x-show="accuracy" :style="{color: accuracyColor()}" x-text="'±' + accuracy + 'm'"></span>
        <span x-show="battery !== null" style="color:var(--mut); margin-left:auto;">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="7" width="18" height="11" rx="2"/><line x1="22" y1="11" x2="22" y2="14"/></svg>
            <span x-text="battery + '%'"></span>
        </span>
    </div>

    {{-- Error GPS --}}
    <div x-show="errorMsg" x-cloak
         style="background:rgba(232,100,90,.12); border:1px solid rgba(232,100,90,.3); border-radius:.6rem; padding:.6rem 1rem; margin-bottom:1rem; font-size:.85rem; color:var(--red2);"
         x-text="errorMsg"></div>

    {{-- Active visit card --}}
    @foreach($this->myVisites as $v)
    <div wire:key="visite-{{ $v->id }}" class="glass-card" style="margin-bottom:1rem; padding:1.25rem;">
        {{-- Header --}}
        <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:.75rem;">
            <div>
                <div style="font-size:1.1rem; font-weight:700; color:var(--ink);">{{ $v->pasien?->nama }}</div>
                <div style="font-size:.82rem; color:var(--mut); margin-top:.1rem;">{{ $v->tanggal_visite->format('d M Y') }}</div>
            </div>
            <span style="background:{{ $v->statusColor() }}22; color:{{ $v->statusColor() }}; border:1px solid {{ $v->statusColor() }}44; border-radius:999px; padding:.2rem .7rem; font-size:.75rem; font-weight:600; white-space:nowrap;">
                {{ $v->statusLabel() }}
            </span>
        </div>

        {{-- Alamat --}}
        <div style="font-size:.85rem; color:var(--mut); margin-bottom:1rem; line-height:1.5; display:flex; gap:.5rem; align-items:flex-start;">
            <svg style="flex-shrink:0; margin-top:.15rem;" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
            {{ $v->alamat_tujuan }}
        </div>

        {{-- Aksi tombol --}}
        <div style="display:flex; flex-direction:column; gap:.6rem;">

            @if($v->canStart())
            <button wire:click="mulai({{ $v->id }})" wire:loading.attr="disabled"
                    style="width:100%; background:var(--gold); color:#0a1410; border:none; border-radius:.7rem; padding:1rem; font-size:1rem; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:.6rem; min-height:56px; transition:opacity .2s;"
                    wire:loading.class="opacity-60">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>
                <span wire:loading.remove wire:target="mulai({{ $v->id }})">Mulai Perjalanan</span>
                <span wire:loading wire:target="mulai({{ $v->id }})">Memulai...</span>
            </button>
            @endif

            @if($v->canArrive())
            <button wire:click="sampai({{ $v->id }})" wire:loading.attr="disabled"
                    style="width:100%; background:var(--blue); color:#0a1410; border:none; border-radius:.7rem; padding:1rem; font-size:1rem; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:.6rem; min-height:56px; transition:opacity .2s;"
                    wire:loading.class="opacity-60">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                <span wire:loading.remove wire:target="sampai({{ $v->id }})">Saya Sudah Sampai</span>
                <span wire:loading wire:target="sampai({{ $v->id }})">Memproses...</span>
            </button>
            @endif

            @if($v->canFinish())
            <textarea wire:model="catatanKaryawan"
                      placeholder="Catatan (opsional)..."
                      style="width:100%; background:rgba(255,255,255,.05); border:1px solid var(--line2); border-radius:.6rem; padding:.7rem; color:var(--ink); font-size:.85rem; resize:none; min-height:72px;"></textarea>
            <button wire:click="selesai({{ $v->id }})" wire:loading.attr="disabled"
                    style="width:100%; background:var(--emer); color:#0a1410; border:none; border-radius:.7rem; padding:1rem; font-size:1rem; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:.6rem; min-height:56px; transition:opacity .2s;"
                    wire:loading.class="opacity-60">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                <span wire:loading.remove wire:target="selesai({{ $v->id }})">Selesai & Kirim Laporan</span>
                <span wire:loading wire:target="selesai({{ $v->id }})">Menyimpan...</span>
            </button>
            @endif

            <div style="display:flex; gap:.5rem; align-items:center;">
                {{-- Google Maps deep link --}}
                <a href="{{ $v->googleMapsUrl() }}" target="_blank" rel="noopener"
                   style="flex:1; background:rgba(111,177,224,.1); border:1px solid rgba(111,177,224,.3); color:var(--blue); border-radius:.6rem; padding:.6rem; font-size:.82rem; font-weight:600; text-decoration:none; display:flex; align-items:center; justify-content:center; gap:.4rem; min-height:44px;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    Buka Google Maps
                </a>

                @if(!$v->isDone())
                <button wire:click="batalkan({{ $v->id }})"
                        onclick="return confirm('Yakin batalkan visite ini?')"
                        style="background:transparent; border:1px solid rgba(232,100,90,.4); color:var(--red2); border-radius:.6rem; padding:.6rem .9rem; font-size:.8rem; cursor:pointer; min-height:44px; white-space:nowrap;">
                    Batalkan
                </button>
                @endif
            </div>
        </div>

        {{-- Telepon pasien --}}
        @if($v->pasien?->telepon)
        <div style="margin-top:.75rem; padding-top:.75rem; border-top:1px solid var(--line);">
            <a href="tel:{{ $v->pasien->telepon }}"
               style="font-size:.82rem; color:var(--mut); text-decoration:none; display:flex; align-items:center; gap:.4rem;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 9.81a19.79 19.79 0 01-3.07-8.72A2 2 0 012.18 1h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.91 8.15a16 16 0 006.29 6.29l1.41-1.41a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 15.92z"/></svg>
                Hubungi: {{ $v->pasien->telepon }}
            </a>
        </div>
        @endif
    </div>
    @endforeach

    @if($this->myVisites->isEmpty())
    <div class="glass-card" style="text-align:center; padding:3rem 1rem; color:var(--mut);">
        <svg style="margin:0 auto 1rem;" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
        <div style="font-size:.95rem;">Tidak ada tugas visite hari ini.</div>
    </div>
    @endif

    {{-- Riwayat 7 hari --}}
    @if($this->riwayatVisites->isNotEmpty())
    <div style="margin-top:1.5rem;">
        <div style="font-size:.78rem; color:var(--mut); text-transform:uppercase; letter-spacing:.06em; margin-bottom:.75rem;">Riwayat 7 Hari</div>
        @foreach($this->riwayatVisites as $r)
        <div style="display:flex; align-items:center; justify-content:space-between; padding:.6rem 0; border-bottom:1px solid var(--line); font-size:.85rem;">
            <div>
                <span style="color:var(--ink);">{{ $r->pasien?->nama }}</span>
                <span style="color:var(--mut); margin-left:.5rem;">{{ $r->tanggal_visite->format('d M') }}</span>
            </div>
            <span style="color:{{ $r->statusColor() }}; font-size:.78rem;">{{ $r->statusLabel() }}</span>
        </div>
        @endforeach
    </div>
    @endif

</div>
