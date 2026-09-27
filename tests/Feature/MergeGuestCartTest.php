<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Penggabungan keranjang guest ke keranjang user saat login
 * (FASE 5 — Rancangan §3.1, listener `MergeGuestCart`).
 */
class MergeGuestCartTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Login sambil membawa cookie session keranjang guest — test client Laravel
     * tidak menyimpan cookie antar request secara otomatis.
     */
    private function loginDenganSessionGuest(User $user): void
    {
        $sessionId = (string) Cart::query()->whereNull('user_id')->value('session_id');

        $this->withCookie(config('session.cookie'), $sessionId)
            ->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_keranjang_guest_dipindahkan_ke_keranjang_user_setelah_login(): void
    {
        $user = User::factory()->create();
        $produk = Product::factory()->create(['price' => 15000, 'stock' => 10]);

        $this->post(route('cart.store'), ['product_id' => $produk->id, 'qty' => 2])->assertRedirect();

        $cartGuest = Cart::query()->whereNull('user_id')->firstOrFail();

        $this->loginDenganSessionGuest($user);

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseMissing('carts', ['id' => $cartGuest->id]);

        $cartUser = Cart::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($cartUser);
        $this->assertSame($produk->id, $cartUser->items()->first()->product_id);
        $this->assertSame(2, $cartUser->items()->first()->qty);
    }

    public function test_keranjang_user_yang_sudah_ada_digabung_dengan_qty_dibatasi_stok(): void
    {
        $user = User::factory()->create();
        $produk = Product::factory()->create(['price' => 10000, 'stock' => 3]);

        // Guest menambahkan 2 unit lebih dulu.
        $this->post(route('cart.store'), ['product_id' => $produk->id, 'qty' => 2])->assertRedirect();

        // Keranjang akun sudah berisi 2 unit produk yang sama.
        $cartUser = Cart::factory()->create(['user_id' => $user->id]);
        CartItem::factory()->create(['cart_id' => $cartUser->id, 'product_id' => $produk->id, 'qty' => 2]);

        $this->loginDenganSessionGuest($user);

        $this->assertDatabaseCount('cart_items', 1);
        $this->assertSame(3, $cartUser->items()->first()->qty);
        $this->assertDatabaseCount('carts', 1);
    }

    public function test_login_tanpa_keranjang_guest_tidak_membuat_keranjang_baru(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseCount('carts', 0);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_keranjang_guest_dengan_produk_nonaktif_tidak_dipindahkan(): void
    {
        $user = User::factory()->create();
        $produk = Product::factory()->create(['stock' => 5]);

        $this->post(route('cart.store'), ['product_id' => $produk->id, 'qty' => 1])->assertRedirect();

        $produk->update(['is_active' => false]);

        $this->loginDenganSessionGuest($user);

        $this->assertDatabaseCount('carts', 0);
        $this->assertDatabaseCount('cart_items', 0);
    }
}
