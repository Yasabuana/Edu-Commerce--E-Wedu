<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'method' => Payment::METHOD_COD,
            'amount' => fn (array $attributes) => (int) Order::find($attributes['order_id'])?->total,
            'proof_path' => null,
            'sender_name' => null,
            'sender_bank' => null,
            'qris_reference' => null,
            'status' => Payment::STATUS_PENDING,
            'verified_by' => null,
            'verified_at' => null,
            'rejection_reason' => null,
            'gateway_response' => null,
        ];
    }

    /**
     * Pembayaran QRIS dengan bukti transfer di disk privat.
     */
    public function qris(): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => Payment::METHOD_QRIS,
            'proof_path' => 'payments/bukti-'.fake()->unique()->numberBetween(1, 9999).'.jpg',
            'sender_name' => fake()->name(),
            'sender_bank' => fake()->randomElement(['BCA', 'Mandiri', 'BRI', 'BNI', 'DANA']),
            'qris_reference' => 'QR'.fake()->unique()->numerify('#####'),
        ]);
    }

    public function verified(?User $admin = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Payment::STATUS_VERIFIED,
            'verified_by' => $admin?->id ?? User::factory()->admin(),
            'verified_at' => now(),
        ]);
    }

    public function rejected(string $reason = 'Bukti transfer tidak terbaca.'): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Payment::STATUS_REJECTED,
            'verified_by' => User::factory()->admin(),
            'verified_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }
}
