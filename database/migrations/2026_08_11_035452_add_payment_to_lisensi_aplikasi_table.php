<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lisensi_aplikasi', function (Blueprint $table) {
            $table->enum('status_pembayaran', ['belum_bayar', 'lunas'])->default('belum_bayar')->after('status');
            $table->date('tanggal_pembayaran')->nullable()->after('status_pembayaran');
            $table->string('metode_pembayaran', 50)->nullable()->after('tanggal_pembayaran');
            $table->text('catatan_pembayaran')->nullable()->after('metode_pembayaran');
        });
    }

    public function down(): void
    {
        Schema::table('lisensi_aplikasi', function (Blueprint $table) {
            $table->dropColumn(['status_pembayaran', 'tanggal_pembayaran', 'metode_pembayaran', 'catatan_pembayaran']);
        });
    }
};
