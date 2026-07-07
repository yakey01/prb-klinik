<?php

namespace App\Console\Commands;

use App\Models\Obat;
use Illuminate\Console\Command;

/**
 * Isi kolom `komposisi` (zat aktif + kekuatan) untuk katalog obat.
 *
 * Sumber data: nama generik yang sudah memuat zat aktif + kekuatan, plus
 * peta merek dagang Indonesia yang sudah diverifikasi (pionas/MIMS/k24).
 * Idempotent: hanya mengisi yang masih kosong, kecuali --force.
 * BMHP dilewati (bukan obat berkandungan zat aktif).
 */
class SeedKomposisi extends Command
{
    protected $signature   = 'obat:seed-komposisi {--force : Timpa komposisi yang sudah terisi}';
    protected $description = 'Isi komposisi/zat aktif untuk katalog obat (generik + merek dagang terverifikasi)';

    /** Peta merek dagang & generik khusus → komposisi terverifikasi (key = nama_obat persis). */
    private const MAP = [
        // ── Merek dagang Indonesia (verifikasi pionas/MIMS/k24) ──────────
        'Akita'                  => 'Attapulgite 600 mg + Pektin 50 mg',
        'Alphamol'               => 'Paracetamol 500 mg',
        'Ambeven'                => 'Ekstrak herbal: Graptophyllum pictum + Sophora japonica + Rubia cordifolia + Coleus atropurpureus + Sanguisorba officinalis',
        'Anelat'                 => 'Asam Folat 1 mg',
        'Arfen'                  => 'Ibuprofen 400 mg',
        'Arkavit'                => 'Vitamin B1 50 mg + B2 25 mg + B3 50 mg + B5 20 mg + B6 10 mg + B12 5 mcg',
        'Aspilet'                => 'Asam Asetilsalisilat (Asetosal) 80 mg',
        'Bioplacenton salp'      => 'Ekstrak Plasenta 10% + Neomisin sulfat 0,5%',
        'Bufacort-N salp'        => 'Hidrokortison asetat 1% + Neomisin sulfat 0,5%',
        'Bufantacyd'             => 'Aluminium Hidroksida 200 mg + Magnesium Hidroksida 200 mg + Simetikon 20 mg',
        'Calcifar'               => 'Kalsium Laktat 500 mg',
        'carvicur'               => 'Ekstrak Curcuma xanthorrhiza + multivitamin (A, B kompleks, D)',
        'Colpica'                => 'Paracetamol 500 mg + Pseudoefedrin HCl 30 mg + Klorfeniramin Maleat 1 mg',
        'Danasone 0,5mg'         => 'Deksametason 0,5 mg',
        'Dionicol'               => 'Tiamfenikol 500 mg',
        'Disfltyl'               => 'Simetikon 40 mg',
        'Dohixat'                => 'Doksisiklin 100 mg',
        'Erladerm-n salp'        => 'Betametason valerat 0,1% + Neomisin sulfat 0,5%',
        'Erlamycetin salep mata' => 'Kloramfenikol 1% (salep mata)',
        'Etabion'                => 'Ferro fumarat 91 mg + Asam Askorbat 50 mg + Asam Folat 0,8 mg + B12 7,5 mcg + Cu + Mn',
        'Fasidol Frt Syr'        => 'Paracetamol 250 mg / 5 mL',
        'Gencef Syr'             => 'Cefadroxil 125 mg / 5 mL',
        'Grantusif'              => 'Dekstrometorfan HBr 15 mg + Guaifenesin 100 mg + Difenhidramin HCl 5 mg',
        'Guanistrep syr'         => 'Kaolin 986 mg + Pektin 40 mg / 5 mL',
        'Helixim syr'            => 'Cefixime 100 mg / 5 mL',
        'Histigo'                => 'Betahistin mesilat 6 mg',
        'Hufamag Syr'            => 'Aluminium Hidroksida + Magnesium Hidroksida + Simetikon (suspensi)',
        'Inamid (Loperamide)'    => 'Loperamide HCl 2 mg',
        'Laxana (Bisacodyl)'     => 'Bisakodil 5 mg',
        'Lecozinc'               => 'Zinc (sebagai Zinc sulfat) 20 mg',
        'Lerzin syr'             => 'Setirizin HCl 5 mg / 5 mL',
        'Lodecon Forte'          => 'Paracetamol 600 mg + Fenilefrin HCl 7,5 mg + Klorfeniramin Maleat 1 mg + Dekstrometorfan HBr 15 mg + Gliseril Guaiakolat 50 mg',
        'Mantino'                => 'Dimenhidrinat 50 mg',
        'Norvom (Metoclopramide)'=> 'Metoklopramid HCl 10 mg',
        'Novacolin'              => 'Paracetamol 500 mg + Pseudoefedrin HCl 7,5 mg + Klorfeniramin Maleat 2 mg',
        'Novastan'               => 'Asam Mefenamat 500 mg',
        'Omekur'                 => 'Omeprazole 20 mg',
        'Orphen'                 => 'Klorfeniramin Maleat 4 mg',
        'Pimtrakol syr'          => 'Paracetamol 125 mg + Gliseril Guaiakolat 50 mg + Efedrin HCl 2 mg + Klorfeniramin Maleat 1 mg / 5 mL',
        'Polofar Plus'           => 'Deksametason 0,5 mg + Deksklorfeniramin Maleat 2 mg',
        'Primadex'               => 'Sulfametoksazol 400 mg + Trimetoprim 80 mg',
        'Primadex syr'           => 'Sulfametoksazol 200 mg + Trimetoprim 40 mg / 5 mL',
        'Pronicy'                => 'Siproheptadin HCl 4 mg',
        'Ramaflu'                => 'Paracetamol 500 mg + Pseudoefedrin HCl 30 mg + Dekstrometorfan HBr 15 mg + Klorfeniramin Maleat 2 mg',
        'Reco tetes mata'        => 'Tetrahidrozolin HCl 0,05% (tetes mata)',
        'Regumen'                => 'Noretisteron 5 mg',
        'Rosidon'                => 'Domperidone 10 mg',
        'Spasminal'              => 'Metampiron 500 mg + Papaverin HCl 25 mg + Ekstrak Belladonna 10 mg',
        'Tialysin syr'           => 'Multivitamin (A, D, B kompleks) + L-Lisin HCl 100 mg + Kalsium / 5 mL',
        'Vastral'                => 'Vitamin B1 100 mg + B6 200 mg + B12 200 mcg',
        'Yusimox syr'            => 'Amoxicillin 125 mg / 5 mL',
        'Zelona'                 => 'Natrium Diklofenak 50 mg',
        'Polofar'                => 'Deksametason 0,5 mg + Deksklorfeniramin Maleat 2 mg',

        // ── Generik (salt form yang benar) ───────────────────────────────
        'Acyclovir 400mg'        => 'Asiklovir 400 mg',
        'Acyclovir salp'         => 'Asiklovir 5% (salep)',
        'Amoxicillin'            => 'Amoxicillin trihydrate 500 mg',
        'Amoksisilin 500mg'      => 'Amoxicillin trihydrate 500 mg',
        'Antasida Doen'          => 'Aluminium Hidroksida 200 mg + Magnesium Hidroksida 200 mg',
        'Antasida Syr'           => 'Aluminium Hidroksida + Magnesium Hidroksida / 5 mL',
        'Betamethasone salp'     => 'Betametason valerat 0,1% (krim/salep)',
        'Cefadroxil'             => 'Cefadroxil monohidrat 500 mg',
        'Cetirizine'             => 'Setirizin HCl 10 mg',
        'Ciprofloxacin'          => 'Siprofloksasin HCl 500 mg',
        'CTM 4mg'                => 'Klorfeniramin Maleat 4 mg',
        'Dexamethasone 0,5mg'    => 'Deksametason 0,5 mg',
        'Digoksin 0.25mg'        => 'Digoksin 0,25 mg',
        'Digoxin'                => 'Digoksin 0,25 mg',
        'Dimenhydrinate'         => 'Dimenhidrinat 50 mg',
        'Domperidone Syr'        => 'Domperidone 5 mg / 5 mL',
        'Furosemide'             => 'Furosemide 40 mg',
        'Gentamicyn salp'        => 'Gentamisin sulfat 0,1% (salep)',
        'Glibenclamide'          => 'Glibenklamid 5 mg',
        'Guaifenesin'            => 'Guaifenesin (Gliseril Guaiakolat) 100 mg',
        'Hidrokortiazid 12.5mg'  => 'Hidroklorotiazid (HCT) 12,5 mg',
        'Hydrocortisone salp'    => 'Hidrokortison asetat 2,5% (krim/salep)',
        'Ibu Profen'             => 'Ibuprofen 400 mg',
        'ISDN'                   => 'Isosorbid Dinitrat 5 mg',
        'Ketokonazole salp'      => 'Ketokonazol 2% (krim/salep)',
        'Ketokonazole tab'       => 'Ketokonazol 200 mg',
        'Lansoprazole'           => 'Lansoprazole 30 mg',
        'Methyl Prednisolone 4mg'=> 'Metilprednisolon 4 mg',
        'Metronidazole'          => 'Metronidazol 500 mg',
        'Miconazole salp'        => 'Mikonazol nitrat 2% (krim/salep)',
        'Natrium Diclofenac'     => 'Natrium Diklofenak 50 mg',
        'Nifedipine'             => 'Nifedipine 10 mg',
        'Omeprazole'             => 'Omeprazole 20 mg',
        'Paracetamol'            => 'Paracetamol 500 mg',
        'Paracetamol syr'        => 'Paracetamol 120 mg / 5 mL',
        'Permethrine 5%10gr'     => 'Permetrin 5% (krim)',
        'Ranitidin 150mg'        => 'Ranitidin HCl 150 mg',
        'Salbutamol 2 mg'        => 'Salbutamol sulfat 2 mg',
        'Sucralfate syr'         => 'Sukralfat 500 mg / 5 mL',
        'Vitamin B Complex'      => 'Vitamin B1 + B2 + B6 + B12 (B Kompleks)',
        'Vitamin BC'             => 'Vitamin B1 + B6 + B12',
        'Vitamin B12 50mcg'      => 'Sianokobalamin (Vit B12) 50 mcg',
        'Vitamin C'              => 'Asam Askorbat (Vitamin C) 500 mg',
        'Asam Tranexamat'        => 'Asam Traneksamat 500 mg',
        'Meloxicam 7,5mg'        => 'Meloksikam 7,5 mg',
        'Meloxicam 15mg'         => 'Meloksikam 15 mg',
        'Glimepiride 2mg'        => 'Glimepiride 2 mg',
        'Glimepiride 4mg'        => 'Glimepiride 4 mg',
    ];

