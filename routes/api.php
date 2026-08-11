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

Route::prefix('v1')->name('api.v1.')->group(function () {

    // Public Auth
    Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');

    // Authenticated Routes
    Route::middleware('auth:sanctum')->group(function () {

        // Auth & Profile
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/auth/me', [AuthController::class, 'me'])->name('auth.me');
        Route::put('/auth/me', [ProfilController::class, 'update'])->name('profil.update');
        Route::put('/auth/me/password', [ProfilController::class, 'updatePassword'])->name('profil.password');

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/chart/penjualan', [DashboardController::class, 'chartPenjualan'])->name('dashboard.chart-penjualan');
        Route::get('/dashboard/chart/peminjaman', [DashboardController::class, 'chartPeminjaman'])->name('dashboard.chart-peminjaman');
        Route::get('/dashboard/alert-stok', [DashboardController::class, 'alertStok'])->name('dashboard.alert-stok');
        Route::get('/dashboard/alert-peminjaman', [DashboardController::class, 'alertPeminjaman'])->name('dashboard.alert-peminjaman');
        Route::get('/dashboard/recent-transactions', [DashboardController::class, 'recentTransactions'])->name('dashboard.recent-transactions');
        Route::get('/dashboard/recent-peminjaman', [DashboardController::class, 'recentPeminjaman'])->name('dashboard.recent-peminjaman');

        // Notifikasi
        Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
        Route::put('/notifikasi/{id}/read', [NotifikasiController::class, 'read'])->name('notifikasi.read');
        Route::put('/notifikasi/read-all', [NotifikasiController::class, 'readAll'])->name('notifikasi.read-all');
        Route::get('/notifikasi/unread-count', [NotifikasiController::class, 'unreadCount'])->name('notifikasi.unread-count');

        // TEFa Module
        Route::prefix('tefa')->group(function () {
            // Kategori Produk
            Route::get('/kategori/select2', [KategoriProdukController::class, 'select2'])->name('tefa.kategori.select2');
            Route::get('/kategori/{id}/produk', [KategoriProdukController::class, 'produk'])->name('tefa.kategori.produk');
            Route::apiResource('/kategori', KategoriProdukController::class)->names([
                'index'   => 'tefa.kategori.index',
                'store'   => 'tefa.kategori.store',
                'show'    => 'tefa.kategori.show',
                'update'  => 'tefa.kategori.update',
                'destroy' => 'tefa.kategori.destroy',
            ]);

            // Produk
            Route::get('/produk/select2', [ProdukController::class, 'select2'])->name('tefa.produk.select2');
            Route::get('/produk/{id}/stok-history', [ProdukController::class, 'stokHistory'])->name('tefa.produk.stok-history');
            Route::get('/produk/{id}/transaksi', [ProdukController::class, 'transaksiHistory'])->name('tefa.produk.transaksi-history');
            Route::apiResource('/produk', ProdukController::class)->names([
                'index'   => 'tefa.produk.index',
                'store'   => 'tefa.produk.store',
                'show'    => 'tefa.produk.show',
                'update'  => 'tefa.produk.update',
                'destroy' => 'tefa.produk.destroy',
            ]);

            // Stok Management
            Route::get('/stok/ringkasan', [StokController::class, 'ringkasan'])->name('tefa.stok.ringkasan');
            Route::get('/stok/kartu-stok/{produk_id}', [StokController::class, 'kartuStok'])->name('tefa.stok.kartu-stok');
            Route::get('/stok/masuk', [StokController::class, 'listMasuk'])->name('tefa.stok.masuk.index');
            Route::post('/stok/masuk', [StokController::class, 'storeMasuk'])->name('tefa.stok.masuk.store');
            Route::get('/stok/masuk/{id}', [StokController::class, 'showMasuk'])->name('tefa.stok.masuk.show');
            Route::delete('/stok/masuk/{id}', [StokController::class, 'destroyMasuk'])->name('tefa.stok.masuk.destroy');
            Route::get('/stok/keluar', [StokController::class, 'listKeluar'])->name('tefa.stok.keluar.index');
            Route::post('/stok/keluar', [StokController::class, 'storeKeluar'])->name('tefa.stok.keluar.store');
            Route::get('/stok/keluar/{id}', [StokController::class, 'showKeluar'])->name('tefa.stok.keluar.show');
            Route::delete('/stok/keluar/{id}', [StokController::class, 'destroyKeluar'])->name('tefa.stok.keluar.destroy');

            // Transaksi Penjualan
            Route::get('/transaksi/kode/{kode}', [TransaksiPenjualanController::class, 'findByKode'])->name('tefa.transaksi.by-kode');
            Route::put('/transaksi/{id}/batal', [TransaksiPenjualanController::class, 'batal'])->name('tefa.transaksi.batal');
            Route::apiResource('/transaksi', TransaksiPenjualanController::class)->except(['update', 'destroy'])->names([
                'index' => 'tefa.transaksi.index',
                'store' => 'tefa.transaksi.store',
                'show'  => 'tefa.transaksi.show',
            ]);

            // Laporan Penjualan
            Route::get('/laporan/penjualan', [LaporanPenjualanController::class, 'index'])->name('tefa.laporan.penjualan.index');
            Route::get('/laporan/penjualan/ringkasan', [LaporanPenjualanController::class, 'ringkasan'])->name('tefa.laporan.penjualan.ringkasan');
            Route::get('/laporan/penjualan/per-produk', [LaporanPenjualanController::class, 'perProduk'])->name('tefa.laporan.penjualan.per-produk');
            Route::get('/laporan/penjualan/per-kategori', [LaporanPenjualanController::class, 'perKategori'])->name('tefa.laporan.penjualan.per-kategori');
            Route::get('/laporan/penjualan/export/excel', [LaporanPenjualanController::class, 'exportExcel'])->name('tefa.laporan.penjualan.export-excel');
            Route::get('/laporan/penjualan/export/pdf', [LaporanPenjualanController::class, 'exportPdf'])->name('tefa.laporan.penjualan.export-pdf');
            Route::get('/laporan/penjualan/{id}/struk', [LaporanPenjualanController::class, 'struk'])->name('tefa.laporan.penjualan.struk');
        });

        // Alat Module
        Route::prefix('alat')->group(function () {
            // Kategori Alat
            Route::get('/kategori/select2', [KategoriAlatController::class, 'select2'])->name('alat.api.kategori.select2');
            Route::get('/kategori/{id}/alat', [KategoriAlatController::class, 'alat'])->name('alat.api.kategori.alat');
            Route::apiResource('/kategori', KategoriAlatController::class)->names([
                'index'   => 'alat.api.kategori.index',
                'store'   => 'alat.api.kategori.store',
                'show'    => 'alat.api.kategori.show',
                'update'  => 'alat.api.kategori.update',
                'destroy' => 'alat.api.kategori.destroy',
            ]);

            // Alat
            Route::get('/select2', [AlatController::class, 'select2'])->name('alat.api.select2');
            Route::get('/kode/{kode}', [AlatController::class, 'findByKode'])->name('alat.api.by-kode');
            Route::get('/{id}/riwayat-kondisi', [AlatController::class, 'riwayatKondisi'])->name('alat.api.riwayat-kondisi');
            Route::get('/{id}/riwayat-perawatan', [AlatController::class, 'riwayatPerawatan'])->name('alat.api.riwayat-perawatan');
            Route::get('/{id}/riwayat-peminjaman', [AlatController::class, 'riwayatPeminjaman'])->name('alat.api.riwayat-peminjaman');
            Route::apiResource('/', AlatController::class)->parameters(['' => 'alat'])->names([
                'index'   => 'alat.api.index',
                'store'   => 'alat.api.store',
                'show'    => 'alat.api.show',
                'update'  => 'alat.api.update',
                'destroy' => 'alat.api.destroy',
            ]);

            // Dokumentasi Alat
            Route::get('/{alat_id}/dokumentasi', [DokumentasiAlatController::class, 'index'])->name('alat.api.dokumentasi.index');
            Route::post('/{alat_id}/dokumentasi', [DokumentasiAlatController::class, 'store'])->name('alat.api.dokumentasi.store');
            Route::get('/dokumentasi/{id}', [DokumentasiAlatController::class, 'show'])->name('alat.api.dokumentasi.show');
            Route::put('/dokumentasi/{id}', [DokumentasiAlatController::class, 'update'])->name('alat.api.dokumentasi.update');
            Route::delete('/dokumentasi/{id}', [DokumentasiAlatController::class, 'destroy'])->name('alat.api.dokumentasi.destroy');
            Route::post('/dokumentasi/reorder', [DokumentasiAlatController::class, 'reorder'])->name('alat.api.dokumentasi.reorder');

            // Perawatan Alat
            Route::get('/{alat_id}/perawatan', [PerawatanAlatController::class, 'index'])->name('alat.api.perawatan.index');
            Route::post('/{alat_id}/perawatan', [PerawatanAlatController::class, 'store'])->name('alat.api.perawatan.store');
            Route::get('/perawatan/{id}', [PerawatanAlatController::class, 'show'])->name('alat.api.perawatan.show');
            Route::put('/perawatan/{id}', [PerawatanAlatController::class, 'update'])->name('alat.api.perawatan.update');
            Route::delete('/perawatan/{id}', [PerawatanAlatController::class, 'destroy'])->name('alat.api.perawatan.destroy');

            // Peminjaman Alat
            Route::get('/peminjaman/saya', [PeminjamanAlatController::class, 'saya'])->name('alat.api.peminjaman.saya');
            Route::get('/peminjaman/kode/{kode}', [PeminjamanAlatController::class, 'findByKode'])->name('alat.api.peminjaman.by-kode');
            Route::put('/peminjaman/{id}/approve', [PeminjamanAlatController::class, 'approve'])->name('alat.api.peminjaman.approve');
            Route::put('/peminjaman/{id}/reject', [PeminjamanAlatController::class, 'reject'])->name('alat.api.peminjaman.reject');
            Route::put('/peminjaman/{id}/proses', [PeminjamanAlatController::class, 'proses'])->name('alat.api.peminjaman.proses');
            Route::put('/peminjaman/{id}/kembalikan', [PeminjamanAlatController::class, 'kembalikan'])->name('alat.api.peminjaman.kembalikan');
            Route::apiResource('/peminjaman', PeminjamanAlatController::class)->except(['update', 'destroy'])->names([
                'index' => 'alat.api.peminjaman.index',
                'store' => 'alat.api.peminjaman.store',
                'show'  => 'alat.api.peminjaman.show',
            ]);

            // Denda
            Route::get('/denda', [DendaAlatController::class, 'index'])->name('alat.api.denda.index');
            Route::put('/denda/{id}/bayar', [DendaAlatController::class, 'bayar'])->name('alat.api.denda.bayar');
            Route::put('/denda/{id}/bebaskan', [DendaAlatController::class, 'bebaskan'])->name('alat.api.denda.bebaskan');

            // Laporan Alat
            Route::get('/laporan/peminjaman', [LaporanPeminjamanController::class, 'index'])->name('alat.api.laporan.peminjaman');
            Route::get('/laporan/peminjaman/ringkasan', [LaporanPeminjamanController::class, 'ringkasan'])->name('alat.api.laporan.peminjaman.ringkasan');
            Route::get('/laporan/peminjaman/per-alat', [LaporanPeminjamanController::class, 'perAlat'])->name('alat.api.laporan.peminjaman.per-alat');
            Route::get('/laporan/peminjaman/per-peminjam', [LaporanPeminjamanController::class, 'perPeminjam'])->name('alat.api.laporan.peminjaman.per-peminjam');
            Route::get('/laporan/peminjaman/export/excel', [LaporanPeminjamanController::class, 'exportExcel'])->name('alat.api.laporan.peminjaman.export-excel');
            Route::get('/laporan/peminjaman/export/pdf', [LaporanPeminjamanController::class, 'exportPdf'])->name('alat.api.laporan.peminjaman.export-pdf');
            Route::get('/laporan/denda', [LaporanPeminjamanController::class, 'denda'])->name('alat.api.laporan.denda');
            Route::get('/laporan/kondisi-alat', [LaporanPeminjamanController::class, 'kondisiAlat'])->name('alat.api.laporan.kondisi-alat');
            Route::get('/laporan/kondisi-alat/export/pdf', [LaporanPeminjamanController::class, 'kondisiAlatPdf'])->name('alat.api.laporan.kondisi-alat-pdf');
            Route::get('/laporan/inventaris', [LaporanPeminjamanController::class, 'inventaris'])->name('alat.api.laporan.inventaris');
            Route::get('/laporan/inventaris/export/excel', [LaporanPeminjamanController::class, 'inventarisExcel'])->name('alat.api.laporan.inventaris-excel');
            Route::get('/laporan/inventaris/export/pdf', [LaporanPeminjamanController::class, 'inventarisPdf'])->name('alat.api.laporan.inventaris-pdf');
        });

        // Pengaturan
        Route::get('/pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.api.index');
        Route::put('/pengaturan', [PengaturanController::class, 'updateBatch'])->name('pengaturan.api.update-batch');
        Route::get('/pengaturan/{kunci}', [PengaturanController::class, 'getByKey'])->name('pengaturan.api.show');
        Route::put('/pengaturan/{kunci}', [PengaturanController::class, 'updateByKey'])->name('pengaturan.api.update');
    });
});
