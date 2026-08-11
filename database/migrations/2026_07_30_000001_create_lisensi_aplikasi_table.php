<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('lisensi_aplikasi')) {
            Schema::create('lisensi_aplikasi', function (Blueprint $table) {
                $table->id();
                $table->string('nomor_lisensi', 50)->unique();
                $table->enum('tipe', ['beli', 'berlangganan']);
                $table->string('nama_pembeli');
                $table->string('email')->nullable();
                $table->string('telepon', 20)->nullable();
                $table->string('nama_sekolah')->nullable();
                $table->decimal('harga', 15, 2)->default(0);
                // Untuk tipe beli
                $table->date('tanggal_beli')->nullable();
                $table->date('tanggal_jatuh_tempo')->nullable();
                // Untuk tipe berlangganan
                $table->date('tanggal_mulai')->nullable();
                $table->unsignedInteger('lama_sewa')->nullable()->comment('Dalam bulan');
                $table->date('tanggal_berakhir')->nullable();
                // Umum
                $table->enum('status', ['aktif', 'kadaluarsa', 'dibatalkan'])->default('aktif');
                $table->text('keterangan')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lisensi_aplikasi');
    }
};
