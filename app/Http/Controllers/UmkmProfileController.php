<?php

namespace App\Http\Controllers;

use App\Models\UmkmProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Direktori mitra UMKM + profil literasi (Rancangan §2.1).
 *
 * Hanya profil terverifikasi (`verified()`) dan terbit (`published()`) yang
 * boleh tampil di sisi publik.
 */
class UmkmProfileController extends Controller
{
    /**
     * Jumlah profil per halaman.
     */
    private const PER_PAGE = 9;

    /**
     * Jumlah produk yang ditampilkan pada tab produk halaman detail.
     */
    private const PRODUCTS_LIMIT = 6;

    /**
     * Daftar mitra UMKM + pencarian, filter kategori, dan pengurutan.
     */
    public function index(Request $request): View
    {
        $kataKunci = mb_substr(trim((string) $request->query('q', '')), 0, 100);
        $kategori = mb_substr(trim((string) $request->query('kategori', '')), 0, 100);
        $urut = (string) $request->query('urut', 'terbaru');

        $profiles = UmkmProfile::query()
            ->withCount(['products' => fn (Builder $query) => $query->active()])
            ->verified()
            ->published()
            ->search($kataKunci)
            ->when($kategori !== '', fn (Builder $query) => $query->where('category_label', $kategori))
            ->when(
                $urut === 'produk',
                fn (Builder $query) => $query->orderByDesc('products_count'),
                fn (Builder $query) => $query->orderByDesc('published_at'),
            )
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $categoryLabels = UmkmProfile::query()
            ->verified()
            ->published()
            ->whereNotNull('category_label')
            ->distinct()
            ->orderBy('category_label')
            ->pluck('category_label');

        return view('umkm.index', [
            'profiles' => $profiles,
            'categoryLabels' => $categoryLabels,
            'kataKunci' => $kataKunci,
            'kategori' => $kategori,
            'urut' => $urut === 'produk' ? 'produk' : 'terbaru',
        ]);
    }

    /**
     * Profil literasi satu mitra: cerita usaha, produk, dan artikelnya.
     */
    public function show(UmkmProfile $umkmProfile): View
    {
        abort_unless(
            $umkmProfile->isVerified()
                && $umkmProfile->published_at !== null
                && $umkmProfile->published_at->isPast(),
            404,
        );

        $umkmProfile->loadCount(['products' => fn (Builder $query) => $query->active()]);

        $products = $umkmProfile->products()
            ->with(['category:id,name,slug', 'images'])
            ->active()
            ->orderByDesc('is_featured')
            ->orderByDesc('sold_count')
            ->limit(self::PRODUCTS_LIMIT)
            ->get();

        $articles = $umkmProfile->articles()
            ->with(['category:id,name,slug', 'author:id,name'])
            ->published()
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view('umkm.show', [
            'umkmProfile' => $umkmProfile,
            'products' => $products,
            'articles' => $articles,
        ]);
    }
}
