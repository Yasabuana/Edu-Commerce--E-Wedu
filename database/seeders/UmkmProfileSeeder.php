<?php

namespace Database\Seeders;

use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * 4 mitra UMKM contoh beserta akunnya (password: `password`).
 * Idempoten: profil dan user dibuat ulang bila sudah ada.
 */
class UmkmProfileSeeder extends Seeder
{
    public function run(): void
    {
        $profiles = [
            [
                'email' => 'umkm@ewedu.test',
                'owner' => 'Mitra UMKM Contoh',
                'business_name' => 'Kopi Tuguran',
                'slug' => 'kopi-tuguran',
                'category_label' => 'Minuman',
                'phone' => '081234567890',
                'whatsapp' => '081234567890',
                'address' => 'Jl. Kapten Suparman No. 12, Potrobangsan, Magelang Utara',
                'latitude' => -7.4625000,
                'longitude' => 110.2153000,
                'description' => 'Kedai kopi rakyat yang menyangrai robusta dan arabika dari lereng Sumbing serta Merbabu. Berdiri sejak 2019, kini melayani pesanan daring melalui E-Wedu.',
            ],
            [
                'email' => 'batik.srikandi@ewedu.test',
                'owner' => 'Siti Srikandi',
                'business_name' => 'Batik Srikandi Magelang',
                'slug' => 'batik-srikandi-magelang',
                'category_label' => 'Fashion & Batik',
                'phone' => '081256789012',
                'whatsapp' => '081256789012',
                'address' => 'Kampung Batik Rejowinangun, Magelang Tengah',
                'latitude' => -7.4733000,
                'longitude' => 110.2245000,
                'description' => 'Sanggar batik tulis dan cap dengan motif khas Magelang-Borobudur. Dikerjakan sepuluh perajin binaan, sebagian ibu rumah tangga di Rejowinangun.',
            ],
            [
                'email' => 'kriya.getas@ewedu.test',
                'owner' => 'Bagas Prayoga',
                'business_name' => 'Kriya Bambu Getas',
                'slug' => 'kriya-bambu-getas',
                'category_label' => 'Kriya & Kerajinan',
                'phone' => '081277889900',
                'whatsapp' => '081277889900',
                'address' => 'Dusun Getas, Bandongan, Kabupaten Magelang',
                'latitude' => -7.4942000,
                'longitude' => 110.1847000,
                'description' => 'Kerajinan anyaman bambu apus: keranjang, tempat tisu, sampai hampers korporat. Bahan dibeli langsung dari petani bambu sekitar Bandongan.',
            ],
            [
                'email' => 'dapur.sari@ewedu.test',
                'owner' => 'Sri Wahyuni',
                'business_name' => 'Dapur Bunda Sari',
                'slug' => 'dapur-bunda-sari',
                'category_label' => 'Pertanian & Olahan',
                'phone' => '081288990011',
                'whatsapp' => '081288990011',
                'address' => 'Jl. Sunan Kalijaga, Banyurojo, Mertoyudan, Kabupaten Magelang',
                'latitude' => -7.4861000,
                'longitude' => 110.2280000,
                'description' => 'Olahan pangan lokal: wedang uwuh instan, gula kelapa, dan camilan tradisional. Bekerja sama dengan kelompok tani perempuan Mertoyudan.',
            ],
        ];

        foreach ($profiles as $index => $data) {
            $user = User::withTrashed()->firstOrNew(['email' => $data['email']]);
            $user->fill([
                'name' => $data['owner'],
                'password' => $user->exists ? $user->password : 'password',
                'role' => User::ROLE_UMKM,
                'phone' => $data['phone'],
                'address' => $data['address'],
                'is_active' => true,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ]);
            $user->deleted_at = null;
            $user->save();

            UmkmProfile::withTrashed()->updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'user_id' => $user->id,
                    'business_name' => $data['business_name'],
                    'owner_name' => $data['owner'],
                    'category_label' => $data['category_label'],
                    'description' => $data['description'],
                    'phone' => $data['phone'],
                    'whatsapp' => $data['whatsapp'],
                    'address' => $data['address'],
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'instagram' => '@'.str_replace('-', '', $data['slug']),
                    'is_verified' => true,
                    'verified_at' => now(),
                    'published_at' => now()->subDays(5 - $index),
                    'deleted_at' => null,
                ],
            );
        }

        $this->command?->info('Profil UMKM: '.UmkmProfile::query()->count().' mitra terverifikasi.');
    }
}
