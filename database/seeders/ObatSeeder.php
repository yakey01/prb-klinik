<?php

namespace Database\Seeders;

use App\Models\Obat;
use Illuminate\Database\Seeder;

class ObatSeeder extends Seeder
{
    public function run(): void
    {
        $obatData = [
            // Diabetes
            ['nama_obat' => 'Metformin 500mg', 'kategori_diagnosis' => 'Diabetes', 'jumlah_pasien' => 45, 'unit_per_bulan' => 2700, 'harga_beli_per_unit' => 320, 'klaim_bpjs_per_unit' => 420, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Metformin 850mg', 'kategori_diagnosis' => 'Diabetes', 'jumlah_pasien' => 30, 'unit_per_bulan' => 1800, 'harga_beli_per_unit' => 480, 'klaim_bpjs_per_unit' => 610, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Glibenklamid 5mg', 'kategori_diagnosis' => 'Diabetes', 'jumlah_pasien' => 35, 'unit_per_bulan' => 2100, 'harga_beli_per_unit' => 280, 'klaim_bpjs_per_unit' => 380, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Glimepirid 1mg', 'kategori_diagnosis' => 'Diabetes', 'jumlah_pasien' => 20, 'unit_per_bulan' => 1200, 'harga_beli_per_unit' => 520, 'klaim_bpjs_per_unit' => 680, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Glimepirid 2mg', 'kategori_diagnosis' => 'Diabetes', 'jumlah_pasien' => 18, 'unit_per_bulan' => 1080, 'harga_beli_per_unit' => 650, 'klaim_bpjs_per_unit' => 820, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Glimepirid 3mg', 'kategori_diagnosis' => 'Diabetes', 'jumlah_pasien' => 12, 'unit_per_bulan' => 720, 'harga_beli_per_unit' => 780, 'klaim_bpjs_per_unit' => 980, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Acarbose 50mg', 'kategori_diagnosis' => 'Diabetes', 'jumlah_pasien' => 10, 'unit_per_bulan' => 900, 'harga_beli_per_unit' => 1200, 'klaim_bpjs_per_unit' => 1520, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Glipizid 5mg', 'kategori_diagnosis' => 'Diabetes', 'jumlah_pasien' => 8, 'unit_per_bulan' => 480, 'harga_beli_per_unit' => 420, 'klaim_bpjs_per_unit' => 540, 'faktor_jasa_farmasi' => 1.15],

            // Hipertensi
            ['nama_obat' => 'Amlodipin 5mg', 'kategori_diagnosis' => 'Hipertensi', 'jumlah_pasien' => 60, 'unit_per_bulan' => 1800, 'harga_beli_per_unit' => 280, 'klaim_bpjs_per_unit' => 380, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Amlodipin 10mg', 'kategori_diagnosis' => 'Hipertensi', 'jumlah_pasien' => 40, 'unit_per_bulan' => 1200, 'harga_beli_per_unit' => 380, 'klaim_bpjs_per_unit' => 500, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Captopril 12.5mg', 'kategori_diagnosis' => 'Hipertensi', 'jumlah_pasien' => 25, 'unit_per_bulan' => 1500, 'harga_beli_per_unit' => 200, 'klaim_bpjs_per_unit' => 280, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Captopril 25mg', 'kategori_diagnosis' => 'Hipertensi', 'jumlah_pasien' => 30, 'unit_per_bulan' => 1800, 'harga_beli_per_unit' => 250, 'klaim_bpjs_per_unit' => 340, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Lisinopril 5mg', 'kategori_diagnosis' => 'Hipertensi', 'jumlah_pasien' => 20, 'unit_per_bulan' => 600, 'harga_beli_per_unit' => 480, 'klaim_bpjs_per_unit' => 620, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Lisinopril 10mg', 'kategori_diagnosis' => 'Hipertensi', 'jumlah_pasien' => 15, 'unit_per_bulan' => 450, 'harga_beli_per_unit' => 580, 'klaim_bpjs_per_unit' => 740, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Candesartan 8mg', 'kategori_diagnosis' => 'Hipertensi', 'jumlah_pasien' => 22, 'unit_per_bulan' => 660, 'harga_beli_per_unit' => 1200, 'klaim_bpjs_per_unit' => 1520, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Candesartan 16mg', 'kategori_diagnosis' => 'Hipertensi', 'jumlah_pasien' => 18, 'unit_per_bulan' => 540, 'harga_beli_per_unit' => 1500, 'klaim_bpjs_per_unit' => 1900, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Bisoprolol 2.5mg', 'kategori_diagnosis' => 'Hipertensi', 'jumlah_pasien' => 15, 'unit_per_bulan' => 450, 'harga_beli_per_unit' => 650, 'klaim_bpjs_per_unit' => 820, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Bisoprolol 5mg', 'kategori_diagnosis' => 'Hipertensi', 'jumlah_pasien' => 12, 'unit_per_bulan' => 360, 'harga_beli_per_unit' => 780, 'klaim_bpjs_per_unit' => 990, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Hidrokortiazid 12.5mg', 'kategori_diagnosis' => 'Hipertensi', 'jumlah_pasien' => 8, 'unit_per_bulan' => 240, 'harga_beli_per_unit' => 180, 'klaim_bpjs_per_unit' => 250, 'faktor_jasa_farmasi' => 1.15],

            // Jantung
            ['nama_obat' => 'Digoksin 0.25mg', 'kategori_diagnosis' => 'Jantung', 'jumlah_pasien' => 10, 'unit_per_bulan' => 300, 'harga_beli_per_unit' => 350, 'klaim_bpjs_per_unit' => 450, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Isosorbid Dinitrat 5mg', 'kategori_diagnosis' => 'Jantung', 'jumlah_pasien' => 8, 'unit_per_bulan' => 480, 'harga_beli_per_unit' => 280, 'klaim_bpjs_per_unit' => 380, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Bisoprolol 10mg', 'kategori_diagnosis' => 'Jantung', 'jumlah_pasien' => 6, 'unit_per_bulan' => 180, 'harga_beli_per_unit' => 920, 'klaim_bpjs_per_unit' => 1160, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Carvedilol 6.25mg', 'kategori_diagnosis' => 'Jantung', 'jumlah_pasien' => 5, 'unit_per_bulan' => 300, 'harga_beli_per_unit' => 850, 'klaim_bpjs_per_unit' => 1080, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Spironolakton 25mg', 'kategori_diagnosis' => 'Jantung', 'jumlah_pasien' => 7, 'unit_per_bulan' => 420, 'harga_beli_per_unit' => 480, 'klaim_bpjs_per_unit' => 610, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Furosemid 40mg', 'kategori_diagnosis' => 'Jantung', 'jumlah_pasien' => 9, 'unit_per_bulan' => 540, 'harga_beli_per_unit' => 180, 'klaim_bpjs_per_unit' => 250, 'faktor_jasa_farmasi' => 1.15],

            // Dislipidemia
            ['nama_obat' => 'Simvastatin 10mg', 'kategori_diagnosis' => 'Dislipidemia', 'jumlah_pasien' => 38, 'unit_per_bulan' => 1140, 'harga_beli_per_unit' => 380, 'klaim_bpjs_per_unit' => 490, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Simvastatin 20mg', 'kategori_diagnosis' => 'Dislipidemia', 'jumlah_pasien' => 25, 'unit_per_bulan' => 750, 'harga_beli_per_unit' => 480, 'klaim_bpjs_per_unit' => 620, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Atorvastatin 10mg', 'kategori_diagnosis' => 'Dislipidemia', 'jumlah_pasien' => 20, 'unit_per_bulan' => 600, 'harga_beli_per_unit' => 650, 'klaim_bpjs_per_unit' => 820, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Atorvastatin 20mg', 'kategori_diagnosis' => 'Dislipidemia', 'jumlah_pasien' => 15, 'unit_per_bulan' => 450, 'harga_beli_per_unit' => 820, 'klaim_bpjs_per_unit' => 1040, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Fenofibrat 200mg', 'kategori_diagnosis' => 'Dislipidemia', 'jumlah_pasien' => 10, 'unit_per_bulan' => 300, 'harga_beli_per_unit' => 1100, 'klaim_bpjs_per_unit' => 1400, 'faktor_jasa_farmasi' => 1.15],

            // Asma & PPOK
            ['nama_obat' => 'Salbutamol 2mg', 'kategori_diagnosis' => 'Asma & PPOK', 'jumlah_pasien' => 12, 'unit_per_bulan' => 720, 'harga_beli_per_unit' => 150, 'klaim_bpjs_per_unit' => 210, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Salbutamol 4mg', 'kategori_diagnosis' => 'Asma & PPOK', 'jumlah_pasien' => 8, 'unit_per_bulan' => 480, 'harga_beli_per_unit' => 180, 'klaim_bpjs_per_unit' => 250, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Teofilin 130mg', 'kategori_diagnosis' => 'Asma & PPOK', 'jumlah_pasien' => 6, 'unit_per_bulan' => 360, 'harga_beli_per_unit' => 220, 'klaim_bpjs_per_unit' => 300, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Teofilin 200mg', 'kategori_diagnosis' => 'Asma & PPOK', 'jumlah_pasien' => 5, 'unit_per_bulan' => 300, 'harga_beli_per_unit' => 280, 'klaim_bpjs_per_unit' => 370, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Metilprednisolon 4mg', 'kategori_diagnosis' => 'Asma & PPOK', 'jumlah_pasien' => 4, 'unit_per_bulan' => 240, 'harga_beli_per_unit' => 350, 'klaim_bpjs_per_unit' => 450, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'N-Asetilsistein 200mg', 'kategori_diagnosis' => 'Asma & PPOK', 'jumlah_pasien' => 7, 'unit_per_bulan' => 630, 'harga_beli_per_unit' => 580, 'klaim_bpjs_per_unit' => 740, 'faktor_jasa_farmasi' => 1.15],

            // Psikiatri
            ['nama_obat' => 'Haloperidol 0.5mg', 'kategori_diagnosis' => 'Psikiatri', 'jumlah_pasien' => 8, 'unit_per_bulan' => 480, 'harga_beli_per_unit' => 280, 'klaim_bpjs_per_unit' => 370, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Haloperidol 1.5mg', 'kategori_diagnosis' => 'Psikiatri', 'jumlah_pasien' => 6, 'unit_per_bulan' => 360, 'harga_beli_per_unit' => 350, 'klaim_bpjs_per_unit' => 460, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Haloperidol 5mg', 'kategori_diagnosis' => 'Psikiatri', 'jumlah_pasien' => 4, 'unit_per_bulan' => 240, 'harga_beli_per_unit' => 420, 'klaim_bpjs_per_unit' => 540, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Risperidon 2mg', 'kategori_diagnosis' => 'Psikiatri', 'jumlah_pasien' => 5, 'unit_per_bulan' => 300, 'harga_beli_per_unit' => 1200, 'klaim_bpjs_per_unit' => 1520, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Triheksifenidil 2mg', 'kategori_diagnosis' => 'Psikiatri', 'jumlah_pasien' => 7, 'unit_per_bulan' => 420, 'harga_beli_per_unit' => 200, 'klaim_bpjs_per_unit' => 280, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Clozapin 25mg', 'kategori_diagnosis' => 'Psikiatri', 'jumlah_pasien' => 3, 'unit_per_bulan' => 270, 'harga_beli_per_unit' => 1800, 'klaim_bpjs_per_unit' => 2280, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Clozapin 100mg', 'kategori_diagnosis' => 'Psikiatri', 'jumlah_pasien' => 3, 'unit_per_bulan' => 270, 'harga_beli_per_unit' => 3500, 'klaim_bpjs_per_unit' => 4430, 'faktor_jasa_farmasi' => 1.15],

            // Imunosupresan
            ['nama_obat' => 'Metilprednisolon 8mg', 'kategori_diagnosis' => 'Imunosupresan', 'jumlah_pasien' => 4, 'unit_per_bulan' => 240, 'harga_beli_per_unit' => 650, 'klaim_bpjs_per_unit' => 820, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Metilprednisolon 16mg', 'kategori_diagnosis' => 'Imunosupresan', 'jumlah_pasien' => 3, 'unit_per_bulan' => 180, 'harga_beli_per_unit' => 1100, 'klaim_bpjs_per_unit' => 1400, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Azatioprin 50mg', 'kategori_diagnosis' => 'Imunosupresan', 'jumlah_pasien' => 2, 'unit_per_bulan' => 120, 'harga_beli_per_unit' => 2800, 'klaim_bpjs_per_unit' => 3550, 'faktor_jasa_farmasi' => 1.15],

            // Gout
            ['nama_obat' => 'Allopurinol 100mg', 'kategori_diagnosis' => 'Gout', 'jumlah_pasien' => 20, 'unit_per_bulan' => 1200, 'harga_beli_per_unit' => 180, 'klaim_bpjs_per_unit' => 250, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Allopurinol 300mg', 'kategori_diagnosis' => 'Gout', 'jumlah_pasien' => 12, 'unit_per_bulan' => 720, 'harga_beli_per_unit' => 280, 'klaim_bpjs_per_unit' => 370, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Kolkisin 0.5mg', 'kategori_diagnosis' => 'Gout', 'jumlah_pasien' => 8, 'unit_per_bulan' => 480, 'harga_beli_per_unit' => 450, 'klaim_bpjs_per_unit' => 580, 'faktor_jasa_farmasi' => 1.15],

            // Lainnya
            ['nama_obat' => 'Asam Folat 0.4mg', 'kategori_diagnosis' => 'Lainnya', 'jumlah_pasien' => 15, 'unit_per_bulan' => 900, 'harga_beli_per_unit' => 120, 'klaim_bpjs_per_unit' => 175, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Kalsium Laktat 500mg', 'kategori_diagnosis' => 'Lainnya', 'jumlah_pasien' => 20, 'unit_per_bulan' => 1200, 'harga_beli_per_unit' => 150, 'klaim_bpjs_per_unit' => 210, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Vitamin B Complex', 'kategori_diagnosis' => 'Lainnya', 'jumlah_pasien' => 25, 'unit_per_bulan' => 1500, 'harga_beli_per_unit' => 200, 'klaim_bpjs_per_unit' => 270, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Vitamin B12 50mcg', 'kategori_diagnosis' => 'Lainnya', 'jumlah_pasien' => 18, 'unit_per_bulan' => 1080, 'harga_beli_per_unit' => 180, 'klaim_bpjs_per_unit' => 250, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Omeprazol 20mg', 'kategori_diagnosis' => 'Lainnya', 'jumlah_pasien' => 22, 'unit_per_bulan' => 1320, 'harga_beli_per_unit' => 380, 'klaim_bpjs_per_unit' => 490, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Ranitidin 150mg', 'kategori_diagnosis' => 'Lainnya', 'jumlah_pasien' => 10, 'unit_per_bulan' => 600, 'harga_beli_per_unit' => 220, 'klaim_bpjs_per_unit' => 300, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Asam Mefenamat 500mg', 'kategori_diagnosis' => 'Lainnya', 'jumlah_pasien' => 8, 'unit_per_bulan' => 480, 'harga_beli_per_unit' => 250, 'klaim_bpjs_per_unit' => 340, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Natrium Diklofenak 25mg', 'kategori_diagnosis' => 'Lainnya', 'jumlah_pasien' => 10, 'unit_per_bulan' => 600, 'harga_beli_per_unit' => 280, 'klaim_bpjs_per_unit' => 370, 'faktor_jasa_farmasi' => 1.15],
            ['nama_obat' => 'Amoksisilin 500mg', 'kategori_diagnosis' => 'Lainnya', 'jumlah_pasien' => 5, 'unit_per_bulan' => 300, 'harga_beli_per_unit' => 380, 'klaim_bpjs_per_unit' => 490, 'faktor_jasa_farmasi' => 1.15],
        ];

        foreach ($obatData as $obat) {
            Obat::create(array_merge($obat, ['sumber_harga' => 'EST']));
        }
    }
}
