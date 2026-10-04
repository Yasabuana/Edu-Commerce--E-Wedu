<?php

namespace Database\Seeders;

use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

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
                'description' => 'Kedai kopi rakyat yang menyangrai robusta dan arabika dari lereng Sumbing serta Merbabu.',
            ],
            [
                'email' => 'angkringan.untidar@ewedu.test',
                'owner' => 'Sutarjo',
                'business_name' => 'Angkringan Kampus Untidar',
                'slug' => 'angkringan-kampus-untidar',
                'category_label' => 'Kuliner',
                'phone' => '081255667788',
                'whatsapp' => '081255667788',
                'address' => 'Jl. Kapten Suparman No. 41, Potrobangsan, Magelang Utara',
                'latitude' => -7.4621000,
                'longitude' => 110.2151000,
                'description' => 'Angkringan mahasiswa dengan menu nasi kucing, sate usus, dan wedang jahe. Buka sore hingga tengah malam, jadi langganan camilan hemat warga Kampus Untidar.',
            ],
            [
                'email' => 'jajanan.tinah@ewedu.test',
                'owner' => 'Tinah Sutarwati',
                'business_name' => 'Jajanan Pasar Bu Tinah',
                'slug' => 'jajanan-pasar-bu-tinah',
                'category_label' => 'Kuliner',
                'phone' => '081266778899',
                'whatsapp' => '081266778899',
                'address' => 'Pasar Rejowinangun, Magelang Tengah, Kota Magelang',
                'latitude' => -7.4736000,
                'longitude' => 110.2248000,
                'description' => 'Aneka getuk, cenil, tiwul, dan jajanan pasar tradisional yang dibuat segar setiap pagi tanpa pengawet. Resep warisan yang dijaga turun-temurun sejak tiga generasi.',
            ],
            [
                'email' => 'kedai.segar@ewedu.test',
                'owner' => 'Ratna Dewi',
                'business_name' => 'Kedai Segar Kekinian',
                'slug' => 'kedai-segar-kekinian',
                'category_label' => 'Minuman',
                'phone' => '081299001122',
                'whatsapp' => '081299001122',
                'address' => 'Jl. Ahmat Yani No. 88, Tidar Baru, Magelang Selatan',
                'latitude' => -7.4802000,
                'longitude' => 110.2211000,
                'description' => 'Kedai minuman kekinian dengan racikan kopi susu gula aren, matcha latte, dan es teler botolan siap kirim. Semua bahan dipilih dari produsen lokal.',
            ],
            [
                'email' => 'dapur.sari@ewedu.test',
                'owner' => 'Sri Wahyuni',
                'business_name' => 'Dapur Bunda Sari',
                'slug' => 'dapur-bunda-sari',
                'category_label' => 'Kuliner',
                'phone' => '081288990011',
                'whatsapp' => '081288990011',
                'address' => 'Jl. Sunan Kalijaga, Banyurojo, Mertoyudan, Kabupaten Magelang',
                'latitude' => -7.4861000,
                'longitude' => 110.2280000,
                'description' => 'Produsen makanan dan minuman tradisional Magelang: Permen Wedang Uwuh, wedang uwuh instan, dan gula kelapa cetak. Permen Wedang Uwuh adalah produk unggulan yang menjadi ikon E-Wedu.',
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
