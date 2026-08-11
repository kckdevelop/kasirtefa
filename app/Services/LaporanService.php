<?php

namespace App\Services;

use App\Models\Alat;
use App\Models\DendaPeminjaman;
use App\Models\PeminjamanAlat;
use App\Models\Produk;
use App\Models\TransaksiPenjualan;
use Illuminate\Support\Facades\DB;

class LaporanService
{
    public function laporanPenjualan(array $filters = [])
    {
        $query = TransaksiPenjualan::with(['items.produk.kategori', 'kasir'])
            ->where('status', 'lunas');

        if (!empty($filters['tanggal_mulai'])) {
            $query->whereDate('tanggal', '>=', $filters['tanggal_mulai']);
        }
        if (!empty($filters['tanggal_selesai'])) {
            $query->whereDate('tanggal', '<=', $filters['tanggal_selesai']);
        }
        if (!empty($filters['metode_pembayaran'])) {
            $query->where('metode_pembayaran', $filters['metode_pembayaran']);
        }
        if (!empty($filters['kategori_id'])) {
            $query->whereHas('items.produk', function ($q) use ($filters) {
                $q->where('kategori_produk_id', $filters['kategori_id']);
            });
        }
        if (!empty($filters['produk_id'])) {
            $query->whereHas('items', function ($q) use ($filters) {
                $q->where('produk_id', $filters['produk_id']);
            });
        }

        $transaksiList = $query->orderBy('tanggal', 'desc')->orderBy('waktu', 'desc')->get();

        $totalTransaksi = $transaksiList->count();
        $totalOmzet = $transaksiList->sum('total_akhir');
        $totalDiskon = $transaksiList->sum('diskon_nominal');
        $rataRata = $totalTransaksi > 0 ? $totalOmzet / $totalTransaksi : 0;

        // Calculate gross profit
        $totalLabaKotor = 0;
        foreach ($transaksiList as $trx) {
            foreach ($trx->items as $item) {
                $modal = $item->produk->harga_modal ?? 0;
                $totalLabaKotor += ($item->harga_satuan - $modal) * $item->jumlah;
            }
            $totalLabaKotor -= $trx->diskon_nominal;
        }

        return [
            'data' => $transaksiList,
            'ringkasan' => [
                'total_transaksi' => $totalTransaksi,
                'total_omzet' => $totalOmzet,
                'total_diskon' => $totalDiskon,
                'total_laba_kotor' => max(0, $totalLabaKotor),
                'rata_rata_transaksi' => $rataRata,
            ]
        ];
    }

    public function laporanPeminjaman(array $filters = [])
    {
        $query = PeminjamanAlat::with(['items.alat.kategori', 'peminjam', 'approver']);

        if (!empty($filters['tanggal_mulai'])) {
            $query->whereDate('tanggal_pinjam', '>=', $filters['tanggal_mulai']);
        }
        if (!empty($filters['tanggal_selesai'])) {
            $query->whereDate('tanggal_pinjam', '<=', $filters['tanggal_selesai']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['peminjam_id'])) {
            $query->where('peminjam_id', $filters['peminjam_id']);
        }

        $data = $query->orderBy('tanggal_pinjam', 'desc')->get();

        return [
            'data' => $data,
            'ringkasan' => [
                'total_peminjaman' => $data->count(),
                'menunggu' => $data->where('status', 'menunggu_persetujuan')->count(),
                'disetujui' => $data->where('status', 'disetujui')->count(),
                'dipinjam' => $data->where('status', 'dipinjam')->count(),
                'dikembalikan' => $data->where('status', 'dikembalikan')->count(),
                'terlambat' => $data->where('status', 'terlambat')->count(),
                'ditolak' => $data->where('status', 'ditolak')->count(),
            ]
        ];
    }

    public function laporanInventaris(array $filters = [])
    {
        $query = Alat::with('kategori');

        if (!empty($filters['kategori_id'])) {
            $query->where('kategori_alat_id', $filters['kategori_id']);
        }
        if (!empty($filters['kondisi'])) {
            $query->where('kondisi', $filters['kondisi']);
        }
        if (!empty($filters['status_ketersediaan'])) {
            $query->where('status_ketersediaan', $filters['status_ketersediaan']);
        }

        $alatList = $query->orderBy('nama')->get();

        $totalBaik = $alatList->sum(fn($a) => ($a->jumlah_baik ?? 0) + ($a->jumlah_cukup ?? 0));
        $totalRusak = $alatList->sum(fn($a) => ($a->jumlah_rusak_ringan ?? 0) + ($a->jumlah_rusak_berat ?? 0));
        $totalHilang = $alatList->sum(fn($a) => $a->jumlah_hilang ?? 0);

        return [
            'data' => $alatList,
            'ringkasan' => [
                'total_jenis_alat' => $alatList->count(),
                'total_unit' => $alatList->sum('jumlah_total'),
                'total_unit_tersedia' => $alatList->sum('jumlah_tersedia'),
                'total_unit_dipinjam' => max(0, $alatList->sum('jumlah_total') - $alatList->sum('jumlah_tersedia') - $totalRusak - $totalHilang),
                'total_baik' => $totalBaik,
                'total_rusak' => $totalRusak,
                'total_hilang' => $totalHilang,
                'total_aset_nilai' => $alatList->sum(fn($a) => ($a->harga_perolehan ?? 0) * $a->jumlah_total),
            ]
        ];
    }

    public function laporanKondisiAlat()
    {
        $totalBaik = Alat::sum(DB::raw('COALESCE(jumlah_baik, 0) + COALESCE(jumlah_cukup, 0)'));
        $totalRusak = Alat::sum(DB::raw('COALESCE(jumlah_rusak_ringan, 0) + COALESCE(jumlah_rusak_berat, 0)'));
        $totalHilang = Alat::sum('jumlah_hilang');

        $detailAlat = Alat::with('kategori')
            ->where(function($q) {
                $q->where('jumlah_rusak_ringan', '>', 0)
                  ->orWhere('jumlah_rusak_berat', '>', 0)
                  ->orWhere('jumlah_hilang', '>', 0);
            })
            ->get();

        return [
            'rekap' => [
                'baik' => $totalBaik,
                'rusak' => $totalRusak,
                'hilang' => $totalHilang,
                'rusak_ringan' => Alat::sum('jumlah_rusak_ringan'),
                'rusak_berat' => Alat::sum('jumlah_rusak_berat'),
            ],
            'detail_perlu_perhatian' => $detailAlat,
        ];
    }

    public function laporanDenda(array $filters = [])
    {
        $query = DendaPeminjaman::with(['peminjaman.peminjam', 'detailPeminjaman.alat']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $data = $query->orderBy('created_at', 'desc')->get();

        return [
            'data' => $data,
            'ringkasan' => [
                'total_kasus_denda' => $data->count(),
                'total_nominal_denda' => $data->sum('total_denda'),
                'total_sudah_bayar' => $data->where('status', 'sudah_bayar')->sum('total_denda'),
                'total_belum_bayar' => $data->where('status', 'belum_bayar')->sum('total_denda'),
                'total_dibebaskan' => $data->where('status', 'dibebaskan')->sum('total_denda'),
            ]
        ];
    }
}
