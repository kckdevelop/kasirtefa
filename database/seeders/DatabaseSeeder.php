<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Roles if not exist
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $kasirRole = Role::firstOrCreate(['name' => 'kasir']);
        $peminjamRole = Role::firstOrCreate(['name' => 'peminjam']);
        Role::firstOrCreate(['name' => 'super_admin']);
        Role::firstOrCreate(['name' => 'admin_tefa']);
        Role::firstOrCreate(['name' => 'admin_alat']);

        // 1. Admin User
        $admin = User::firstOrCreate(
            ['email' => 'admin@tefa.com'],
            [
                'nama' => 'Administrator TEFa',
                'name' => 'Administrator TEFa',
                'password' => Hash::make('password'),
                'status' => 'aktif',
            ]
        );
        $admin->assignRole($adminRole);

        // 2. Kasir User
        $kasir = User::firstOrCreate(
            ['email' => 'kasir@tefa.com'],
            [
                'nama' => 'Kasir TEFa',
                'name' => 'Kasir TEFa',
                'password' => Hash::make('password'),
                'status' => 'aktif',
            ]
        );
        $kasir->assignRole($kasirRole);

        // 3. Peminjam User (Siswa)
        $peminjam = User::firstOrCreate(
            ['email' => 'siswa@tefa.com'],
            [
                'nama' => 'Budi Santoso',
                'name' => 'Budi Santoso',
                'nomor_induk' => '20261001',
                'jenis_nomor_induk' => 'siswa',
                'kelas' => 'XII RPL 1',
                'jurusan' => 'Rekayasa Perangkat Lunak',
                'password' => Hash::make('password'),
                'status' => 'aktif',
            ]
        );
        $peminjam->assignRole($peminjamRole);
    }
}
