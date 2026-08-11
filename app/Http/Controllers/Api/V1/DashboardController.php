<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Alat;
use App\Models\PeminjamanAlat;
use App\Models\Produk;
use App\Models\TransaksiPenjualan;
use App\Traits\ApiResponse;
use Carbon\Carbon;

class DashboardController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        $totalProduk = Produk::where('status', 'aktif')->count();
        $stokMenipis = Produk::where('status', 'aktif')->whereColumn('stok', '<=', 'stok_minimum')->count();
        $transaksiHariIni = TransaksiPenjualan::where('status', 'lunas')->whereDate('tanggal', $today)->count();
        $omzetBulanIni = TransaksiPenjualan::where('status', 'lunas')->whereDate('tanggal', '>=', $thisMonth)->sum('total_akhir');

        $alatTersedia = Alat::where('status', 'aktif')->sum('jumlah_tersedia');
        $sedangDipinjam = PeminjamanAlat::whereIn('status', ['disetujui', 'dipinjam'])->count();
        $terlambatKembali = PeminjamanAlat::where('status', 'terlambat')->count();

        return $this->successResponse([
            'total_produk' => $totalProduk,
            'stok_menipis' => $stokMenipis,
            'transaksi_hari_ini' => $transaksiHariIni,
            'omzet_bulan_ini' => $omzetBulanIni,
            'alat_tersedia' => $alatTersedia,
            'sedang_dipinjam' => $sedangDipinjam,
            'terlambat_kembali' => $terlambatKembali,
        ], 'Ringkasan data dashboard');
    }

    public function chartPenjualan()
    {
        $days = [];
        $omzetData = [];
        $transaksiData = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateStr = $date->toDateString();
            $days[] = $date->format('d/m');

            $omzet = TransaksiPenjualan::where('status', 'lunas')
                ->whereDate('tanggal', $dateStr)
                ->sum('total_akhir');

            $count = TransaksiPenjualan::where('status', 'lunas')
                ->whereDate('tanggal', $dateStr)
                ->count();

            $omzetData[] = (float)$omzet;
            $transaksiData[] = $count;
        }

        return $this->successResponse([
            'labels' => $days,
            'omzet' => $omzetData,
            'transaksi' => $transaksiData,
        ], 'Data chart penjualan');
    }

    public function chartPeminjaman()
    {
        $days = [];
        $peminjamanData = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $dateStr = $date->toDateString();
            $days[] = $date->format('d/m');

            $count = PeminjamanAlat::whereDate('tanggal_pinjam', $dateStr)->count();
            $peminjamanData[] = $count;
        }

        return $this->successResponse([
            'labels' => $days,
            'total_peminjaman' => $peminjamanData,
        ], 'Data chart peminjaman');
    }

    public function alertStok()
    {
        $list = Produk::with('kategori')
            ->where('status', 'aktif')
            ->whereColumn('stok', '<=', 'stok_minimum')
            ->get();

        return $this->successResponse($list, 'Daftar produk stok menipis');
    }

    public function alertPeminjaman()
    {
        $list = PeminjamanAlat::with(['peminjam', 'items.alat'])
            ->where('status', 'terlambat')
            ->orWhere(function ($q) {
                $q->whereIn('status', ['disetujui', 'dipinjam'])
                    ->whereDate('tanggal_kembali_rencana', '<', Carbon::today());
            })
            ->get();

        return $this->successResponse($list, 'Daftar peminjaman terlambat');
    }

    public function recentTransactions()
    {
        $list = TransaksiPenjualan::with(['kasir', 'items.produk'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('waktu', 'desc')
            ->take(10)
            ->get();

        return $this->successResponse($list, '10 transaksi terbaru');
    }

    public function recentPeminjaman()
    {
        $list = PeminjamanAlat::with(['peminjam', 'items.alat'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return $this->successResponse($list, '10 peminjaman terbaru');
    }
}
