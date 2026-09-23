<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Perilaku model `Article` yang dipakai halaman publik FASE 4:
 * status terbit, format tanggal Indonesia, dan scope pencarian/kategori.
 */
class ArticleModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_published_butuh_status_published_dan_tanggal_terlampaui(): void
    {
        $this->assertTrue((new Article([
            'status' => Article::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
        ]))->isPublished());

        $this->assertFalse((new Article([
            'status' => Article::STATUS_DRAFT,
            'published_at' => now()->subDay(),
        ]))->isPublished());

        $this->assertFalse((new Article([
            'status' => Article::STATUS_PUBLISHED,
            'published_at' => null,
        ]))->isPublished());

        $this->assertFalse((new Article([
            'status' => Article::STATUS_PUBLISHED,
            'published_at' => now()->addDay(),
        ]))->isPublished());
    }

    public function test_tanggal_terbit_diformat_dalam_bahasa_indonesia(): void
    {
        $artikel = Article::factory()->create(['published_at' => '2026-01-05 08:30:00']);

        $this->assertSame('id', config('app.locale'));
        $this->assertSame('05 Januari 2026', $artikel->published_at_formatted);
    }

    public function test_scope_published_menolak_draft_dan_artikel_terjadwal(): void
    {
        Article::factory()->create(['title' => 'Terbit Hari Ini']);
        Article::factory()->draft()->create();
        Article::factory()->create(['published_at' => now()->addDay()]);

        $judul = Article::query()->published()->pluck('title')->all();

        $this->assertSame(['Terbit Hari Ini'], $judul);
    }

    public function test_scope_search_dan_category_slug_memfilter_artikel(): void
    {
        $pemasaran = ArticleCategory::factory()->create(['slug' => 'pemasaran']);

        Article::factory()->create([
            'article_category_id' => $pemasaran->id,
            'title' => 'Trik Foto Produk',
            'excerpt' => 'Ringkasan',
            'body' => 'Isi',
        ]);
        Article::factory()->create(['title' => 'Topik Lain', 'excerpt' => 'Ringkasan', 'body' => 'Isi']);

        $this->assertSame(1, Article::query()->published()->search('Foto')->count());
        $this->assertSame(2, Article::query()->published()->search('   ')->count());
        $this->assertSame(1, Article::query()->published()->categorySlug('pemasaran')->count());
        $this->assertSame(0, Article::query()->published()->categorySlug('pembukuan')->count());
    }
}
