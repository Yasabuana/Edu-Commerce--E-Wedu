<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderStatusHistory>
 */
class OrderStatusHistoryFactory extends Factory
{
    protected $model = OrderStatusHistory::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'status' => Order::STATUS_PENDING,
            'note' => fake()->sentence(8),
            'changed_by' => User::factory()->admin(),
        ];
    }

    /**
     * Riwayat tanpa pelaku (mis. perubahan otomatis sistem).
     */
    public function bySystem(): static
    {
        return $this->state(fn (array $attributes) => [
            'changed_by' => null,
        ]);
    }

    public function status(string $status, ?string $note = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
            'note' => $note ?? $attributes['note'],
        ]);
    }
}
