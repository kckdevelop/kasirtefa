<?php

use App\Http\Controllers\Api\V1\Tefa\LaporanPenjualanController;
use App\Http\Controllers\Web\AlatWebController;
use App\Http\Controllers\Web\AuthWebController;
use App\Http\Controllers\Web\DashboardWebController;
use App\Http\Controllers\Web\KasirWebController;
use App\Http\Controllers\Web\LaporanWebController;
use App\Http\Controllers\Web\PelangganWebController;
use App\Http\Controllers\Web\PengaturanWebController;
use App\Http\Controllers\Web\TefahWebController;
use App\Http\Controllers\Web\UserWebController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthWebController::class, 'showLogin'])->name('home');

// Auth Routes
Route::get('/login', [AuthWebController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthWebController::class, 'login']);
Route::match(['get', 'post'], '/logout', [AuthWebController::class, 'logout'])->name('logout');

// Protected Web Admin Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardWebController::class, 'index'])->name('dashboard');

    // POS Kasir
    Route::get('/tefa/kasir', [KasirWebController::class, 'index'])->name('tefa.kasir');
    Route::post('/tefa/kasir', [KasirWebController::class, 'store'])->name('tefa.kasir.store');
    Route::get('/tefa/transaksi/{id}/cetak-struk', [LaporanPenjualanController::class, 'struk'])->name('tefa.transaksi.cetak-struk');

    // Management User & Role
    Route::get('/users', [UserWebController::class, 'index'])->name('users.index');
    Route::post('/users', [UserWebController::class, 'store'])->name('users.store');
    Route::put('/users/{id}/toggle-status', [UserWebController::class, 'toggleStatus'])->name('users.toggle-status');

    // TEFa Modules Web Views — connected to TefahWebController
    Route::get('/tefa/kategori', [TefahWebController::class, 'kategoriIndex'])->name('tefa.kategori.index');
    Route::post('/tefa/kategori', [TefahWebController::class, 'kategoriStore'])->name('tefa.kategori.store');
    Route::put('/tefa/kategori/{kategoriProduk}', [TefahWebController::class, 'kategoriUpdate'])->name('tefa.kategori.update');
    Route::delete('/tefa/kategori/{kategoriProduk}', [TefahWebController::class, 'kategoriDestroy'])->name('tefa.kategori.destroy');

    Route::get('/tefa/produk', [TefahWebController::class, 'produkIndex'])->name('tefa.produk.index');
    Route::post('/tefa/produk', [TefahWebController::class, 'produkStore'])->name('tefa.produk.store');
    Route::delete('/tefa/produk/bulk-delete', [TefahWebController::class, 'produkBulkDestroy'])->name('tefa.produk.bulk-destroy');
    Route::put('/tefa/produk/{produk}', [TefahWebController::class, 'produkUpdate'])->name('tefa.produk.update');
    Route::delete('/tefa/produk/{produk}', [TefahWebController::class, 'produkDestroy'])->name('tefa.produk.destroy');

    Route::get('/tefa/stok-masuk', [TefahWebController::class, 'stokMasukIndex'])->name('tefa.stok-masuk');
    Route::post('/tefa/stok-masuk', [TefahWebController::class, 'stokMasukStore'])->name('tefa.stok-masuk.store');

    Route::get('/tefa/stok-keluar', [TefahWebController::class, 'stokKeluarIndex'])->name('tefa.stok-keluar');
    Route::post('/tefa/stok-keluar', [TefahWebController::class, 'stokKeluarStore'])->name('tefa.stok-keluar.store');

    Route::get('/tefa/transaksi', [TefahWebController::class, 'transaksiIndex'])->name('tefa.transaksi.index');
    Route::delete('/tefa/transaksi/bulk-delete', [TefahWebController::class, 'transaksiBulkDestroy'])->name('tefa.transaksi.bulk-destroy');
    Route::get('/tefa/transaksi/{transaksi}', [TefahWebController::class, 'transaksiShow'])->name('tefa.transaksi.show');
    Route::delete('/tefa/transaksi/{transaksi}', [TefahWebController::class, 'transaksiDestroy'])->name('tefa.transaksi.destroy');

    // Data Pelanggan
    Route::get('/tefa/pelanggan', [PelangganWebController::class, 'index'])->name('tefa.pelanggan.index');
    Route::post('/tefa/pelanggan', [PelangganWebController::class, 'store'])->name('tefa.pelanggan.store');
    Route::put('/tefa/pelanggan/{pelanggan}', [PelangganWebController::class, 'update'])->name('tefa.pelanggan.update');
    Route::delete('/tefa/pelanggan/{pelanggan}', [PelangganWebController::class, 'destroy'])->name('tefa.pelanggan.destroy');

    // Lisensi Aplikasi (Beli & Berlangganan)
    Route::get('/tefa/lisensi', [TefahWebController::class, 'lisensiIndex'])->name('tefa.lisensi.index');
    Route::post('/tefa/lisensi', [TefahWebController::class, 'lisensiStore'])->name('tefa.lisensi.store');
    Route::get('/tefa/lisensi/{lisensi}/cetak', [TefahWebController::class, 'lisensiCetak'])->name('tefa.lisensi.cetak');
    Route::post('/tefa/lisensi/{lisensi}/tandai-lunas', [TefahWebController::class, 'lisensiTandaiLunas'])->name('tefa.lisensi.tandai-lunas');
    Route::put('/tefa/lisensi/{lisensi}', [TefahWebController::class, 'lisensiUpdate'])->name('tefa.lisensi.update');
    Route::delete('/tefa/lisensi/{lisensi}', [TefahWebController::class, 'lisensiDestroy'])->name('tefa.lisensi.destroy');

    // Reset Transaksi
    Route::get('/tefa/reset-transaksi', [TefahWebController::class, 'resetTransaksiIndex'])->name('tefa.reset-transaksi.index');
    Route::post('/tefa/reset-transaksi', [TefahWebController::class, 'resetTransaksiStore'])->name('tefa.reset-transaksi.store');

    // Alat Modules Web Views — connected to AlatWebController
    Route::get('/alat/kategori', [AlatWebController::class, 'kategoriIndex'])->name('alat.kategori.index');
    Route::post('/alat/kategori', [AlatWebController::class, 'kategoriStore'])->name('alat.kategori.store');
    Route::put('/alat/kategori/{kategoriAlat}', [AlatWebController::class, 'kategoriUpdate'])->name('alat.kategori.update');

    Route::get('/alat/daftar', [AlatWebController::class, 'index'])->name('alat.daftar.index');
    Route::post('/alat/daftar', [AlatWebController::class, 'store'])->name('alat.daftar.store');
    Route::get('/alat/detail/{alat}', [AlatWebController::class, 'show'])->name('alat.detail');
    Route::put('/alat/daftar/{alat}', [AlatWebController::class, 'update'])->name('alat.daftar.update');
    Route::delete('/alat/daftar/{alat}', [AlatWebController::class, 'destroy'])->name('alat.daftar.destroy');

    Route::get('/alat/peminjaman', [AlatWebController::class, 'peminjamanIndex'])->name('alat.peminjaman.index');
    Route::post('/alat/peminjaman', [AlatWebController::class, 'peminjamanStore'])->name('alat.peminjaman.store');
    Route::get('/alat/peminjaman/{peminjaman}', [AlatWebController::class, 'peminjamanShow'])->name('alat.peminjaman.show');
    Route::put('/alat/peminjaman/{peminjaman}', [AlatWebController::class, 'peminjamanUpdate'])->name('alat.peminjaman.update');
    Route::delete('/alat/peminjaman/{peminjaman}', [AlatWebController::class, 'peminjamanDestroy'])->name('alat.peminjaman.destroy');
    Route::post('/alat/peminjaman/{peminjaman}/approve', [AlatWebController::class, 'peminjamanApprove'])->name('alat.peminjaman.approve');
    Route::post('/alat/peminjaman/{peminjaman}/reject', [AlatWebController::class, 'peminjamanReject'])->name('alat.peminjaman.reject');
    Route::post('/alat/peminjaman/{peminjaman}/kembali', [AlatWebController::class, 'peminjamanProsesPengembalian'])->name('alat.peminjaman.kembali');

    Route::get('/alat/denda', [AlatWebController::class, 'dendaIndex'])->name('alat.denda.index');
    Route::post('/alat/denda/{denda}/bayar', [AlatWebController::class, 'dendaBayar'])->name('alat.denda.bayar');

    // Web Reports Views
    Route::get('/laporan/penjualan', [LaporanWebController::class, 'penjualan'])->name('laporan.penjualan');
    Route::get('/laporan/peminjaman', [LaporanWebController::class, 'peminjaman'])->name('laporan.peminjaman');
    Route::get('/laporan/inventaris', [LaporanWebController::class, 'inventaris'])->name('laporan.inventaris');
    Route::get('/laporan/kondisi-alat', [LaporanWebController::class, 'kondisiAlat'])->name('laporan.kondisi-alat');

    // Pengaturan Web
    Route::get('/pengaturan', [PengaturanWebController::class, 'index'])->name('pengaturan.index');
    Route::post('/pengaturan', [PengaturanWebController::class, 'update'])->name('pengaturan.update');
});
