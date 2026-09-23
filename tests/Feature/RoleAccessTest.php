<?php

namespace Tests\Feature;

use App\Http\Middleware\RoleMiddleware;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Route uji: tidak didaftarkan di routes/web.php agar produksi tetap bersih.
        Route::middleware(['web', 'auth', 'role:admin'])
            ->get('/uji-hanya-admin', fn () => 'hanya-admin');

        Route::middleware(['web', 'auth', 'role:admin,umkm'])
            ->get('/uji-admin-umkm', fn () => 'admin-umkm');

        Route::middleware(['web', 'auth', 'role:admin|umkm'])
            ->get('/uji-pemisah-pipa', fn () => 'pemisah-pipa');
    }

    public function test_tamu_diarahkan_ke_halaman_login(): void
    {
        $this->get('/uji-hanya-admin')->assertRedirect(route('login'));
    }

    public function test_admin_diberi_akses_halaman_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/uji-hanya-admin')
            ->assertOk()
            ->assertSee('hanya-admin');
    }

    public function test_customer_ditolak_dengan_403(): void
    {
        $customer = User::factory()->create(); // default role customer

        $this->actingAs($customer)->get('/uji-hanya-admin')->assertForbidden();
    }

    public function test_umkm_ditolak_di_halaman_admin(): void
    {
        $umkm = User::factory()->umkm()->create();

        $this->actingAs($umkm)->get('/uji-hanya-admin')->assertForbidden();
    }

    public function test_beberapa_role_dipisah_koma_sama_sama_diizinkan(): void
    {
        $this->actingAs(User::factory()->umkm()->create())
            ->get('/uji-admin-umkm')
            ->assertOk();

        $this->actingAs(User::factory()->admin()->create())
            ->get('/uji-admin-umkm')
            ->assertOk();

        $this->actingAs(User::factory()->create())
            ->get('/uji-admin-umkm')
            ->assertForbidden();
    }

    public function test_penulisan_pemisah_pipa_juga_didukung(): void
    {
        $this->actingAs(User::factory()->umkm()->create())
            ->get('/uji-pemisah-pipa')
            ->assertOk();
    }

    public function test_akun_tidak_aktif_selalu_ditolak(): void
    {
        $adminNonaktif = User::factory()->admin()->inactive()->create();

        $this->actingAs($adminNonaktif)->get('/uji-hanya-admin')->assertForbidden();
    }

    public function test_alias_middleware_role_terdaftar_di_router(): void
    {
        // Kernel menyinkronkan alias middleware ke router saat pertama kali menangani request,
        // sehingga satu request dilakukan lebih dulu.
        $this->actingAs(User::factory()->admin()->create())->get('/uji-hanya-admin')->assertOk();

        $this->assertSame(
            RoleMiddleware::class,
            $this->app['router']->getMiddleware()['role'] ?? null,
            'Alias middleware "role" tidak terdaftar pada router.',
        );
    }
}
