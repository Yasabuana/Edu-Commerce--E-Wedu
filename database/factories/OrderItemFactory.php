<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Field snapshot (`product_name`, `price`, ...) diturunkan dari produk
 * yang dipilih agar data uji konsisten.
 *
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'umkm_profile_id' => fn (array $attributes) => Product::find($attributes['product_id'])?->umkm_profile_id,
            'product_name' => fn (array $attributes) => Product::find($attributes['product_id'])?->name ?? 'Produk Contoh',
            'product_sku' => fn (array $attributes) => Product::find($attributes['product_id'])?->sku,
            'product_thumbnail' => null,
            'price' => fn (array $attributes) => (int) Product::find($attributes['product_id'])?->price,
            'qty' => 1,
            'subtotal' => fn (array $attributes) => (int) Product::find($attributes['product_id'])?->price,
        ];
    }

    public function qty(int $qty): static
    {
        return $this->state(fn (array $attributes) => [
            'qty' => $qty,
            'subtotal' => (int) $attributes['price'] * $qty,
        ]);
    }
}
