<?php

namespace App\Http\Controllers;

use App\Models\HomeVisite;
use App\Models\HomeVisiteTrack;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class HomeVisiteController extends Controller
{
    // POST /api/visite/{id}/track — kurir kirim titik GPS
    public function track(Request $request, int $id): JsonResponse
    {
        $visite = HomeVisite::findOrFail($id);

        if ($visite->assigned_to !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!$visite->isOngoing()) {
            return response()->json(['error' => 'Visite tidak sedang berjalan'], 422);
        }

        $data = $request->validate([
            'latitude'      => 'required|numeric|between:-90,90',
            'longitude'     => 'required|numeric|between:-180,180',
            'accuracy'      => 'nullable|numeric|min:0',
            'speed'         => 'nullable|numeric|min:0',
            'battery_level' => 'nullable|integer|min:0|max:100',
            'recorded_at'   => 'required|date',
        ]);

        // Dedup: skip if exact recorded_at already exists for this visite
        $exists = HomeVisiteTrack::where('home_visite_id', $id)
            ->where('recorded_at', $data['recorded_at'])
            ->exists();

        if (!$exists) {
            HomeVisiteTrack::create(array_merge($data, [
                'home_visite_id' => $id,
                'created_at'     => now(),
            ]));
        }

        return response()->json(['success' => true, 'server_time' => now()->toISOString()]);
    }

    // POST /api/visite/{id}/status — kurir ubah status
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $visite = HomeVisite::findOrFail($id);

        if ($visite->assigned_to !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'status'           => 'required|in:dalam_perjalanan,sampai,selesai,dibatalkan',
            'catatan_karyawan' => 'nullable|string|max:500',
        ]);

        $newStatus = $data['status'];

        // Validate state machine transitions
        $allowed = match ($visite->status) {
            'ditugaskan'       => ['dalam_perjalanan', 'dibatalkan'],
            'dalam_perjalanan' => ['sampai', 'dibatalkan'],
            'sampai'           => ['selesai', 'dibatalkan'],
            default            => [],
        };

        if (!in_array($newStatus, $allowed)) {
            return response()->json(['error' => "Tidak bisa mengubah dari {$visite->status} ke {$newStatus}"], 422);
        }

        $updates = ['status' => $newStatus];
        if (isset($data['catatan_karyawan'])) {
            $updates['catatan_karyawan'] = $data['catatan_karyawan'];
        }

        match ($newStatus) {
            'dalam_perjalanan' => $updates['started_at']   = now(),
            'sampai'           => $updates['arrived_at']   = now(),
            'selesai'          => $updates['completed_at'] = now(),
            default            => null,
        };

        $old = $visite->only('status');
        $visite->update($updates);

        // Saat selesai: sinkronisasi ke PengambilanObat jika terhubung
        if ($newStatus === 'selesai' && $visite->pengambilan_obat_id) {
            $visite->pengambilanObat?->update(['status' => 'selesai']);
        }

        ActivityLog::record(
            'updated',
            "Home visite #{$id} status: {$old['status']} → {$newStatus}",
            'HomeVisite', $id, $old, ['status' => $newStatus]
        );

        return response()->json(['success' => true, 'status' => $newStatus]);
    }

    // GET /api/visite/active-list — admin: semua kurir aktif + posisi terbaru
    public function activeList(): JsonResponse
    {
        if (!auth()->user()?->isApoteker()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $visites = HomeVisite::dalamPerjalanan()
            ->with(['pasien:id,nama', 'kurir:id,name', 'latestTrack'])
            ->get()
            ->map(function ($v) {
                $track = $v->latestTrack->first();
                return [
                    'id'           => $v->id,
                    'karyawan_nama'=> $v->kurir?->name,
                    'pasien_nama'  => $v->pasien?->nama,
                    'alamat'       => $v->alamat_tujuan,
                    'status'       => $v->status,
                    'status_label' => $v->statusLabel(),
                    'lat'          => $track?->latitude,
                    'lng'          => $track?->longitude,
                    'accuracy'     => $track?->accuracy,
                    'updated_at'   => $track?->recorded_at?->toISOString(),
                    'lat_tujuan'   => $v->lat_tujuan,
                    'lng_tujuan'   => $v->lng_tujuan,
                    'maps_url'     => $v->googleMapsUrl(),
                ];
            });

        return response()->json($visites);
    }

    // GET /api/visite/{id}/live — admin: posisi terbaru 1 kurir
    public function live(int $id): JsonResponse
    {
        if (!auth()->user()?->isApoteker()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $visite = HomeVisite::with(['pasien:id,nama', 'kurir:id,name'])->findOrFail($id);
        $track  = HomeVisiteTrack::where('home_visite_id', $id)->latest('recorded_at')->first();

        return response()->json([
            'id'           => $visite->id,
            'status'       => $visite->status,
            'status_label' => $visite->statusLabel(),
            'karyawan_nama'=> $visite->kurir?->name,
            'pasien_nama'  => $visite->pasien?->nama,
            'lat'          => $track?->latitude,
            'lng'          => $track?->longitude,
            'accuracy'     => $track?->accuracy,
            'updated_at'   => $track?->recorded_at?->toISOString(),
        ]);
    }

    // GET /api/visite/{id}/route — riwayat polyline penuh
    public function route(int $id): JsonResponse
    {
        if (!auth()->user()?->isApoteker() && auth()->id() !== HomeVisite::findOrFail($id)->assigned_to) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $tracks = HomeVisiteTrack::where('home_visite_id', $id)
            ->orderBy('recorded_at')
            ->get(['latitude', 'longitude', 'accuracy', 'speed', 'battery_level', 'recorded_at']);

        return response()->json($tracks);
    }
}
