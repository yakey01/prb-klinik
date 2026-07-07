<x-app-layout>
    <x-slot name="header">
        <h2 style="font-size:1.1rem; font-weight:600; color:var(--ink);">Peta Tracking Real-Time</h2>
    </x-slot>

    {{-- Leaflet CSS --}}
    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @endpush

    <div style="padding:1rem 1.5rem 0;">
        <livewire:home-visite-map />
    </div>

    {{-- Leaflet JS --}}
    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @endpush
</x-app-layout>
