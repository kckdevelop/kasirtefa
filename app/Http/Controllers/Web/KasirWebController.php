<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Services\Tefa\PenjualanService;
use Exception;
use Illuminate\Http\Request;

class KasirWebController extends Controller
{
    protected $penjualanService;

    public function __construct(PenjualanService $penjualanService)
    {
        $this->penjualanService = $penjualanService;
    }

    public function index()
    {
        $kategori = KategoriProduk::where('status', 'aktif')->orderBy('nama')->get();
        $produk = Produk::with('kategori')
            ->where('status', 'aktif')
            ->where('is_ready', true)
            ->where('stok', '>', 0)
            ->orderBy('nama')
            ->get();

        $pelanggan = \App\Models\Pelanggan::aktif()->orderBy('nama')->get();

        return view('tefa.kasir.index', compact('kategori', 'produk', 'pelanggan'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_nama' => 'nullable|string|max:200',
            'metode_pembayaran' => 'required|in:tunai,transfer,qris',
            'nominal_bayar' => 'required|numeric|min:0',
            'diskon_persen' => 'nullable|numeric|min:0|max:100',
            'items' => 'required|array|min:1',
            'items.*.produk_id' => 'required|exists:produk,id',
            'items.*.jumlah' => 'required|integer|min:1',
        ]);

        try {
            $transaksi = $this->penjualanService->createTransaksi($request->all(), auth()->id());
            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dibuat',
                'transaksi_id' => $transaksi->id,
                'cetak_url' => route('tefa.transaksi.cetak-struk', $transaksi->id),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
