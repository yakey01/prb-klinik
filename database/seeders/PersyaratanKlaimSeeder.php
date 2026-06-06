<?php
namespace Database\Seeders;
use App\Models\PersyaratanKlaim;
use Illuminate\Database\Seeder;

class PersyaratanKlaimSeeder extends Seeder
{
    public function run(): void
    {
        PersyaratanKlaim::truncate();

        $data = [
            // Diabetes
            ['Diabetes','Fotokopi KTP + Kartu BPJS','Identitas diri yang masih berlaku','dokumen',6,true,1],
            ['Diabetes','Resep Dokter','Resep bulan berjalan dari DPJP','dokumen',1,true,2],
            ['Diabetes','Hasil HbA1c','Pemeriksaan HbA1c ≤ 3 bulan terakhir','lab',3,true,3],
            ['Diabetes','Glukosa Darah Puasa (GDP)','Hasil GDP dari lab / puskesmas','lab',1,true,4],
            ['Diabetes','Glukosa Darah 2 Jam PP','GD 2 jam post-prandial (opsional)','lab',1,false,5],

            // Hipertensi
            ['Hipertensi','Fotokopi KTP + Kartu BPJS','Identitas diri yang masih berlaku','dokumen',6,true,1],
            ['Hipertensi','Resep Dokter','Resep bulan berjalan dari DPJP','dokumen',1,true,2],
            ['Hipertensi','Tekanan Darah Terukur','Hasil pengukuran TD sebelum pengambilan obat','pemeriksaan',1,true,3],

            // Jantung
            ['Jantung','Fotokopi KTP + Kartu BPJS','Identitas diri yang masih berlaku','dokumen',6,true,1],
            ['Jantung','Resep Dokter','Resep bulan berjalan dari DPJP','dokumen',1,true,2],
            ['Jantung','Tekanan Darah Terukur','Hasil pengukuran TD sebelum pengambilan','pemeriksaan',1,true,3],
            ['Jantung','Hasil EKG','EKG ≤ 6 bulan terakhir','lab',6,false,4],

            // Dislipidemia
            ['Dislipidemia','Fotokopi KTP + Kartu BPJS','Identitas diri yang masih berlaku','dokumen',6,true,1],
            ['Dislipidemia','Resep Dokter','Resep bulan berjalan dari DPJP','dokumen',1,true,2],
            ['Dislipidemia','Profil Lipid / Kolesterol','Hasil lab ≤ 3 bulan terakhir','lab',3,true,3],

            // Asma & PPOK
            ['Asma & PPOK','Fotokopi KTP + Kartu BPJS','Identitas diri yang masih berlaku','dokumen',6,true,1],
            ['Asma & PPOK','Resep Dokter','Resep bulan berjalan dari DPJP','dokumen',1,true,2],

            // Gout
            ['Gout','Fotokopi KTP + Kartu BPJS','Identitas diri yang masih berlaku','dokumen',6,true,1],
            ['Gout','Resep Dokter','Resep bulan berjalan dari DPJP','dokumen',1,true,2],
            ['Gout','Kadar Asam Urat','Hasil lab ≤ 3 bulan terakhir','lab',3,true,3],

            // Psikiatri
            ['Psikiatri','Fotokopi KTP + Kartu BPJS','Identitas diri yang masih berlaku','dokumen',6,true,1],
            ['Psikiatri','Resep Dokter','Resep bulan berjalan dari DPJP / psikiater','dokumen',1,true,2],
            ['Psikiatri','Surat Kontrol Dokter','Bukti kunjungan kontrol bulan berjalan','dokumen',1,false,3],

            // Imunosupresan
            ['Imunosupresan','Fotokopi KTP + Kartu BPJS','Identitas diri yang masih berlaku','dokumen',6,true,1],
            ['Imunosupresan','Resep Dokter Spesialis','Resep dari dokter spesialis','dokumen',1,true,2],
            ['Imunosupresan','Hasil Lab Darah Lengkap','Hematologi rutin ≤ 1 bulan terakhir','lab',1,true,3],
        ];

        foreach ($data as $row) {
            PersyaratanKlaim::create([
                'diagnosis'     => $row[0],
                'nama_syarat'   => $row[1],
                'deskripsi'     => $row[2],
                'tipe'          => $row[3],
                'periode_bulan' => $row[4],
                'is_wajib'      => $row[5],
                'urutan'        => $row[6],
            ]);
        }
    }
}
