<?php

namespace App\Http\Controllers\Api\V1\Tefa;

use App\Http\Controllers\Controller;
use App\Models\KategoriProduk;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KategoriProdukController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = KategoriProduk::withCount('produk');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('nama', 'like', "%{$search}%");
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->get('per_page', 15);
        $kategori = $query->orderBy('urutan')->orderBy('nama')->paginate($perPage);

        return $this->successResponse($kategori, 'Daftar kategori produk');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'deskripsi' => 'nullable|string',
            'ikon' => 'nullable|string',
            'urutan' => 'nullable|integer',
            'status' => 'nullable|in:aktif,nonaktif',
        ]);

        $slug = Str::slug($request->nama);
        $originalSlug = $slug;
        $count = 1;
        while (KategoriProduk::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        $kategori = KategoriProduk::create([
            'nama' => $request->nama,
            'slug' => $slug,
            'deskripsi' => $request->deskripsi,
            'ikon' => $request->ikon,
            'urutan' => $request->urutan ?? 0,
            'status' => $request->status ?? 'aktif',
        ]);

        return $this->successResponse($kategori, 'Kategori produk berhasil ditambahkan', 201);
    }

    public function show($id)
    {
        $kategori = KategoriProduk::with('produk')->findOrFail($id);
        return $this->successResponse($kategori, 'Detail kategori produk');
    }

    public function update(Request $request, $id)
    {
        $kategori = KategoriProduk::findOrFail($id);

        $request->validate([
            'nama' => 'sometimes|required|string|max:100',
            'deskripsi' => 'nullable|string',
            'ikon' => 'nullable|string',
            'urutan' => 'nullable|integer',
            'status' => 'nullable|in:aktif,nonaktif',
        ]);

        if ($request->has('nama') && $request->nama !== $kategori->nama) {
            $slug = Str::slug($request->nama);
            $originalSlug = $slug;
            $count = 1;
            while (KategoriProduk::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                $slug = "{$originalSlug}-{$count}";
                $count++;
            }
            $kategori->slug = $slug;
        }

        $kategori->update($request->only(['nama', 'deskripsi', 'ikon', 'urutan', 'status']));

        return $this->successResponse($kategori, 'Kategori produk berhasil diperbarui');
    }

    public function destroy($id)
    {
        $kategori = KategoriProduk::findOrFail($id);
        $kategori->delete();
        return $this->successResponse(null, 'Kategori produk berhasil dihapus');
    }

    public function select2()
    {
        $data = KategoriProduk::where('status', 'aktif')
            ->select('id', 'nama')
            ->orderBy('nama')
            ->get();
        return $this->successResponse($data, 'Data dropdown kategori produk');
    }

    public function produk($id)
    {
        $kategori = KategoriProduk::findOrFail($id);
        $produk = $kategori->produk()->where('status', 'aktif')->paginate(15);
        return $this->successResponse($produk, 'Daftar produk dalam kategori');
    }
}
