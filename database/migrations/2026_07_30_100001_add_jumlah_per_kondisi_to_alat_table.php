<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alat', function (Blueprint $table) {
            // Tambah kolom jumlah per kondisi setelah kolom jumlah_tersedia
            $table->integer('jumlah_baik')->default(0)->after('jumlah_tersedia');
            $table->integer('jumlah_cukup')->default(0)->after('jumlah_baik');
            $table->integer('jumlah_rusak_ringan')->default(0)->after('jumlah_cukup');
            $table->integer('jumlah_rusak_berat')->default(0)->after('jumlah_rusak_ringan');
        });
    }

    public function down(): void
    {
        Schema::table('alat', function (Blueprint $table) {
            $table->dropColumn(['jumlah_baik', 'jumlah_cukup', 'jumlah_rusak_ringan', 'jumlah_rusak_berat']);
        });
    }
};
