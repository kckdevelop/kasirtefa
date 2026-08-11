<?php

namespace Tests\Feature\Api;

use App\Models\Alat;
use App\Models\KategoriAlat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlatModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
    }

    public function test_can_list_and_create_kategori_alat(): void
    {
        $user = User::first() ?? User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/alat/kategori');
        $response->assertStatus(200);

        $createRes = $this->actingAs($user)->postJson('/api/v1/alat/kategori', [
            'nama' => 'Peralatan Listrik',
            'kode' => 'LST',
            'status' => 'aktif',
        ]);
        $createRes->assertStatus(201);
    }

    public function test_can_create_and_fetch_alat(): void
    {
        $user = User::first() ?? User::factory()->create();
        $kategori = KategoriAlat::create([
            'nama' => 'Perkakas Tangan',
            'slug' => 'perkakas-tangan',
            'kode' => 'PRK',
            'status' => 'aktif',
        ]);

        $createRes = $this->actingAs($user)->postJson('/api/v1/alat', [
            'kategori_alat_id' => $kategori->id,
            'nama' => 'Bor Listrik Heavy Duty',
            'merek' => 'Bosch',
            'jumlah_total' => 5,
            'kondisi' => 'baik',
            'status_ketersediaan' => 'tersedia',
            'status' => 'aktif',
        ]);
        $createRes->assertStatus(201);

        $listRes = $this->actingAs($user)->getJson('/api/v1/alat');
        $listRes->assertStatus(200);
    }
}
