<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Inti FASE 6 (Rancangan §3.3 & §5.1 — anti-oversell).
 *
 * `createFromCart()` membungkus seluruh proses dalam `DB::transaction()` +
 * `lockForUpdate()`:
 *   1. Kunci baris `cart_items` lalu kunci seluruh baris produk terkait.
 *   2. Validasi ulang ketersediaan & stok (jangan percaya snapshot keranjang).
 *   3. Simpan snapshot item ke `order_items`, lalu kurangi stok &
 *      naikkan `sold_count`.
 *   4. Catat riwayat status awal + hapus `cart_items` (memindahkan, bukan
 *      menyalin) sehingga keranjang bersih setelah checkout sukses.
 *
 * Jika stok tidak mencukupi, transaksi di-rollback dan `ValidationException`
 * dilempar — tidak ada stok yang pernah terpotong sebagian.
 */
class OrderService
{
    /**
     * @param  array<string, mixed>  $attributes  Data pelanggan & pembayaran (sudah divalidasi).
     * @param  array<string, mixed>  $shipping    Hasil resolusi ongkir (titik/kustom + fee).
     */
    public function createFromCart(Cart $cart, array $attributes, array $shipping, ?User $user = null): Order
    {
        return DB::transaction(function () use ($cart, $attributes, $shipping, $user): Order {
            /** @var \Illuminate\Support\Collection<int, CartItem> $items */
            $items = CartItem::query()
                ->where('cart_id', $cart->getKey())
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'cart' => 'Keranjang kosong, tidak ada yang bisa di-checkout.',
                ]);
            }

            /** @var \Illuminate\Support\Collection<int, Product> $products */
            $products = Product::withTrashed()
                ->whereKey($items->pluck('product_id')->unique()->all())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0;
            $lines = [];

            foreach ($items as $item) {
                $product = $products->get($item->product_id);
                $qty = (int) $item->qty;

                $this->guardStock($product, $qty);

                $price = $product->finalPrice();
                $lineSubtotal = $price * $qty;
                $subtotal += $lineSubtotal;

                $lines[] = ['product' => $product, 'qty' => $qty, 'price' => $price, 'subtotal' => $lineSubtotal];
            }

            $discount = max(0, (int) ($attributes['discount'] ?? 0));
            $shippingFee = max(0, (int) ($shipping['shipping_fee'] ?? 0));
            $total = max(0, $subtotal - $discount) + $shippingFee;

            $order = Order::query()->create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $user?->getKey(),
                'customer_name' => $attributes['customer_name'],
                'customer_phone' => $attributes['customer_phone'],
                'customer_email' => $attributes['customer_email'] ?? null,
                'delivery_point_id' => $shipping['delivery_point_id'] ?? null,
                'shipping_method' => $shipping['shipping_method'],
                'shipping_address' => $shipping['shipping_address'] ?? null,
                'shipping_lat' => $shipping['shipping_lat'] ?? null,
                'shipping_lng' => $shipping['shipping_lng'] ?? null,
                'shipping_distance_km' => $shipping['shipping_distance_km'] ?? 0,
                'shipping_fee' => $shippingFee,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'payment_method' => $attributes['payment_method'],
                'payment_status' => $attributes['payment_method'] === Order::PAYMENT_METHOD_COD
                    ? Order::PAYMENT_PAID
                    : Order::PAYMENT_UNPAID,
                'status' => $attributes['payment_method'] === Order::PAYMENT_METHOD_COD
                    ? Order::STATUS_CONFIRMED
                    : Order::STATUS_PENDING,
                'customer_note' => $attributes['customer_note'] ?? null,
            ]);

            foreach ($lines as $line) {
                /** @var Product $product */
                $product = $line['product'];

                // Snapshot nama/harga agar histori tidak berubah bila produk diedit.
                $order->items()->create([
                    'product_id' => $product->getKey(),
                    'umkm_profile_id' => $product->umkm_profile_id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'product_thumbnail' => $product->thumbnail,
                    'price' => $line['price'],
                    'qty' => $line['qty'],
                    'subtotal' => $line['subtotal'],
                ]);

                $product->decrement('stock', $line['qty']);
                $product->increment('sold_count', $line['qty']);
            }

            $initialStatus = $attributes['payment_method'] === Order::PAYMENT_METHOD_COD
                ? Order::STATUS_CONFIRMED
                : Order::STATUS_PENDING;

            $order->statusHistories()->create([
                'status' => $initialStatus,
                'note' => $attributes['payment_method'] === Order::PAYMENT_METHOD_COD
                    ? 'Pesanan dibuat dan langsung dikonfirmasi (COD).'
                    : 'Pesanan dibuat pelanggan melalui checkout.',
                'changed_by' => $user?->getKey(),
            ]);

            // Pindahkan (bukan salin): keranjang dikosongkan setelah stok aman.
            $cart->items()->delete();

            return $order->load(['items', 'deliveryPoint']);
        });
    }

    /**
     * Produk wajib ada, aktif, dan stoknya mencukupi qty keranjang.
     */
    private function guardStock(?Product $product, int $qty): void
    {
        if ($product === null || $product->trashed() || ! $product->is_active) {
            throw ValidationException::withMessages([
                'cart' => 'Salah satu produk di keranjang sudah tidak dijual lagi.',
            ]);
        }

        if ($qty < 1) {
            throw ValidationException::withMessages([
                'cart' => "Jumlah untuk {$product->name} tidak valid.",
            ]);
        }

        if ($product->stock < $qty) {
            throw ValidationException::withMessages([
                'cart' => "Stok {$product->name} tinggal {$product->stock} {$product->unit} — sesuaikan jumlah di keranjang.",
            ]);
        }
    }
}