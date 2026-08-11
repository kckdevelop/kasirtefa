<?php

namespace App\Http\Controllers\Api\V1\Tefa;

use App\Exports\PenjualanExport;
use App\Http\Controllers\Controller;
use App\Models\DetailPenjualan;
use App\Models\TransaksiPenjualan;
use App\Services\LaporanService;
use App\Traits\ApiResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class LaporanPenjualanController extends Controller
{
    use ApiResponse;

    protected $laporanService;

    public function __construct(LaporanService $laporanService)
    {
        $this->laporanService = $laporanService;
    }

    public function index(Request $request)
    {
        $result = $this->laporanService->laporanPenjualan($request->all());
        return $this->successResponse($result, 'Laporan penjualan');
    }

    public function ringkasan(Request $request)
    {
        $result = $this->laporanService->laporanPenjualan($request->all());
        return $this->successResponse($result['ringkasan'], 'Ringkasan laporan penjualan');
    }

    public function perProduk(Request $request)
    {
        $query = DetailPenjualan::select(
            'produk_id',
            DB::raw('SUM(jumlah) as total_terjual'),
            DB::raw('SUM(subtotal) as total_omzet')
        )
            ->whereHas('transaksi', function ($q) use ($request) {
                $q->where('status', 'lunas');
                if ($request->filled('tanggal_mulai')) {
                    $q->whereDate('tanggal', '>=', $request->tanggal_mulai);
                }
                if ($request->filled('tanggal_selesai')) {
                    $q->whereDate('tanggal', '<=', $request->tanggal_selesai);
                }
            })
            ->groupBy('produk_id')
            ->with('produk.kategori');

        $result = $query->orderBy('total_terjual', 'desc')->get();
        return $this->successResponse($result, 'Laporan penjualan per produk');
    }

    public function perKategori(Request $request)
    {
        $result = DetailPenjualan::join('produk', 'detail_penjualan.produk_id', '=', 'produk.id')
            ->join('kategori_produk', 'produk.kategori_produk_id', '=', 'kategori_produk.id')
            ->join('transaksi_penjualan', 'detail_penjualan.transaksi_penjualan_id', '=', 'transaksi_penjualan.id')
            ->where('transaksi_penjualan.status', 'lunas')
            ->select(
                'kategori_produk.id',
                'kategori_produk.nama as kategori',
                DB::raw('SUM(detail_penjualan.jumlah) as total_terjual'),
                DB::raw('SUM(detail_penjualan.subtotal) as total_omzet')
            )
            ->groupBy('kategori_produk.id', 'kategori_produk.nama')
            ->get();

        return $this->successResponse($result, 'Laporan penjualan per kategori');
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new PenjualanExport($request->all()), 'laporan-penjualan-' . date('Ymd-His') . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $result = $this->laporanService->laporanPenjualan($request->all());
        $pdf = Pdf::loadView('laporan.pdf.laporan-penjualan', [
            'data' => $result['data'],
            'ringkasan' => $result['ringkasan'],
            'filters' => $request->all(),
            'tanggalCetak' => formatTanggalWaktu(now()),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('laporan-penjualan-' . date('Ymd-His') . '.pdf');
    }

    public function struk($id)
    {
        $transaksi = TransaksiPenjualan::with(['items.produk', 'kasir'])->findOrFail($id);
        $pengaturan = \App\Models\PengaturanAplikasi::all()->pluck('nilai', 'kunci')->toArray();
        return view('tefa.transaksi.struk', compact('transaksi', 'pengaturan'));
    }
}
