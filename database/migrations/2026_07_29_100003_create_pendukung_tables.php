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
        // 18. pengaturan_aplikasi
        if (!Schema::hasTable('pengaturan_aplikasi')) {
            Schema::create('pengaturan_aplikasi', function (Blueprint $table) {
                $table->id();
                $table->string('kunci', 100)->unique();
                $table->text('nilai')->nullable();
                $table->enum('tipe', ['text', 'number', 'boolean', 'json', 'file'])->default('text');
                $table->string('kategori', 100)->nullable();
                $table->text('deskripsi')->nullable();
                $table->timestamps();
            });
        }

        // 19. notifikasi
        if (!Schema::hasTable('notifikasi')) {
            Schema::create('notifikasi', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('judul', 200);
                $table->text('pesan');
                $table->enum('tipe', ['info', 'peringatan', 'kesalahan', 'sukses'])->default('info');
                $table->enum('kategori', ['tefa', 'alat', 'sistem'])->default('sistem');
                $table->json('data')->nullable();
                $table->boolean('is_read')->default(false);
                $table->timestamp('read_at')->nullable();
                $table->string('url')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'is_read', 'kategori']);
            });
        }

        // 20. activity_logs
        if (!Schema::hasTable('activity_logs')) {
            Schema::create('activity_logs', function (Blueprint $table) {
                $table->id();
                $table->string('log_name')->nullable();
                $table->text('description');
                $table->string('subject_type')->nullable();
                $table->unsignedBigInteger('subject_id')->nullable();
                $table->string('causer_type')->nullable();
                $table->unsignedBigInteger('causer_id')->nullable();
                $table->json('properties')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('notifikasi');
        Schema::dropIfExists('pengaturan_aplikasi');
    }
};
