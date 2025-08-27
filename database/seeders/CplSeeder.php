<?php

namespace Database\Seeders;

use App\Models\CPL;
use App\Models\Prodi;
use Illuminate\Database\Seeder;

class CplSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cari prodi Teknik Informatika
        $prodi = Prodi::where('nama_prodi', 'Teknik Informatika')->firstOrFail();

        // Data CPL: kode_cpl,  deskripsi
        $cpls = [
            [
                'kode_cpl'  => 'CPL01',
                'deskripsi' => 'Bertakwa kepada Tuhan Yang Maha Esa, taat hukum, dan disiplin dalam kehidupan bermasyarakat dan bernegara',
            ],
            [
                'kode_cpl'  => 'CPL02',
                'deskripsi' => 'Menunjukkan sikap profesional dalam bentuk kepatuhan pada etika profesi, kemampuan bekerjasama dalam tim multidisiplin, pemahaman tentang pembelajaran sepanjang hayat, dan respon terhadap isu sosial dan perkembangan teknologi.',
            ],
            [
                'kode_cpl'  => 'CPL03',
                'deskripsi' => 'Memiliki pengetahuan yang memadai terkait cara kerja sistem komputer dan mampu menerapkan/menggunakan berbagai algoritma/metode untuk memecahkan masalah pada suatu organisasi',
            ],
            [
                'kode_cpl'  => 'CPL04',
                'deskripsi' => 'Memiliki kompetensi untuk menganalisis persoalan computing yang kompleks untuk mengidentifikasi solusi pengelolaan proyek teknologi bidang informatika/ilmu komputer dengan mempertimbangkan wawasan perkembangan ilmu transdisiplin',
            ],
            [
                'kode_cpl'  => 'CPL05',
                'deskripsi' => 'Menguasai konsep teoritis bidang pengetahuan Ilmu Komputer/Informatika dalam mendesain dan mensimulasikan aplikasi teknologi multi-platform yang relevan dengan kebutuhan industri dan masyarakat.',
            ],
            [
                'kode_cpl'  => 'CPL06',
                'deskripsi' => 'Mampu berpikir kritis dan inovatif dalam pengembangan ilmu pengetahuan dan teknologi dengan mempertimbangkan nilai humaniora serta kaidah ilmiah untuk menghasilkan solusi atau gagasan.',
            ],
            [
                'kode_cpl'  => 'CPL07',
                'deskripsi' => 'Kemampuan berkomunikasi dan kerjasama tim dalam berbagai konteks professional',
            ],
            [
                'kode_cpl'  => 'CPL08',
                'deskripsi' => 'Kemampuan mengimplementasi kebutuhan computing dengan mempertimbangkan berbagai metode/algoritma yang sesuai',
            ],
            [
                'kode_cpl'  => 'CPL09',
                'deskripsi' => 'Kemampuan menganalisis, merancang, membuat dan mengevaluasi user interface dan aplikasi interaktif dengan mempertimbangkan kebutuhan pengguna dan perkembangan ilmu transdisiplin.',
            ],
            [
                'kode_cpl'  => 'CPL10',
                'deskripsi' => 'Kemampuan mendesain, mengimplementasi dan mengevaluasi solusi berbasis computing multi-platform yang memenuhi kebutuhan computing pada sebuah organisasi.',
            ],
        ];

        foreach ($cpls as $cpl) {
            CPL::create([
                'kode_cpl'  => $cpl['kode_cpl'],
                'deskripsi' => $cpl['deskripsi'],
                'prodi_id'  => $prodi->prodi_id,
            ]);
        }
    }
}
