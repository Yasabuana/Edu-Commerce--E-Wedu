<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class UserRoleHelperTest extends TestCase
{
    public function test_helper_role_mengenali_setiap_role(): void
    {
        $admin = new User(['role' => User::ROLE_ADMIN]);
        $umkm = new User(['role' => User::ROLE_UMKM]);
        $customer = new User(['role' => User::ROLE_CUSTOMER]);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isUmkm());
        $this->assertFalse($admin->isCustomer());

        $this->assertTrue($umkm->isUmkm());
        $this->assertFalse($umkm->isAdmin());

        $this->assertTrue($customer->isCustomer());
        $this->assertFalse($customer->isAdmin());
    }

    public function test_has_role_menerima_banyak_role(): void
    {
        $umkm = new User(['role' => User::ROLE_UMKM]);

        $this->assertTrue($umkm->hasRole(User::ROLE_ADMIN, User::ROLE_UMKM));
        $this->assertFalse($umkm->hasRole(User::ROLE_ADMIN, User::ROLE_CUSTOMER));
    }

    public function test_label_dan_badge_role(): void
    {
        $this->assertSame('Admin', (new User(['role' => User::ROLE_ADMIN]))->roleLabel());
        $this->assertSame('Mitra UMKM', (new User(['role' => User::ROLE_UMKM]))->roleLabel());
        $this->assertSame('Pembeli', (new User(['role' => User::ROLE_CUSTOMER]))->roleLabel());

        $this->assertStringContainsString('brand-primary', (new User(['role' => User::ROLE_ADMIN]))->roleBadgeClass());
        $this->assertStringContainsString('brand-accent', (new User(['role' => User::ROLE_UMKM]))->roleBadgeClass());
    }

    public function test_is_active_di_cast_ke_boolean(): void
    {
        $this->assertTrue((new User(['is_active' => 1]))->isActive());
        $this->assertFalse((new User(['is_active' => 0]))->isActive());
    }
}
