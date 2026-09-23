<?php

namespace Database\Factories;

use App\Models\DeliveryPoint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeliveryPoint>
 */
class DeliveryPointFactory extends Factory
{
    protected $model = DeliveryPoint::class;

    /**
     * Koordinat default di sekitar Magelang (origin Kampus Tuguran).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Titik Jemput '.fake()->unique()->city(),
            'code' => 'TGR-'.str_pad((string) fake()->unique()->numberBetween(1, 99), 2, '0', STR_PAD_LEFT),
            'type' => DeliveryPoint::TYPE_FREE_POINT,
            'address' => fake()->streetAddress().', Magelang',
            'latitude' => -7.4752000,
            'longitude' => 110.2177000,
            'is_free_shipping' => true,
            'free_radius_km' => 0,
            'operation_hours' => '08.00-16.00 WIB',
            'notes' => 'Tunjukkan nomor pesanan kepada petugas.',
            'sort_order' => fake()->numberBetween(1, 10),
            'is_active' => true,
        ];
    }

    /**
     * Master rule untuk opsi alamat kustom (radius gratis & tarif per km).
     */
    public function custom(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Alamat Kustom (Luar Titik Gratis)',
            'code' => 'CST-00',
            'type' => DeliveryPoint::TYPE_CUSTOM,
            'is_free_shipping' => false,
            'free_radius_km' => 3.00,
            'sort_order' => 99,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
