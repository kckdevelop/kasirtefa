<?php

namespace App\Http\Controllers\Api\V1\Tefa;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProdukController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = Produk::with('kategori');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('kode_produk', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        if ($request->filled('kategori_id')) {
            $query->where('kategori_produk_id', $request->kategori_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('is_ready')) {
            $query->where('is_ready', filter_var($request->is_ready, FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('stok_min')) {
            $query->whereColumn('stok', '<=', 'stok_minimum');
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $perPage = $request->get('per_page', 15);

        $produk = $query->orderBy($sortBy, $sortDir)->paginate($perPage);

        return $this->successResponse($produk, 'Daftar produk');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kategori_produk_id' => 'required|exists:kategori_produk,id',
            'nama' => 'required|string|max:200',
            'deskripsi' => 'nullable|string',
            'foto' => 'nullable|image|max:2048',
            'harga_jual' => 'required|numeric|min:0',
            'harga_modal' => 'nullable|numeric|min:0',
            'satuan' => 'required|string|max:50',
            'stok' => 'nullable|integer|min:0',
            'stok_minimum' => 'nullable|integer|min:0',
            'berat' => 'nullable|numeric|min:0',
            'is_ready' => 'nullable|boolean',
            'spesifikasi' => 'nullable|array',
            'catatan' => 'nullable|string',
            'status' => 'nullable|in:aktif,nonaktif',
        ]);

        $todayStr = Carbon::now()->format('Ymd');
        $lastCount = Produk::withTrashed()->whereDate('created_at', Carbon::today())->count();
        $kode = 'PRD-' . $todayStr . '-' . str_pad($lastCount + 1, 4, '0', STR_PAD_LEFT);

        $slug = Str::slug($request->nama);
        $originalSlug = $slug;
        $count = 1;
        while (Produk::where('slug', $slug)->exists()) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('produk', 'public');
        }

        $produk = Produk::create([
            'kategori_produk_id' => $request->kategori_produk_id,
            'kode_produk' => $kode,
            'nama' => $request->nama,
            'slug' => $slug,
            'deskripsi' => $request->deskripsi,
            'foto' => $fotoPath,
            'harga_jual' => $request->harga_jual,
            'harga_modal' => $request->harga_modal,
            'satuan' => $request->satuan,
            'stok' => $request->stok ?? 0,
            'stok_minimum' => $request->stok_minimum ?? 5,
            'berat' => $request->berat,
            'is_ready' => $request->is_ready ?? true,
            'spesifikasi' => $request->spesifikasi,
            'catatan' => $request->catatan,
            'status' => $request->status ?? 'aktif',
            'created_by' => auth()->id(),
        ]);

        return $this->successResponse($produk->load('kategori'), 'Produk berhasil ditambahkan', 201);
    }

    public function show($id)
    {
        $produk = Produk::with(['kategori', 'creator', 'stokMasuk', 'stokKeluar'])->findOrFail($id);
        return $this->successResponse($produk, 'Detail produk');
    }

    public function update(Request $request, $id)
    {
        $produk = Produk::findOrFail($id);

        $request->validate([
            'kategori_produk_id' => 'sometimes|required|exists:kategori_produk,id',
            'nama' => 'sometimes|required|string|max:200',
            'deskripsi' => 'nullable|string',
            'foto' => 'nullable|image|max:2048',
            'harga_jual' => 'sometimes|required|numeric|min:0',
            'harga_modal' => 'nullable|numeric|min:0',
            'satuan' => 'sometimes|required|string|max:50',
            'stok' => 'nullable|integer|min:0',
            'stok_minimum' => 'nullable|integer|min:0',
            'berat' => 'nullable|numeric|min:0',
            'is_ready' => 'nullable|boolean',
            'spesifikasi' => 'nullable|array',
            'catatan' => 'nullable|string',
            'status' => 'nullable|in:aktif,nonaktif',
        ]);

        if ($request->has('nama') && $request->nama !== $produk->nama) {
            $slug = Str::slug($request->nama);
            $originalSlug = $slug;
            $count = 1;
            while (Produk::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                $slug = "{$originalSlug}-{$count}";
                $count++;
            }
            $produk->slug = $slug;
        }

        if ($request->hasFile('foto')) {
            $produk->foto = $request->file('foto')->store('produk', 'public');
        }

        $produk->updated_by = auth()->id();
        $produk->update($request->except(['foto', 'slug']));

        return $this->successResponse($produk->load('kategori'), 'Produk berhasil diperbarui');
    }

    public function destroy($id)
    {
        $produk = Produk::findOrFail($id);
        $produk->delete();
        return $this->successResponse(null, 'Produk berhasil dihapus');
    }

    public function select2()
    {
        $data = Produk::where('status', 'aktif')
            ->select('id', 'kode_produk', 'nama', 'harga_jual', 'stok', 'satuan')
            ->orderBy('nama')
            ->get();
        return $this->successResponse($data, 'Data dropdown produk');
    }

    public function stokHistory($id)
    {
        $produk = Produk::findOrFail($id);
        $masuk = $produk->stokMasuk()->with('creator')->latest()->get();
        $keluar = $produk->stokKeluar()->with('creator')->latest()->get();

        return $this->successResponse([
            'stok_masuk' => $masuk,
            'stok_keluar' => $keluar,
        ], 'Riwayat stok produk');
    }

    public function transaksiHistory($id)
    {
        $produk = Produk::findOrFail($id);
        $transaksi = $produk->detailPenjualan()->with('transaksi.kasir')->latest()->paginate(15);
        return $this->successResponse($transaksi, 'Riwayat transaksi produk');
    }
}
