<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Sumber tunggal logika keranjang (Rancangan §3.1 & §6 FASE 5).
 *
 * Aturan penting:
 * - Harga disimpan sebagai snapshot saat item masuk keranjang.
 * - Qty tidak pernah melebihi stok yang tersedia (divalidasi ulang saat merge).
 * - Produk nonaktif / terhapus tidak bisa ditambahkan.
 */
class CartService
{
    /**
     * Keranjang berjalan tanpa efek samping (bisa null).
     */
    public function read(): ?Cart
    {
        return Cart::currentCart();
    }

    /**
     * Keranjang berjalan, dibuat bila belum ada (dipakai operasi tulis).
     */
    public function currentCart(): Cart
    {
        return Cart::forCurrentUser();
    }

    /**
     * Total qty untuk badge navbar.
     */
    public function count(): int
    {
        return Cart::currentItemCount();
    }

    /**
     * Tambah produk ke keranjang; qty digabung bila produk sudah ada di sana.
     */
    public function add(Product $product, int $qty = 1): CartItem
    {
        $cart = $this->currentCart();

        return DB::transaction(function () use ($cart, $product, $qty): CartItem {
            $produk = Product::query()->whereKey($product->getKey())->lockForUpdate()->first();

            $this->guardAvailability($produk, $qty);

            $item = CartItem::query()
                ->where('cart_id', $cart->getKey())
                ->where('product_id', $produk->getKey())
                ->lockForUpdate()
                ->first();

            $totalQty = ($item?->qty ?? 0) + $qty;

            if ($totalQty > $produk->stock) {
                throw ValidationException::withMessages([
                    'qty' => "Stok {$produk->name} tersedia {$produk->stock} {$produk->unit}.",
                ]);
            }

            if ($item === null) {
                return $cart->items()->create([
                    'product_id' => $produk->getKey(),
                    'umkm_profile_id' => $produk->umkm_profile_id,
                    'price_snapshot' => $produk->finalPrice(),
                    'qty' => $totalQty,
                ]);
            }

            $item->update([
                'qty' => $totalQty,
                'umkm_profile_id' => $produk->umkm_profile_id,
                'price_snapshot' => $produk->finalPrice(),
            ]);

            return $item;
        });
    }

    /**
     * Ubah qty satu baris keranjang (validasi stok ulang).
     */
    public function updateQty(CartItem $item, int $qty): CartItem
    {
        return DB::transaction(function () use ($item, $qty): CartItem {
            $baris = CartItem::query()->whereKey($item->getKey())->lockForUpdate()->firstOrFail();
            $produk = Product::query()->whereKey($baris->product_id)->lockForUpdate()->first();

            $this->guardAvailability($produk, $qty);

            if ($qty > $produk->stock) {
                throw ValidationException::withMessages([
                    'qty' => "Stok tersisa {$produk->stock} {$produk->unit}.",
                ]);
            }

            $baris->update([
                'qty' => $qty,
                'price_snapshot' => $produk->finalPrice(),
            ]);

            return $baris;
        });
    }

    /**
     * Hapus satu baris keranjang.
     */
    public function remove(CartItem $item): void
    {
        $item->delete();
    }

    /**
     * Kosongkan seluruh isi keranjang.
     */
    public function clear(Cart $cart): int
    {
        return $cart->items()->delete();
    }

    /**
     * Gabungkan keranjang guest ke keranjang user saat login (Rancangan §3.1).
     *
     * - Qty produk yang sama dijumlahkan, dibatasi stok.
     * - Produk nonaktif/terhapus/stok habis dilewati (tidak dipindahkan).
     * - Baris & cart guest selalu dibersihkan setelah proses selesai.
     */
    public function mergeGuestCart(User $user, ?string $sessionId = null): void
    {
        $sessionId ??= $this->guestSessionId();

        if ($sessionId === '') {
            return;
        }

        $guestCart = Cart::query()
            ->forSession($sessionId)
            ->with('items')
            ->first();

        if ($guestCart === null) {
            return;
        }

        if ((int) $guestCart->user_id === (int) $user->getKey()) {
            // Cart session ini ternyata sudah milik user → cukup lepaskan session_id.
            $guestCart->update(['session_id' => null]);

            return;
        }

        DB::transaction(function () use ($user, $guestCart): void {
            $userCart = Cart::query()->forUser($user->getKey())->lockForUpdate()->first();

            foreach ($guestCart->items as $item) {
                $produk = Product::query()->whereKey($item->product_id)->lockForUpdate()->first();

                if ($produk === null || ! $produk->is_active || $produk->stock < 1) {
                    continue;
                }

                // Cart user baru dibuat saat benar-benar ada item yang dipindahkan.
                $userCart ??= Cart::query()->create(['user_id' => $user->getKey()]);

                $existing = CartItem::query()
                    ->where('cart_id', $userCart->getKey())
                    ->where('product_id', $produk->getKey())
                    ->lockForUpdate()
                    ->first();

                $qty = min(($existing?->qty ?? 0) + (int) $item->qty, (int) $produk->stock);

                if ($existing === null) {
                    $userCart->items()->create([
                        'product_id' => $produk->getKey(),
                        'umkm_profile_id' => $produk->umkm_profile_id,
                        'price_snapshot' => $produk->finalPrice(),
                        'qty' => $qty,
                    ]);

                    continue;
                }

                $existing->update([
                    'qty' => $qty,
                    'price_snapshot' => $produk->finalPrice(),
                ]);
            }

            $guestCart->items()->delete();
            $guestCart->delete();
        });
    }

    /**
     * Id session keranjang guest: jejak di session (ditulis saat cart guest dibuat
     * pada `Cart::forCurrentUser()`), fallback ke session id yang sedang aktif.
     * Nilai jejak diambil sekali pakai agar tidak basi.
     */
    private function guestSessionId(): string
    {
        if (! app()->bound('session')) {
            return '';
        }

        return (string) session()->pull(Cart::GUEST_SESSION_KEY, (string) session()->getId());
    }

    /**
     * Produk wajib ada, aktif, dan stoknya masih tersedia.
     */
    private function guardAvailability(?Product $product, int $qty): void
    {
        if ($product === null || ! $product->is_active) {
            throw ValidationException::withMessages([
                'product_id' => 'Produk tidak tersedia atau sudah tidak dijual.',
            ]);
        }

        if ($qty < 1) {
            throw ValidationException::withMessages([
                'qty' => 'Jumlah minimal 1.',
            ]);
        }

        if ($product->stock < 1) {
            throw ValidationException::withMessages([
                'qty' => "Stok {$product->name} sedang habis.",
            ]);
        }
    }
}
