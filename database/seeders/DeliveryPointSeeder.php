<?php

namespace Database\Seeders;

use App\Models\DeliveryPoint;
use Illuminate\Database\Seeder;

/**
 * 4 titik gratis ongkir + 1 master rule alamat kustom (Rancangan §1.2 & §2.4).
 */
class DeliveryPointSeeder extends Seeder
{
    public function run(): void
    {
        $points = [
            [
                'code' => 'KMP-01',
                'name' => 'Kampus Untidar',
                'type' => DeliveryPoint::TYPE_FREE_POINT,
                'address' => 'Jl. Kapten Suparman No. 39, Potrobangsan, Magelang Utara, Kota Magelang',
                'latitude' => -7.4618000,
                'longitude' => 110.2148000,
                'is_free_shipping' => true,
                'free_radius_km' => 0,
                'operation_hours' => '08.00-16.00 WIB',
                'notes' => 'Titik utama (origin). Tunjukkan nomor pesanan kepada petugas loket E-Wedu.',
                'sort_order' => 1,
            ],
            [
                'code' => 'ALN-02',
                'name' => 'Alun-Alun Magelang',
                'type' => DeliveryPoint::TYPE_FREE_POINT,
                'address' => 'Jl. Alun-Alun Selatan, Kemirirejo, Kec. Magelang Tengah, Kota Magelang',
                'latitude' => -7.4704000,
                'longitude' => 110.2176000,
                'is_free_shipping' => true,
                'free_radius_km' => 0,
                'operation_hours' => '08.00-20.00 WIB',
                'notes' => 'Ambil pesanan di sisi selatan alun-alun, dekat deretan pedagang kaki lima.',
                'sort_order' => 2,
            ],
            [
                'code' => 'RND-03',
                'name' => 'Rindam Magelang',
                'type' => DeliveryPoint::TYPE_FREE_POINT,
                'address' => 'Jl. Kesatrian, Gelangan, Kec. Magelang Tengah, Kota Magelang',
                'latitude' => -7.4789000,
                'longitude' => 110.2195000,
                'is_free_shipping' => true,
                'free_radius_km' => 0,
                'operation_hours' => '08.00-17.00 WIB',
                'notes' => 'Ambil di area penerimaan tamu Rindam IV/Diponegoro.',
                'sort_order' => 3,
            ],
            [
                'code' => 'ART-04',
                'name' => 'Artos Magelang',
                'type' => DeliveryPoint::TYPE_FREE_POINT,
                'address' => 'Jl. Mayjen Bambang Soegeng No.1, Kedungdowo, Kec. Mertoyudan, Kabupaten Magelang',
                'latitude' => -7.4735000,
                'longitude' => 110.2270000,
                'is_free_shipping' => true,
                'free_radius_km' => 0,
                'operation_hours' => '09.00-21.00 WIB',
                'notes' => 'Ambil pesanan di lobi utama Artos Magelang.',
                'sort_order' => 4,
            ],
            [
                'code' => 'CST-00',
                'name' => 'Alamat Kustom (Luar Titik Gratis)',
                'type' => DeliveryPoint::TYPE_CUSTOM,
                'address' => 'Dikirim ke alamat pembeli di sekitar Magelang dengan kalkulasi jarak dari Kampus Tuguran.',
                'latitude' => -7.4618000,
                'longitude' => 110.2148000,
                'is_free_shipping' => false,
                'free_radius_km' => 3.00,
                'operation_hours' => null,
                'notes' => 'Opsi virtual ke-5: ongkir dihitung otomatis (radius gratis 3 km, maksimal 25 km).',
                'sort_order' => 99,
            ],
        ];

        foreach ($points as $point) {
            DeliveryPoint::withTrashed()->updateOrCreate(
                ['code' => $point['code']],
                $point + ['is_active' => true, 'deleted_at' => null],
            );
        }

        $this->command?->info('Titik pengiriman: '.DeliveryPoint::freePoints()->count().' titik gratis + 1 master rule alamat kustom.');
    }
}
