<?php

namespace App\Services\Tefa;

use App\Models\Produk;
use App\Models\StokKeluar;
use App\Models\StokMasuk;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class StokService
{
    public function tambahStok(array $data, int $userId)
    {
        return DB::transaction(function () use ($data, $userId) {
            $produk = Produk::findOrFail($data['produk_id']);
            $todayStr = Carbon::now()->format('Ymd');
            do {
                $kode = 'SM-' . $todayStr . '-' . strtoupper(substr(uniqid(), -4));
            } while (StokMasuk::where('kode_transaksi', $kode)->exists());

            $stokMasuk = StokMasuk::create([
                'produk_id' => $produk->id,
                'kode_transaksi' => $kode,
                'tanggal' => $data['tanggal'] ?? Carbon::now()->toDateString(),
                'jumlah' => $data['jumlah'],
                'sumber' => $data['sumber'] ?? 'produksi',
                'keterangan' => $data['keterangan'] ?? null,
                'dokumen' => $data['dokumen'] ?? null,
                'created_by' => $userId,
            ]);

            $produk->increment('stok', $data['jumlah']);

            return $stokMasuk->load(['produk', 'creator']);
        });
    }

    public function kurangiStok(array $data, int $userId)
    {
        return DB::transaction(function () use ($data, $userId) {
            $produk = Produk::findOrFail($data['produk_id']);
            if ($produk->stok < $data['jumlah']) {
                throw new Exception("Stok produk tidak mencukupi (Tersedia: {$produk->stok})");
            }

            $todayStr = Carbon::now()->format('Ymd');
            do {
                $kode = 'SK-' . $todayStr . '-' . strtoupper(substr(uniqid(), -4));
            } while (StokKeluar::where('kode_transaksi', $kode)->exists());

            $stokKeluar = StokKeluar::create([
                'produk_id' => $produk->id,
                'kode_transaksi' => $kode,
                'tanggal' => $data['tanggal'] ?? Carbon::now()->toDateString(),
                'jumlah' => $data['jumlah'],
                'tujuan' => $data['tujuan'] ?? 'penggunaan',
                'keterangan' => $data['keterangan'] ?? null,
                'created_by' => $userId,
            ]);

            $produk->decrement('stok', $data['jumlah']);

            return $stokKeluar->load(['produk', 'creator']);
        });
    }

    public function rollbackStokMasuk(int $id)
    {
        return DB::transaction(function () use ($id) {
            $stokMasuk = StokMasuk::findOrFail($id);
            $produk = Produk::findOrFail($stokMasuk->produk_id);

            if ($produk->stok < $stokMasuk->jumlah) {
                throw new Exception("Tidak dapat rollback, stok produk tersisa {$produk->stok}");
            }

            $produk->decrement('stok', $stokMasuk->jumlah);
            $stokMasuk->delete();

            return true;
        });
    }

    public function rollbackStokKeluar(int $id)
    {
        return DB::transaction(function () use ($id) {
            $stokKeluar = StokKeluar::findOrFail($id);
            $produk = Produk::findOrFail($stokKeluar->produk_id);

            $produk->increment('stok', $stokKeluar->jumlah);
            $stokKeluar->delete();

            return true;
        });
    }

    public function getKartuStok(int $produkId)
    {
        $produk = Produk::with('kategori')->findOrFail($produkId);

        $masuk = StokMasuk::where('produk_id', $produkId)
            ->select('tanggal', 'kode_transaksi as kode', 'jumlah as masuk', DB::raw('0 as keluar'), 'sumber as keterangan', 'created_at')
            ->get();

        $keluar = StokKeluar::where('produk_id', $produkId)
            ->select('tanggal', 'kode_transaksi as kode', DB::raw('0 as masuk'), 'jumlah as keluar', 'tujuan as keterangan', 'created_at')
            ->get();

        $merged = $masuk->concat($keluar)->sortBy('created_at');

        $runningSaldo = 0;
        $kartuStok = [];

        foreach ($merged as $item) {
            $runningSaldo += ($item->masuk - $item->keluar);
            $kartuStok[] = [
                'tanggal' => formatTanggal($item->tanggal),
                'kode' => $item->kode,
                'masuk' => $item->masuk,
                'keluar' => $item->keluar,
                'saldo' => $runningSaldo,
                'keterangan' => ucfirst($item->keterangan),
            ];
        }

        return [
            'produk' => $produk,
            'riwayat' => array_reverse($kartuStok),
        ];
    }

    public function checkStokMinimum()
    {
        return Produk::where('status', 'aktif')
            ->whereColumn('stok', '<=', 'stok_minimum')
            ->with('kategori')
            ->get();
    }
}
