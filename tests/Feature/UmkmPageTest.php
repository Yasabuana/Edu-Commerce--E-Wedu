<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Product;
use App\Models\UmkmProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Direktori mitra UMKM publik: daftar (pencarian, filter kategori, urutan)
 * dan profil literasi satu mitra (FASE 4 — Rancangan §2.1).
 */
class UmkmPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_daftar_mitra_menampilkan_profil_terverifikasi_dan_terbit(): void
    {
        $mitra = UmkmProfile::factory()->create(['business_name' => 'Batik Srikandi Magelang']);

        $this->get(route('umkm.index'))
            ->assertOk()
            ->assertSee('Mitra UMKM Magelang')
            ->assertSee($mitra->business_name)
            ->assertSee('Terverifikasi')
            ->assertSee('Daftar Mitra UMKM');
    }

    public function test_daftar_mitra_menyembunyikan_profil_belum_terverifikasi_atau_belum_terbit(): void
    {
        UmkmProfile::factory()->unverified()->create(['business_name' => 'Mitra Belum Diverifikasi']);
        UmkmProfile::factory()->unpublished()->create(['business_name' => 'Mitra Belum Dipublikasikan']);

        $this->get(route('umkm.index'))
            ->assertOk()
            ->assertDontSee('Mitra Belum Diverifikasi')
            ->assertDontSee('Mitra Belum Dipublikasikan')
            ->assertSee('Mitra tidak ditemukan');
    }

    public function test_pencarian_mitra_mencakup_nama_usaha_pemilik_dan_deskripsi(): void
    {
        UmkmProfile::factory()->create(['business_name' => 'Kriya Bambu Getas', 'owner_name' => 'Pak Slamet', 'description' => 'Anyaman bambu']);
        UmkmProfile::factory()->create(['business_name' => 'Dapur Bunda Sari', 'owner_name' => 'Bu Sari', 'description' => 'Katering rumahan']);
        UmkmProfile::factory()->create(['business_name' => 'Rajut Magelang', 'owner_name' => 'Bu Wati', 'description' => 'Rajut shawl']);

        $this->assertSame(3, $this->get(route('umkm.index'))->viewData('profiles')->total());
        $this->assertSame(1, $this->get(route('umkm.index', ['q' => 'Slamet']))->viewData('profiles')->total());
        $this->assertSame(1, $this->get(route('umkm.index', ['q' => 'Dapur']))->viewData('profiles')->total());
        $this->assertSame(1, $this->get(route('umkm.index', ['q' => 'shawl']))->viewData('profiles')->total());
    }

    public function test_filter_kategori_usaha_dan_pengurutan_jumlah_produk(): void
    {
        $ramai = UmkmProfile::factory()->create(['business_name' => 'Mitra Ramai', 'category_label' => 'Kuliner']);
        $sepi = UmkmProfile::factory()->create(['business_name' => 'Mitra Sepi', 'category_label' => 'Kriya']);

        Product::factory()->count(3)->create(['umkm_profile_id' => $ramai->id]);
        Product::factory()->create(['umkm_profile_id' => $sepi->id]);

        $filter = $this->get(route('umkm.index', ['kategori' => 'Kuliner']))->assertOk();
        $this->assertSame(1, $filter->viewData('profiles')->total());
        $this->assertSame('Kuliner', $filter->viewData('kategori'));
        $this->assertSame(['Kriya', 'Kuliner'], $filter->viewData('categoryLabels')->all());

        $urut = $this->get(route('umkm.index', ['urut' => 'produk']))->assertOk();
        $this->assertSame('Mitra Ramai', $urut->viewData('profiles')->first()->business_name);
        $this->assertSame(3, $urut->viewData('profiles')->first()->products_count);
    }

    public function test_parameter_urutan_tidak_dikenal_dinormalkan_ke_terbaru(): void
    {
        $response = $this->get(route('umkm.index', ['urut' => 'termurah']))->assertOk();

        $this->assertSame('terbaru', $response->viewData('urut'));
    }

    public function test_detail_mitra_menampilkan_cerita_produk_dan_artikel(): void
    {
        $mitra = UmkmProfile::factory()->create([
            'business_name' => 'Dapur Bunda Sari',
            'description' => 'Melayani katering rumahan sejak 2015.',
        ]);

        $produk = Product::factory()->create(['umkm_profile_id' => $mitra->id, 'name' => 'Sambal Bawang Bu Sari']);
        Product::factory()->create(['umkm_profile_id' => $mitra->id, 'is_active' => false, 'name' => 'Menu Tidak Aktif']);

        $artikel = Article::factory()->forUmkm($mitra)->create(['title' => 'Cerita Dapur Bunda Sari']);

        $this->get(route('umkm.show', $mitra))
            ->assertOk()
            ->assertSee('Cerita Usaha')
            ->assertSee('Melayani katering rumahan sejak 2015.')
            ->assertSee($produk->name)
            ->assertDontSee('Menu Tidak Aktif')
            ->assertSee($artikel->title)
            ->assertSee('Informasi kontak');
    }

    public function test_detail_mitra_belum_terverifikasi_atau_belum_terbit_dianggap_tidak_ada(): void
    {
        $belumVerifikasi = UmkmProfile::factory()->unverified()->create();
        $belumTerbit = UmkmProfile::factory()->unpublished()->create();

        $this->get(route('umkm.show', $belumVerifikasi))->assertNotFound();
        $this->get(route('umkm.show', $belumTerbit))->assertNotFound();
    }

    public function test_detail_mitra_tanpa_produk_menampilkan_empty_state(): void
    {
        $mitra = UmkmProfile::factory()->create();

        $this->get(route('umkm.show', $mitra))
            ->assertOk()
            ->assertSee('Belum ada produk aktif')
            ->assertSee('Mitra ini belum menulis artikel literasi.');
    }
}
