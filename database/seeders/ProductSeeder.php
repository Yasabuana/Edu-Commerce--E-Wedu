<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\UmkmProfile;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'umkm' => 'dapur-bunda-sari', 'category' => 'kuliner',
                'name' => 'Permen Wedang Uwuh', 'price' => 25000, 'stock' => 60,
                'weight_gram' => 200, 'unit' => 'pack', 'sold_count' => 215, 'rating_avg' => 4.9,
                'is_featured' => true,
                'description' => 'Permen jahe rempah khas Magelang dengan resep Wedang Uwuh asli. Terbuat dari jahe emprit, kayu secang, cengkih, kayu manis, dan gula kelapa asli. Dikemas dalam pouch 200 gram, cocok untuk pelega tenggorokan dan teman perjalanan. Produk unggulan E-Wedu yang wajib kamu coba!',
            ],
            [
                'umkm' => 'dapur-bunda-sari', 'category' => 'kuliner',
                'name' => 'Wedang Uwuh Instan Premium', 'price' => 35000, 'stock' => 50,
                'weight_gram' => 250, 'unit' => 'box', 'sold_count' => 141, 'rating_avg' => 4.8,
                'is_featured' => true,
                'description' => 'Racikan rempah tradisional: jahe, kayu secang, cengkih, kayu manis, dan gula batu. Satu boks berisi 10 sachet siap seduh.',
            ],
            [
                'umkm' => 'dapur-bunda-sari', 'category' => 'kuliner',
                'name' => 'Gula Kelapa Cetak 500 gram', 'price' => 28000, 'stock' => 45,
                'weight_gram' => 500, 'unit' => 'pack', 'sold_count' => 97, 'rating_avg' => 4.7,
                'is_featured' => false,
                'description' => 'Gula kelapa cetak tanpa pemanis tambahan dari nira kelapa petani Mertoyudan.',
            ],
            [
                'umkm' => 'kopi-tuguran', 'category' => 'minuman',
                'name' => 'Kopi Robusta Tuguran 250 gram', 'price' => 45000, 'stock' => 40,
                'weight_gram' => 300, 'unit' => 'pack', 'sold_count' => 128, 'rating_avg' => 4.8,
                'is_featured' => true,
                'description' => 'Robusta sangrai medium-dark dari lereng Sumbing.',
            ],
            [
                'umkm' => 'kopi-tuguran', 'category' => 'minuman',
                'name' => 'Kopi Arabika Tuguran 200 gram', 'price' => 62000, 'stock' => 25,
                'weight_gram' => 250, 'unit' => 'pack', 'sold_count' => 74, 'rating_avg' => 4.9,
                'is_featured' => false,
                'description' => 'Arabika Magelang.',
            ],
            // ---- Angkringan Kampus Untidar ----
            [
                'umkm' => 'angkringan-kampus-untidar', 'category' => 'kuliner',
                'name' => 'Nasi Kucing Teri Sambal Ijo', 'price' => 5000, 'stock' => 120,
                'weight_gram' => 150, 'unit' => 'pcs', 'sold_count' => 326, 'rating_avg' => 4.7,
                'is_featured' => false,
                'description' => 'Nasi porsi kecil khas angkringan dengan teri medan goreng dan sambal ijo pedas segar. Dibungkus daun pisang, cocok untuk camilan malam.',
            ],
            [
                'umkm' => 'angkringan-kampus-untidar', 'category' => 'kuliner',
                'name' => 'Sate Usus Bakar Bumbu Kecap', 'price' => 8000, 'stock' => 90,
                'weight_gram' => 100, 'unit' => 'porsi', 'sold_count' => 214, 'rating_avg' => 4.6,
                'is_featured' => false,
                'description' => 'Sate usus ayam yang dibakar dengan bumbu kecap manis dan sedikit cabai. Satu porsi berisi lima tusuk, siap dipanaskan kembali.',
            ],
            [
                'umkm' => 'angkringan-kampus-untidar', 'category' => 'minuman',
                'name' => 'Wedang Jahe Angkringan', 'price' => 7000, 'stock' => 100,
                'weight_gram' => 250, 'unit' => 'botol', 'sold_count' => 168, 'rating_avg' => 4.7,
                'is_featured' => false,
                'description' => 'Wedang jahe merah hangat dengan gula batu, dikemas botol agar mudah dibawa dan dipanaskan ulang. Menghangatkan badan saat malam.',
            ],

            // ---- Jajanan Pasar Bu Tinah ----
            [
                'umkm' => 'jajanan-pasar-bu-tinah', 'category' => 'kuliner',
                'name' => 'Getuk Lindri Gula Jawa', 'price' => 15000, 'stock' => 60,
                'weight_gram' => 400, 'unit' => 'pack', 'sold_count' => 152, 'rating_avg' => 4.8,
                'is_featured' => true,
                'description' => 'Getuk lindri singkong yang diuleni dengan gula jawa asli dan kelapa parut. Teksturnya lembut dan manisnya pas, dibuat segar setiap pagi.',
            ],
            [
                'umkm' => 'jajanan-pasar-bu-tinah', 'category' => 'kuliner',
                'name' => 'Kue Lapis Legit Mini', 'price' => 32000, 'stock' => 40,
                'weight_gram' => 500, 'unit' => 'box', 'sold_count' => 88, 'rating_avg' => 4.9,
                'is_featured' => false,
                'description' => 'Lapis legit mini dengan aroma spekuk dan mentega wisman. Satu boks berisi enam potong siap saji, cocok untuk oleh-oleh.',
            ],
            [
                'umkm' => 'jajanan-pasar-bu-tinah', 'category' => 'kuliner',
                'name' => 'Cenil Tiwul Klasik', 'price' => 18000, 'stock' => 50,
                'weight_gram' => 350, 'unit' => 'pack', 'sold_count' => 96, 'rating_avg' => 4.6,
                'is_featured' => false,
                'description' => 'Paduan cenil kenyal dan tiwul legendaris dengan taburan kelapa parut serta gula jawa cair. Jajanan pasar yang tak lekang waktu.',
            ],

            // ---- Kedai Segar Kekinian ----
            [
                'umkm' => 'kedai-segar-kekinian', 'category' => 'minuman',
                'name' => 'Kopi Susu Gula Aren 1 Liter', 'price' => 35000, 'stock' => 40,
                'weight_gram' => 1000, 'unit' => 'botol', 'sold_count' => 123, 'rating_avg' => 4.8,
                'is_featured' => true,
                'description' => 'Kopi susu gula aren dengan espresso robusta Magelang dan susu segar, dikemas botol satu liter untuk berbagi. Sajikan dingin setelah dikocok.',
            ],
            [
                'umkm' => 'kedai-segar-kekinian', 'category' => 'minuman',
                'name' => 'Matcha Latte Bubuk Premium', 'price' => 42000, 'stock' => 45,
                'weight_gram' => 250, 'unit' => 'pack', 'sold_count' => 75, 'rating_avg' => 4.7,
                'is_featured' => false,
                'description' => 'Bubuk matcha grade premium yang cukup diseduh dengan susu panas atau dingin. Tanpa gula tambahan, cocok untuk penikmat rasa asli matcha.',
            ],
            [
                'umkm' => 'kedai-segar-kekinian', 'category' => 'minuman',
                'name' => 'Es Teler Kuah Santan 1 Liter', 'price' => 30000, 'stock' => 35,
                'weight_gram' => 1000, 'unit' => 'botol', 'sold_count' => 64, 'rating_avg' => 4.5,
                'is_featured' => false,
                'description' => 'Es teler botolan berisi nangka, kelapa muda, dan alpukat dengan kuah santan manis. Cukup tambahkan es batu sebelum disajikan.',
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
