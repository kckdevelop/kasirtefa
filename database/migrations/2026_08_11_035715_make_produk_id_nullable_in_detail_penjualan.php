<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        if (DB::getDriverName() === 'sqlite') {
            Schema::table('detail_penjualan', function (Blueprint $table) {
                $table->unsignedBigInteger('produk_id')->nullable()->change();
            });
        } else {
            Schema::table('detail_penjualan', function (Blueprint $table) {
                try {
                    $table->dropForeign(['produk_id']);
                } catch (\Throwable $e) {
                }
                $table->unsignedBigInteger('produk_id')->nullable()->change();
                $table->foreign('produk_id')->references('id')->on('produk')->onDelete('cascade');
            });
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        if (DB::getDriverName() === 'sqlite') {
            Schema::table('detail_penjualan', function (Blueprint $table) {
                $table->unsignedBigInteger('produk_id')->nullable(false)->change();
            });
        } else {
            Schema::table('detail_penjualan', function (Blueprint $table) {
                try {
                    $table->dropForeign(['produk_id']);
                } catch (\Throwable $e) {
                }
                $table->unsignedBigInteger('produk_id')->nullable(false)->change();
                $table->foreign('produk_id')->references('id')->on('produk')->onDelete('cascade');
            });
        }

        Schema::enableForeignKeyConstraints();
    }
};

