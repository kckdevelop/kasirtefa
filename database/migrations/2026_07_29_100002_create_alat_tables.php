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
        // 10. kategori_alat
        if (!Schema::hasTable('kategori_alat')) {
            Schema::create('kategori_alat', function (Blueprint $table) {
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
        }

        // 11. alat
        if (!Schema::hasTable('alat')) {
            Schema::create('alat', function (Blueprint $table) {
                $table->id();
                $table->foreignId('kategori_alat_id')->constrained('kategori_alat')->onDelete('cascade');
                $table->string('kode_alat', 50)->unique();
                $table->string('nama', 200);
                $table->string('slug')->unique();
                $table->string('merek', 100)->nullable();
                $table->string('tipe', 100)->nullable();
                $table->string('serial_number', 100)->nullable();
                $table->year('tahun_perolehan')->nullable();
                $table->enum('kondisi', ['baik', 'cukup', 'rusak_ringan', 'rusak_berat'])->default('baik');
                $table->enum('status_ketersediaan', ['tersedia', 'dipinjam', 'dalam_perbaikan', 'dikeluarkan'])->default('tersedia');
                $table->string('lokasi_penyimpanan', 200)->nullable();
                $table->integer('jumlah_total')->default(1);
                $table->integer('jumlah_tersedia')->default(1);
                $table->string('satuan', 50)->default('unit');
                $table->decimal('harga_perolehan', 15, 2)->nullable();
                $table->enum('sumber_perolehan', ['dinas', 'bos', 'donasi', 'pembelian_sendiri', 'lainnya'])->nullable();
                $table->string('foto')->nullable();
                $table->json('spesifikasi_teknis')->nullable();
                $table->text('cara_penggunaan')->nullable();
                $table->text('peringatan_keamanan')->nullable();
                $table->integer('umur_pakai')->nullable(); // dalam tahun
                $table->date('kalibrasi_terakhir')->nullable();
                $table->date('kalibrasi_berikutnya')->nullable();
                $table->text('catatan')->nullable();
                $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
                $table->softDeletes();

                $table->index(['kategori_alat_id', 'kode_alat', 'slug', 'kondisi', 'status_ketersediaan']);
            });
        }

        // 12. dokumentasi_alat
        if (!Schema::hasTable('dokumentasi_alat')) {
            Schema::create('dokumentasi_alat', function (Blueprint $table) {
                $table->id();
                $table->foreignId('alat_id')->constrained('alat')->onDelete('cascade');
                $table->enum('jenis', ['foto', 'video', 'dokumen', 'manual', 'sertifikat', 'lainnya']);
                $table->string('judul', 200);
                $table->text('deskripsi')->nullable();
                $table->string('file_path');
                $table->string('file_nama_asli');
                $table->integer('file_ukuran')->nullable();
                $table->string('file_tipe')->nullable();
                $table->string('thumbnail')->nullable();
                $table->integer('urutan')->default(0);
                $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
                $table->timestamps();

                $table->index(['alat_id', 'jenis']);
            });
        }

        // 13. riwayat_kondisi_alat
        if (!Schema::hasTable('riwayat_kondisi_alat')) {
            Schema::create('riwayat_kondisi_alat', function (Blueprint $table) {
                $table->id();
                $table->foreignId('alat_id')->constrained('alat')->onDelete('cascade');
                $table->enum('kondisi_sebelum', ['baik', 'cukup', 'rusak_ringan', 'rusak_berat']);
                $table->enum('kondisi_sesudah', ['baik', 'cukup', 'rusak_ringan', 'rusak_berat']);
                $table->date('tanggal_perubahan');
                $table->text('keterangan')->nullable();
                $table->string('bukti_foto')->nullable();
                $table->foreignId('dilakukan_oleh')->constrained('users')->onDelete('cascade');
                $table->timestamps();
            });
        }

        // 14. perawatan_alat
        if (!Schema::hasTable('perawatan_alat')) {
            Schema::create('perawatan_alat', function (Blueprint $table) {
                $table->id();
                $table->foreignId('alat_id')->constrained('alat')->onDelete('cascade');
                $table->string('kode_perawatan', 50)->unique();
                $table->enum('jenis', ['preventif', 'korektif', 'kalibrasi', 'lainnya']);
                $table->date('tanggal_mulai');
                $table->date('tanggal_selesai')->nullable();
                $table->decimal('biaya', 15, 2)->default(0);
                $table->string('pelaksana', 200)->nullable();
                $table->text('deskripsi_pekerjaan');
                $table->text('hasil')->nullable();
                $table->enum('status', ['direncanakan', 'berlangsung', 'selesai', 'batal'])->default('direncanakan');
                $table->string('bukti_foto')->nullable();
                $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
                $table->timestamps();
            });
        }

        // 15. peminjaman_alat
        if (!Schema::hasTable('peminjaman_alat')) {
            Schema::create('peminjaman_alat', function (Blueprint $table) {
                $table->id();
                $table->string('kode_peminjaman', 50)->unique();
                $table->foreignId('peminjam_id')->constrained('users')->onDelete('cascade');
                $table->date('tanggal_pinjam');
                $table->date('tanggal_kembali_rencana');
                $table->date('tanggal_kembali_aktual')->nullable();
                $table->text('keperluan');
                $table->string('tujuan_penggunaan', 200)->nullable();
                $table->string('lokasi_penggunaan', 200)->nullable();
                $table->enum('status', ['menunggu_persetujuan', 'disetujui', 'dipinjam', 'dikembalikan_sebagian', 'dikembalikan', 'ditolak', 'terlambat'])->default('menunggu_persetujuan');
                $table->text('catatan_peminjam')->nullable();
                $table->text('catatan_admin')->nullable();
                $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('approved_at')->nullable();
                $table->foreignId('rejected_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('rejected_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->string('diterima_oleh', 200)->nullable();
                $table->string('dikembalikan_oleh', 200)->nullable();
                $table->string('diterima_pengembalian_oleh', 200)->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->index(['kode_peminjaman', 'peminjam_id', 'status', 'tanggal_pinjam', 'tanggal_kembali_rencana']);
            });
        }

        // 16. detail_peminjaman
        if (!Schema::hasTable('detail_peminjaman')) {
            Schema::create('detail_peminjaman', function (Blueprint $table) {
                $table->id();
                $table->foreignId('peminjaman_alat_id')->constrained('peminjaman_alat')->onDelete('cascade');
                $table->foreignId('alat_id')->constrained('alat')->onDelete('cascade');
                $table->integer('jumlah_pinjam');
                $table->integer('jumlah_dikembalikan')->default(0);
                $table->enum('kondisi_saat_dipinjam', ['baik', 'cukup', 'rusak_ringan', 'rusak_berat'])->default('baik');
                $table->enum('kondisi_saat_dikembalikan', ['baik', 'cukup', 'rusak_ringan', 'rusak_berat'])->nullable();
                $table->text('catatan_kerusakan')->nullable();
                $table->string('foto_pengembalian')->nullable();
                $table->enum('status_item', ['dipinjam', 'dikembalikan', 'rusak', 'hilang'])->default('dipinjam');
                $table->timestamps();

                $table->index(['peminjaman_alat_id', 'alat_id', 'status_item']);
            });
        }

        // 17. denda_peminjaman
        if (!Schema::hasTable('denda_peminjaman')) {
            Schema::create('denda_peminjaman', function (Blueprint $table) {
                $table->id();
                $table->foreignId('detail_peminjaman_id')->nullable()->constrained('detail_peminjaman')->onDelete('cascade');
                $table->foreignId('peminjaman_alat_id')->constrained('peminjaman_alat')->onDelete('cascade');
                $table->enum('jenis', ['terlambat', 'rusak', 'hilang']);
                $table->integer('jumlah_hari_terlambat')->default(0);
                $table->decimal('tarif_per_hari', 15, 2)->default(0);
                $table->decimal('estimasi_kerugian', 15, 2)->default(0);
                $table->decimal('total_denda', 15, 2)->default(0);
                $table->enum('status', ['belum_bayar', 'sudah_bayar', 'dibebaskan'])->default('belum_bayar');
                $table->string('metode_pembayaran', 50)->nullable();
                $table->string('bukti_bayar')->nullable();
                $table->date('tanggal_bayar')->nullable();
                $table->text('catatan')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('denda_peminjaman');
        Schema::dropIfExists('detail_peminjaman');
        Schema::dropIfExists('peminjaman_alat');
        Schema::dropIfExists('perawatan_alat');
        Schema::dropIfExists('riwayat_kondisi_alat');
        Schema::dropIfExists('dokumentasi_alat');
        Schema::dropIfExists('alat');
        Schema::dropIfExists('kategori_alat');
    }
};
