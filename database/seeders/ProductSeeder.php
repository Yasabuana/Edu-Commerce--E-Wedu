<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\UmkmProfile;
use Illuminate\Database\Seeder;

/**
 * 8 produk contoh (2 per mitra UMKM) — idempoten berdasarkan slug.
 *
 * Catatan: `thumbnail` dikosongkan karena berkas gambar belum tersedia;
 * halaman katalog memakai placeholder sampai admin mengunggah foto (FASE 8).
 */
class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'umkm' => 'kopi-tuguran', 'category' => 'minuman',
                'name' => 'Kopi Robusta Tuguran 250 gram', 'price' => 45000, 'stock' => 40,
                'weight_gram' => 300, 'unit' => 'pack', 'sold_count' => 128, 'rating_avg' => 4.8,
                'is_featured' => true,
                'description' => 'Robusta sangrai medium-dark dari lereng Sumbing. Profil rasa cokelat pekat dengan keasaman rendah, cocok untuk seduhan tubruk maupun espresso.',
            ],
            [
                'umkm' => 'kopi-tuguran', 'category' => 'minuman',
                'name' => 'Kopi Arabika Tuguran 200 gram', 'price' => 62000, 'stock' => 25,
                'weight_gram' => 250, 'unit' => 'pack', 'sold_count' => 74, 'rating_avg' => 4.9,
                'is_featured' => false,
                'description' => 'Arabika Magelang dengan sentuhan rasa gula aren dan jeruk manis. Digiling sesuai pesanan agar aroma tetap terjaga.',
            ],
            [
                'umkm' => 'batik-srikandi-magelang', 'category' => 'fashion-batik',
                'name' => 'Batik Tulis Srikandi Motif Borobudur', 'price' => 285000, 'stock' => 12,
                'weight_gram' => 700, 'unit' => 'pcs', 'sold_count' => 46, 'rating_avg' => 4.9,
                'is_featured' => true,
                'description' => 'Batik tulis 2,4 meter dengan motif stupa Borobudur dan flora lokal. Pewarna alam indigo dan soga tingi, dikerjakan sekitar sepuluh hari per lembar.',
            ],
            [
                'umkm' => 'batik-srikandi-magelang', 'category' => 'fashion-batik',
                'name' => 'Kain Batik Cap Srikandi 2 Meter', 'price' => 175000, 'stock' => 20,
                'weight_gram' => 600, 'unit' => 'pcs', 'sold_count' => 63, 'rating_avg' => 4.7,
                'is_featured' => false,
                'description' => 'Batik cap katun primisima, cocok untuk seragam komunitas maupun kemeja harian. Tersedia beberapa pilihan warna dasar.',
            ],
            [
                'umkm' => 'kriya-bambu-getas', 'category' => 'kriya-kerajinan',
                'name' => 'Keranjang Bambu Serbaguna', 'price' => 65000, 'stock' => 30,
                'weight_gram' => 900, 'unit' => 'pcs', 'sold_count' => 52, 'rating_avg' => 4.6,
                'is_featured' => true,
                'description' => 'Anyaman bambu apus ukuran 35x25x20 cm untuk hampers, penyimpanan, atau dekorasi. Rangka kokoh dengan finishing pelapis anti jamur.',
            ],
            [
                'umkm' => 'kriya-bambu-getas', 'category' => 'kriya-kerajinan',
                'name' => 'Anyaman Bambu Tempat Tisu', 'price' => 42000, 'stock' => 35,
                'weight_gram' => 400, 'unit' => 'pcs', 'sold_count' => 88, 'rating_avg' => 4.5,
                'is_featured' => false,
                'description' => 'Tempat tisu meja dengan anyaman rapat dan tutup engsel. Bisa dipesan dengan ukiran nama sesuai permintaan.',
            ],
            [
                'umkm' => 'dapur-bunda-sari', 'category' => 'pertanian-olahan',
                'name' => 'Wedang Uwuh Instan Premium', 'price' => 35000, 'stock' => 50,
                'weight_gram' => 250, 'unit' => 'box', 'sold_count' => 141, 'rating_avg' => 4.8,
                'is_featured' => true,
                'description' => 'Racikan rempah tradisional: jahe, kayu secang, cengkih, kayu manis, dan gula batu. Satu boks berisi 10 sachet siap seduh.',
            ],
            [
                'umkm' => 'dapur-bunda-sari', 'category' => 'pertanian-olahan',
                'name' => 'Gula Kelapa Cetak 500 gram', 'price' => 28000, 'stock' => 45,
                'weight_gram' => 500, 'unit' => 'pack', 'sold_count' => 97, 'rating_avg' => 4.7,
                'is_featured' => false,
                'description' => 'Gula kelapa cetak tanpa pemanis tambahan dari nira kelapa petani Mertoyudan. Aroma karamel lembut untuk masakan maupun minuman.',
            ],
        ];

        foreach ($products as $index => $data) {
            $umkm = UmkmProfile::query()->where('slug', $data['umkm'])->firstOrFail();
            $category = Category::query()->where('slug', $data['category'])->firstOrFail();

            unset($data['umkm'], $data['category']);

            Product::withTrashed()->updateOrCreate(
                ['slug' => str($data['name'])->slug()->value()],
                $data + [
                    'umkm_profile_id' => $umkm->id,
                    'category_id' => $category->id,
                    'sku' => 'EWD-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                    'thumbnail' => null,
                    'discount_price' => null,
                    'views' => 0,
                    'is_active' => true,
                    'deleted_at' => null,
                ],
            );
        }

        $this->command?->info('Produk contoh: '.Product::query()->count().' baris.');
    }
}
