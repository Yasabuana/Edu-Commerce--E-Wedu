<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\UmkmProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Katalog produk publik & halaman detail (FASE 5 — Rancangan §2.1).
 */
class CatalogPageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Produk uji dengan nama eksplisit agar assertion mudah dibaca.
     *
     * @param  array<string, mixed>  $atribut
     */
    private function produk(string $nama, array $atribut = []): Product
    {
        return Product::factory()->create(array_merge(['name' => $nama], $atribut));
    }

    public function test_katalog_menampilkan_produk_dari_mitra_terverifikasi(): void
    {
        $produk = $this->produk('Kopi Robusta Tuguran', ['price' => 25000, 'stock' => 15]);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('Produk UMKM Magelang')
            ->assertSee('Kopi Robusta Tuguran')
            ->assertSee('Terapkan filter')
            ->assertSee($produk->price_formatted);
    }

    public function test_katalog_menyembunyikan_produk_nonaktif_dan_mitra_belum_terbit(): void
    {
        Product::factory()->inactive()->create(['name' => 'Produk Nonaktif']);

        $mitraBelumTerbit = UmkmProfile::factory()->unpublished()->create();
        Product::factory()->create([
            'name' => 'Produk Mitra Belum Terbit',
            'umkm_profile_id' => $mitraBelumTerbit->id,
        ]);

        $mitraBelumVerifikasi = UmkmProfile::factory()->unverified()->create();
        Product::factory()->create([
            'name' => 'Produk Mitra Belum Terverifikasi',
            'umkm_profile_id' => $mitraBelumVerifikasi->id,
        ]);

        $this->get(route('products.index'))
            ->assertOk()
            ->assertDontSee('Produk Nonaktif')
            ->assertDontSee('Produk Mitra Belum Terbit')
            ->assertDontSee('Produk Mitra Belum Terverifikasi')
            ->assertSee('Produk tidak ditemukan');
    }

    public function test_pencarian_produk_mencakup_nama_deskripsi_dan_nama_mitra(): void
    {
        $this->produk('Kopi Robusta', ['description' => 'Biji kopi sangrai pilihan']);
        $this->produk('Batik Tulis', ['description' => 'Motif klasik Magelang']);

        $mitra = UmkmProfile::factory()->create(['business_name' => 'Anyaman Bambu Getas']);
        Product::factory()->create([
            'name' => 'Keranjang Serbaguna',
            'description' => 'Kuat dan ringan',
            'umkm_profile_id' => $mitra->id,
        ]);

        $this->assertSame(3, $this->get(route('products.index'))->viewData('products')->total());
        $this->assertSame(1, $this->get(route('products.index', ['q' => 'Batik']))->viewData('products')->total());
        $this->assertSame(1, $this->get(route('products.index', ['q' => 'Motif klasik']))->viewData('products')->total());
        $this->assertSame(1, $this->get(route('products.index', ['q' => 'Bambu']))->viewData('products')->total());
    }

    public function test_filter_kategori_dan_penjual_membatasi_hasil(): void
    {
        $kuliner = Category::factory()->create(['name' => 'Kuliner', 'slug' => 'kuliner']);
        $kriya = Category::factory()->create(['name' => 'Kriya', 'slug' => 'kriya']);

        $mitraA = UmkmProfile::factory()->create(['business_name' => 'Dapur Bunda Sari', 'slug' => 'dapur-bunda-sari']);
        $mitraB = UmkmProfile::factory()->create(['business_name' => 'Kriya Bambu Getas', 'slug' => 'kriya-bambu-getas']);

        $this->produk('Sambal Bawang', ['category_id' => $kuliner->id, 'umkm_profile_id' => $mitraA->id]);
        $this->produk('Kue Lapis', ['category_id' => $kuliner->id, 'umkm_profile_id' => $mitraA->id]);
        $this->produk('Tas Anyaman', ['category_id' => $kriya->id, 'umkm_profile_id' => $mitraB->id]);

        $this->assertSame(2, $this->get(route('products.index', ['kategori' => 'kuliner']))->viewData('products')->total());
        $this->assertSame(1, $this->get(route('products.index', ['umkm' => 'kriya-bambu-getas']))->viewData('products')->total());
        $this->assertSame(
            1,
            $this->get(route('products.index', ['kategori' => 'kriya', 'umkm' => 'kriya-bambu-getas']))
                ->viewData('products')->total(),
        );

        // Kombinasi filter yang tidak punya irisan menghasilkan hasil kosong.
        $this->assertSame(
            0,
            $this->get(route('products.index', ['kategori' => 'kuliner', 'umkm' => 'kriya-bambu-getas']))
                ->viewData('products')->total(),
        );

        $response = $this->get(route('products.index', ['kategori' => 'kuliner']))->assertOk();
        $this->assertSame($kuliner->id, $response->viewData('kategoriAktif')->id);
        $this->assertSame($mitraA->id, $this->get(route('products.index', ['umkm' => 'dapur-bunda-sari']))->viewData('umkmAktif')->id);
    }

    public function test_filter_rentang_harga_memakai_harga_setelah_diskon(): void
    {
        $this->produk('Harga Murah', ['price' => 10000]);
        $this->produk('Harga Sedang', ['price' => 50000]);
        $this->produk('Harga Mahal', ['price' => 150000]);
        $this->produk('Harga Diskon', ['price' => 100000, 'discount_price' => 8000]);

        $this->assertSame(
            2,
            $this->get(route('products.index', ['min' => 9000, 'max' => 60000]))->viewData('products')->total(),
        );

        // Rentang terbalik tetap dipahami sebagai rentang yang sama.
        $this->assertSame(
            2,
            $this->get(route('products.index', ['min' => 60000, 'max' => 9000]))->viewData('products')->total(),
        );

        // Nilai tidak masuk akal diabaikan, bukan membuat hasil kosong.
        $this->assertSame(
            4,
            $this->get(route('products.index', ['min' => 'abc', 'max' => '-5']))->viewData('products')->total(),
        );

        $this->assertSame(
            1,
            $this->get(route('products.index', ['max' => 8000]))->viewData('products')->total(),
        );
    }

    public function test_pengurutan_harga_memakai_harga_efektif(): void
    {
        $this->produk('Mahal', ['price' => 200000]);
        $this->produk('Murah', ['price' => 12000]);
        $this->produk('Diskon', ['price' => 90000, 'discount_price' => 5000]);

        $termurah = $this->get(route('products.index', ['urut' => 'termurah']))->viewData('products');
        $this->assertSame(['Diskon', 'Murah', 'Mahal'], $termurah->pluck('name')->all());

        $termahal = $this->get(route('products.index', ['urut' => 'termahal']))->viewData('products');
        $this->assertSame(['Mahal', 'Murah', 'Diskon'], $termahal->pluck('name')->all());
    }

    public function test_pengurutan_terlaris_rating_dan_nilai_tak_dikenal(): void
    {
        $this->produk('Kurang Laris', ['sold_count' => 1, 'rating_avg' => 3.8]);
        $this->produk('Paling Laris', ['sold_count' => 50, 'rating_avg' => 4.2]);
        $this->produk('Rating Terbaik', ['sold_count' => 10, 'rating_avg' => 4.9]);

        $terlaris = $this->get(route('products.index', ['urut' => 'terlaris']))->viewData('products');
        $this->assertSame('Paling Laris', $terlaris->first()->name);

        $rating = $this->get(route('products.index', ['urut' => 'rating']))->viewData('products');
        $this->assertSame('Rating Terbaik', $rating->first()->name);

        $tidakDikenal = $this->get(route('products.index', ['urut' => 'urut-yang-tidak-ada']))->assertOk();
        $this->assertSame(Product::SORT_TERBARU, $tidakDikenal->viewData('filter')['urut']);
        $this->assertSame('Rating Terbaik', $tidakDikenal->viewData('products')->first()->name);
    }

    public function test_paginasi_katalog_dua_belas_per_halaman_dan_filter_terbawa(): void
    {
        Product::factory()->count(15)->create(['name' => 'Produk Katalog']);

        $halamanPertama = $this->get(route('products.index'))->assertOk()->viewData('products');
        $this->assertSame(15, $halamanPertama->total());
        $this->assertCount(12, $halamanPertama->items());

        $halamanKedua = $this->get(route('products.index', ['page' => 2]))->assertOk()->viewData('products');
        $this->assertCount(3, $halamanKedua->items());

        $denganFilter = $this->get(route('products.index', ['q' => 'Katalog']))->assertOk()->viewData('products');
        $this->assertStringContainsString('q=Katalog', $denganFilter->url(2));
    }

    public function test_detail_produk_menampilkan_harga_penjual_dan_form_keranjang(): void
    {
        $mitra = UmkmProfile::factory()->create([
            'business_name' => 'Kopi Tuguran',
            'whatsapp' => '081234567890',
        ]);

        $produk = Product::factory()->discounted()->create([
            'name' => 'Kopi Robusta 250g',
            'umkm_profile_id' => $mitra->id,
            'stock' => 12,
            'weight_gram' => 250,
            'unit' => 'pack',
        ]);

        $this->get(route('products.show', $produk))
            ->assertOk()
            ->assertSee('Kopi Robusta 250g')
            ->assertSee($produk->price_formatted)
            ->assertSee($produk->original_price_formatted)
            ->assertSee('Kopi Tuguran')
            ->assertSee('Stok tersedia 12 pack')
            ->assertSee('Tambah ke keranjang')
            ->assertSee('action="'.route('cart.store').'"', false)
            ->assertSee('wa.me', false)
            ->assertSee('Diskon '.$produk->discount_percent.'%');
    }

    public function test_detail_produk_dengan_stok_habis_tidak_menampilkan_form_keranjang(): void
    {
        $produk = Product::factory()->outOfStock()->create(['name' => 'Produk Stok Habis']);

        $this->get(route('products.show', $produk))
            ->assertOk()
            ->assertSee('Produk Stok Habis')
            ->assertSee('Stok sedang habis')
            ->assertDontSee('Tambah ke keranjang');
    }

    public function test_detail_produk_menambah_penghitung_dilihat_dan_menampilkan_rekomendasi_kategori(): void
    {
        $kategori = Category::factory()->create();
        $produk = $this->produk('Produk Utama', ['category_id' => $kategori->id, 'views' => 7]);
        $this->produk('Produk Serupa', ['category_id' => $kategori->id]);

        $this->get(route('products.show', $produk))
            ->assertOk()
            ->assertSee('Produk lain di kategori ini')
            ->assertSee('Produk Serupa');

        $this->assertSame(8, $produk->refresh()->views);
    }

    public function test_detail_produk_nonaktif_atau_mitra_tidak_valid_menghasilkan_404(): void
    {
        $this->get(route('products.show', Product::factory()->inactive()->create()))->assertNotFound();

        $mitraBelumTerbit = UmkmProfile::factory()->unpublished()->create();
        $produkBelumTerbit = Product::factory()->create(['umkm_profile_id' => $mitraBelumTerbit->id]);
        $this->get(route('products.show', $produkBelumTerbit))->assertNotFound();

        $mitraBelumVerifikasi = UmkmProfile::factory()->unverified()->create();
        $produkBelumVerifikasi = Product::factory()->create(['umkm_profile_id' => $mitraBelumVerifikasi->id]);
        $this->get(route('products.show', $produkBelumVerifikasi))->assertNotFound();

        $this->get(route('products.show', 'slug-yang-tidak-ada'))->assertNotFound();
    }

    public function test_galeri_produk_mendahulukan_thumbnail_lalu_foto_galeri_tanpa_duplikat(): void
    {
        Storage::fake('public');

        $produk = Product::factory()->create(['thumbnail' => 'products/utama.jpg']);
        ProductImage::factory()->create(['product_id' => $produk->id, 'path' => 'products/galeri-1.jpg']);
        ProductImage::factory()->create(['product_id' => $produk->id, 'path' => 'products/utama.jpg']);

        $this->assertSame([
            Storage::disk('public')->url('products/utama.jpg'),
            Storage::disk('public')->url('products/galeri-1.jpg'),
        ], $produk->fresh()->load('images')->galleryUrls());

        $this->assertSame([], Product::factory()->create(['name' => 'Tanpa Foto'])->galleryUrls());
    }
}
