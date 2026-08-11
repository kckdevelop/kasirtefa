<?php

use App\Http\Controllers\Api\V1\Alat\AlatController;
use App\Http\Controllers\Api\V1\Alat\DendaAlatController;
use App\Http\Controllers\Api\V1\Alat\DokumentasiAlatController;
use App\Http\Controllers\Api\V1\Alat\KategoriAlatController;
use App\Http\Controllers\Api\V1\Alat\LaporanPeminjamanController;
use App\Http\Controllers\Api\V1\Alat\PeminjamanAlatController;
use App\Http\Controllers\Api\V1\Alat\PerawatanAlatController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\NotifikasiController;
use App\Http\Controllers\Api\V1\PengaturanController;
use App\Http\Controllers\Api\V1\ProfilController;
use App\Http\Controllers\Api\V1\Tefa\KategoriProdukController;
use App\Http\Controllers\Api\V1\Tefa\LaporanPenjualanController;
use App\Http\Controllers\Api\V1\Tefa\ProdukController;
use App\Http\Controllers\Api\V1\Tefa\StokController;
use App\Http\Controllers\Api\V1\Tefa\TransaksiPenjualanController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Public Auth
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Authenticated Routes
    Route::middleware('auth:sanctum')->group(function () {

        // Auth & Profile
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::put('/auth/me', [ProfilController::class, 'update']);
        Route::put('/auth/me/password', [ProfilController::class, 'updatePassword']);

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/dashboard/chart/penjualan', [DashboardController::class, 'chartPenjualan']);
        Route::get('/dashboard/chart/peminjaman', [DashboardController::class, 'chartPeminjaman']);
        Route::get('/dashboard/alert-stok', [DashboardController::class, 'alertStok']);
        Route::get('/dashboard/alert-peminjaman', [DashboardController::class, 'alertPeminjaman']);
        Route::get('/dashboard/recent-transactions', [DashboardController::class, 'recentTransactions']);
        Route::get('/dashboard/recent-peminjaman', [DashboardController::class, 'recentPeminjaman']);

        // Notifikasi
        Route::get('/notifikasi', [NotifikasiController::class, 'index']);
        Route::put('/notifikasi/{id}/read', [NotifikasiController::class, 'read']);
        Route::put('/notifikasi/read-all', [NotifikasiController::class, 'readAll']);
        Route::get('/notifikasi/unread-count', [NotifikasiController::class, 'unreadCount']);

        // TEFa Module
        Route::prefix('tefa')->group(function () {
            // Dropdown & Public List within TEFa
            Route::get('/kategori/select2', [KategoriProdukController::class, 'select2']);
            Route::get('/kategori/{id}/produk', [KategoriProdukController::class, 'produk']);
            Route::apiResource('/kategori', KategoriProdukController::class);

            Route::get('/produk/select2', [ProdukController::class, 'select2']);
            Route::get('/produk/{id}/stok-history', [ProdukController::class, 'stokHistory']);
            Route::get('/produk/{id}/transaksi', [ProdukController::class, 'transaksiHistory']);
            Route::apiResource('/produk', ProdukController::class);

            // Stok Management
            Route::get('/stok/ringkasan', [StokController::class, 'ringkasan']);
            Route::get('/stok/kartu-stok/{produk_id}', [StokController::class, 'kartuStok']);
            Route::get('/stok/masuk', [StokController::class, 'listMasuk']);
            Route::post('/stok/masuk', [StokController::class, 'storeMasuk']);
            Route::get('/stok/masuk/{id}', [StokController::class, 'showMasuk']);
            Route::delete('/stok/masuk/{id}', [StokController::class, 'destroyMasuk']);

            Route::get('/stok/keluar', [StokController::class, 'listKeluar']);
            Route::post('/stok/keluar', [StokController::class, 'storeKeluar']);
            Route::get('/stok/keluar/{id}', [StokController::class, 'showKeluar']);
            Route::delete('/stok/keluar/{id}', [StokController::class, 'destroyKeluar']);

            // Transaksi Penjualan
            Route::get('/transaksi/kode/{kode}', [TransaksiPenjualanController::class, 'findByKode']);
            Route::put('/transaksi/{id}/batal', [TransaksiPenjualanController::class, 'batal']);
            Route::apiResource('/transaksi', TransaksiPenjualanController::class)->except(['update', 'destroy']);

            // Laporan Penjualan
            Route::get('/laporan/penjualan', [LaporanPenjualanController::class, 'index']);
            Route::get('/laporan/penjualan/ringkasan', [LaporanPenjualanController::class, 'ringkasan']);
            Route::get('/laporan/penjualan/per-produk', [LaporanPenjualanController::class, 'perProduk']);
            Route::get('/laporan/penjualan/per-kategori', [LaporanPenjualanController::class, 'perKategori']);
            Route::get('/laporan/penjualan/export/excel', [LaporanPenjualanController::class, 'exportExcel']);
            Route::get('/laporan/penjualan/export/pdf', [LaporanPenjualanController::class, 'exportPdf']);
            Route::get('/laporan/penjualan/{id}/struk', [LaporanPenjualanController::class, 'struk']);
        });

        // Alat Module
        Route::prefix('alat')->group(function () {
            Route::get('/kategori/select2', [KategoriAlatController::class, 'select2']);
            Route::get('/kategori/{id}/alat', [KategoriAlatController::class, 'alat']);
            Route::apiResource('/kategori', KategoriAlatController::class);

            Route::get('/select2', [AlatController::class, 'select2']);
            Route::get('/kode/{kode}', [AlatController::class, 'findByKode']);
            Route::get('/{id}/riwayat-kondisi', [AlatController::class, 'riwayatKondisi']);
            Route::get('/{id}/riwayat-perawatan', [AlatController::class, 'riwayatPerawatan']);
            Route::get('/{id}/riwayat-peminjaman', [AlatController::class, 'riwayatPeminjaman']);
            Route::apiResource('/', AlatController::class)->parameters(['' => 'alat']);

            // Dokumentasi Alat
            Route::get('/{alat_id}/dokumentasi', [DokumentasiAlatController::class, 'index']);
            Route::post('/{alat_id}/dokumentasi', [DokumentasiAlatController::class, 'store']);
            Route::get('/dokumentasi/{id}', [DokumentasiAlatController::class, 'show']);
            Route::put('/dokumentasi/{id}', [DokumentasiAlatController::class, 'update']);
            Route::delete('/dokumentasi/{id}', [DokumentasiAlatController::class, 'destroy']);
            Route::post('/dokumentasi/reorder', [DokumentasiAlatController::class, 'reorder']);

            // Perawatan Alat
            Route::get('/{alat_id}/perawatan', [PerawatanAlatController::class, 'index']);
            Route::post('/{alat_id}/perawatan', [PerawatanAlatController::class, 'store']);
            Route::get('/perawatan/{id}', [PerawatanAlatController::class, 'show']);
            Route::put('/perawatan/{id}', [PerawatanAlatController::class, 'update']);
            Route::delete('/perawatan/{id}', [PerawatanAlatController::class, 'destroy']);

            // Peminjaman Alat
            Route::get('/peminjaman/saya', [PeminjamanAlatController::class, 'saya']);
            Route::get('/peminjaman/kode/{kode}', [PeminjamanAlatController::class, 'findByKode']);
            Route::put('/peminjaman/{id}/approve', [PeminjamanAlatController::class, 'approve']);
            Route::put('/peminjaman/{id}/reject', [PeminjamanAlatController::class, 'reject']);
            Route::put('/peminjaman/{id}/proses', [PeminjamanAlatController::class, 'proses']);
            Route::put('/peminjaman/{id}/kembalikan', [PeminjamanAlatController::class, 'kembalikan']);
            Route::apiResource('/peminjaman', PeminjamanAlatController::class)->except(['update', 'destroy']);

            // Denda
            Route::get('/denda', [DendaAlatController::class, 'index']);
            Route::put('/denda/{id}/bayar', [DendaAlatController::class, 'bayar']);
            Route::put('/denda/{id}/bebaskan', [DendaAlatController::class, 'bebaskan']);

            // Laporan Alat
            Route::get('/laporan/peminjaman', [LaporanPeminjamanController::class, 'index']);
            Route::get('/laporan/peminjaman/ringkasan', [LaporanPeminjamanController::class, 'ringkasan']);
            Route::get('/laporan/peminjaman/per-alat', [LaporanPeminjamanController::class, 'perAlat']);
            Route::get('/laporan/peminjaman/per-peminjam', [LaporanPeminjamanController::class, 'perPeminjam']);
            Route::get('/laporan/peminjaman/export/excel', [LaporanPeminjamanController::class, 'exportExcel']);
            Route::get('/laporan/peminjaman/export/pdf', [LaporanPeminjamanController::class, 'exportPdf']);
            Route::get('/laporan/denda', [LaporanPeminjamanController::class, 'denda']);
            Route::get('/laporan/kondisi-alat', [LaporanPeminjamanController::class, 'kondisiAlat']);
            Route::get('/laporan/kondisi-alat/export/pdf', [LaporanPeminjamanController::class, 'kondisiAlatPdf']);
            Route::get('/laporan/inventaris', [LaporanPeminjamanController::class, 'inventaris']);
            Route::get('/laporan/inventaris/export/excel', [LaporanPeminjamanController::class, 'inventarisExcel']);
            Route::get('/laporan/inventaris/export/pdf', [LaporanPeminjamanController::class, 'inventarisPdf']);
        });

        // Pengaturan
        Route::get('/pengaturan', [PengaturanController::class, 'index']);
        Route::put('/pengaturan', [PengaturanController::class, 'updateBatch']);
        Route::get('/pengaturan/{kunci}', [PengaturanController::class, 'getByKey']);
        Route::put('/pengaturan/{kunci}', [PengaturanController::class, 'updateByKey']);
    });
});
