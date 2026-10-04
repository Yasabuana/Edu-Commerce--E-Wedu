<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCheckoutRequest;
use App\Models\DeliveryPoint;
use App\Models\Order;
use App\Models\Setting;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\ShippingCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Checkout & logistik mandiri (Rancangan §2.4 & §6 FASE 6).
 *
 * Halaman checkout menawarkan 5 opsi pengiriman: 4 titik gratis ongkir
 * (`pickup_point`) + 1 alamat kustom (`custom_delivery`) yang ongkirnya
 * dihitung otomatis via `ShippingCalculator`.
 */
class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly OrderService $orderService,
        private readonly ShippingCalculator $shippingCalculator,
    ) {
    }

    /**
     * Tampilkan halaman checkout (keranjang wajib terisi).
     */
    public function index(): View|RedirectResponse
    {
        $cart = $this->cartService->read();

        if ($cart === null || $cart->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Keranjang masih kosong. Tambahkan produk dulu sebelum checkout.');
        }

        $cart->load(['items.product.images', 'items.product.category', 'items.umkmProfile']);

        return view('checkout.index', [
            'cart' => $cart,
            'items' => $cart->items,
            'subtotal' => (int) $cart->subtotal,
            'jumlahItem' => (int) $cart->total_qty,
            'totalBerat' => (int) $cart->items->sum(
                fn ($item): int => (int) ($item->product?->weight_gram ?? 0) * (int) $item->qty,
            ),
            'deliveryPoints' => DeliveryPoint::query()->active()->freePoints()->ordered()->get(),
            'pengguna' => auth()->user(),
            'originLat' => Setting::getFloat('campus_origin_lat'),
            'originLng' => Setting::getFloat('campus_origin_lng'),
        ]);
    }

    /**
     * Buat pesanan dari keranjang (stok dikunci di `OrderService`).
     */
    public function store(StoreCheckoutRequest $request): RedirectResponse
    {
        $cart = $this->cartService->read();

        if ($cart === null || $cart->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Keranjang masih kosong. Tambahkan produk dulu sebelum checkout.');
        }

        $validated = $request->validated();
        $shipping = $this->resolveShipping($validated);

        $order = $this->orderService->createFromCart($cart, $validated, $shipping, $request->user());

        if ($order->payment_method === Order::PAYMENT_METHOD_QRIS) {
            return redirect()
                ->route('orders.pay', $order)
                ->with('success', "Pesanan {$order->order_number} berhasil dibuat. Silakan lakukan pembayaran QRIS.");
        }

        return redirect()
            ->route('orders.show', $order)
            ->with('success', "Pesanan {$order->order_number} berhasil dibuat. Terima kasih!");
    }

    /**
     * Terjemahkan pilihan pengiriman menjadi data kolom `orders`.
     *
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function resolveShipping(array $validated): array
    {
        if ($validated['shipping_method'] === Order::SHIPPING_PICKUP_POINT) {
            $point = DeliveryPoint::query()
                ->active()
                ->freePoints()
                ->findOrFail((int) $validated['delivery_point_id']);

            return [
                'delivery_point_id' => $point->getKey(),
                'shipping_method' => Order::SHIPPING_PICKUP_POINT,
                'shipping_address' => $point->address,
                'shipping_lat' => (float) $point->latitude,
                'shipping_lng' => (float) $point->longitude,
                'shipping_distance_km' => 0.0,
                'shipping_fee' => 0,
            ];
        }

        $quote = $this->shippingCalculator->calculate(
            (float) $validated['shipping_lat'],
            (float) $validated['shipping_lng'],
        );

        if ($quote['out_of_range']) {
            throw ValidationException::withMessages([
                'shipping_lat' => 'Alamat berada di luar jangkauan pengiriman (maksimal '
                    .$quote['max_distance_km'].' km dari Kampus Untidar).',
            ]);
        }

        return [
            'delivery_point_id' => null,
            'shipping_method' => Order::SHIPPING_CUSTOM_DELIVERY,
            'shipping_address' => $validated['shipping_address'],
            'shipping_lat' => (float) $validated['shipping_lat'],
            'shipping_lng' => (float) $validated['shipping_lng'],
            'shipping_distance_km' => $quote['distance_km'],
            'shipping_fee' => $quote['fee'],
        ];
    }
}