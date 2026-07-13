<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Distributor;
use Illuminate\Http\Request;

class RiwayatController extends Controller
{
    public function index(Request $request)
    {
        // Terapkan koreksi yang SUDAH disetujui manajer tapi belum direkonsiliasi.
        \App\Models\KoreksiPo::terapkanYangDisetujui();

        $query = PurchaseOrder::with(['distributor', 'items.obat', 'koreksi'])
            ->latest('tanggal_po');

        if ($request->filled('distributor_id')) {
            $query->where('distributor_id', $request->distributor_id);
        }
        if ($request->filled('dari')) {
            $query->whereDate('tanggal_po', '>=', $request->dari);
        }
        if ($request->filled('sampai')) {
            $query->whereDate('tanggal_po', '<=', $request->sampai);
        }

        $orders = $query->paginate(15)->withQueryString();
        $distributors = Distributor::where('is_active', true)->orderBy('name')->get();

        // Guardian AI: peta risiko per PO (rekonsiliasi PO↔Tagihan + anomali) untuk badge.
        $guardianRisk = [];
        $guardianSummary = ['total' => 0, 'kritis' => 0, 'tinggi' => 0, 'sedang' => 0, 'rendah' => 0];
        try {
            $gm = app(\App\Services\Guardian\GuardianEngine::class)->riskMap();
            $guardianRisk = $gm['risk'] ?? [];
            $guardianSummary = $gm['counts'] ?? $guardianSummary;
        } catch (\Throwable $e) {
        }

        return view('riwayat.index', compact('orders', 'distributors', 'guardianRisk', 'guardianSummary'));
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->delete();
        return back()->with('success', 'Purchase Order berhasil dihapus.');
    }

    public function exportCsv(Request $request)
    {
        $query = PurchaseOrder::with(['distributor', 'items.obat'])->latest('tanggal_po');

        if ($request->filled('distributor_id')) {
            $query->where('distributor_id', $request->distributor_id);
        }
        if ($request->filled('dari')) {
            $query->whereDate('tanggal_po', '>=', $request->dari);
        }
        if ($request->filled('sampai')) {
            $query->whereDate('tanggal_po', '<=', $request->sampai);
        }

        $orders = $query->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="riwayat-po-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Tanggal PO', 'Distributor', 'No Invoice', 'Obat', 'Box', 'Isi/Box', 'Harga/Box', 'Subtotal', 'Total PO']);
            foreach ($orders as $po) {
                foreach ($po->items as $i => $item) {
                    fputcsv($file, [
                        $i === 0 ? $po->tanggal_po->format('d/m/Y') : '',
                        $i === 0 ? $po->distributor->name : '',
                        $i === 0 ? ($po->nomor_invoice ?? '-') : '',
                        $item->obat->nama_obat,
                        $item->jumlah_box,
                        $item->isi_per_box,
                        $item->harga_per_box,
                        $item->subtotal,
                        $i === 0 ? $po->total_nilai : '',
                    ]);
                }
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