    /** Salt-form generik berdasarkan prefix nama (untuk fallback yang lebih tepat). */
    private const SALT = [
        'metformin'    => 'Metformin HCl',
        'amlodipin'    => 'Amlodipine besilat',
        'amlodipine'   => 'Amlodipine besilat',
        'candesartan'  => 'Candesartan silexetil',
        'bisoprolol'   => 'Bisoprolol fumarat',
        'atorvastatin' => 'Atorvastatin kalsium',
        'clopidogrel'  => 'Clopidogrel bisulfat',
        'ambroxol'     => 'Ambroxol HCl',
        'clindamicyn'  => 'Klindamisin HCl',
        'risperidon'   => 'Risperidone',
        'azatioprin'   => 'Azathioprine',
        'triheksifenidil' => 'Trihexyphenidyl',
        'glibenklamid' => 'Glibenklamid',
        'glipizid'     => 'Glipizide',
        'carvedilol'   => 'Carvedilol',
        'fenofibrat'   => 'Fenofibrat',
        'kolkisin'     => 'Kolkisin',
        'spironolakton'=> 'Spironolakton',
    ];

    public function handle(): int
    {
        $force = (bool) $this->option('force');
        $obats = Obat::where('tipe_obat', '!=', 'bmhp')->orderBy('nama_obat')->get();

        $filled = 0; $skipped = 0; $fallback = 0;

        foreach ($obats as $o) {
            if (!$force && filled($o->komposisi)) { $skipped++; continue; }

            // Lewati entri uji.
            if (preg_match('/\b(test|yaya|tanpa diagnosis)\b/i', $o->nama_obat)) { $skipped++; continue; }

            if (isset(self::MAP[$o->nama_obat])) {
                $komposisi = self::MAP[$o->nama_obat];
            } else {
                $komposisi = $this->generic($o->nama_obat);
                $fallback++;
            }
            if ($komposisi === null) { $skipped++; continue; }

            $o->update(['komposisi' => $komposisi]);
            $filled++;
        }

        $this->info("Komposisi terisi: {$filled} (fallback generik: {$fallback}) · dilewati: {$skipped}");
        return self::SUCCESS;
    }

    /**
     * Fallback: rapikan nama generik menjadi pernyataan komposisi.
     * "Metformin 500mg" → "Metformin HCl 500 mg".
     */
    private function generic(string $nama): ?string
    {
        $s = trim($nama);

        // Beri spasi sebelum satuan kekuatan: 500mg → 500 mg, 50mcg → 50 mcg.
        $s = preg_replace('/(\d)\s*(mg|mcg|g|ml|%|iu)\b/i', '$1 $2', $s);
        // Normalisasi unit ke huruf kecil baku.
        $s = preg_replace_callback('/\b(MG|MCG|ML|IU)\b/', fn ($m) => strtolower($m[1]) === 'iu' ? 'IU' : strtolower($m[1]), $s);

        // Terapkan salt-form bila prefix dikenali.
        $low = strtolower($s);
        foreach (self::SALT as $key => $proper) {
            if (str_starts_with($low, $key)) {
                // Ganti kata pertama (nama generik) dengan bentuk salt yang benar.
                $rest = trim(substr($s, strlen($key)));
                return trim($proper . ' ' . $rest);
            }
        }

        return $s;
    }
}
