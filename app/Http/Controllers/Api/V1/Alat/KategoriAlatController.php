<?php

namespace App\Http\Controllers\Api\V1\Alat;

use App\Http\Controllers\Controller;
use App\Models\KategoriAlat;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KategoriAlatController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = KategoriAlat::withCount('alat');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('nama', 'like', "%{$search}%");
        }

        $list = $query->orderBy('urutan')->orderBy('nama')->paginate($request->get('per_page', 15));
        return $this->successResponse($list, 'Daftar kategori alat');
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
        while (KategoriAlat::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        $kategori = KategoriAlat::create([
            'nama' => $request->nama,
            'slug' => $slug,
            'deskripsi' => $request->deskripsi,
            'ikon' => $request->ikon,
            'urutan' => $request->urutan ?? 0,
            'status' => $request->status ?? 'aktif',
        ]);

        return $this->successResponse($kategori, 'Kategori alat berhasil ditambahkan', 201);
    }

    public function show($id)
    {
        $kategori = KategoriAlat::with('alat')->findOrFail($id);
        return $this->successResponse($kategori, 'Detail kategori alat');
    }

    public function update(Request $request, $id)
    {
        $kategori = KategoriAlat::findOrFail($id);

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
            while (KategoriAlat::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                $slug = "{$originalSlug}-{$count}";
                $count++;
            }
            $kategori->slug = $slug;
        }

        $kategori->update($request->only(['nama', 'deskripsi', 'ikon', 'urutan', 'status']));

        return $this->successResponse($kategori, 'Kategori alat berhasil diperbarui');
    }

    public function destroy($id)
    {
        $kategori = KategoriAlat::findOrFail($id);
        $kategori->delete();
        return $this->successResponse(null, 'Kategori alat berhasil dihapus');
    }

    public function select2()
    {
        $data = KategoriAlat::where('status', 'aktif')->select('id', 'nama')->orderBy('nama')->get();
        return $this->successResponse($data, 'Data dropdown kategori alat');
    }

    public function alat($id)
    {
        $kategori = KategoriAlat::findOrFail($id);
        $alat = $kategori->alat()->where('status', 'aktif')->paginate(15);
        return $this->successResponse($alat, 'Daftar alat dalam kategori');
    }
}
