<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['slug' => 'kuliner', 'name' => 'Kuliner', 'icon' => 'heroicon-o-cake', 'sort_order' => 1],
            ['slug' => 'kriya-kerajinan', 'name' => 'Kriya & Kerajinan', 'icon' => 'heroicon-o-sparkles', 'sort_order' => 2],
            ['slug' => 'fashion-batik', 'name' => 'Fashion & Batik', 'icon' => 'heroicon-o-briefcase', 'sort_order' => 3],
            ['slug' => 'pertanian-olahan', 'name' => 'Pertanian & Olahan', 'icon' => 'heroicon-o-beaker', 'sort_order' => 4],
            ['slug' => 'minuman', 'name' => 'Minuman', 'icon' => 'heroicon-o-beaker', 'sort_order' => 5],
            ['slug' => 'jasa-layanan', 'name' => 'Jasa & Layanan', 'icon' => 'heroicon-o-paint-brush', 'sort_order' => 6],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                $category + [
                    'description' => 'Produk kategori '.$category['name'].' dari mitra UMKM Magelang.',
                    'is_active' => true,
                ],
            );
        }

        $this->command?->info('Kategori produk: '.Category::query()->count().' baris.');
    }
}
