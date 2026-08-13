<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Alat;
use App\Models\LisensiAplikasi;
use App\Models\PeminjamanAlat;
use App\Models\Produk;
use App\Models\SewaGedung;
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

        // Sewa Gedung Data for Dashboard Calendar
        $colorPalette = [
            'bg-blue-600 border-blue-700 text-white',
            'bg-emerald-600 border-emerald-700 text-white',
            'bg-violet-600 border-violet-700 text-white',
            'bg-amber-600 border-amber-700 text-white',
            'bg-rose-600 border-rose-700 text-white',
            'bg-teal-600 border-teal-700 text-white',
            'bg-indigo-600 border-indigo-700 text-white',
            'bg-sky-600 border-sky-700 text-white',
        ];

        $rawSewaList = SewaGedung::with('gedung')
            ->where('status_sewa', '!=', 'dibatalkan')
            ->orderBy('tanggal_mulai', 'asc')
            ->get();

        $sewaGedungCalendar = $rawSewaList->map(function ($sewa, $index) use ($colorPalette) {
            $color = $colorPalette[$index % count($colorPalette)];
            return [
                'id'                => $sewa->id,
                'kode_sewa'         => $sewa->kode_sewa,
                'gedung_lab_id'     => $sewa->gedung_lab_id,
                'nama_gedung'       => $sewa->gedung?->nama_gedung ?? 'Gedung/Lab',
                'kode_gedung'       => $sewa->gedung?->kode_gedung ?? '',
                'nama_penyewa'      => $sewa->nama_penyewa,
                'instansi_penyewa' => $sewa->instansi_penyewa ?? '',
                'tanggal_mulai'     => $sewa->tanggal_mulai->format('Y-m-d'),
                'tanggal_selesai'   => $sewa->tanggal_selesai->format('Y-m-d'),
                'lama_sewa'         => $sewa->lama_sewa,
                'status_sewa'       => $sewa->status_sewa,
                'status_pembayaran' => $sewa->status_pembayaran,
                'total_biaya'       => $sewa->total_biaya,
                'color'             => $color,
            ];
        });

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
            'recentPeminjaman',
            'sewaGedungCalendar'
        ));
    }
}
