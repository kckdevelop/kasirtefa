<?php

namespace Tests\Feature\Web;

use App\Models\Alat;
use App\Models\KategoriAlat;
use App\Models\KategoriProduk;
use App\Models\PeminjamanAlat;
use App\Models\Produk;
use App\Models\TransaksiPenjualan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_web_pages(): void
    {
        $user = User::first() ?? User::factory()->create();

        // Dashboard
        $this->actingAs($user)->get('/dashboard')->assertStatus(200);

        // POS Kasir
        $this->actingAs($user)->get('/tefa/kasir')->assertStatus(200);

        // TEFa Views
        $this->actingAs($user)->get('/tefa/kategori')->assertStatus(200);
        $this->actingAs($user)->get('/tefa/produk')->assertStatus(200);
        $this->actingAs($user)->get('/tefa/stok-masuk')->assertStatus(200);
        $this->actingAs($user)->get('/tefa/stok-keluar')->assertStatus(200);
        $this->actingAs($user)->get('/tefa/transaksi')->assertStatus(200);

        // Alat Views
        $this->actingAs($user)->get('/alat/kategori')->assertStatus(200);
        $this->actingAs($user)->get('/alat/daftar')->assertStatus(200);
        $this->actingAs($user)->get('/alat/peminjaman')->assertStatus(200);
        $this->actingAs($user)->get('/alat/denda')->assertStatus(200);

        // Laporan & Users & Settings
        $this->actingAs($user)->get('/laporan/penjualan')->assertStatus(200);
        $this->actingAs($user)->get('/laporan/peminjaman')->assertStatus(200);
        $this->actingAs($user)->get('/laporan/inventaris')->assertStatus(200);
        $this->actingAs($user)->get('/laporan/kondisi-alat')->assertStatus(200);
        $this->actingAs($user)->get('/users')->assertStatus(200);
        $this->actingAs($user)->get('/pengaturan')->assertStatus(200);
    }

    public function test_authenticated_user_can_create_produk_and_alat_via_web_routes(): void
    {
        $user = User::first() ?? User::factory()->create();

        $kategoriProduk = KategoriProduk::create([
            'nama' => 'Pakaian Test',
            'slug' => 'pakaian-test',
            'status' => 'aktif',
        ]);
        $responseProduk = $this->actingAs($user)->post('/tefa/produk', [
            'nama' => 'Produk Test Web',
            'kategori_produk_id' => $kategoriProduk->id,
            'satuan' => 'pcs',
            'harga_jual' => 15000,
            'harga_modal' => 10000,
            'stok_minimum' => 5,
            'status' => 'aktif',
            'deskripsi' => 'Deskripsi test produk',
        ]);
        $responseProduk->assertRedirect('/tefa/produk');
        $this->assertDatabaseHas('produk', ['nama' => 'Produk Test Web']);

        $kategoriAlat = KategoriAlat::create([
            'nama' => 'Perkakas Test',
            'slug' => 'perkakas-test',
            'status' => 'aktif',
        ]);
        $responseAlat = $this->actingAs($user)->post('/alat/daftar', [
            'nama' => 'Alat Test Web',
            'kategori_alat_id' => $kategoriAlat->id,
            'merek' => 'Bosch',
            'kondisi' => 'baik',
            'stok_total' => 10,
        ]);
        $responseAlat->assertRedirect('/alat/daftar');
        $this->assertDatabaseHas('alat', ['nama' => 'Alat Test Web']);
    }
}
