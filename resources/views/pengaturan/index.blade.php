<x-app-layout>
    <x-slot name="title">Pengaturan</x-slot>

    <div style="margin-bottom:1.5rem;">
        <h1 class="font-heading" style="font-size:1.6rem;color:var(--ink);margin:0 0 .3rem;">Pengaturan Sistem</h1>
        <p style="font-size:.82rem;color:var(--mut);margin:0;">Konfigurasi pengguna, akses, dan kategori diagnosis Klinik Dokterku.</p>
    </div>

    <div x-data="{ tab: 'users' }">
        {{-- Tab buttons --}}
        <div style="display:flex;gap:.5rem;margin-bottom:1.5rem;border-bottom:1px solid var(--line);padding-bottom:0;">
            <button @click="tab='users'"
                :style="tab==='users' ? 'color:var(--gold2);border-bottom:2px solid var(--gold);' : 'color:var(--mut);border-bottom:2px solid transparent;'"
                style="background:none;border:none;border-left:none;border-right:none;border-top:none;padding:.65rem 1.1rem;font-size:.85rem;cursor:pointer;font-weight:500;display:inline-flex;align-items:center;gap:.4rem;transition:color .2s;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                Pengguna
            </button>
            <button @click="tab='diagnosis'"
                :style="tab==='diagnosis' ? 'color:var(--gold2);border-bottom:2px solid var(--gold);' : 'color:var(--mut);border-bottom:2px solid transparent;'"
                style="background:none;border:none;border-left:none;border-right:none;border-top:none;padding:.65rem 1.1rem;font-size:.85rem;cursor:pointer;font-weight:500;display:inline-flex;align-items:center;gap:.4rem;transition:color .2s;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                Kategori Diagnosis
            </button>
        </div>

        {{-- Tab content --}}
        <div x-show="tab==='users'" x-cloak>
            <livewire:user-manager />
        </div>
        <div x-show="tab==='diagnosis'" x-cloak>
            <livewire:diagnosis-manager />
        </div>
    </div>
</x-app-layout>
