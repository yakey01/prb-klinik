@props(['icon' => 'inbox', 'title' => 'Belum ada data', 'hint' => null])

{{-- Empty-state konsisten: ikon Lucide + judul + hint + aksi opsional (slot). --}}
<div style="display:flex;flex-direction:column;align-items:center;gap:.7rem;padding:2.8rem 1rem;color:var(--mut);text-align:center;">
    <div style="width:52px;height:52px;border-radius:14px;background:rgba(255,255,255,.03);border:1px solid var(--line);display:flex;align-items:center;justify-content:center;color:var(--mut2);">
        <x-i :name="$icon" :size="24" />
    </div>
    <div style="font-weight:700;color:var(--ink);font-size:.95rem;">{{ $title }}</div>
    @if($hint)
    <div style="font-size:.8rem;max-width:24rem;line-height:1.5;">{{ $hint }}</div>
    @endif
    @if(trim($slot) !== '')
    <div style="margin-top:.5rem;">{{ $slot }}</div>
    @endif
</div>
