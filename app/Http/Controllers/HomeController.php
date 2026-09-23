<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use App\Models\DeliveryPoint;
use App\Models\Product;
use App\Models\Setting;
use App\Models\UmkmProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

/**
 * Landing page publik E-Wedu (Rancangan §2.1 — route `home`).
 *
 * Semua data ditarik dengan scope domain (FASE 3) + eager loading agar
 * tidak ada N+1 saat render Blade.
 */
class HomeController extends Controller
{
    public function index(): View
    {
        $featuredProducts = Product::query()
            ->with([
                'category:id,name,slug',
                'umkmProfile:id,business_name,slug,is_verified',
                'images',
            ])
            ->active()
            ->featured()
            ->inStock()
            ->orderByDesc('sold_count')
            ->limit(8)
            ->get();

        $latestArticles = Article::query()
            ->with(['category:id,name,slug', 'author:id,name', 'umkmProfile:id,business_name,slug'])
            ->published()
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        $featuredUmkms = UmkmProfile::query()
            ->withCount(['products' => fn (Builder $query) => $query->active()])
            ->verified()
            ->published()
            ->orderByDesc('verified_at')
            ->limit(4)
            ->get();

        $categories = Category::query()
            ->active()
            ->ordered()
            ->withCount(['products' => fn (Builder $query) => $query->active()])
            ->get();

        return view('home', [
            'featuredProducts' => $featuredProducts,
            'latestArticles' => $latestArticles,
            'featuredUmkms' => $featuredUmkms,
            'categories' => $categories,
            'tagline' => Setting::get('site_tagline'),
            'whatsappAdmin' => Setting::get('whatsapp_admin'),
            'stats' => [
                'produk' => Product::query()->active()->count(),
                'mitra' => UmkmProfile::query()->verified()->published()->count(),
                'artikel' => Article::query()->published()->count(),
                'titik' => DeliveryPoint::query()->active()->freePoints()->count(),
            ],
        ]);
    }
}
