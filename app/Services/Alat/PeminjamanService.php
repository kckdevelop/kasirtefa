<?php

namespace App\Services\Alat;

use App\Models\Alat;
use App\Models\DendaPeminjaman;
use App\Models\DetailPeminjaman;
use App\Models\Notifikasi;
use App\Models\PeminjamanAlat;
use App\Models\PengaturanAplikasi;
use App\Models\RiwayatKondisiAlat;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class PeminjamanService
{
    public function generateKodePeminjaman()
    {
        $todayStr = Carbon::now()->format('Ymd');
        $prefix = "PNM-{$todayStr}-";

        $lastCount = PeminjamanAlat::withTrashed()
            ->whereDate('created_at', Carbon::today())
            ->count();

        $sequence = str_pad($lastCount + 1, 4, '0', STR_PAD_LEFT);
        return $prefix . $sequence;
    }

    public function createPeminjaman(array $data, int $peminjamId)
    {
        return DB::transaction(function () use ($data, $peminjamId) {
            foreach ($data['items'] as $item) {
                $alat = Alat::findOrFail($item['alat_id']);
                if ($alat->status_ketersediaan !== 'tersedia' || $alat->jumlah_tersedia < $item['jumlah']) {
                    throw new Exception("Alat '{$alat->nama}' tidak tersedia cukup (Tersedia: {$alat->jumlah_tersedia})");
                }
            }

            $kode = $this->generateKodePeminjaman();
            $peminjaman = PeminjamanAlat::create([
                'kode_peminjaman' => $kode,
                'pelanggan_id' => $data['pelanggan_id'] ?? null,
                'peminjam_id' => $data['peminjam_id'] ?? $peminjamId,
                'tanggal_pinjam' => $data['tanggal_pinjam'],
                'tanggal_kembali_rencana' => $data['tanggal_kembali_rencana'],
                'keperluan' => $data['keperluan'],
                'tujuan_penggunaan' => $data['tujuan_penggunaan'] ?? null,
                'lokasi_penggunaan' => $data['lokasi_penggunaan'] ?? null,
                'status' => 'menunggu_persetujuan',
                'catatan_peminjam' => $data['catatan_peminjam'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $alat = Alat::findOrFail($item['alat_id']);
                DetailPeminjaman::create([
                    'peminjaman_alat_id' => $peminjaman->id,
                    'alat_id' => $alat->id,
                    'jumlah_pinjam' => $item['jumlah'],
                    'jumlah_dikembalikan' => 0,
                    'kondisi_saat_dipinjam' => $alat->kondisi,
                    'status_item' => 'dipinjam',
                ]);
            }

            // Send notifications to Tool Admins
            $admins = User::whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'super_admin', 'admin_alat']))->get();
            foreach ($admins as $admin) {
                Notifikasi::create([
                    'user_id' => $admin->id,
                    'judul' => 'Pengajuan Peminjaman Alat Baru',
                    'pesan' => "Peminjaman {$kode} diajukan oleh " . User::find($peminjamId)?->nama,
                    'tipe' => 'info',
                    'kategori' => 'alat',
                    'url' => '/alat/peminjaman/' . $peminjaman->id,
                ]);
            }

            return $peminjaman->load(['items.alat', 'peminjam']);
        });
    }

    public function approvePeminjaman(int $id, int $adminId, ?string $catatanAdmin = null)
    {
        return DB::transaction(function () use ($id, $adminId, $catatanAdmin) {
            $peminjaman = PeminjamanAlat::with('items.alat')->findOrFail($id);

            if ($peminjaman->status !== 'menunggu_persetujuan') {
                throw new Exception('Peminjaman tidak dalam status menunggu persetujuan');
            }

            foreach ($peminjaman->items as $item) {
                if ($item->alat->jumlah_tersedia < $item->jumlah_pinjam) {
                    throw new Exception("Stok alat '{$item->alat->nama}' tidak mencukupi untuk disetujui.");
                }
                $item->alat->decrement('jumlah_tersedia', $item->jumlah_pinjam);
                if ($item->alat->jumlah_tersedia == 0) {
                    $item->alat->update(['status_ketersediaan' => 'dipinjam']);
                }
            }

            $peminjaman->update([
                'status' => 'disetujui',
                'approved_by' => $adminId,
                'approved_at' => Carbon::now(),
                'catatan_admin' => $catatanAdmin,
            ]);

            Notifikasi::create([
                'user_id' => $peminjaman->peminjam_id,
                'judul' => 'Peminjaman Disetujui',
                'pesan' => "Pengajuan peminjaman {$peminjaman->kode_peminjaman} telah disetujui. Silakan mengambil alat.",
                'tipe' => 'sukses',
                'kategori' => 'alat',
                'url' => '/alat/peminjaman/' . $peminjaman->id,
            ]);

            return $peminjaman;
        });
    }

    public function rejectPeminjaman(int $id, int $adminId, string $reason)
    {
        return DB::transaction(function () use ($id, $adminId, $reason) {
            $peminjaman = PeminjamanAlat::findOrFail($id);

            if ($peminjaman->status !== 'menunggu_persetujuan') {
                throw new Exception('Peminjaman tidak dalam status menunggu persetujuan');
            }

            $peminjaman->update([
                'status' => 'ditolak',
                'rejected_by' => $adminId,
                'rejected_at' => Carbon::now(),
                'rejection_reason' => $reason,
            ]);

            Notifikasi::create([
                'user_id' => $peminjaman->peminjam_id,
                'judul' => 'Peminjaman Ditolak',
                'pesan' => "Pengajuan peminjaman {$peminjaman->kode_peminjaman} ditolak. Alasan: {$reason}",
                'tipe' => 'kesalahan',
                'kategori' => 'alat',
                'url' => '/alat/peminjaman/' . $peminjaman->id,
            ]);

            return $peminjaman;
        });
    }

    public function prosesPeminjaman(int $id, ?string $diterimaOleh = null)
    {
        $peminjaman = PeminjamanAlat::findOrFail($id);
        if ($peminjaman->status !== 'disetujui') {
            throw new Exception('Peminjaman belum disetujui');
        }

        $peminjaman->update([
            'status' => 'dipinjam',
            'diterima_oleh' => $diterimaOleh ?? $peminjaman->peminjam->nama,
        ]);

        return $peminjaman;
    }

    public function prosesPengembalian(int $id, array $itemsData, ?string $dikembalikanOleh = null, ?string $diterimaPengembalianOleh = null)
    {
        return DB::transaction(function () use ($id, $itemsData, $dikembalikanOleh, $diterimaPengembalianOleh) {
            $peminjaman = PeminjamanAlat::with('items.alat')->findOrFail($id);

            $today = Carbon::now()->startOfDay();
            $rencanaKembali = Carbon::parse($peminjaman->tanggal_kembali_rencana)->startOfDay();
            $hariTerlambat = max(0, (int)$rencanaKembali->diffInDays($today, false));

            $tarifPerHari = (float)PengaturanAplikasi::get('tarif_denda_per_hari', 5000);

            $semuaDikembalikan = true;

            foreach ($itemsData as $itemInput) {
                $detail = DetailPeminjaman::where('peminjaman_alat_id', $peminjaman->id)
                    ->where('id', $itemInput['detail_peminjaman_id'])
                    ->firstOrFail();

                $jumlahKembali = (int)($itemInput['jumlah_dikembalikan'] ?? $detail->jumlah_pinjam);
                $kondisiAkhir = $itemInput['kondisi_saat_dikembalikan'] ?? 'baik';
                $fotoPath = $itemInput['foto_pengembalian'] ?? null;
                $catatanKerusakan = $itemInput['catatan_kerusakan'] ?? null;

                $detail->update([
                    'jumlah_dikembalikan' => $detail->jumlah_dikembalikan + $jumlahKembali,
                    'kondisi_saat_dikembalikan' => $kondisiAkhir,
                    'catatan_kerusakan' => $catatanKerusakan,
                    'foto_pengembalian' => $fotoPath ?? $detail->foto_pengembalian,
                    'status_item' => ($detail->jumlah_dikembalikan + $jumlahKembali >= $detail->jumlah_pinjam) ? 'dikembalikan' : 'dipinjam',
                ]);

                if ($detail->jumlah_dikembalikan < $detail->jumlah_pinjam) {
                    $semuaDikembalikan = false;
                }

                // Restore tool available quantity
                $detail->alat->increment('jumlah_tersedia', $jumlahKembali);
                if ($detail->alat->jumlah_tersedia > 0) {
                    $detail->alat->update(['status_ketersediaan' => 'tersedia']);
                }

                // Check condition change
                if ($kondisiAkhir !== $detail->kondisi_saat_dipinjam) {
                    RiwayatKondisiAlat::create([
                        'alat_id' => $detail->alat_id,
                        'kondisi_sebelum' => $detail->kondisi_saat_dipinjam,
                        'kondisi_sesudah' => $kondisiAkhir,
                        'tanggal_perubahan' => Carbon::now()->toDateString(),
                        'keterangan' => "Pengembalian Peminjaman {$peminjaman->kode_peminjaman}: {$catatanKerusakan}",
                        'dilakukan_oleh' => auth()->id() ?? $peminjaman->peminjam_id,
                    ]);

                    $detail->alat->update(['kondisi' => $kondisiAkhir]);

                    // Generate fine for damage
                    if (in_array($kondisiAkhir, ['rusak_ringan', 'rusak_berat'])) {
                        $estimasi = ($kondisiAkhir === 'rusak_berat') ? ($detail->alat->harga_perolehan ?? 500000) : 100000;
                        DendaPeminjaman::create([
                            'detail_peminjaman_id' => $detail->id,
                            'peminjaman_alat_id' => $peminjaman->id,
                            'jenis' => 'rusak',
                            'jumlah_hari_terlambat' => 0,
                            'tarif_per_hari' => 0,
                            'estimasi_kerugian' => $estimasi,
                            'total_denda' => $estimasi,
                            'status' => 'belum_bayar',
                            'catatan' => "Kerusakan alat '{$detail->alat->nama}' ({$kondisiAkhir}): {$catatanKerusakan}",
                        ]);
                    }
                }
            }

            // Handle late fine
            if ($hariTerlambat > 0) {
                $totalDendaTerlambat = $hariTerlambat * $tarifPerHari;
                DendaPeminjaman::create([
                    'peminjaman_alat_id' => $peminjaman->id,
                    'jenis' => 'terlambat',
                    'jumlah_hari_terlambat' => $hariTerlambat,
                    'tarif_per_hari' => $tarifPerHari,
                    'estimasi_kerugian' => 0,
                    'total_denda' => $totalDendaTerlambat,
                    'status' => 'belum_bayar',
                    'catatan' => "Keterlambatan pengembalian selama {$hariTerlambat} hari",
                ]);
            }

            $peminjaman->update([
                'tanggal_kembali_aktual' => Carbon::now()->toDateString(),
                'status' => $semuaDikembalikan ? 'dikembalikan' : 'dikembalikan_sebagian',
                'dikembalikan_oleh' => $dikembalikanOleh ?? $peminjaman->peminjam->nama,
                'diterima_pengembalian_oleh' => $diterimaPengembalianOleh ?? auth()->user()?->nama,
            ]);

            Notifikasi::create([
                'user_id' => $peminjaman->peminjam_id,
                'judul' => 'Pengembalian Alat Selesai',
                'pesan' => "Proses pengembalian peminjaman {$peminjaman->kode_peminjaman} telah berhasil diproses.",
                'tipe' => 'sukses',
                'kategori' => 'alat',
                'url' => '/alat/peminjaman/' . $peminjaman->id,
            ]);

            return $peminjaman;
        });
    }

    public function checkTerlambat()
    {
        $today = Carbon::now()->startOfDay();
        $overdueLoans = PeminjamanAlat::whereIn('status', ['disetujui', 'dipinjam'])
            ->whereDate('tanggal_kembali_rencana', '<', $today)
            ->get();

        foreach ($overdueLoans as $peminjaman) {
            $peminjaman->update(['status' => 'terlambat']);

            Notifikasi::create([
                'user_id' => $peminjaman->peminjam_id,
                'judul' => 'Peringatan: Peminjaman Terlambat',
                'pesan' => "Peminjaman {$peminjaman->kode_peminjaman} sudah melewati batas waktu pengembalian (" . formatTanggal($peminjaman->tanggal_kembali_rencana) . "). Harap segera mengembalikan alat.",
                'tipe' => 'peringatan',
                'kategori' => 'alat',
                'url' => '/alat/peminjaman/' . $peminjaman->id,
            ]);
        }

        return count($overdueLoans);
    }
}
