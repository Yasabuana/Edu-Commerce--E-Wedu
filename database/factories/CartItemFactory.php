<?php

namespace Database\Factories;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CartItem>
 */
class CartItemFactory extends Factory
{
    protected $model = CartItem::class;

    /**
     * `umkm_profile_id` dan `price_snapshot` diturunkan dari produk terpilih
     * agar data konsisten dengan harga katalog.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cart_id' => Cart::factory(),
            'product_id' => Product::factory(),
            'umkm_profile_id' => fn (array $attributes) => Product::find($attributes['product_id'])?->umkm_profile_id,
            'price_snapshot' => fn (array $attributes) => (int) Product::find($attributes['product_id'])?->price,
            'qty' => 1,
        ];
    }

    public function qty(int $qty): static
    {
        return $this->state(fn (array $attributes) => [
            'qty' => $qty,
        ]);
    }
}
