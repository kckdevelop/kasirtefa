<?php

namespace App\Services\Tefa;

use App\Models\DetailPenjualan;
use App\Models\Notifikasi;
use App\Models\Produk;
use App\Models\StokKeluar;
use App\Models\TransaksiPenjualan;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class PenjualanService
{
    public function generateKodeTransaksi()
    {
        $todayStr = Carbon::now()->format('Ymd');
        $prefix = "TRX-{$todayStr}-";
        
        $lastCount = TransaksiPenjualan::withTrashed()
            ->whereDate('created_at', Carbon::today())
            ->count();
            
        do {
            $lastCount++;
            $sequence = str_pad($lastCount, 4, '0', STR_PAD_LEFT);
            $kode = $prefix . $sequence;
        } while (TransaksiPenjualan::withTrashed()->where('kode_transaksi', $kode)->exists());

        return $kode;
    }

    public function hitungTotal(array $items, float $diskonPersen = 0, float $nominalBayar = 0)
    {
        $subtotal = 0;
        $processedItems = [];

        foreach ($items as $item) {
            $produk = Produk::findOrFail($item['produk_id']);
            if ($produk->stok < $item['jumlah']) {
                throw new Exception("Stok produk '{$produk->nama}' tidak mencukupi (Tersedia: {$produk->stok})");
            }
            $hargaSatuan = $produk->harga_jual;
            $itemSubtotal = $hargaSatuan * $item['jumlah'];
            $subtotal += $itemSubtotal;

            $processedItems[] = [
                'produk' => $produk,
                'harga_satuan' => $hargaSatuan,
                'jumlah' => $item['jumlah'],
                'subtotal' => $itemSubtotal,
                'catatan' => $item['catatan'] ?? null,
            ];
        }

        $diskonNominal = ($subtotal * $diskonPersen) / 100;
        $totalAkhir = max(0, $subtotal - $diskonNominal);
        $nominalKembalian = max(0, $nominalBayar - $totalAkhir);

        return [
            'subtotal' => $subtotal,
            'diskon_persen' => $diskonPersen,
            'diskon_nominal' => $diskonNominal,
            'total_akhir' => $totalAkhir,
            'nominal_kembalian' => $nominalKembalian,
            'items' => $processedItems,
        ];
    }

    public function createTransaksi(array $data, int $userId)
    {
        return DB::transaction(function () use ($data, $userId) {
            $calculated = $this->hitungTotal(
                $data['items'],
                (float)($data['diskon_persen'] ?? 0),
                (float)($data['nominal_bayar'] ?? 0)
            );

            $kode = $this->generateKodeTransaksi();
            $now = Carbon::now();

            $transaksi = TransaksiPenjualan::create([
                'kode_transaksi' => $kode,
                'tanggal' => $now->toDateString(),
                'waktu' => $now->toTimeString(),
                'user_id' => $userId,
                'customer_nama' => $data['customer_nama'] ?? null,
                'customer_telepon' => $data['customer_telepon'] ?? null,
                'customer_alamat' => $data['customer_alamat'] ?? null,
                'subtotal' => $calculated['subtotal'],
                'diskon_persen' => $calculated['diskon_persen'],
                'diskon_nominal' => $calculated['diskon_nominal'],
                'total_akhir' => $calculated['total_akhir'],
                'metode_pembayaran' => $data['metode_pembayaran'] ?? 'tunai',
                'nominal_bayar' => $data['nominal_bayar'] ?? $calculated['total_akhir'],
                'nominal_kembalian' => $calculated['nominal_kembalian'],
                'no_referensi' => $data['no_referensi'] ?? null,
                'status' => 'lunas',
                'catatan' => $data['catatan'] ?? null,
            ]);

            foreach ($calculated['items'] as $index => $item) {
                DetailPenjualan::create([
                    'transaksi_penjualan_id' => $transaksi->id,
                    'produk_id' => $item['produk']->id,
                    'harga_satuan' => $item['harga_satuan'],
                    'jumlah' => $item['jumlah'],
                    'subtotal' => $item['subtotal'],
                    'catatan' => $item['catatan'],
                ]);

                // Kurangi stok produk
                $item['produk']->decrement('stok', $item['jumlah']);

                // Generate unique transaction code for stok keluar per item
                $baseKodeStok = 'SK-' . substr($kode, 4);
                $kodeStok = $baseKodeStok . '-' . sprintf('%02d', $index + 1);

                $suffix = 1;
                while (StokKeluar::where('kode_transaksi', $kodeStok)->exists()) {
                    $kodeStok = $baseKodeStok . '-' . sprintf('%02d', $index + 1) . '-' . $suffix;
                    $suffix++;
                }

                // Catat stok keluar
                StokKeluar::create([
                    'produk_id' => $item['produk']->id,
                    'kode_transaksi' => $kodeStok,
                    'tanggal' => $now->toDateString(),
                    'jumlah' => $item['jumlah'],
                    'tujuan' => 'penjualan',
                    'keterangan' => "Penjualan TRX: {$kode}",
                    'created_by' => $userId,
                ]);

                // Cek alert stok minimum
                if ($item['produk']->stok <= $item['produk']->stok_minimum) {
                    $admins = User::whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'super_admin', 'admin_tefa']))->get();
                    foreach ($admins as $admin) {
                        Notifikasi::create([
                            'user_id' => $admin->id,
                            'judul' => 'Stok Produk Menipis',
                            'pesan' => "Stok produk '{$item['produk']->nama}' tersisa {$item['produk']->stok} {$item['produk']->satuan} (Minimum: {$item['produk']->stok_minimum}).",
                            'tipe' => 'peringatan',
                            'kategori' => 'tefa',
                            'url' => '/tefa/produk/' . $item['produk']->id,
                        ]);
                    }
                }
            }

            return $transaksi->load(['items.produk', 'kasir']);
        });
    }

    public function prosesPenjualan(array $data, int $userId)
    {
        return $this->createTransaksi($data, $userId);
    }

    public function batalkanTransaksi(int $id, int $userId)
    {
        return DB::transaction(function () use ($id, $userId) {
            $transaksi = TransaksiPenjualan::with('items.produk')->findOrFail($id);

            if ($transaksi->status === 'batal') {
                throw new Exception('Transaksi sudah dibatalkan sebelumnya');
            }

            foreach ($transaksi->items as $item) {
                // Rollback stok
                $item->produk->increment('stok', $item->jumlah);
            }

            $transaksi->update(['status' => 'batal']);

            return $transaksi;
        });
    }
}
