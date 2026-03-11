<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_id')->unique();
            $table->text('ringkasan_sinyal')->nullable();
            $table->string('judul_sinyal')->nullable();
            $table->text('deskripsi_sinyal')->nullable();
            $table->string('level_sinyal')->nullable();
            $table->string('urgensi_sinyal')->nullable();
            $table->string('kategori_sinyal')->nullable();
            $table->string('nama_program')->nullable();
            $table->string('nama_pelapor')->nullable();
            $table->string('nama_lokasi')->nullable();
            $table->text('risiko_teridentifikasi')->nullable();
            $table->text('rekomendasi_ai')->nullable();
            $table->string('jenis_sumber')->nullable();
            $table->timestamp('waktu_catat')->nullable();
            $table->string('wa_chat_id')->nullable();
            $table->string('status_ticket')->default('Open');
            $table->string('status_eskalasi')->default('Normal');
            $table->timestamp('waktu_eskalasi')->nullable();
            $table->text('rekomendasi_keputusan')->nullable();
            $table->integer('skor_urgensi_hukum')->default(0);
            $table->string('dasar_hukum')->nullable();
            $table->string('bahasa_output')->nullable();
            $table->string('nama_pengirim')->nullable();
            $table->string('nomor_pengirim')->nullable();
            $table->string('level_laporan')->nullable();
            $table->string('urgensi_awal')->nullable();
            $table->string('timestamp_laporan_ai')->nullable();
            $table->integer('skor_urgensi_tertinggi')->default(0);
            $table->integer('sinyal_index')->default(0);
            $table->integer('sinyal_total')->default(0);
            $table->string('rr_tenggat_waktu')->nullable();
            $table->text('rr_tindakan_utama')->nullable();
            $table->text('rr_pihak_yang_dihubungi')->nullable();
            $table->text('rr_langkah_dokumentasi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
