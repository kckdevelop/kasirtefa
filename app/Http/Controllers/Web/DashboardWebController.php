<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Alat;
use App\Models\LisensiAplikasi;
use App\Models\PeminjamanAlat;
use App\Models\Produk;
use App\Models\StokMasuk;
use App\Models\StokKeluar;
use App\Models\TransaksiPenjualan;
use Carbon\Carbon;

class DashboardWebController extends Controller
{
    public function index()
    {
        $today     = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        // TEFa stats
        $totalProduk       = Produk::where('status', 'aktif')->count();
        $stokMenipis       = Produk::where('status', 'aktif')->whereColumn('stok', '<=', 'stok_minimum')->get();
        $transaksiHariIni  = TransaksiPenjualan::where('status', 'lunas')->whereDate('tanggal', $today)->count();
        $omzetBulanIni     = TransaksiPenjualan::where('status', 'lunas')->whereDate('tanggal', '>=', $thisMonth)->sum('total_akhir');
        $omzetBulanLalu    = TransaksiPenjualan::where('status', 'lunas')
            ->whereDate('tanggal', '>=', $lastMonth)
            ->whereDate('tanggal', '<=', $lastMonthEnd)
            ->sum('total_akhir');

        // Pertumbuhan omzet
        $pertumbuhanOmzet = 0;
        if ($omzetBulanLalu > 0) {
            $pertumbuhanOmzet = round((($omzetBulanIni - $omzetBulanLalu) / $omzetBulanLalu) * 100, 1);
        } elseif ($omzetBulanIni > 0) {
            $pertumbuhanOmzet = 100;
        }

        // Stok masuk & keluar bulan ini
        $stokMasukBulanIni  = StokMasuk::whereDate('tanggal', '>=', $thisMonth)->sum('jumlah');
        $stokKeluarBulanIni = StokKeluar::whereDate('tanggal', '>=', $thisMonth)->sum('jumlah');

        // Alat stats
        $alatTersedia    = Alat::where('status', 'aktif')->sum('jumlah_tersedia');
        $sedangDipinjam  = PeminjamanAlat::whereIn('status', ['disetujui', 'dipinjam'])->count();
        $peminjamanTerlambat = PeminjamanAlat::with(['peminjam', 'items.alat'])
            ->where('status', 'terlambat')
            ->orWhere(function ($q) {
                $q->whereIn('status', ['disetujui', 'dipinjam'])
                    ->whereDate('tanggal_kembali_rencana', '<', Carbon::today());
            })->get();

        // Lisensi stats
        $lisensiAktif         = LisensiAplikasi::aktif()->count();
        $lisensiSegera        = LisensiAplikasi::mendekatiKadaluarsa(30)->count();
        $lisensiExpiredHariIni = LisensiAplikasi::aktif()
            ->where(function ($q) {
                $q->whereDate('tanggal_jatuh_tempo', '<', Carbon::today())
                  ->orWhereDate('tanggal_berakhir', '<', Carbon::today());
            })->count();
        $recentLisensi = LisensiAplikasi::latest()->take(5)->get();

        // Recent records
        $recentTransactions = TransaksiPenjualan::with(['kasir', 'items.produk'])
            ->latest('tanggal')->take(5)->get();
        $recentPeminjaman = PeminjamanAlat::with(['peminjam', 'items.alat'])
            ->latest()->take(5)->get();

        return view('dashboard.index', compact(
            'totalProduk',
            'stokMenipis',
            'transaksiHariIni',
            'omzetBulanIni',
            'omzetBulanLalu',
            'pertumbuhanOmzet',
            'stokMasukBulanIni',
            'stokKeluarBulanIni',
            'alatTersedia',
            'sedangDipinjam',
            'peminjamanTerlambat',
            'lisensiAktif',
            'lisensiSegera',
            'lisensiExpiredHariIni',
            'recentLisensi',
            'recentTransactions',
            'recentPeminjaman'
        ));
    }
}

