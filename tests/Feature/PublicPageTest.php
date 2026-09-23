<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\DeliveryPoint;
use App\Models\Product;
use App\Models\Setting;
use App\Models\UmkmProfile;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Landing page & halaman statis publik (FASE 4 — Rancangan §2.1).
 *
 * Memastikan route `home` benar-benar menampilkan seksi utama beserta data
 * yang sudah lolos scope domain, dan route `about` menarik tarif/titik
 * pengiriman dari tabel `settings` + `delivery_points` (tanpa hardcode).
 */
class PublicPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Identitas situs, tarif ongkir, dan nomor WhatsApp admin.
        $this->seed(SettingSeeder::class);
    }

    public function test_landing_page_menampilkan_seksi_dan_data_unggulan(): void
    {
        $produk = Product::factory()->featured()->create(['name' => 'Kopi Robusta Tuguran']);
        $artikel = Article::factory()->create(['title' => 'Strategi Jualan Online untuk UMKM']);
        $mitra = UmkmProfile::factory()->create(['business_name' => 'Batik Srikandi Magelang']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Produk Unggulan')
            ->assertSee('Artikel Literasi Terbaru')
            ->assertSee('UMKM Terverifikasi')
            ->assertSee('Sorotan Produk')
            ->assertSee($produk->name)
            ->assertSee($artikel->title)
            ->assertSee($mitra->business_name)
            ->assertSee(route('articles.index'), false)
            ->assertSee(route('umkm.index'), false)
            ->assertSee(Setting::get('site_tagline'));
    }

    public function test_landing_page_menyembunyikan_data_yang_belum_tayang(): void
    {
        // Produk & mitra tersembunyi sengaja dipasang pada mitra yang belum
        // diverifikasi agar seksi "UMKM Terverifikasi" ikut kosong.
        $mitraTersembunyi = UmkmProfile::factory()->unverified()->create([
            'business_name' => 'Mitra Belum Diverifikasi',
        ]);

        Product::factory()->featured()->inactive()->create([
            'name' => 'Produk Nonaktif',
            'umkm_profile_id' => $mitraTersembunyi->id,
        ]);
        Product::factory()->featured()->outOfStock()->create([
            'name' => 'Produk Stok Kosong',
            'umkm_profile_id' => $mitraTersembunyi->id,
        ]);
        Product::factory()->create([
            'name' => 'Produk Tanpa Unggulan',
            'umkm_profile_id' => $mitraTersembunyi->id,
        ]);

        Article::factory()->draft()->create(['title' => 'Draft Rahasia Literasi']);
        UmkmProfile::factory()->unpublished()->create(['business_name' => 'Mitra Belum Dipublikasikan']);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Produk Nonaktif')
            ->assertDontSee('Produk Stok Kosong')
            ->assertDontSee('Produk Tanpa Unggulan')
            ->assertDontSee('Draft Rahasia Literasi')
            ->assertDontSee('Mitra Belum Diverifikasi')
            ->assertDontSee('Mitra Belum Dipublikasikan')
            ->assertSee('Belum ada produk unggulan')
            ->assertSee('Belum ada artikel terbit')
            ->assertSee('Belum ada mitra terverifikasi');
    }

    public function test_landing_page_menampilkan_statistik_dari_database(): void
    {
        $mitra = UmkmProfile::factory()->create();

        Product::factory()->count(2)->create(['umkm_profile_id' => $mitra->id]);
        Article::factory()->count(3)->create();
        DeliveryPoint::factory()->count(4)->create();

        $response = $this->get(route('home'))->assertOk();

        $this->assertSame(2, $response->viewData('stats')['produk']);
        $this->assertSame(1, $response->viewData('stats')['mitra']);
        $this->assertSame(3, $response->viewData('stats')['artikel']);
        $this->assertSame(4, $response->viewData('stats')['titik']);
    }

    public function test_landing_page_tetap_tampil_tanpa_whatsapp_admin(): void
    {
        Setting::query()->where('key', 'whatsapp_admin')->delete();

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('wa.me', false);
    }

    public function test_halaman_tentang_menampilkan_tarif_dan_titik_pengiriman(): void
    {
        DeliveryPoint::factory()->create(['name' => 'UMKM Center Magelang']);

        $this->get(route('about'))
            ->assertOk()
            ->assertSee('Edu-Commerce untuk UMKM Magelang')
            ->assertSee('Rp 5.000')                    // shipping_base_fee
            ->assertSee('Rp 2.500')                    // shipping_cost_per_km
            ->assertSee('3 km')                        // shipping_free_radius_km
            ->assertSee('UMKM Center Magelang')
            ->assertSee('Titik Pengiriman')
            ->assertSee('Pertanyaan yang sering diajukan')
            ->assertSee(Setting::get('whatsapp_admin'), false);
    }
}
