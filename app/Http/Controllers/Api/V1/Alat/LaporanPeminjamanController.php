<?php

namespace App\Http\Controllers\Api\V1\Alat;

use App\Exports\InventarisExport;
use App\Exports\PeminjamanExport;
use App\Http\Controllers\Controller;
use App\Models\DetailPeminjaman;
use App\Services\LaporanService;
use App\Traits\ApiResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class LaporanPeminjamanController extends Controller
{
    use ApiResponse;

    protected $laporanService;

    public function __construct(LaporanService $laporanService)
    {
        $this->laporanService = $laporanService;
    }

    public function index(Request $request)
    {
        $result = $this->laporanService->laporanPeminjaman($request->all());
        return $this->successResponse($result, 'Laporan peminjaman alat');
    }

    public function ringkasan(Request $request)
    {
        $result = $this->laporanService->laporanPeminjaman($request->all());
        return $this->successResponse($result['ringkasan'], 'Ringkasan peminjaman alat');
    }

    public function perAlat(Request $request)
    {
        $list = DetailPeminjaman::select('alat_id', DB::raw('COUNT(*) as total_dipinjam'), DB::raw('SUM(jumlah_pinjam) as total_unit_dipinjam'))
            ->groupBy('alat_id')
            ->with('alat.kategori')
            ->orderBy('total_dipinjam', 'desc')
            ->get();

        return $this->successResponse($list, 'Laporan peminjaman per alat');
    }

    public function perPeminjam(Request $request)
    {
        $list = DetailPeminjaman::join('peminjaman_alat', 'detail_peminjaman.peminjaman_alat_id', '=', 'peminjaman_alat.id')
            ->join('users', 'peminjaman_alat.peminjam_id', '=', 'users.id')
            ->select('users.id', 'users.nama', 'users.kelas', 'users.jurusan', DB::raw('COUNT(DISTINCT peminjaman_alat.id) as total_peminjaman'))
            ->groupBy('users.id', 'users.nama', 'users.kelas', 'users.jurusan')
            ->orderBy('total_peminjaman', 'desc')
            ->get();

        return $this->successResponse($list, 'Laporan peminjaman per peminjam');
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new PeminjamanExport($request->all()), 'laporan-peminjaman-' . date('Ymd-His') . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $result = $this->laporanService->laporanPeminjaman($request->all());
        $pdf = Pdf::loadView('laporan.pdf.laporan-peminjaman', [
            'data' => $result['data'],
            'ringkasan' => $result['ringkasan'],
            'filters' => $request->all(),
            'tanggalCetak' => formatTanggalWaktu(now()),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('laporan-peminjaman-' . date('Ymd-His') . '.pdf');
    }

    public function denda(Request $request)
    {
        $result = $this->laporanService->laporanDenda($request->all());
        return $this->successResponse($result, 'Laporan denda peminjaman');
    }

    public function kondisiAlat()
    {
        $result = $this->laporanService->laporanKondisiAlat();
        return $this->successResponse($result, 'Laporan kondisi alat');
    }

    public function kondisiAlatPdf()
    {
        $result = $this->laporanService->laporanKondisiAlat();
        $pdf = Pdf::loadView('laporan.pdf.laporan-kondisi-alat', [
            'rekap' => $result['rekap'],
            'detail' => $result['detail_perlu_perhatian'],
            'tanggalCetak' => formatTanggalWaktu(now()),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('laporan-kondisi-alat-' . date('Ymd-His') . '.pdf');
    }

    public function inventaris(Request $request)
    {
        $result = $this->laporanService->laporanInventaris($request->all());
        return $this->successResponse($result, 'Laporan inventaris alat');
    }

    public function inventarisExcel(Request $request)
    {
        return Excel::download(new InventarisExport($request->all()), 'laporan-inventaris-alat-' . date('Ymd-His') . '.xlsx');
    }

    public function inventarisPdf(Request $request)
    {
        $result = $this->laporanService->laporanInventaris($request->all());
        $pdf = Pdf::loadView('laporan.pdf.laporan-inventaris', [
            'data' => $result['data'],
            'ringkasan' => $result['ringkasan'],
            'tanggalCetak' => formatTanggalWaktu(now()),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('laporan-inventaris-alat-' . date('Ymd-His') . '.pdf');
    }
}
