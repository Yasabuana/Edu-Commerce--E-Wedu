<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\DeliveryPoint;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Checkout & logistik mandiri (FASE 6 — Rancangan §2.4, §3.3 & §5.1).
 */
class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Origin & tarif logistik agar kalkulasi Haversine deterministik.
     */
    private function setShippingSettings(): void
    {
        Setting::set('campus_origin_lat', '-7.4618000');
        Setting::set('campus_origin_lng', '110.2148000');
        Setting::set('shipping_base_fee', '5000', Setting::TYPE_INTEGER);
        Setting::set('shipping_cost_per_km', '2500', Setting::TYPE_INTEGER);
        Setting::set('shipping_free_radius_km', '3', Setting::TYPE_INTEGER);
        Setting::set('shipping_max_distance_km', '25', Setting::TYPE_INTEGER);
    }

    /**
     * Produk publik (mitra terverifikasi & terbit) dengan stok tertentu.
     */
    private function produk(int $stock = 10, int $price = 15000): Product
    {
        return Product::factory()->create(['price' => $price, 'stock' => $stock]);
    }

    /**
     * Keranjang user login berisi satu produk.
     */
    private function keranjang(User $user, Product $product, int $qty): Cart
    {
        $cart = Cart::factory()->create(['user_id' => $user->id]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'umkm_profile_id' => $product->umkm_profile_id,
            'price_snapshot' => $product->finalPrice(),
            'qty' => $qty,
        ]);

        return $cart;
    }

    public function test_guest_diarahkan_ke_login_saat_membuka_checkout(): void
    {
        $this->get(route('checkout.index'))->assertRedirect(route('login'));
    }

    public function test_checkout_menampilkan_empat_titik_gratis_dan_opsi_alamat_kustom(): void
    {
        $this->setShippingSettings();

        $user = User::factory()->create();
        $product = $this->produk();
        $this->keranjang($user, $product, 1);

        DeliveryPoint::factory()->create(['name' => 'Kampus Untidar']);
        DeliveryPoint::factory()->create(['name' => 'Alun-Alun Magelang']);

        $this->actingAs($user)
            ->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('Kampus Untidar')
            ->assertSee('Alun-Alun Magelang')
            ->assertSee('Alamat Kustom')
            ->assertSee('Buat Pesanan');
    }

    public function test_checkout_titik_gratis_membuat_pesanan_dan_memindahkan_keranjang(): void
    {
        $this->setShippingSettings();

        $user = User::factory()->create();
        $product = $this->produk(stock: 5, price: 15000);
        $cart = $this->keranjang($user, $product, 2);
        $point = DeliveryPoint::factory()->create(['name' => 'Kampus Untidar']);

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'customer_name' => $user->name,
            'customer_phone' => '081234567890',
            'shipping_method' => Order::SHIPPING_PICKUP_POINT,
            'delivery_point_id' => $point->id,
            'payment_method' => Order::PAYMENT_METHOD_COD,
        ]);

        $order = Order::query()->latest('id')->firstOrFail();

        $response->assertRedirect(route('orders.show', $order));

        // Snapshot item & total (ongkir gratis).
        $this->assertSame(1, $order->items()->count());
        $this->assertSame(30000, (int) $order->subtotal);
        $this->assertSame(0, (int) $order->shipping_fee);
        $this->assertSame(30000, (int) $order->total);
        $this->assertSame(Order::STATUS_CONFIRMED, $order->status);
        $this->assertSame(Order::PAYMENT_PAID, $order->payment_status);
        $this->assertSame($point->id, $order->delivery_point_id);
        $this->assertSame($product->name, $order->items()->first()->product_name);

        // Stok berkurang & keranjang dikosongkan (dipindahkan, bukan salinan).
        $this->assertSame(3, $product->fresh()->stock);
        $this->assertSame(0, $cart->items()->count());
        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseCount('order_status_histories', 1);
    }

    public function test_checkout_alamat_kustom_menghitung_ongkir_dan_menyimpannya(): void
    {
        $this->setShippingSettings();

        $user = User::factory()->create();
        $product = $this->produk(stock: 5, price: 10000);
        $this->keranjang($user, $product, 1);

        // ≈ 8,9 km dari origin (gratis radius 3 km, maks 25 km).
        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'customer_name' => $user->name,
            'customer_phone' => '081234567890',
            'shipping_method' => Order::SHIPPING_CUSTOM_DELIVERY,
            'shipping_address' => 'Jl. Uji Coba No. 1, Magelang',
            'shipping_lat' => -7.3818000,
            'shipping_lng' => 110.2148000,
            'payment_method' => Order::PAYMENT_METHOD_QRIS,
        ]);

        $order = Order::query()->latest('id')->firstOrFail();

        $response->assertRedirect(route('orders.pay', $order));
        $this->assertSame(Order::SHIPPING_CUSTOM_DELIVERY, $order->shipping_method);
        $this->assertNull($order->delivery_point_id);
        $this->assertGreaterThan(0, (int) $order->shipping_fee);
        $this->assertSame((int) $order->total, (int) $order->subtotal + (int) $order->shipping_fee);
        $this->assertSame(Order::PAYMENT_UNPAID, $order->payment_status);
        $this->assertSame(Order::STATUS_PENDING, $order->status);
    }

    public function test_checkout_alamat_kustom_di_luar_jangkauan_ditolak(): void
    {
        $this->setShippingSettings();

        $user = User::factory()->create();
        $product = $this->produk(stock: 5, price: 10000);
        $this->keranjang($user, $product, 1);

        $response = $this->actingAs($user)
            ->from(route('checkout.index'))
            ->post(route('checkout.store'), [
                'customer_name' => $user->name,
                'customer_phone' => '081234567890',
                'shipping_method' => Order::SHIPPING_CUSTOM_DELIVERY,
                'shipping_address' => 'Jl. Jauh Sekali, Luar Kota',
                'shipping_lat' => -5.4618000,
                'shipping_lng' => 110.2148000,
                'payment_method' => Order::PAYMENT_METHOD_COD,
            ]);

        $response->assertSessionHasErrors('shipping_lat');
        $this->assertSame(0, Order::query()->count());
        // Stok tidak terpotong karena transaksi tidak pernah dibuat.
        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_checkout_ditolak_saat_stok_tidak_cukup(): void
    {
        $this->setShippingSettings();

        $user = User::factory()->create();
        $product = $this->produk(stock: 1, price: 10000);
        $cart = $this->keranjang($user, $product, 5);
        $point = DeliveryPoint::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('checkout.index'))
            ->post(route('checkout.store'), [
                'customer_name' => $user->name,
                'customer_phone' => '081234567890',
                'shipping_method' => Order::SHIPPING_PICKUP_POINT,
                'delivery_point_id' => $point->id,
                'payment_method' => Order::PAYMENT_METHOD_COD,
            ]);

        $response->assertSessionHasErrors('cart');
        $this->assertSame(0, Order::query()->count());
        $this->assertSame(1, $product->fresh()->stock);
        $this->assertSame(1, $cart->items()->count());
    }

    public function test_endpoint_hitung_ongkir_mengembalikan_json(): void
    {
        $this->setShippingSettings();

        $response = $this->postJson(route('shipping.calculate'), [
            'latitude' => -7.4618000,
            'longitude' => 110.2148000,
        ]);

        $response->assertOk()
            ->assertJsonStructure(['distance_km', 'fee', 'is_free', 'out_of_range'])
            ->assertJson(['is_free' => true, 'out_of_range' => false]);
    }

    public function test_customer_tidak_bisa_melihat_pesanan_orang_lain(): void
    {
        $pemilik = User::factory()->create();
        $penyusup = User::factory()->create();

        $order = Order::factory()->create(['user_id' => $pemilik->id]);

        $this->actingAs($penyusup)
            ->get(route('orders.show', $order))
            ->assertNotFound();
    }
}