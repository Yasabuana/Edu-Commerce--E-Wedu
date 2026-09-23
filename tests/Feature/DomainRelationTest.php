<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\DeliveryPoint;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Setting;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Uji lapisan domain FASE 3: relasi antar-tabel, scope, accessor,
 * transisi status pesanan, dan cache pengaturan.
 */
class DomainRelationTest extends TestCase
{
    use RefreshDatabase;

    public function test_relasi_umkm_produk_dan_artikel_terhubung(): void
    {
        $user = User::factory()->umkm()->create();
        $profile = UmkmProfile::factory()->for($user)->create();
        $product = Product::factory()->for($profile, 'umkmProfile')->create();
        $article = Article::factory()->for($profile, 'umkmProfile')->create(['user_id' => $user->id]);

        $this->assertTrue($user->umkmProfile->is($profile));
        $this->assertTrue($profile->user->is($user));
        $this->assertCount(1, $profile->products);
        $this->assertCount(1, $profile->articles);
        $this->assertTrue($product->umkmProfile->is($profile));
        $this->assertDatabaseHas('categories', ['id' => $product->category_id]);
        $this->assertTrue($article->author->is($user));
        $this->assertNotNull($article->category);
        $this->assertTrue($article->umkmProfile->is($profile));

        $this->assertSame(1, UmkmProfile::query()->verified()->published()->count());
    }

    public function test_produk_diskon_dan_galeri_gambar(): void
    {
        $product = Product::factory()->state(['price' => 100000])->discounted()->create();

        ProductImage::factory()->count(2)->for($product)->create();
        ProductImage::factory()->for($product)->primary()->create();

        $product->refresh();

        $this->assertSame(80000, $product->finalPrice());
        $this->assertTrue($product->hasDiscount());
        $this->assertSame(20, $product->discount_percent);
        $this->assertSame('Rp 80.000', $product->price_formatted);
        $this->assertSame('Rp 100.000', $product->original_price_formatted);
        $this->assertCount(3, $product->images);
        $this->assertNotNull($product->images->firstWhere('is_primary', true));
        $this->assertTrue($product->isAvailable());

        // Tanpa diskon: harga efektif = harga normal dan persentase diskon 0.
        $plain = Product::factory()->create(['price' => 50000, 'discount_price' => null]);

        $this->assertSame(50000, $plain->finalPrice());
        $this->assertSame(0, $plain->discount_percent);
        $this->assertFalse($plain->hasDiscount());
    }

    public function test_route_key_model_publik_memakai_slug(): void
    {
        $this->assertSame('slug', (new Product)->getRouteKeyName());
        $this->assertSame('slug', (new Article)->getRouteKeyName());
        $this->assertSame('slug', (new UmkmProfile)->getRouteKeyName());

        $product = Product::factory()->create();

        $this->assertTrue($product->is(Product::query()->where('slug', $product->slug)->firstOrFail()));
    }

    public function test_order_lengkap_dengan_item_pembayaran_dan_riwayat_status(): void
    {
        $customer = User::factory()->create();
        $order = Order::factory()->for($customer)->create();

        OrderItem::factory()->count(2)->for($order, 'order')->create();
        $payment = Payment::factory()->qris()->for($order, 'order')->create();
        OrderStatusHistory::factory()->for($order, 'order')->create(['status' => Order::STATUS_PENDING]);

        $order->refresh();

        $this->assertTrue($order->user->is($customer));
        $this->assertCount(2, $order->items);
        $this->assertSame(2, $order->items_count);
        $this->assertSame(1, $order->payments()->count());
        $this->assertSame(1, $order->statusHistories()->count());
        $this->assertTrue($payment->isPending());
        $this->assertTrue($payment->hasProof());
        $this->assertNull($payment->proofUrl());

        $this->assertSame('Gratis', $order->shipping_fee_formatted);
        $this->assertSame('Rp '.number_format((int) $order->total, 0, ',', '.'), $order->total_formatted);
        $this->assertSame('Menunggu', $order->statusLabel());
        $this->assertSame('Titik Jemput Gratis', $order->shippingMethodLabel());
        $this->assertTrue($order->canTransitionTo(Order::STATUS_CONFIRMED));
        $this->assertFalse($order->canTransitionTo(Order::STATUS_DELIVERED));
        $this->assertTrue($order->canBeCancelled());
    }

    public function test_nomor_pesanan_mengikuti_format_ewd_dan_berurutan(): void
    {
        $first = Order::factory()->create();
        $second = Order::factory()->create();

        $this->assertMatchesRegularExpression('/^EWD-\d{8}-\d{4}$/', $first->order_number);
        $this->assertStringEndsWith('0001', $first->order_number);
        $this->assertStringEndsWith('0002', $second->order_number);
    }

