<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Category;
use App\Models\DeliveryPoint;
use App\Models\Product;
use App\Models\Setting;
use App\Models\UmkmProfile;
use App\Models\User;
use App\Services\DistanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Memastikan `db:seed` menyiapkan seluruh data awal domain (FASE 3)
 * dan tetap idempoten bila dijalankan berulang.
 */
class DomainSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_mengisi_seluruh_data_awal_domain(): void
    {
        $this->seed();

        // Akun: 3 dari UserSeeder + 3 mitra UMKM tambahan dari UmkmProfileSeeder.
        $this->assertSame(6, User::query()->count());
        $this->assertSame(4, User::query()->where('role', User::ROLE_UMKM)->count());
        $this->assertSame(1, User::query()->where('role', User::ROLE_ADMIN)->count());

        // Pengaturan (settings) — tarif logistik & identitas situs.
        $this->assertGreaterThanOrEqual(12, Setting::query()->count());
        $this->assertSame('E-Wedu', Setting::get('site_name'));
        $this->assertSame(5000, Setting::getInt('shipping_base_fee'));
        $this->assertSame(2500, Setting::getInt('shipping_cost_per_km'));
        $this->assertTrue(Setting::getBool('cod_enabled'));
        $this->assertEqualsWithDelta(-7.4618, Setting::getFloat('campus_origin_lat'), 0.0001);

        // Titik pengiriman: 4 titik gratis + 1 master rule alamat kustom.
        $this->assertSame(5, DeliveryPoint::query()->count());
        $this->assertSame(4, DeliveryPoint::query()->active()->freePoints()->count());
        $this->assertSame(1, DeliveryPoint::query()->custom()->count());
        $this->assertSame(
            ['TGR-01', 'UMC-02', 'BKM-03', 'SNT-04'],
            DeliveryPoint::query()->active()->freePoints()->ordered()->pluck('code')->all(),
        );

        // Kategori produk & artikel.
        $this->assertSame(6, Category::query()->count());
        $this->assertSame(4, ArticleCategory::query()->count());

        // Mitra UMKM terverifikasi dan sudah terbit.
        $this->assertSame(4, UmkmProfile::query()->count());
        $this->assertSame(4, UmkmProfile::query()->verified()->published()->count());

        // Katalog: 8 produk aktif, masing-masing punya mitra & kategori.
        $this->assertSame(8, Product::query()->count());
        $this->assertSame(8, Product::query()->active()->count());
        $this->assertSame(4, Product::query()->featured()->count());
        $this->assertSame(0, Product::query()->whereNull('umkm_profile_id')->count());
        $this->assertSame(0, Product::query()->whereNull('category_id')->count());
        $this->assertSame(0, Product::query()->where('stock', '<=', 0)->count());
        // Thumbnail baru diisi admin pada FASE 8 — katalog memakai placeholder.
        $this->assertSame(8, Product::query()->whereNull('thumbnail')->count());

        // Literasi: 6 artikel terbit, 2 di antaranya unggulan.
        $this->assertSame(6, Article::query()->count());
        $this->assertSame(6, Article::query()->published()->count());
        $this->assertSame(2, Article::query()->featured()->count());

        $story = Article::query()->where('slug', 'cerita-kopi-tuguran-dari-kedai-kecil-ke-marketplace')->firstOrFail();
        $kopiTuguran = UmkmProfile::query()->where('slug', 'kopi-tuguran')->firstOrFail();

        $this->assertTrue($story->umkmProfile->is($kopiTuguran));
        $this->assertSame('umkm@ewedu.test', $story->author->email);
        $this->assertSame('Kopi Tuguran', $story->umkmProfile->business_name);
    }

    public function test_setting_awal_dipakai_kalkulasi_jarak_dari_kampus_tuguran(): void
    {
        $this->seed();

        // Origin settings (Kampus Tuguran) -> UMKM Center Magelang ≈ 2,04 km.
        $distance = app(DistanceService::class)->distanceFromOrigin(-7.4793, 110.2204);

        $this->assertEqualsWithDelta(2.04, $distance, 0.05);
    }

    public function test_akun_mitra_tambahan_bisa_login_dengan_password_default(): void
    {
        $this->seed();

        $user = User::query()->where('email', 'batik.srikandi@ewedu.test')->firstOrFail();

        $this->assertSame(User::ROLE_UMKM, $user->role);
        $this->assertTrue(Hash::check('password', $user->password));
        $this->assertTrue($user->isActive());
        $this->assertDatabaseHas('umkm_profiles', ['user_id' => $user->id]);
    }

    public function test_seeder_idempoten_saat_dijalankan_dua_kali(): void
    {
        $this->seed();

        $before = [
            'users' => User::query()->count(),
            'umkm' => UmkmProfile::query()->count(),
            'products' => Product::query()->count(),
            'articles' => Article::query()->count(),
            'points' => DeliveryPoint::query()->count(),
            'settings' => Setting::query()->count(),
        ];

        $this->seed();

        $this->assertSame($before, [
            'users' => User::query()->count(),
            'umkm' => UmkmProfile::query()->count(),
            'products' => Product::query()->count(),
            'articles' => Article::query()->count(),
            'points' => DeliveryPoint::query()->count(),
            'settings' => Setting::query()->count(),
        ]);
    }
}
