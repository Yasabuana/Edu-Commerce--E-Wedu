<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

/**
 * Memastikan layout publik/admin/umkm benar-benar bisa dirender lewat HTTP
 * (termasuk komponen x-navbar, x-footer, x-sidebar-link, dan x-alert).
 */
class LayoutRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_layout_publik_menampilkan_navbar_footer_dan_flash_session(): void
    {
        Route::middleware('web')->get('/uji-layout-publik', function () {
            session()->flash('success', 'Pesanan berhasil dibuat.');

            return response()->view('layouts.public');
        });

        $this->get('/uji-layout-publik')
            ->assertOk()
            ->assertSee('Navigasi utama', false)
            ->assertSee('Titik Pengiriman')
            ->assertSee('Kampus Tuguran')
            ->assertSee('Pesanan berhasil dibuat.')
            ->assertSee('role="alert"', false);
    }

    public function test_layout_publik_menampilkan_error_validasi(): void
    {
        Route::middleware('web')->get('/uji-layout-publik-error', fn () => response()->view('layouts.public'));

        $errorBag = new ViewErrorBag;
        $errorBag->put('default', new MessageBag(['nama' => ['Nama penerima wajib diisi.']]));

        $this->withSession(['errors' => $errorBag])
            ->get('/uji-layout-publik-error')
            ->assertOk()
            ->assertSee('Periksa kembali isian Anda')
            ->assertSee('Nama penerima wajib diisi.');
    }

    public function test_layout_admin_tampil_untuk_admin(): void
    {
        Route::middleware(['web', 'auth', 'role:admin'])
            ->get('/uji-layout-admin', fn () => response()->view('layouts.admin'));

        $this->actingAs(User::factory()->admin()->create())
            ->get('/uji-layout-admin')
            ->assertOk()
            ->assertSee('Menu admin')
            ->assertSee('Verifikasi Pembayaran')
            ->assertSee('Panel Admin');
    }

    public function test_layout_umkm_tampil_untuk_pemilik_umkm(): void
    {
        Route::middleware(['web', 'auth', 'role:umkm'])
            ->get('/uji-layout-umkm', fn () => response()->view('layouts.umkm'));

        $this->actingAs(User::factory()->umkm()->create())
            ->get('/uji-layout-umkm')
            ->assertOk()
            ->assertSee('Menu UMKM')
            ->assertSee('Produk Saya');
    }

    public function test_customer_tidak_bisa_membuka_layout_admin(): void
    {
        Route::middleware(['web', 'auth', 'role:admin'])
            ->get('/uji-layout-admin-terkunci', fn () => response()->view('layouts.admin'));

        $this->actingAs(User::factory()->create())
            ->get('/uji-layout-admin-terkunci')
            ->assertForbidden();
    }
}
