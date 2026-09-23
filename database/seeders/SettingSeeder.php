<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Seed konfigurasi awal (Rancangan §1.2) — idempoten, aman dijalankan ulang.
 */
class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Identitas situs
            'site_name' => ['E-Wedu', Setting::TYPE_STRING, 'site'],
            'site_tagline' => ['Marketplace & Literasi UMKM Magelang', Setting::TYPE_STRING, 'site'],

            // Origin & tarif logistik mandiri (Kampus Tuguran Untidar, Magelang)
            'campus_origin_lat' => ['-7.4618000', Setting::TYPE_STRING, 'shipping'],
            'campus_origin_lng' => ['110.2148000', Setting::TYPE_STRING, 'shipping'],
            'shipping_base_fee' => ['5000', Setting::TYPE_INTEGER, 'shipping'],
            'shipping_cost_per_km' => ['2500', Setting::TYPE_INTEGER, 'shipping'],
            'shipping_free_radius_km' => ['3', Setting::TYPE_INTEGER, 'shipping'],
            'shipping_max_distance_km' => ['25', Setting::TYPE_INTEGER, 'shipping'],

            // Pembayaran & kontak admin
            'whatsapp_admin' => ['6281234567890', Setting::TYPE_STRING, 'site'],
            // Diisi admin lewat menu Pengaturan (upload QRIS statis) pada FASE 8.
            'qris_image' => ['', Setting::TYPE_STRING, 'payment'],
            'qris_merchant_name' => ['E-Wedu UMKM Magelang', Setting::TYPE_STRING, 'payment'],
            'cod_enabled' => ['1', Setting::TYPE_BOOLEAN, 'payment'],
        ];

        foreach ($settings as $key => [$value, $type, $group]) {
            Setting::set($key, $value, $type, $group);
        }

        $this->command?->info('Pengaturan awal: '.count($settings).' baris siap ('.Setting::query()->count().' total).');
    }
}
