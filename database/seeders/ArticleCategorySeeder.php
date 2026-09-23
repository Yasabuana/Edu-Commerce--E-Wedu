<?php

namespace Database\Seeders;

use App\Models\ArticleCategory;
use Illuminate\Database\Seeder;

class ArticleCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['slug' => 'pemasaran-digital', 'name' => 'Pemasaran Digital', 'sort_order' => 1],
            ['slug' => 'manajemen-usaha', 'name' => 'Manajemen Usaha', 'sort_order' => 2],
            ['slug' => 'legalitas-umkm', 'name' => 'Legalitas & Perizinan', 'sort_order' => 3],
            ['slug' => 'cerita-umkm', 'name' => 'Cerita UMKM', 'sort_order' => 4],
        ];

        foreach ($categories as $category) {
            ArticleCategory::updateOrCreate(
                ['slug' => $category['slug']],
                $category + [
                    'description' => 'Kumpulan artikel '.$category['name'].' untuk pelaku UMKM Magelang.',
                    'is_active' => true,
                ],
            );
        }

        $this->command?->info('Kategori artikel: '.ArticleCategory::query()->count().' baris.');
    }
}
