<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'ringkasan_sinyal',
        'judul_sinyal',
        'deskripsi_sinyal',
        'level_sinyal',
        'urgensi_sinyal',
        'kategori_sinyal',
        'nama_program',
        'nama_pelapor',
        'nama_lokasi',
        'risiko_teridentifikasi',
        'rekomendasi_ai',
        'jenis_sumber',
        'waktu_catat',
        'wa_chat_id',
        'status_ticket',
        'status_eskalasi',
        'waktu_eskalasi',
        'rekomendasi_keputusan',
        'skor_urgensi_hukum',
        'dasar_hukum',
        'bahasa_output',
        'nama_pengirim',
        'nomor_pengirim',
        'level_laporan',
        'urgensi_awal',
        'timestamp_laporan_ai',
        'skor_urgensi_tertinggi',
        'sinyal_index',
        'sinyal_total',
        'rr_tenggat_waktu',
        'rr_tindakan_utama',
        'rr_pihak_yang_dihubungi',
        'rr_langkah_dokumentasi',
    ];

    protected $casts = [
        'waktu_catat' => 'datetime',
        'waktu_eskalasi' => 'datetime',
        'skor_urgensi_hukum' => 'integer',
        'skor_urgensi_tertinggi' => 'integer',
        'sinyal_index' => 'integer',
        'sinyal_total' => 'integer',
    ];

    public function actions()
    {
        return $this->hasMany(TicketAction::class);
    }
}
