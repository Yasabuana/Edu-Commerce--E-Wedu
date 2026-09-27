<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\UmkmProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Katalog produk publik (Rancangan §2.1 & §6 FASE 5).
 *
 * Hanya produk aktif milik UMKM terverifikasi & terbit yang tampil
 * (`publiclyVisible()`); selain itu 404.
 */
class ProductController extends Controller
{
    /**
     * Jumlah produk per halaman katalog.
     */
    private const PER_PAGE = 12;

    /**
     * Jumlah produk rekomendasi di halaman detail.
     */
    private const RELATED_LIMIT = 4;

    /**
     * Katalog produk: pencarian, filter kategori/UMKM/rentang harga, urut, paginasi.
     */
    public function index(Request $request): View
    {
        $filter = $this->filters($request);

        $products = Product::query()
            ->with(['category:id,name,slug', 'umkmProfile', 'images'])
            ->publiclyVisible()
            ->search($filter['q'])
            ->categorySlug($filter['kategori'])
            ->umkmSlug($filter['umkm'])
            ->priceBetween($filter['min'], $filter['max'])
            ->sorted($filter['urut'])
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $bounds = Product::query()
            ->publiclyVisible()
            ->selectRaw('MIN(COALESCE(discount_price, price)) as min_price, MAX(COALESCE(discount_price, price)) as max_price')
            ->first();

        return view('products.index', [
            'products' => $products,
            'filter' => $filter,
            'categories' => Category::query()
                ->active()
                ->ordered()
                ->withCount(['products' => fn (Builder $query) => $query->active()])
                ->get(),
            'umkmOptions' => UmkmProfile::query()
                ->verified()
                ->published()
                ->withCount(['products' => fn (Builder $query) => $query->active()])
                ->orderBy('business_name')
                ->get(),
            'kategoriAktif' => $filter['kategori'] === ''
                ? null
                : Category::query()->where('slug', $filter['kategori'])->first(),
            'umkmAktif' => $filter['umkm'] === ''
                ? null
                : UmkmProfile::query()->where('slug', $filter['umkm'])->first(),
            'hargaMinimum' => (int) ($bounds->min_price ?? 0),
            'hargaMaksimum' => (int) ($bounds->max_price ?? 0),
            'sortOptions' => Product::sortOptions(),
        ]);
    }

    /**
     * Detail produk: galeri, informasi penjual, form tambah ke keranjang, rekomendasi.
     */
    public function show(Product $product): View
    {
        abort_unless($product->isPubliclyVisible(), 404);

        $product->load(['category:id,name,slug', 'umkmProfile', 'images']);
        $product->increment('views');

        $related = Product::query()
            ->with(['category:id,name,slug', 'umkmProfile', 'images'])
            ->publiclyVisible()
            ->where('category_id', $product->category_id)
            ->whereKeyNot($product->getKey())
            ->orderByDesc('sold_count')
            ->orderByDesc('rating_avg')
            ->limit(self::RELATED_LIMIT)
            ->get();

        return view('products.show', [
            'product' => $product,
            'related' => $related,
        ]);
    }

    /**
     * Normalisasi seluruh parameter filter katalog dari query string.
     *
     * @return array{q: string, kategori: string, umkm: string, min: int|null, max: int|null, urut: string}
     */
    private function filters(Request $request): array
    {
        return [
            'q' => mb_substr(trim((string) $request->query('q', '')), 0, 100),
            'kategori' => mb_substr(trim((string) $request->query('kategori', '')), 0, 100),
            'umkm' => mb_substr(trim((string) $request->query('umkm', '')), 0, 100),
            'min' => $this->priceQuery($request->query('min')),
            'max' => $this->priceQuery($request->query('max')),
            'urut' => Product::normalizeSort((string) $request->query('urut', Product::SORT_TERBARU)),
        ];
    }

    /**
     * Nilai harga dari query string; null bila kosong/bukan angka/non-positif.
     */
    private function priceQuery(mixed $value): ?int
    {
        $value = is_string($value) ? trim($value) : $value;

        if ($value === null || $value === '' || filter_var($value, FILTER_VALIDATE_INT) === false) {
            return null;
        }

        $number = (int) $value;

        return $number >= 0 ? $number : null;
    }
}
