<?php

namespace Database\Factories;

use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<UmkmProfile>
 */
class UmkmProfileFactory extends Factory
{
    protected $model = UmkmProfile::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $businessName = fake()->unique()->company();

        return [
            'user_id' => User::factory()->umkm(),
            'business_name' => $businessName,
            'slug' => Str::slug($businessName).'-'.fake()->unique()->numberBetween(1, 9999),
            'owner_name' => fake()->name(),
            'category_label' => fake()->randomElement([
                'Kuliner', 'Kriya', 'Fashion', 'Jasa', 'Pertanian',
            ]),
            'description' => fake()->paragraphs(2, true),
            'logo' => null,
            'cover_image' => null,
            'phone' => '08'.fake()->numerify('##########'),
            'whatsapp' => '08'.fake()->numerify('##########'),
            'address' => fake()->streetAddress().', Magelang',
            'latitude' => fake()->latitude(-7.62, -7.35),
            'longitude' => fake()->longitude(110.10, 110.35),
            'instagram' => '@'.Str::slug($businessName, ''),
            'website' => null,
            'is_verified' => true,
            'verified_at' => now(),
            'published_at' => now(),
        ];
    }

    /**
     * Mitra UMKM yang belum diverifikasi admin.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_verified' => false,
            'verified_at' => null,
        ]);
    }

    /**
     * Profil belum tampil di halaman publik.
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'published_at' => null,
        ]);
    }
}
