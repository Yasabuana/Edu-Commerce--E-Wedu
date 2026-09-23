<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Literasi UMKM publik: daftar & detail artikel (Rancangan §2.1).
 *
 * Hanya artikel dengan scope `published()` yang tampil; draft/terjadwal
 * dianggap tidak ada (404), termasuk saat dibuka langsung lewat slug.
 */
class ArticleController extends Controller
{
    /**
     * Jumlah artikel per halaman.
     */
    private const PER_PAGE = 6;

    /**
     * Daftar artikel + filter kategori + pencarian.
     */
    public function index(Request $request): View
    {
        [$kataKunci, $kategoriSlug] = $this->filters($request);

        $articles = Article::query()
            ->with(['category:id,name,slug', 'author:id,name', 'umkmProfile:id,business_name,slug'])
            ->published()
            ->search($kataKunci)
            ->when($kategoriSlug !== '', fn (Builder $query) => $query->categorySlug($kategoriSlug))
            ->orderByDesc('published_at')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $categories = ArticleCategory::query()
            ->active()
            ->ordered()
            ->withCount(['articles' => fn (Builder $query) => $query->published()])
            ->get();

        $popular = Article::query()
            ->published()
            ->orderByDesc('views')
            ->orderByDesc('published_at')
            ->limit(5)
            ->get(['id', 'title', 'slug', 'views', 'published_at', 'article_category_id']);

        return view('articles.index', [
            'articles' => $articles,
            'categories' => $categories,
            'popular' => $popular,
            'kataKunci' => $kataKunci,
            'kategoriSlug' => $kategoriSlug,
        ]);
    }

    /**
     * Halaman baca artikel + pencatat jumlah dibaca.
     */
    public function show(Article $article): View
    {
        abort_unless($article->isPublished(), 404);

        $article->increment('views');
        $article->load(['category', 'author', 'umkmProfile']);

        $related = Article::query()
            ->with(['category:id,name,slug'])
            ->published()
            ->where('article_category_id', $article->article_category_id)
            ->whereKeyNot($article->getKey())
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view('articles.show', [
            'article' => $article,
            'related' => $related,
        ]);
    }

    /**
     * Normalisasi filter query string (aman untuk LIKE & slug — selalu
     * di-bind sebagai parameter, bukan concat mentah ke SQL).
     *
     * @return array{0: string, 1: string}
     */
    private function filters(Request $request): array
    {
        $kataKunci = mb_substr(trim((string) $request->query('q', '')), 0, 100);
        $kategoriSlug = mb_substr(trim((string) $request->query('kategori', '')), 0, 100);

        return [$kataKunci, $kategoriSlug];
    }
}
