<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_penjualan', function (Blueprint $table) {
            // Make produk_id nullable so lisensi transactions don't need a produk
            $table->unsignedBigInteger('produk_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('detail_penjualan', function (Blueprint $table) {
            $table->unsignedBigInteger('produk_id')->nullable(false)->change();
        });
    }
};
