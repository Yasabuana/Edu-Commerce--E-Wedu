<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = Str::title(fake()->unique()->sentence(6));

        return [
            'user_id' => User::factory()->admin(),
            'article_category_id' => ArticleCategory::factory(),
            'umkm_profile_id' => null,
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 9999),
            'excerpt' => fake()->sentence(18),
            'body' => fake()->paragraphs(6, true),
            'cover_image' => null,
            'status' => Article::STATUS_PUBLISHED,
            'is_featured' => false,
            'views' => fake()->numberBetween(0, 500),
            'published_at' => now()->subDays(fake()->numberBetween(1, 30)),
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Article::STATUS_DRAFT,
            'published_at' => null,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Artikel sebagai profil/sejarah mitra UMKM.
     */
    public function forUmkm(?UmkmProfile $umkmProfile = null): static
    {
        return $this->state(fn (array $attributes) => [
            'umkm_profile_id' => $umkmProfile?->id ?? UmkmProfile::factory(),
            'user_id' => User::factory()->umkm(),
        ]);
    }
}
