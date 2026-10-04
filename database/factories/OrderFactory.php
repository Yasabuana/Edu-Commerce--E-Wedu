<?php

namespace Database\Factories;

use App\Models\DeliveryPoint;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(2, 40) * 5000;

        return [
            'order_number' => fn () => Order::generateOrderNumber(),
            'user_id' => User::factory(),
            'customer_name' => fake()->name(),
            'customer_phone' => '08'.fake()->numerify('##########'),
            'customer_email' => fake()->safeEmail(),
            'delivery_point_id' => null,
            'shipping_method' => Order::SHIPPING_PICKUP_POINT,
            'shipping_address' => null,
            'shipping_lat' => null,
            'shipping_lng' => null,
            'shipping_distance_km' => 0,
            'shipping_fee' => 0,
            'subtotal' => $subtotal,
            'discount' => 0,
            'total' => $subtotal,
            'payment_method' => Order::PAYMENT_METHOD_COD,
            'payment_status' => Order::PAYMENT_UNPAID,
            'status' => Order::STATUS_PENDING,
            'customer_note' => null,
            'admin_note' => null,
        ];
    }

    /**
     * Guest checkout (tanpa `user_id`) — Rancangan §1.2.
     */
    public function guest(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }

    public function qris(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => Order::PAYMENT_METHOD_QRIS,
            'payment_status' => Order::PAYMENT_UNPAID,
            'status' => Order::STATUS_PENDING,
        ]);
    }

    public function awaitingVerification(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_method' => Order::PAYMENT_METHOD_QRIS,
            'payment_status' => Order::PAYMENT_AWAITING_VERIFICATION,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => Order::PAYMENT_PAID,
            'verified_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_COMPLETED,
            'payment_status' => Order::PAYMENT_PAID,
            'delivered_at' => now(),
            'completed_at' => now(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Order::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancel_reason' => 'Dibatalkan pembeli.',
        ]);
    }

    /**
     * Opsi 5: alamat kustom dengan jarak & ongkir hasil kalkulasi.
     */
    public function customDelivery(float $distanceKm = 4.25, int $fee = 9000): static
    {
        return $this->state(fn (array $attributes) => [
            'delivery_point_id' => null,
            'shipping_method' => Order::SHIPPING_CUSTOM_DELIVERY,
            'shipping_address' => fake()->streetAddress().', Magelang',
            'shipping_lat' => -7.4700000,
            'shipping_lng' => 110.2300000,
            'shipping_distance_km' => $distanceKm,
            'shipping_fee' => $fee,
            'total' => (int) $attributes['subtotal'] + $fee,
        ]);
    }

    /**
     * Ambil salah satu titik gratis ongkir sebagai tujuan.
     */
    public function pickupAt(?DeliveryPoint $deliveryPoint = null): static
    {
        return $this->state(fn (array $attributes) => [
            'delivery_point_id' => $deliveryPoint?->id ?? DeliveryPoint::factory(),
            'shipping_method' => Order::SHIPPING_PICKUP_POINT,
            'shipping_fee' => 0,
        ]);
    }
}
