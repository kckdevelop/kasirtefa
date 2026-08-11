<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\KategoriProduk;
use App\Models\PeminjamanAlat;
use App\Models\TransaksiPenjualan;
use App\Services\LaporanService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LaporanWebController extends Controller
{
    public function __construct(protected LaporanService $laporanService) {}

    private function parseFilter(Request $request): array
    {
        $periode = $request->input('periode');
        $start   = $request->input('tanggal_mulai', Carbon::now()->startOfMonth()->toDateString());
        $end     = $request->input('tanggal_selesai', Carbon::now()->toDateString());

        return match ($periode) {
            'hari_ini'   => ['tanggal_mulai' => Carbon::today()->toDateString(), 'tanggal_selesai' => Carbon::today()->toDateString()],
            'minggu_ini' => ['tanggal_mulai' => Carbon::now()->startOfWeek()->toDateString(), 'tanggal_selesai' => Carbon::now()->endOfWeek()->toDateString()],
            'bulan_ini'  => ['tanggal_mulai' => Carbon::now()->startOfMonth()->toDateString(), 'tanggal_selesai' => Carbon::now()->endOfMonth()->toDateString()],
            'tahun_ini'  => ['tanggal_mulai' => Carbon::now()->startOfYear()->toDateString(), 'tanggal_selesai' => Carbon::now()->endOfYear()->toDateString()],
            default      => ['tanggal_mulai' => $start, 'tanggal_selesai' => $end],
        };
    }

    public function penjualan(Request $request)
    {
        $filter = $this->parseFilter($request);

        if ($request->export === 'excel') {
            return $this->laporanService->exportPenjualanExcel($filter);
        }
        if ($request->export === 'pdf') {
            return $this->laporanService->exportPenjualanPdf($filter);
        }

        $report = $this->laporanService->laporanPenjualan($request->all());
        $laporan = $report;
        $transaksi = TransaksiPenjualan::with('kasir')->latest()->paginate(15);
        $kategori = KategoriProduk::all();

        return view('laporan.penjualan', compact('report', 'laporan', 'transaksi', 'kategori', 'filter'));
    }

    public function peminjaman(Request $request)
    {
        $filter = $this->parseFilter($request);

        if ($request->export === 'excel') {
            return $this->laporanService->exportPeminjamanExcel($filter);
        }
        if ($request->export === 'pdf') {
            return $this->laporanService->exportPeminjamanPdf($filter);
        }

        $report = $this->laporanService->laporanPeminjaman($request->all());
        $laporan = $report;
        $peminjaman = PeminjamanAlat::with(['peminjam', 'denda'])->latest('tanggal_pinjam')->paginate(15);

        return view('laporan.peminjaman', compact('report', 'laporan', 'peminjaman', 'filter'));
    }

    public function inventaris(Request $request)
    {
        $report = $this->laporanService->laporanInventaris($request->all());
        return view('laporan.inventaris', compact('report'));
    }

    public function kondisiAlat()
    {
        $report = $this->laporanService->laporanKondisiAlat();
        return view('laporan.kondisi-alat', compact('report'));
    }

    public function stok(Request $request)
    {
        $laporan = $this->laporanService->ringkasanStok();
        return view('laporan.stok', compact('laporan'));
    }
}