    public function test_keranjang_menghitung_subtotal_dan_total_qty(): void
    {
        $cart = Cart::factory()->create();
        CartItem::factory()->count(2)->for($cart, 'cart')->qty(2)->create();

        $cart->refresh()->load('items');

        $expectedSubtotal = $cart->items->sum(fn (CartItem $item): int => $item->price_snapshot * 2);

        $this->assertSame(2, $cart->item_count);
        $this->assertSame(4, $cart->total_qty);
        $this->assertSame($expectedSubtotal, $cart->subtotal);
        $this->assertFalse($cart->isEmpty());
        $this->assertTrue($cart->items->every(fn (CartItem $item): bool => $item->stockIsEnough()));

        $empty = Cart::factory()->guest()->create();

        $this->assertTrue($empty->fresh()->isEmpty());
        $this->assertSame(0, $empty->subtotal);
    }

    public function test_snapshot_order_item_tetap_utuh_setelah_produk_dihapus(): void
    {
        $item = OrderItem::factory()->create();
        $product = $item->product()->firstOrFail();

        $name = $item->product_name;
        $price = $item->price;

        $product->delete(); // soft delete — produk masih bisa dipulihkan

        $this->assertSoftDeleted('products', ['id' => $product->id]);
        $this->assertSame($name, $item->fresh()->product_name);
        $this->assertSame($price, $item->fresh()->price);

        $product->forceDelete(); // hapus permanen — snapshot tetap, FK produk menjadi null

        $item->refresh();

        $this->assertNull($item->product_id);
        $this->assertSame($name, $item->product_name);
        $this->assertNull($item->product);
    }

    public function test_delivery_point_scope_titik_gratis_dan_alamat_kustom(): void
    {
        DeliveryPoint::factory()->count(4)->create();
        $master = DeliveryPoint::factory()->custom()->create();
        DeliveryPoint::factory()->inactive()->create();

        $this->assertSame(4, DeliveryPoint::query()->active()->freePoints()->count());
        $this->assertSame(1, DeliveryPoint::query()->custom()->count());
        $this->assertSame(5, DeliveryPoint::query()->active()->count());
        $this->assertFalse($master->isFreePoint());
        $this->assertEqualsWithDelta(3.0, $master->free_radius_km, 0.001);

        $firstFreePoint = DeliveryPoint::query()->active()->freePoints()->ordered()->first();
        [$latitude, $longitude] = $firstFreePoint->coordinates();

        // `ordered()` menaikkan `sort_order` sehingga titik dengan urutan terkecil diambil lebih dulu.
        $this->assertSame(
            DeliveryPoint::query()->active()->freePoints()->min('sort_order'),
            $firstFreePoint->sort_order,
        );
        $this->assertMatchesRegularExpression('/^TGR-\d{2}$/', $firstFreePoint->code);
        $this->assertEqualsWithDelta(-7.4752, $latitude, 0.00001);
        $this->assertEqualsWithDelta(110.2177, $longitude, 0.00001);
        $this->assertStringContainsString('Gratis Ongkir', $firstFreePoint->shippingLabel());
    }

    public function test_setting_disimpan_dan_diambil_dengan_cast_tipe_serta_cache(): void
    {
        Setting::set('shipping_base_fee', 5000, Setting::TYPE_INTEGER, 'shipping');
        Setting::set('cod_enabled', true, Setting::TYPE_BOOLEAN, 'payment');
        Setting::set('qris_meta', ['merchant' => 'E-Wedu', 'nmid' => '1234'], Setting::TYPE_JSON, 'payment');

        $this->assertSame(5000, Setting::getInt('shipping_base_fee'));
        $this->assertTrue(Setting::getBool('cod_enabled'));
        $this->assertSame(['merchant' => 'E-Wedu', 'nmid' => '1234'], Setting::get('qris_meta'));
        $this->assertSame('shipping', Setting::query()->where('key', 'shipping_base_fee')->value('group'));

        // Perubahan nilai menyegarkan cache (tidak mengembalikan nilai lama).
        Setting::set('shipping_base_fee', 7000, Setting::TYPE_INTEGER);
        $this->assertSame(7000, Setting::getInt('shipping_base_fee'));

        $this->assertSame('default', Setting::get('tidak-ada', 'default'));
        $this->assertSame(0, Setting::getInt('tidak-ada'));
    }
}
