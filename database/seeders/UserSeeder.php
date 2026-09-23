<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Akun awal E-Wedu: 1 admin, 1 mitra UMKM contoh, 1 pembeli contoh.
     *
     * Kredensial default (password: `password`):
     * - admin@ewedu.test    -> role admin    (akses /admin)
     * - umkm@ewedu.test     -> role umkm     (akses /umkm-panel)
     * - pembeli@ewedu.test  -> role customer
     *
     * Catatan: nilai `password` ditulis polos — cast `hashed` pada model User
     * yang melakukan hashing otomatis.
     */
    public function run(): void
    {
        $akun = [
            [
                'name' => 'Admin E-Wedu',
                'email' => 'admin@ewedu.test',
                'password' => 'password',
                'role' => User::ROLE_ADMIN,
                'phone' => '0293123456',
                'address' => 'Kampus Tuguran, Magelang',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Mitra UMKM Contoh',
                'email' => 'umkm@ewedu.test',
                'password' => 'password',
                'role' => User::ROLE_UMKM,
                'phone' => '081234567890',
                'address' => 'UMKM Center Magelang',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Pembeli Contoh',
                'email' => 'pembeli@ewedu.test',
                'password' => 'password',
                'role' => User::ROLE_CUSTOMER,
                'phone' => '081298765432',
                'address' => 'Balai Kota Magelang',
                'is_active' => true,
                'email_verified_at' => now(),
            ],
        ];

        foreach ($akun as $data) {
            // withTrashed(): email unik, jadi baris yang pernah di-soft delete harus dihidupkan ulang
            // agar `php artisan db:seed` tetap idempoten.
            $user = User::withTrashed()->firstOrNew(['email' => $data['email']]);
            $user->fill($data);
            $user->deleted_at = null;
            $user->save();
        }
    }
}
