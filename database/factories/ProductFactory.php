<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use App\Models\UmkmProfile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = Str::title(fake()->unique()->words(3, true));

        return [
            'umkm_profile_id' => UmkmProfile::factory(),
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'sku' => 'EWD-'.Str::upper(Str::random(6)),
            'description' => fake()->paragraphs(3, true),
            'price' => fake()->numberBetween(5, 300) * 1000,
            'discount_price' => null,
            'stock' => fake()->numberBetween(5, 60),
            'weight_gram' => fake()->numberBetween(200, 2000),
            'unit' => fake()->randomElement(['pcs', 'pack', 'box', 'kg']),
            'thumbnail' => null,
            'is_active' => true,
            'is_featured' => false,
            'sold_count' => fake()->numberBetween(0, 40),
            'views' => fake()->numberBetween(0, 300),
            'rating_avg' => fake()->randomFloat(2, 3.5, 5.0),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
        ]);
    }

    /**
     * Produk diskon 20% (pembulatan ribuan).
     */
    public function discounted(): static
    {
        return $this->state(function (array $attributes) {
            $price = (int) ($attributes['price'] ?? 50000);

            return [
                'price' => $price,
                'discount_price' => (int) (round($price * 0.8 / 1000) * 1000),
            ];
        });
    }
}
