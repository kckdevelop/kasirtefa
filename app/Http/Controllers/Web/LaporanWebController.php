<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\TransaksiPenjualan;
use App\Models\PeminjamanAlat;
use App\Services\LaporanService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LaporanWebController extends Controller
{
    public function __construct(protected LaporanService $laporanService) {}

    private function parseFilter(Request $request): array
    {
        // Handle quick period selection
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

        // Export handlers
        if ($request->export === 'excel') {
            return $this->laporanService->exportPenjualanExcel($filter);
        }
        if ($request->export === 'pdf') {
            return $this->laporanService->exportPenjualanPdf($filter);
        }

        $laporan = $this->laporanService->ringkasanPenjualan($filter);

        $transaksi = TransaksiPenjualan::with(['kasir', 'items'])
            ->whereDate('tanggal', '>=', $filter['tanggal_mulai'])
            ->whereDate('tanggal', '<=', $filter['tanggal_selesai'])
            ->where('status', 'selesai')
            ->latest('tanggal')
            ->paginate(25);

        return view('laporan.penjualan', compact('laporan', 'transaksi', 'filter'));
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

        $laporan = $this->laporanService->ringkasanPeminjaman($filter);

        $peminjaman = PeminjamanAlat::with(['peminjam', 'items.alat'])
            ->whereDate('tanggal_pinjam', '>=', $filter['tanggal_mulai'])
            ->whereDate('tanggal_pinjam', '<=', $filter['tanggal_selesai'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->latest('tanggal_pinjam')
            ->paginate(25);

        return view('laporan.peminjaman', compact('laporan', 'peminjaman', 'filter'));
    }

    public function stok(Request $request)
    {
        $laporan = $this->laporanService->ringkasanStok();
        return view('laporan.stok', compact('laporan'));
    }
}
