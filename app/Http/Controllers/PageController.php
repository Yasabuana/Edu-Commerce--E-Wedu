<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\DeliveryPoint;
use App\Models\Product;
use App\Models\Setting;
use App\Models\UmkmProfile;
use Illuminate\View\View;

/**
 * Halaman statis publik: "Tentang E-Wedu" (Rancangan §2.1 — route `about`).
 *
 * Angka identitas & tarif diambil dari tabel `settings` (Rancangan §1.2),
 * sehingga tidak ada tarif yang di-hardcode di Blade.
 */
class PageController extends Controller
{
    public function about(): View
    {
        return view('about', [
            'siteName' => Setting::get('site_name', config('app.name', 'E-Wedu')),
            'tagline' => Setting::get('site_tagline'),
            'whatsappAdmin' => Setting::get('whatsapp_admin'),
            'points' => DeliveryPoint::query()
                ->active()
                ->freePoints()
                ->ordered()
                ->get(),
            'shipping' => [
                'base_fee' => Setting::getInt('shipping_base_fee'),
                'per_km' => Setting::getInt('shipping_cost_per_km'),
                'free_radius' => Setting::getInt('shipping_free_radius_km'),
                'max_km' => Setting::getInt('shipping_max_distance_km'),
            ],
            'stats' => [
                'produk' => Product::query()->active()->count(),
                'mitra' => UmkmProfile::query()->verified()->published()->count(),
                'artikel' => Article::query()->published()->count(),
                'titik' => DeliveryPoint::query()->active()->freePoints()->count(),
            ],
        ]);
    }
}
