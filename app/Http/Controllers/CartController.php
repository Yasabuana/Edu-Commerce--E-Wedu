<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Keranjang belanja berbasis session (Rancangan §2.1 & §3.1 — FASE 5).
 *
 * Bisa dipakai guest (cart per `session_id`) maupun user login; saat login,
 * `MergeGuestCart` menggabungkan cart guest ke cart user.
 */
class CartController extends Controller
{
    /**
     * Jumlah produk rekomendasi di halaman keranjang kosong.
     */
    private const RECOMMENDATION_LIMIT = 4;

    public function __construct(private readonly CartService $cartService)
    {
    }

    /**
     * Isi keranjang dikelompokkan per UMKM penjual, plus rekomendasi produk.
     */
    public function index(): View
    {
        $cart = $this->cartService->read();

        $cart?->load([
            'items' => fn ($query) => $query->orderBy('id'),
            'items.product.images',
            'items.product.category',
            'items.umkmProfile',
        ]);

        /** @var Collection<int, CartItem> $items */
        $items = $cart?->items ?? collect();

        return view('cart.index', [
            'cart' => $cart,
            'groups' => $items->groupBy(fn (CartItem $item): string => (string) $item->umkm_profile_id),
            'subtotal' => (int) ($cart?->subtotal ?? 0),
            'jumlahItem' => (int) ($cart?->total_qty ?? 0),
            'totalBerat' => (int) $items->sum(
                fn (CartItem $item): int => (int) ($item->product?->weight_gram ?? 0) * (int) $item->qty,
            ),
            'rekomendasi' => $this->recommendations($items),
        ]);
    }

    /**
     * Tambah produk ke keranjang (mendukung respons JSON untuk fetch/AJAX).
     */
    public function store(StoreCartItemRequest $request): RedirectResponse|JsonResponse
    {
        $product = Product::query()->with('umkmProfile')->findOrFail((int) $request->input('product_id'));

        abort_unless($product->isPubliclyVisible(), 404);

        $item = $this->cartService->add($product, (int) $request->input('qty'));

        if ($request->expectsJson()) {
            $cart = $this->cartService->currentCart();

            return response()->json([
                'message' => "{$product->name} ditambahkan ke keranjang.",
                'item_id' => $item->getKey(),
                'qty' => (int) $item->qty,
                'count' => (int) $cart->total_qty,
                'subtotal' => (int) $cart->subtotal,
            ]);
        }

        return back()->with('success', "{$product->name} ditambahkan ke keranjang.");
    }

    /**
     * Ubah jumlah satu baris keranjang.
     */
    public function update(UpdateCartItemRequest $request, CartItem $cartItem): RedirectResponse
    {
        $this->authorizeItem($cartItem);

        $this->cartService->updateQty($cartItem, (int) $request->input('qty'));

        return back()->with('success', 'Jumlah produk di keranjang diperbarui.');
    }

    /**
     * Hapus satu baris keranjang.
     */
    public function destroy(CartItem $cartItem): RedirectResponse
    {
        $this->authorizeItem($cartItem);

        $nama = $cartItem->product?->name ?? 'Produk';

        $this->cartService->remove($cartItem);

        return back()->with('success', "{$nama} dihapus dari keranjang.");
    }

    /**
     * Baris keranjang hanya boleh diubah oleh pemilik cart-nya (selain itu 404).
     */
    private function authorizeItem(CartItem $cartItem): void
    {
        abort_unless($cartItem->isOwnedBy($this->cartService->read()), 404);
    }

    /**
     * Rekomendasi produk: aktif, terverifikasi, stok ada, dan belum ada di keranjang.
     *
     * @param  Collection<int, CartItem>  $items
     * @return Collection<int, Product>
     */
    private function recommendations(Collection $items): Collection
    {
        return Product::query()
            ->with(['category:id,name,slug', 'umkmProfile', 'images'])
            ->publiclyVisible()
            ->inStock()
            ->whereKeyNot($items->pluck('product_id')->all())
            ->orderByDesc('sold_count')
            ->orderByDesc('rating_avg')
            ->limit(self::RECOMMENDATION_LIMIT)
            ->get();
    }
}
