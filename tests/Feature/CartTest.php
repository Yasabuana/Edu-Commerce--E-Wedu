<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\UmkmProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Keranjang belanja lewat HTTP (FASE 5 — Rancangan §2.1 & §3.1):
 * tambah/ubah/hapus item, validasi, respons JSON, dan badge navbar.
 */
class CartTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test client Laravel tidak otomatis membawa cookie antar request, sehingga
     * id session keranjang guest harus dikirim eksplisit pada request berikutnya.
     */
    private function gunakanSessionGuest(): string
    {
        $sessionId = (string) Cart::query()->whereNull('user_id')->value('session_id');

        $this->withCookie(config('session.cookie'), $sessionId);

        return $sessionId;
    }

    public function test_halaman_keranjang_kosong_tidak_membuat_baris_cart(): void
    {
        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Keranjang masih kosong')
            ->assertSee('Jelajahi katalog');

        $this->get(route('home'))->assertOk();

        $this->assertDatabaseCount('carts', 0);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_guest_dapat_menambahkan_produk_dan_melihat_isi_keranjang(): void
    {
        $mitra = UmkmProfile::factory()->create(['business_name' => 'Dapur Bunda Sari']);
        $produk = Product::factory()->create([
            'name' => 'Sambal Bawang',
            'umkm_profile_id' => $mitra->id,
            'price' => 25000,
            'stock' => 10,
            'unit' => 'pcs',
        ]);

        $this->post(route('cart.store'), ['product_id' => $produk->id, 'qty' => 2])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('cart_items', ['product_id' => $produk->id, 'qty' => 2]);
        $this->assertDatabaseHas('carts', ['user_id' => null]);

        $this->gunakanSessionGuest();

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('Sambal Bawang')
            ->assertSee('Dapur Bunda Sari')
            ->assertSee('Rp 50.000')
            ->assertSee('2 item siap diproses');
    }

    public function test_menambah_produk_yang_sama_menggabungkan_qty(): void
    {
        $produk = Product::factory()->create(['stock' => 10]);

        $this->post(route('cart.store'), ['product_id' => $produk->id, 'qty' => 1])->assertRedirect();
        $this->gunakanSessionGuest();
        $this->post(route('cart.store'), ['product_id' => $produk->id, 'qty' => 2])->assertRedirect();

        $this->assertDatabaseCount('cart_items', 1);
        $this->assertSame(3, CartItem::first()->qty);
    }

    public function test_qty_melebihi_stok_atau_produk_tidak_dikenal_menghasilkan_error_validasi(): void
    {
        $produk = Product::factory()->create(['stock' => 2]);

        $this->post(route('cart.store'), ['product_id' => $produk->id, 'qty' => 5])
            ->assertSessionHasErrors('qty');

        $this->post(route('cart.store'), [])->assertSessionHasErrors(['product_id', 'qty']);
        $this->post(route('cart.store'), ['product_id' => 999999, 'qty' => 1])->assertSessionHasErrors('product_id');
        $this->post(route('cart.store'), ['product_id' => $produk->id, 'qty' => 0])->assertSessionHasErrors('qty');
        $this->post(route('cart.store'), ['product_id' => $produk->id, 'qty' => 100])->assertSessionHasErrors('qty');

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_produk_nonaktif_atau_mitra_belum_terverifikasi_menghasilkan_404(): void
    {
        $this->post(route('cart.store'), [
            'product_id' => Product::factory()->inactive()->create()->id,
            'qty' => 1,
        ])->assertNotFound();

        $mitra = UmkmProfile::factory()->unverified()->create();
        $produk = Product::factory()->create(['umkm_profile_id' => $mitra->id]);

        $this->post(route('cart.store'), ['product_id' => $produk->id, 'qty' => 1])->assertNotFound();
    }

    public function test_permintaan_ajax_mendapat_respons_json(): void
    {
        $produk = Product::factory()->create(['name' => 'Kopi Robusta', 'price' => 25000, 'stock' => 10]);

        $this->postJson(route('cart.store'), ['product_id' => $produk->id, 'qty' => 3])
            ->assertOk()
            ->assertJson([
                'message' => 'Kopi Robusta ditambahkan ke keranjang.',
                'qty' => 3,
                'count' => 3,
                'subtotal' => 75000,
            ]);

        $this->postJson(route('cart.store'), ['product_id' => $produk->id, 'qty' => 50])
            ->assertStatus(422)
            ->assertJsonValidationErrors('qty');
    }

    public function test_ubah_qty_dan_hapus_item_dari_keranjang(): void
    {
        $produk = Product::factory()->create(['stock' => 10]);

        $this->post(route('cart.store'), ['product_id' => $produk->id, 'qty' => 1])->assertRedirect();
        $this->gunakanSessionGuest();
        $item = CartItem::first();

        $this->patch(route('cart.update', $item), ['qty' => 4])
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertSame(4, $item->refresh()->qty);

        $this->patch(route('cart.update', $item), ['qty' => 99])->assertSessionHasErrors('qty');
        $this->assertSame(4, $item->refresh()->qty);

        $this->delete(route('cart.destroy', $item))
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_baris_keranjang_milik_orang_lain_tidak_bisa_diubah(): void
    {
        $itemLain = CartItem::factory()->create(['cart_id' => Cart::factory()->create()->id, 'qty' => 1]);

        $this->patch(route('cart.update', $itemLain), ['qty' => 2])->assertNotFound();
        $this->delete(route('cart.destroy', $itemLain))->assertNotFound();

        $this->assertSame(1, $itemLain->refresh()->qty);
    }

    public function test_badge_navbar_menampilkan_jumlah_item_keranjang(): void
    {
        $produk = Product::factory()->create(['stock' => 5]);

        $this->get(route('home'))->assertOk()->assertDontSee('Jumlah item di keranjang', false);

        $this->post(route('cart.store'), ['product_id' => $produk->id, 'qty' => 3])->assertRedirect();

        $this->gunakanSessionGuest();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Jumlah item di keranjang', false)
            ->assertSee('>3</span>', false);
    }

    public function test_user_login_memakai_keranjang_milik_akunnya(): void
    {
        $user = User::factory()->create();
        $produk = Product::factory()->create(['stock' => 5]);

        $this->actingAs($user)
            ->post(route('cart.store'), ['product_id' => $produk->id, 'qty' => 2])
            ->assertRedirect();

        $this->assertDatabaseHas('carts', ['user_id' => $user->id]);
        $this->assertDatabaseHas('cart_items', ['product_id' => $produk->id, 'qty' => 2]);

        $this->actingAs($user)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('2 item siap diproses');
    }
}
