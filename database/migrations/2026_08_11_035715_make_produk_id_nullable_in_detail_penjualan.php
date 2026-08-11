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
            // In MySQL, foreign key must be dropped before modifying column
            try {
                $table->dropForeign(['produk_id']);
            } catch (\Throwable $e) {
                // Ignore if FK doesn't exist
            }
            $table->unsignedBigInteger('produk_id')->nullable()->change();
            $table->foreign('produk_id')->references('id')->on('produk')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('detail_penjualan', function (Blueprint $table) {
            try {
                $table->dropForeign(['produk_id']);
            } catch (\Throwable $e) {
                // Ignore if FK doesn't exist
            }
            $table->unsignedBigInteger('produk_id')->nullable(false)->change();
            $table->foreign('produk_id')->references('id')->on('produk')->onDelete('cascade');
        });
    }
};

