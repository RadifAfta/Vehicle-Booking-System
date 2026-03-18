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
        Schema::create('kendaraan', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->enum('jenis', ['angkutan_orang', 'angkutan_barang']);
            $table->enum('kepemilikan', ['milik_perusahaan', 'sewa']);
            $table->foreignId('kantor_id')->constrained('kantor')->cascadeOnUpdate()->restrictOnDelete();
            $table->float('konsumsi_bbm_liter_per_km');
            $table->date('tanggal_servis_terakhir');
            $table->timestamps();
        });

        Schema::create('driver', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('telepon');
            $table->enum('status', ['tersedia', 'sibuk'])->default('tersedia');
            $table->timestamps();
        });

        Schema::create('pemesanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('kendaraan_id')->constrained('kendaraan')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('driver_id')->constrained('driver')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('atasan_1_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('atasan_2_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->dateTime('tanggal_mulai');
            $table->dateTime('tanggal_selesai');
            $table->enum('status_pemesanan', ['menunggu_persetujuan', 'disetujui_level_1', 'disetujui_final', 'ditolak'])->default('menunggu_persetujuan');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('log_persetujuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemesanan_id')->constrained('pemesanan')->cascadeOnDelete();
            $table->foreignId('penyetujui_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();
            $table->unsignedTinyInteger('level');
            $table->enum('aksi', ['setuju', 'tolak']);
            $table->text('catatan_tambahan')->nullable();
            $table->timestamps();
        });

        Schema::create('riwayat_pemakaian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pemesanan_id')->constrained('pemesanan')->cascadeOnDelete();
            $table->float('jarak_tempuh_km');
            $table->float('bbm_terpakai_liter');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_pemakaian');
        Schema::dropIfExists('log_persetujuan');
        Schema::dropIfExists('pemesanan');
        Schema::dropIfExists('driver');
        Schema::dropIfExists('kendaraan');
    }
};
