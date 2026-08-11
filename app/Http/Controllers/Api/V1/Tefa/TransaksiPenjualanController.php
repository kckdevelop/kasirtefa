<?php

namespace App\Http\Controllers\Api\V1\Tefa;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Tefa\TransaksiPenjualanRequest;
use App\Models\TransaksiPenjualan;
use App\Services\Tefa\PenjualanService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\Request;

class TransaksiPenjualanController extends Controller
{
    use ApiResponse;

    protected $penjualanService;

    public function __construct(PenjualanService $penjualanService)
    {
        $this->penjualanService = $penjualanService;
    }

    public function index(Request $request)
    {
        $query = TransaksiPenjualan::with(['items.produk', 'kasir']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('kode_transaksi', 'like', "%{$search}%")
                ->orWhere('customer_nama', 'like', "%{$search}%");
        }

        if ($request->filled('tanggal_mulai')) {
            $query->whereDate('tanggal', '>=', $request->tanggal_mulai);
        }

        if ($request->filled('tanggal_selesai')) {
            $query->whereDate('tanggal', '<=', $request->tanggal_selesai);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('metode_pembayaran')) {
            $query->where('metode_pembayaran', $request->metode_pembayaran);
        }

        $perPage = $request->get('per_page', 15);
        $transaksi = $query->orderBy('tanggal', 'desc')->orderBy('waktu', 'desc')->paginate($perPage);

        return $this->successResponse($transaksi, 'Daftar transaksi penjualan');
    }

    public function store(TransaksiPenjualanRequest $request)
    {
        try {
            $transaksi = $this->penjualanService->createTransaksi($request->validated(), auth()->id());
            return $this->successResponse($transaksi, 'Transaksi penjualan berhasil dibuat', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function show($id)
    {
        $transaksi = TransaksiPenjualan::with(['items.produk.kategori', 'kasir'])->findOrFail($id);
        return $this->successResponse($transaksi, 'Detail transaksi penjualan');
    }

    public function batal($id)
    {
        try {
            $transaksi = $this->penjualanService->batalkanTransaksi($id, auth()->id());
            return $this->successResponse($transaksi, 'Transaksi berhasil dibatalkan');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function findByKode($kode)
    {
        $transaksi = TransaksiPenjualan::with(['items.produk', 'kasir'])
            ->where('kode_transaksi', $kode)
            ->firstOrFail();

        return $this->successResponse($transaksi, 'Data transaksi penjualan');
    }
}
