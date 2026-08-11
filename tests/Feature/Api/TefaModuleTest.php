<?php

namespace Tests\Feature\Api;

use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TefaModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed basic role & admin user
        $this->artisan('db:seed');
    }

    public function test_can_list_and_create_kategori_produk(): void
    {
        $user = User::first() ?? User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/tefa/kategori');
        $response->assertStatus(200);

        $createResponse = $this->actingAs($user)->postJson('/api/v1/tefa/kategori', [
            'nama' => 'Makanan & Minuman',
            'kode' => 'MNK',
            'deskripsi' => 'Kategori produk konsumsi',
            'status' => 'aktif',
        ]);
        $createResponse->assertStatus(201);
    }

    public function test_can_create_and_list_produk(): void
    {
        $user = User::first() ?? User::factory()->create();
        $kategori = KategoriProduk::create([
            'nama' => 'Pakaian',
            'slug' => 'pakaian',
            'kode' => 'PKN',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($user)->postJson('/api/v1/tefa/produk', [
            'kategori_produk_id' => $kategori->id,
            'nama' => 'Kaos TEFa Logotype',
            'harga_jual' => 75000,
            'harga_modal' => 50000,
            'stok' => 20,
            'satuan' => 'pcs',
            'status' => 'aktif',
        ]);

        $response->assertStatus(201);

        $listResponse = $this->actingAs($user)->getJson('/api/v1/tefa/produk');
        $listResponse->assertStatus(200);
    }
}
