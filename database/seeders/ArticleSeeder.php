<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Artikel literasi UMKM + artikel profil mitra — idempoten berdasarkan slug.
 */
class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->articles() as $index => $data) {
            $category = ArticleCategory::query()->where('slug', $data['category'])->firstOrFail();

            $author = User::query()->where('email', $data['author'])->first()
                ?? User::query()->where('role', User::ROLE_ADMIN)->firstOrFail();

            $umkmProfileId = isset($data['umkm'])
                ? UmkmProfile::query()->where('slug', $data['umkm'])->value('id')
                : null;

            Article::withTrashed()->updateOrCreate(
                ['slug' => Str::slug($data['title'])],
                [
                    'user_id' => $author->id,
                    'article_category_id' => $category->id,
                    'umkm_profile_id' => $umkmProfileId,
                    'title' => $data['title'],
                    'excerpt' => $data['excerpt'],
                    'body' => implode("\n\n", $data['body']),
                    'cover_image' => null,
                    'status' => Article::STATUS_PUBLISHED,
                    'is_featured' => (bool) ($data['featured'] ?? false),
                    'views' => 0,
                    'published_at' => now()->subDays(count($this->articles()) - $index),
                    'deleted_at' => null,
                ],
            );
        }

        $this->command?->info('Artikel literasi: '.Article::query()->count().' baris ('.Article::published()->count().' terbit).');
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function articles(): array
    {
        return [
            [
                'title' => 'Panduan Foto Produk UMKM Cukup dengan HP',
                'category' => 'pemasaran-digital',
                'author' => 'admin@ewedu.test',
                'featured' => true,
                'excerpt' => 'Foto yang jelas meningkatkan kepercayaan pembeli daring. Berikut langkah sederhana memotret produk hanya dengan ponsel.',
                'body' => [
                    'Banyak pelaku UMKM menganggap foto produk harus memakai kamera profesional. Padahal dengan ponsel masa kini dan pencahayaan yang tepat, foto produk bisa tampak rapi tanpa biaya tambahan.',
                    'Mulailah dari latar polos. Gunakan kertas putih, kain abu-abu, atau meja kayu bersih. Cahaya alami dari jendela pada pagi hingga siang adalah pilihan terbaik karena lembut dan merata. Hindari lampu kuning langsung karena membuat warna produk berubah.',
                    'Ambil minimal tiga sudut: foto utama produk menghadap depan, foto detail bagian penting, dan foto saat produk digunakan. Simpan dalam ukuran minimal 1000 piksel untuk sisi terpanjang agar tidak pecah ketika ditampilkan besar.',
                    'Setelah itu, kompres berkas sebelum diunggah. Foto besar membuat halaman lambat dibuka dan calon pembeli meninggalkan toko daring. Cukup tambahkan keterangan singkat berisi ukuran dan bahan agar pembeli tidak perlu bertanya berulang.',
                ],
            ],
            [
                'title' => 'Cara Menentukan Harga Jual Produk UMKM',
                'category' => 'manajemen-usaha',
                'author' => 'admin@ewedu.test',
                'featured' => true,
                'excerpt' => 'Harga yang terlalu murah menggerus keuntungan, terlalu mahal membuat pembeli berpaling. Ini rumus dasar yang bisa langsung dipakai.',
                'body' => [
                    'Langkah pertama adalah menghitung harga pokok produksi (HPP): bahan baku, kemasan, tenaga kerja, serta biaya operasional seperti listrik dan transportasi. Catat semuanya, sekecil apa pun, karena biaya kecil sering terlupakan.',
                    'Setelah HPP diketahui, tambahkan margin keuntungan. Untuk produk kuliner rumahan biasanya 30 sampai 50 persen, sedangkan kerajinan tangan bisa 50 sampai 100 persen karena nilai keunikannya lebih tinggi.',
                    'Jangan lupa menghitung biaya penjualan daring, misalnya ongkos kirim yang disubsidi atau biaya promosi. Bila memakai marketplace, sisihkan juga potongan biaya layanan.',
                    'Evaluasi harga setiap tiga bulan. Jika bahan baku naik, kenaikan bisa disesuaikan secara bertahap agar pelanggan lama tidak terkejut, misalnya dengan mengurangi gramasi kemasan terlebih dahulu.',
                ],
            ],
            [
                'title' => 'Mengurus NIB dan Sertifikasi Halal untuk UMKM',
                'category' => 'legalitas-umkm',
                'author' => 'admin@ewedu.test',
                'featured' => false,
                'excerpt' => 'Legalitas usaha membuka akses pembiayaan dan kerja sama institusi. Prosesnya kini bisa dilakukan daring.',
                'body' => [
                    'Nomor Induk Berusaha (NIB) adalah identitas usaha yang menjadi pintu masuk perizinan lain. Pendaftaran dilakukan gratis melalui sistem OSS dengan bermodal Nomor Induk Kependudukan dan alamat usaha.',
                    'Untuk produk makanan dan minuman, sertifikat halal menjadi nilai jual utama. Program sertifikasi halal gratis untuk usaha mikro dapat diajukan dengan lampiran daftar bahan dan alur produksi.',
                    'Siapkan dokumen pendukung sejak awal: foto tempat produksi, daftar pemasok bahan, dan catatan kebersihan harian. Berkas yang rapi mempercepat proses verifikasi.',
                    'Setelah legalitas lengkap, cantumkan nomor izin pada kemasan dan halaman toko daring. Pembeli institusi seperti kantor dan sekolah biasanya mensyaratkan dokumen ini sebelum memesan dalam jumlah besar.',
                ],
            ],
            [
                'title' => 'Cerita Kopi Tuguran: dari Kedai Kecil ke Marketplace',
                'category' => 'cerita-umkm',
                'author' => 'umkm@ewedu.test',
                'umkm' => 'kopi-tuguran',
                'featured' => false,
                'excerpt' => 'Berawal dari dua kilogram robusta per minggu, Kopi Tuguran kini mengirim pesanan ke luar kota melalui E-Wedu.',
                'body' => [
                    'Kedai Kopi Tuguran dimulai pada 2019 dengan satu mesin sangrai sederhana dan dua kilogram biji robusta per minggu. Pemiliknya, pak Yanto, menjual kopi bubuk kepada tetangga dan mahasiswa sekitar kampus.',
                    'Tantangan terbesar muncul saat pembatasan kegiatan: kedai sepi dan stok menumpuk. Melalui pendampingan literasi UMKM, ia belajar mengemas produk dalam ukuran kecil, menetapkan harga jual yang benar, dan memotret produk dengan ponsel.',
                    'Setelah bergabung sebagai mitra E-Wedu, pesanan mulai datang dari luar Magelang. Sistem titik jemput gratis membuat ongkos kirim lebih hemat bagi pembeli di kota, sementara alamat kustom melayani pembeli di kecamatan sekitar.',
                    'Kini Kopi Tuguran mempekerjakan tiga orang dan menyerap hasil panen kopi dari sepuluh petani lereng Sumbing. Rencana berikutnya adalah menambah varian arabika dan mengikuti pelatihan standar mutu pangan.',
                ],
            ],
            [
                'title' => 'Memanfaatkan WhatsApp Business untuk Melayani Pesanan',
                'category' => 'pemasaran-digital',
                'author' => 'admin@ewedu.test',
                'featured' => false,
                'excerpt' => 'WhatsApp masih menjadi kanal favorit pembeli Indonesia. Atur agar tidak kewalahan membalas pesan.',
                'body' => [
                    'Gunakan akun WhatsApp Business agar bisa memasang katalog, jam operasional, dan pesan sambutan otomatis. Pisahkan nomor usaha dari nomor pribadi supaya tidak terganggu saat jam istirahat.',
                    'Buat balasan cepat untuk pertanyaan yang paling sering muncul: ketersediaan stok, cara pembayaran, dan estimasi pengiriman. Balasan cepat menghemat waktu dan membuat pembeli merasa diperhatikan.',
                    'Selalu konfirmasi ulang detail pesanan secara tertulis: nama produk, jumlah, alamat, dan total pembayaran. Percakapan tertulis menjadi bukti bila terjadi perbedaan penafsiran.',
                    'Simpan kontak pelanggan yang sudah membeli dalam label khusus. Ketika ada produk baru, tawarkan lebih dulu kepada pelanggan lama karena peluang pembelian ulang jauh lebih besar.',
                ],
            ],
            [
                'title' => 'Mengelola Stok dan Keuangan Usaha secara Sederhana',
                'category' => 'manajemen-usaha',
                'author' => 'admin@ewedu.test',
                'featured' => false,
                'excerpt' => 'Pencatatan rapi membuat pemilik usaha tahu produk mana yang menguntungkan dan kapan harus menambah stok.',
                'body' => [
                    'Mulai dengan buku stok sederhana berisi tanggal masuk, jumlah, tanggal keluar, dan sisa barang. Catat setiap transaksi pada hari yang sama agar tidak menumpuk.',
                    'Pisahkan uang usaha dan uang pribadi. Ambil penghasilan pemilik dalam jumlah tetap setiap bulan sebagai gaji, bukan mengambil uang kas sesuka hati ketika ada kebutuhan mendadak.',
                    'Hitung titik pemesanan ulang (reorder point): rata-rata penjualan harian dikali waktu tunggu bahan dari pemasok, ditambah stok cadangan untuk hari ramai. Angka ini mencegah kejadian stok habis saat permintaan tinggi.',
                    'Setiap akhir bulan, bandingkan penjualan dengan pengeluaran. Jika ada produk yang perputarannya lambat, pertimbangkan bundling dengan produk terlaris atau potongan harga terbatas agar modal tidak tertahan.',
                ],
            ],
        ];
    }
}
