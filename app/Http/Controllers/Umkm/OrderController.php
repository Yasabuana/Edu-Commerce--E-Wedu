<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Pesanan masuk untuk mitra UMKM (FASE 8).
 *
 * PENTING: Setiap query DIPAKSA menyaring order_items hanya dari produk
 * milik UMKM yang sedang login — tidak mungkin bocor data pesanan UMKM lain.
 */
class OrderController extends Controller
{
    /**
     * Daftar item pesanan untuk produk milik UMKM yang sedang login.
     */
    public function index(Request $request): View
    {
        $umkmProfile = $request->user()->umkmProfile;

        abort_if($umkmProfile === null, 404, 'Profil UMKM belum lengkap.');

        $query = OrderItem::with(['order', 'product'])
            ->where('umkm_profile_id', $umkmProfile->id);

        // Filter status pesanan
        if ($status = $request->query('status')) {
            $query->whereHas('order', function ($q) use ($status): void {
                $q->where('status', $status);
            });
        }

        // Urutkan: yang terbaru di atas
        $query->orderBy('created_at', 'desc');

        $items = $query->paginate(15)->withQueryString();

        return view('umkm-panel.pesanan.index', compact('items', 'umkmProfile'));
    }

    /**
     * Detail satu item pesanan (termasuk info order dan produk).
     */
    public function show(OrderItem $orderItem, Request $request): View
    {
        $umkmProfile = $request->user()->umkmProfile;

        abort_if($umkmProfile === null, 404);
        abort_if((int) $orderItem->umkm_profile_id !== (int) $umkmProfile->id, 403,
            'Item pesanan ini bukan milik UMKM Anda.');

        $orderItem->load(['order.user', 'order.items', 'product']);

        return view('umkm-panel.pesanan.show', compact('orderItem', 'umkmProfile'));
    }
}
