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
                'code' => 'TGR-01',
                'name' => 'Kampus Tuguran Untidar',
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
                'code' => 'UMC-02',
                'name' => 'UMKM Center Magelang',
                'type' => DeliveryPoint::TYPE_FREE_POINT,
                'address' => 'Jl. Wilis No. 12, Magersari, Magelang Selatan, Kota Magelang',
                'latitude' => -7.4793000,
                'longitude' => 110.2204000,
                'is_free_shipping' => true,
                'free_radius_km' => 0,
                'operation_hours' => '08.00-20.00 WIB',
                'notes' => 'Ambil pesanan di galeri UMKM Center lantai 1.',
                'sort_order' => 2,
            ],
            [
                'code' => 'BKM-03',
                'name' => 'Balai Kota Magelang',
                'type' => DeliveryPoint::TYPE_FREE_POINT,
                'address' => 'Jl. Tenaga No. 1, Magelang Tengah, Kota Magelang',
                'latitude' => -7.4708000,
                'longitude' => 110.2177000,
                'is_free_shipping' => true,
                'free_radius_km' => 0,
                'operation_hours' => '08.00-15.00 WIB',
                'notes' => 'Ambil di area parkir timur Balai Kota.',
                'sort_order' => 3,
            ],
            [
                'code' => 'SNT-04',
                'name' => 'Sentra UMKM Magelang',
                'type' => DeliveryPoint::TYPE_FREE_POINT,
                'address' => 'Jl. Sunan Kalijaga, Banyurojo, Mertoyudan, Kabupaten Magelang',
                'latitude' => -7.4885000,
                'longitude' => 110.2290000,
                'is_free_shipping' => true,
                'free_radius_km' => 0,
                'operation_hours' => '09.00-17.00 WIB',
                'notes' => 'Ambil di stand E-Wedu area sentra oleh-oleh.',
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
