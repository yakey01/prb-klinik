<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.2rem;flex-wrap:wrap;gap:.75rem;">
        <div>
            <h2 class="font-heading" style="font-size:1.1rem;color:var(--ink);margin:0 0 .2rem;">Audit Trail</h2>
            <p style="font-size:.75rem;color:var(--mut);margin:0;">Log semua perubahan data sistem.</p>
        </div>
        <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;">
            <div style="position:relative;">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="position:absolute;left:.7rem;top:50%;transform:translateY(-50%);color:var(--mut);"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari deskripsi..." class="form-input" style="padding-left:2.1rem;min-width:200px;">
            </div>
            @foreach(['semua'=>'Semua','created'=>'Dibuat','updated'=>'Diubah','deleted'=>'Dihapus','login'=>'Login','export'=>'Export'] as $v => $l)
            <button wire:click="$set('filterAction','{{ $v }}')"
                style="padding:.28rem .7rem;border-radius:999px;font-size:.7rem;cursor:pointer;border:1px solid;transition:all .2s;
                    {{ $filterAction===$v ? 'background:var(--gold);border-color:var(--gold);color:#1a0e00;font-weight:700;' : 'background:transparent;border-color:var(--line2);color:var(--mut);' }}">
                {{ $l }}
            </button>
            @endforeach
        </div>
    </div>

    <div class="glass-card" style="overflow-x:auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width:140px;">Waktu</th>
                    <th style="width:120px;">User</th>
                    <th style="width:70px;text-align:center;">Aksi</th>
                    <th>Deskripsi</th>
                    <th style="width:80px;">Model</th>
                    <th style="width:90px;">IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse($this->logs as $log)
                <tr>
                    <td class="font-mono" style="font-size:.72rem;color:var(--mut2);white-space:nowrap;">
                        {{ $log->created_at->format('d/m/y H:i:s') }}
                    </td>
                    <td style="font-size:.78rem;color:var(--mut);">{{ $log->user?->name ?? '—' }}</td>
                    <td style="text-align:center;">
                        <span style="font-size:.85rem;color:{{ $this->actionColor[$log->action] ?? 'var(--mut)' }};">
                            {{ $this->actionIcon[$log->action] ?? '·' }}
                        </span>
                    </td>
                    <td style="font-size:.8rem;">{{ $log->description }}</td>
                    <td style="font-size:.72rem;color:var(--mut2);">{{ $log->model_type ? class_basename($log->model_type) : '—' }}</td>
                    <td class="font-mono" style="font-size:.7rem;color:var(--mut2);">{{ $log->ip_address ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--mut);">Belum ada aktivitas tercatat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:.75rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem;">
        <div style="font-size:.73rem;color:var(--mut2);">{{ $this->logs->total() }} total log</div>
        <div>{{ $this->logs->links() }}</div>
    </div>
</div>
