<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\UmkmProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Literasi UMKM publik: daftar (pencarian + filter kategori + paginasi)
 * dan halaman baca artikel (FASE 4 — Rancangan §2.1).
 */
class ArticlePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_daftar_artikel_menampilkan_artikel_terbit_dan_sidebar(): void
    {
        $artikel = Article::factory()->create(['title' => 'Cara Memotret Produk dengan Ponsel']);

        $this->get(route('articles.index'))
            ->assertOk()
            ->assertSee('Artikel Literasi')
            ->assertSee($artikel->title)
            ->assertSee('Artikel Populer')
            ->assertSee('Kategori Literasi');
    }

    public function test_daftar_artikel_menyembunyikan_draft_dan_menampilkan_empty_state(): void
    {
        Article::factory()->draft()->create(['title' => 'Draft Rahasia Literasi']);

        $this->get(route('articles.index'))
            ->assertOk()
            ->assertDontSee('Draft Rahasia Literasi')
            ->assertSee('Artikel tidak ditemukan')
            ->assertSee('Belum ada artikel terbit.');
    }

    public function test_pencarian_artikel_memfilter_judul_ringkasan_dan_isi(): void
    {
        Article::factory()->create(['title' => 'Panduan Kemasan Produk', 'excerpt' => 'Ringkasan biasa', 'body' => 'Isi biasa']);
        Article::factory()->create(['title' => 'Topik Lain', 'excerpt' => 'Menyinggung kemasan juga', 'body' => 'Isi biasa']);
        Article::factory()->create(['title' => 'Sama Sekali Berbeda', 'excerpt' => 'Tidak relevan', 'body' => 'Isi tidak memuat kata kunci']);

        $response = $this->get(route('articles.index', ['q' => 'kemasan']))->assertOk();

        $this->assertSame(2, $response->viewData('articles')->total());
        $this->assertSame('kemasan', $response->viewData('kataKunci'));
    }

    public function test_kata_kunci_pencarian_dipotong_100_karakter(): void
    {
        $response = $this->get(route('articles.index', ['q' => str_repeat('a', 150)]))->assertOk();

        $this->assertSame(100, mb_strlen($response->viewData('kataKunci')));
    }

    public function test_filter_kategori_hanya_menampilkan_artikel_kategori_itu(): void
    {
        $pemasaran = ArticleCategory::factory()->create(['name' => 'Pemasaran', 'slug' => 'pemasaran']);
        ArticleCategory::factory()->create(['name' => 'Pembukuan', 'slug' => 'pembukuan']);

        Article::factory()->count(2)->create(['article_category_id' => $pemasaran->id]);
        Article::factory()->create();

        $response = $this->get(route('articles.index', ['kategori' => 'pemasaran']))->assertOk();

        $this->assertSame(2, $response->viewData('articles')->total());
        $this->assertSame('pemasaran', $response->viewData('kategoriSlug'));
    }

    public function test_daftar_artikel_dipaginasi_enam_per_halaman(): void
    {
        $artikel = Article::factory()->count(7)->create([
            'published_at' => fn () => now()->subDay(),
        ]);

        $response = $this->get(route('articles.index'))->assertOk();
        $this->assertSame(6, $response->viewData('articles')->count());

        $halamanDua = $this->get(route('articles.index', ['page' => 2]))->assertOk();
        $this->assertSame(1, $halamanDua->viewData('articles')->count());
    }

    public function test_detail_artikel_menampilkan_isi_dan_menambah_jumlah_dibaca(): void
    {
        $artikel = Article::factory()->create([
            'title' => 'Menghitung Harga Jual dengan Benar',
            'body' => 'Langkah pertama adalah mencatat seluruh biaya bahan baku.',
            'views' => 10,
        ]);

        $mitra = UmkmProfile::factory()->create(['business_name' => 'Kopi Tuguran']);
        $artikel->update(['umkm_profile_id' => $mitra->id]);

        $terkait = Article::factory()->create([
            'article_category_id' => $artikel->article_category_id,
            'title' => 'Artikel Sekategori Lain',
        ]);

        $viewsTerkait = $terkait->views;

        $this->get(route('articles.show', $artikel))
            ->assertOk()
            ->assertSee('Menghitung Harga Jual dengan Benar')
            ->assertSee('Langkah pertama adalah mencatat seluruh biaya bahan baku.')
            ->assertSee('Kopi Tuguran')
            ->assertSee($terkait->title)
            ->assertSee('Bagikan');

        $this->assertSame(11, $artikel->refresh()->views);
        $this->assertSame($viewsTerkait, $terkait->refresh()->views); // tidak ikut naik
    }

    public function test_detail_artikel_draft_atau_terjadwal_dianggap_tidak_ada(): void
    {
        $draft = Article::factory()->draft()->create();
        $terjadwal = Article::factory()->create(['published_at' => now()->addDay()]);

        $this->get(route('articles.show', $draft))->assertNotFound();
        $this->get(route('articles.show', $terjadwal))->assertNotFound();
    }
}
