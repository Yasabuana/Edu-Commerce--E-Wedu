<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\UmkmProfile;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Logika keranjang pada `CartService` (FASE 5 — Rancangan §3.1).
 *
 * Alur guest lewat HTTP diuji di `tests/Feature/CartTest.php`, sedangkan
 * penggabungan setelah login diuji di sini dan di `MergeGuestCartTest`.
 */
class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    private CartService $service;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(CartService::class);
        $this->user = User::factory()->create();

        $this->actingAs($this->user);
    }

    public function test_menambah_produk_membuat_baris_dengan_snapshot_harga_efektif(): void
    {
        $mitra = UmkmProfile::factory()->create();
        $produk = Product::factory()->create([
            'umkm_profile_id' => $mitra->id,
            'price' => 25000,
            'discount_price' => 20000,
            'stock' => 10,
        ]);

        $item = $this->service->add($produk, 2);

        $this->assertSame($produk->id, $item->product_id);
        $this->assertSame($mitra->id, $item->umkm_profile_id);
        $this->assertSame(20000, $item->price_snapshot);
        $this->assertSame(2, $item->qty);
        $this->assertDatabaseCount('carts', 1);
        $this->assertSame(2, $this->service->count());
        $this->assertSame(40000, (int) $this->service->read()->subtotal);
    }

    public function test_menambah_produk_yang_sama_menggabungkan_qty_pada_satu_baris(): void
    {
        $produk = Product::factory()->create(['price' => 10000, 'stock' => 10]);

        $this->service->add($produk, 1);
        $this->service->add($produk, 2);

        $this->assertDatabaseCount('cart_items', 1);
        $this->assertSame(3, CartItem::first()->qty);
        $this->assertSame(3, $this->service->count());
    }

    public function test_qty_gabungan_tidak_boleh_melebihi_stok(): void
    {
        $produk = Product::factory()->create(['name' => 'Kopi Robusta', 'stock' => 3, 'unit' => 'pack']);

        $this->service->add($produk, 2);

        try {
            $this->service->add($produk, 2);
            $this->fail('Qty melebihi stok seharusnya ditolak.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('qty', $exception->errors());
            $this->assertStringContainsString('tersedia 3 pack', $exception->errors()['qty'][0]);
        }

        $this->assertSame(2, CartItem::first()->qty);
    }

    public function test_produk_nonaktif_atau_stok_habis_tidak_bisa_ditambahkan(): void
    {
        try {
            $this->service->add(Product::factory()->inactive()->create(), 1);
            $this->fail('Produk nonaktif seharusnya ditolak.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('product_id', $exception->errors());
        }

        try {
            $this->service->add(Product::factory()->outOfStock()->create(['name' => 'Stok Kosong']), 1);
            $this->fail('Produk tanpa stok seharusnya ditolak.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('qty', $exception->errors());
            $this->assertStringContainsString('sedang habis', $exception->errors()['qty'][0]);
        }

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_update_qty_menolak_qty_nol_atau_di_atas_stok(): void
    {
        $produk = Product::factory()->create(['stock' => 5, 'price' => 3000]);
        $item = $this->service->add($produk, 2);

        $this->assertSame(4, $this->service->updateQty($item, 4)->qty);

        try {
            $this->service->updateQty($item, 6);
            $this->fail('Qty di atas stok seharusnya ditolak.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('qty', $exception->errors());
        }

        try {
            $this->service->updateQty($item, 0);
            $this->fail('Qty nol seharusnya ditolak.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('qty', $exception->errors());
        }

        $this->assertSame(4, $item->refresh()->qty);
    }

    public function test_remove_dan_clear_membersihkan_baris_keranjang(): void
    {
        $itemA = $this->service->add(Product::factory()->create(), 1);
        $this->service->add(Product::factory()->create(), 2);

        $this->service->remove($itemA);
        $this->assertDatabaseCount('cart_items', 1);

        $this->assertSame(1, $this->service->clear($this->service->read()));
        $this->assertDatabaseCount('cart_items', 0);
        $this->assertSame(0, $this->service->count());
    }

    public function test_merge_guest_cart_menggabungkan_qty_dengan_batas_stok(): void
    {
        $produk = Product::factory()->create(['stock' => 3, 'price' => 12000]);

        $cartUser = Cart::factory()->create(['user_id' => $this->user->id]);
        CartItem::factory()->create(['cart_id' => $cartUser->id, 'product_id' => $produk->id, 'qty' => 2]);

        $cartGuest = Cart::factory()->guest('guest-session-gabung')->create();
        CartItem::factory()->create(['cart_id' => $cartGuest->id, 'product_id' => $produk->id, 'qty' => 2]);

        $this->service->mergeGuestCart($this->user, 'guest-session-gabung');

        $this->assertDatabaseCount('cart_items', 1);
        $this->assertSame(3, $cartUser->items()->first()->qty);
        $this->assertDatabaseMissing('carts', ['id' => $cartGuest->id]);
        $this->assertDatabaseCount('carts', 1);
    }

    public function test_merge_guest_cart_memindahkan_produk_ke_cart_user_yang_belum_ada(): void
    {
        $produk = Product::factory()->create(['stock' => 5, 'price' => 9000]);

        $cartGuest = Cart::factory()->guest('guest-session-baru')->create();
        CartItem::factory()->create(['cart_id' => $cartGuest->id, 'product_id' => $produk->id, 'qty' => 3]);

        $this->assertNull(Cart::query()->where('user_id', $this->user->id)->first());

        $this->service->mergeGuestCart($this->user, 'guest-session-baru');

        $cartUser = Cart::query()->where('user_id', $this->user->id)->first();
        $this->assertNotNull($cartUser);
        $this->assertSame($produk->id, $cartUser->items()->first()->product_id);
        $this->assertSame(3, $cartUser->items()->first()->qty);
        $this->assertSame(9000, (int) $cartUser->items()->first()->price_snapshot);
    }

    public function test_merge_guest_cart_melewati_produk_nonaktif_tanpa_membuat_cart_kosong(): void
    {
        $produk = Product::factory()->create(['stock' => 5]);

        $cartGuest = Cart::factory()->guest('guest-session-nonaktif')->create();
        CartItem::factory()->create(['cart_id' => $cartGuest->id, 'product_id' => $produk->id, 'qty' => 1]);

        $produk->update(['is_active' => false]);

        $this->service->mergeGuestCart($this->user, 'guest-session-nonaktif');

        $this->assertDatabaseCount('cart_items', 0);
        $this->assertDatabaseCount('carts', 0);
    }

    public function test_merge_cart_yang_sudah_milik_user_hanya_melepas_session_id(): void
    {
        $cart = Cart::factory()->create(['user_id' => $this->user->id, 'session_id' => 'guest-session-sama']);
        CartItem::factory()->create(['cart_id' => $cart->id, 'qty' => 2]);

        $this->service->mergeGuestCart($this->user, 'guest-session-sama');

        $this->assertNull($cart->refresh()->session_id);
        $this->assertSame(2, $cart->items()->first()->qty);
        $this->assertSame(2, $this->service->count());
    }

    public function test_merge_tanpa_cart_guest_tidak_mengubah_apa_pun(): void
    {
        Cart::factory()->create(['user_id' => $this->user->id]);

        $this->service->mergeGuestCart($this->user, 'guest-session-tidak-ada');

        $this->assertDatabaseCount('carts', 1);
        $this->assertDatabaseCount('cart_items', 0);
    }
}
