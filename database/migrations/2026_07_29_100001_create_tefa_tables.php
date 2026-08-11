<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 4. kategori_produk
        Schema::create('kategori_produk', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->string('slug')->unique();
            $table->text('deskripsi')->nullable();
            $table->string('ikon')->nullable();
            $table->integer('urutan')->default(0);
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
            $table->softDeletes();
        });

        // 5. produk
        Schema::create('produk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_produk_id')->constrained('kategori_produk')->onDelete('cascade');
            $table->string('kode_produk', 50)->unique();
            $table->string('nama', 200);
            $table->string('slug')->unique();
            $table->text('deskripsi')->nullable();
            $table->string('foto')->nullable();
            $table->decimal('harga_jual', 15, 2);
            $table->decimal('harga_modal', 15, 2)->nullable();
            $table->string('satuan', 50);
            $table->integer('stok')->default(0);
            $table->integer('stok_minimum')->default(5);
            $table->decimal('berat', 10, 2)->nullable();
            $table->boolean('is_ready')->default(true);
            $table->json('spesifikasi')->nullable();
            $table->text('catatan')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['kategori_produk_id', 'kode_produk', 'slug', 'is_ready', 'stok']);
        });

        // 6. stok_masuk
        Schema::create('stok_masuk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produk')->onDelete('cascade');
            $table->string('kode_transaksi', 50)->unique();
            $table->date('tanggal');
            $table->integer('jumlah');
            $table->enum('sumber', ['produksi', 'pembelian', 'donasi', 'lainnya']);
            $table->text('keterangan')->nullable();
            $table->string('dokumen')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // 7. stok_keluar
        Schema::create('stok_keluar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produk')->onDelete('cascade');
            $table->string('kode_transaksi', 50)->unique();
            $table->date('tanggal');
            $table->integer('jumlah');
            $table->enum('tujuan', ['penjualan', 'penggunaan', 'rusak', 'kadaluarsa', 'lainnya']);
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // 8. transaksi_penjualan
        Schema::create('transaksi_penjualan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi', 50)->unique();
            $table->date('tanggal');
            $table->time('waktu');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('customer_nama', 200)->nullable();
            $table->string('customer_telepon', 20)->nullable();
            $table->text('customer_alamat')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('diskon_persen', 5, 2)->default(0);
            $table->decimal('diskon_nominal', 15, 2)->default(0);
            $table->decimal('total_akhir', 15, 2)->default(0);
            $table->enum('metode_pembayaran', ['tunai', 'transfer', 'qris']);
            $table->decimal('nominal_bayar', 15, 2)->default(0);
            $table->decimal('nominal_kembalian', 15, 2)->default(0);
            $table->string('no_referensi', 100)->nullable();
            $table->enum('status', ['pending', 'lunas', 'batal'])->default('pending');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['kode_transaksi', 'tanggal', 'user_id', 'status', 'metode_pembayaran']);
        });

        // 9. detail_penjualan
        Schema::create('detail_penjualan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_penjualan_id')->constrained('transaksi_penjualan')->onDelete('cascade');
            $table->foreignId('produk_id')->constrained('produk')->onDelete('cascade');
            $table->decimal('harga_satuan', 15, 2);
            $table->integer('jumlah');
            $table->decimal('subtotal', 15, 2);
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['transaksi_penjualan_id', 'produk_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_penjualan');
        Schema::dropIfExists('transaksi_penjualan');
        Schema::dropIfExists('stok_keluar');
        Schema::dropIfExists('stok_masuk');
        Schema::dropIfExists('produk');
        Schema::dropIfExists('kategori_produk');
    }
};
