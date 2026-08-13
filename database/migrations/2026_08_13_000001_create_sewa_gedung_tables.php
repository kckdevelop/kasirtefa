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
        // 1. gedung_lab
        if (!Schema::hasTable('gedung_lab')) {
            Schema::create('gedung_lab', function (Blueprint $table) {
                $table->id();
                $table->string('kode_gedung', 50)->unique();
                $table->string('nama_gedung', 200);
                $table->string('lokasi', 255)->nullable();
                $table->integer('kapasitas')->default(0);
                $table->decimal('harga_sewa_per_hari', 15, 2)->default(0);
                $table->text('deskripsi')->nullable();
                $table->string('foto')->nullable();
                $table->enum('status', ['tersedia', 'diperbaiki', 'nonaktif'])->default('tersedia');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 2. fasilitas_gedung
        if (!Schema::hasTable('fasilitas_gedung')) {
            Schema::create('fasilitas_gedung', function (Blueprint $table) {
                $table->id();
                $table->foreignId('gedung_lab_id')->constrained('gedung_lab')->onDelete('cascade');
                $table->string('nama_fasilitas', 200);
                $table->string('kode_fasilitas', 50)->nullable();
                $table->integer('jumlah_tersedia')->default(1);
                $table->decimal('harga_per_item', 15, 2)->default(0);
                $table->string('satuan', 50)->default('unit');
                $table->text('keterangan')->nullable();
                $table->enum('status', ['baik', 'perbaikan', 'rusak'])->default('baik');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 3. sewa_gedung
        if (!Schema::hasTable('sewa_gedung')) {
            Schema::create('sewa_gedung', function (Blueprint $table) {
                $table->id();
                $table->string('kode_sewa', 50)->unique();
                $table->foreignId('gedung_lab_id')->constrained('gedung_lab')->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('pelanggan_id')->nullable()->constrained('pelanggan')->onDelete('set null');
                $table->string('nama_penyewa', 200);
                $table->string('telepon_penyewa', 50)->nullable();
                $table->string('instansi_penyewa', 200)->nullable();
                $table->date('tanggal_mulai');
                $table->date('tanggal_selesai');
                $table->integer('lama_sewa')->default(1); // dalam hari
                $table->decimal('harga_sewa_gedung', 15, 2)->default(0);
                $table->decimal('subtotal_gedung', 15, 2)->default(0);
                $table->decimal('subtotal_fasilitas', 15, 2)->default(0);
                $table->decimal('total_biaya', 15, 2)->default(0);
                $table->decimal('jumlah_dibayar', 15, 2)->default(0);
                $table->enum('status_pembayaran', ['belum_bayar', 'dp', 'lunas'])->default('belum_bayar');
                $table->enum('status_sewa', ['booking', 'disetujui', 'berlangsung', 'selesai', 'dibatalkan'])->default('booking');
                $table->text('catatan')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 4. detail_sewa_gedung
        if (!Schema::hasTable('detail_sewa_gedung')) {
            Schema::create('detail_sewa_gedung', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sewa_gedung_id')->constrained('sewa_gedung')->onDelete('cascade');
                $table->foreignId('fasilitas_gedung_id')->nullable()->constrained('fasilitas_gedung')->onDelete('set null');
                $table->string('nama_fasilitas', 200);
                $table->integer('jumlah')->default(1);
                $table->decimal('harga_per_item', 15, 2)->default(0);
                $table->decimal('subtotal', 15, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_sewa_gedung');
        Schema::dropIfExists('sewa_gedung');
        Schema::dropIfExists('fasilitas_gedung');
        Schema::dropIfExists('gedung_lab');
    }
};
