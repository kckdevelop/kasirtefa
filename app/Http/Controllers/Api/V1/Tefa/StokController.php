<?php

namespace App\Http\Controllers\Api\V1\Tefa;

use App\Http\Controllers\Controller;
use App\Models\StokKeluar;
use App\Models\StokMasuk;
use App\Services\Tefa\StokService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;

class StokController extends Controller
{
    use ApiResponse;

    protected $stokService;

    public function __construct(StokService $stokService)
    {
        $this->stokService = $stokService;
    }

    public function listMasuk(Request $request)
    {
        $query = StokMasuk::with(['produk', 'creator']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('produk', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")->orWhere('kode_produk', 'like', "%{$search}%");
            })->orWhere('kode_transaksi', 'like', "%{$search}%");
        }

        $list = $query->latest()->paginate($request->get('per_page', 15));
        return $this->successResponse($list, 'Daftar stok masuk');
    }

    public function storeMasuk(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|exists:produk,id',
            'tanggal' => 'required|date',
            'jumlah' => 'required|integer|min:1',
            'sumber' => 'required|in:produksi,pembelian,donasi,lainnya',
            'keterangan' => 'nullable|string',
            'dokumen' => 'nullable|file|max:5000',
        ]);

        $data = $request->all();
        if ($request->hasFile('dokumen')) {
            $data['dokumen'] = $request->file('dokumen')->store('stok', 'public');
        }

        try {
            $result = $this->stokService->tambahStok($data, auth()->id());
            return $this->successResponse($result, 'Stok masuk berhasil ditambahkan', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function showMasuk($id)
    {
        $stok = StokMasuk::with(['produk', 'creator'])->findOrFail($id);
        return $this->successResponse($stok, 'Detail stok masuk');
    }

    public function destroyMasuk($id)
    {
        try {
            $this->stokService->rollbackStokMasuk($id);
            return $this->successResponse(null, 'Stok masuk berhasil dihapus & stok produk di-rollback');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function listKeluar(Request $request)
    {
        $query = StokKeluar::with(['produk', 'creator']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('produk', function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")->orWhere('kode_produk', 'like', "%{$search}%");
            })->orWhere('kode_transaksi', 'like', "%{$search}%");
        }

        $list = $query->latest()->paginate($request->get('per_page', 15));
        return $this->successResponse($list, 'Daftar stok keluar');
    }

    public function storeKeluar(Request $request)
    {
        $request->validate([
            'produk_id' => 'required|exists:produk,id',
            'tanggal' => 'required|date',
            'jumlah' => 'required|integer|min:1',
            'tujuan' => 'required|in:penjualan,penggunaan,rusak,kadaluarsa,lainnya',
            'keterangan' => 'nullable|string',
        ]);

        try {
            $result = $this->stokService->kurangiStok($request->all(), auth()->id());
            return $this->successResponse($result, 'Stok keluar berhasil ditambahkan', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function showKeluar($id)
    {
        $stok = StokKeluar::with(['produk', 'creator'])->findOrFail($id);
        return $this->successResponse($stok, 'Detail stok keluar');
    }

    public function destroyKeluar($id)
    {
        try {
            $this->stokService->rollbackStokKeluar($id);
            return $this->successResponse(null, 'Stok keluar berhasil dihapus & stok produk di-rollback');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function kartuStok($produk_id)
    {
        $kartu = $this->stokService->getKartuStok($produk_id);
        return $this->successResponse($kartu, 'Kartu stok produk');
    }

    public function ringkasan()
    {
        $stokLow = $this->stokService->checkStokMinimum();
        return $this->successResponse([
            'produk_stok_menipis' => $stokLow,
            'total_produk_menipis' => $stokLow->count(),
        ], 'Ringkasan stok produk');
    }
}
