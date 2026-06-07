<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Obat;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class ImportObat extends Component
{
    use WithFileUploads;

    public bool $show = false;

    #[Validate('required|file|mimes:csv,txt|max:4096')]
    public $csvFile = null;

    public ?int $lastImportCount = null;
    public ?string $lastImportError = null;

    public function import(): void
    {
        $this->validate();
        $this->lastImportError = null;

        try {
            $path   = $this->csvFile->getRealPath();
            $handle = fopen($path, 'r');
            $header = array_map('trim', fgetcsv($handle));
            $count  = 0;

            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) < count($header)) continue;
                $data = array_combine($header, $row);
                if (empty(trim($data['nama_obat'] ?? ''))) continue;

                Obat::updateOrCreate(
                    ['nama_obat' => trim($data['nama_obat'])],
                    [
                        'kategori_diagnosis'  => trim($data['kategori_diagnosis']  ?? 'Lainnya'),
                        'kode_obat'           => trim($data['kode_obat']           ?? '') ?: null,
                        'harga_beli_per_unit' => (float) ($data['harga_beli_per_unit'] ?? 0),
                        'sumber_harga'        => in_array(strtoupper($data['sumber_harga'] ?? ''), ['PO','REAL','EST'])
                                                ? strtoupper($data['sumber_harga']) : 'EST',
                        'klaim_bpjs_per_unit' => (float) ($data['klaim_bpjs_per_unit'] ?? 0),
                        'faktor_jasa_farmasi' => (float) ($data['faktor_jasa_farmasi'] ?? 1.15),
                        'tipe_obat'           => in_array($data['tipe_obat'] ?? '', ['kronis','non_kronis'])
                                                ? $data['tipe_obat'] : 'kronis',
                        'satuan'              => trim($data['satuan'] ?? 'tablet'),
                        'stok_minimum'        => (int) ($data['stok_minimum'] ?? 0),
                        'is_active'           => true,
                    ]
                );
                $count++;
            }
            fclose($handle);

            $this->lastImportCount = $count;
            $this->csvFile = null;
            $this->show    = false;

            ActivityLog::record('import', "Import CSV Dashboard: {$count} obat diproses");
            $this->dispatch('toast', message: "{$count} obat berhasil diimpor.", type: 'success');

        } catch (\Throwable $e) {
            $this->lastImportError = 'Gagal memproses file: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.import-obat');
    }
}
