<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelanggan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pelanggan', 50)->unique();
            $table->string('nama', 200);
            $table->enum('tipe', ['umum', 'siswa', 'guru', 'instansi'])->default('umum');
            $table->string('telepon', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('alamat')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelanggan');
    }
};
