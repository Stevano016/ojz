<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Services\GoogleSheetsService;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function n8n(Request $request)
    {
        $data = $request->all();

        $ticketId = $data['ticket_id'] ?? 'OZJ-' . now()->format('Ymd') . '-' . rand(1000, 9999);

        $skor = intval($data['skor_urgensi_hukum'] ?? 0);
        $statusEskalasi = 'Normal';
        if ($skor >= 90) $statusEskalasi = 'KRITIS';
        elseif ($skor >= 70) $statusEskalasi = 'Tinggi';
        elseif ($skor >= 50) $statusEskalasi = 'Sedang';

        $ticket = Ticket::updateOrCreate(
            ['ticket_id' => $ticketId],
            [
                'ringkasan_sinyal' => $data['ringkasan_sinyal'] ?? null,
                'judul_sinyal' => $data['judul_sinyal'] ?? null,
                'deskripsi_sinyal' => $data['deskripsi_sinyal'] ?? null,
                'level_sinyal' => $data['level_sinyal'] ?? null,
                'urgensi_sinyal' => $data['urgensi_sinyal'] ?? null,
                'kategori_sinyal' => $data['kategori_sinyal'] ?? null,
                'nama_program' => $data['nama_program'] ?? null,
                'nama_pelapor' => $data['nama_pelapor'] ?? null,
                'nama_lokasi' => $data['nama_lokasi'] ?? null,
                'risiko_teridentifikasi' => $data['risiko_teridentifikasi'] ?? null,
                'rekomendasi_ai' => $data['rekomendasi_ai'] ?? null,
                'jenis_sumber' => $data['jenis_sumber'] ?? null,
                'waktu_catat' => $data['waktu_catat'] ?? now(),
                'wa_chat_id' => $data['wa_chat_id'] ?? null,
                'status_ticket' => $data['status_ticket'] ?? 'Open',
                'status_eskalasi' => $data['status_eskalasi'] ?? $statusEskalasi,
                'waktu_eskalasi' => $data['waktu_eskalasi'] ?? null,
                'rekomendasi_keputusan' => $data['rekomendasi_keputusan'] ?? null,
                'skor_urgensi_hukum' => $skor,
                'dasar_hukum' => $data['dasar_hukum'] ?? null,
                'bahasa_output' => $data['bahasa_output'] ?? null,
                'nama_pengirim' => $data['nama_pengirim'] ?? null,
                'nomor_pengirim' => $data['nomor_pengirim'] ?? null,
                'level_laporan' => $data['level_laporan'] ?? null,
                'urgensi_awal' => $data['urgensi_awal'] ?? null,
                'timestamp_laporan_ai' => $data['timestamp_laporan_ai'] ?? null,
                'skor_urgensi_tertinggi' => intval($data['skor_urgensi_tertinggi'] ?? $skor),
                'sinyal_index' => intval($data['sinyal_index'] ?? 0),
                'sinyal_total' => intval($data['sinyal_total'] ?? 0),
                'rr_tenggat_waktu' => $data['rr_tenggat_waktu'] ?? null,
                'rr_tindakan_utama' => $data['rr_tindakan_utama'] ?? null,
                'rr_pihak_yang_dihubungi' => $data['rr_pihak_yang_dihubungi'] ?? null,
                'rr_langkah_dokumentasi' => $data['rr_langkah_dokumentasi'] ?? null,
            ]
        );

        try {
            app(GoogleSheetsService::class)->syncTicket($ticket);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Webhook n8n: sync ke Google Sheets gagal', [
                'ticket_id' => $ticket->ticket_id,
                'message' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'status' => 'sukses',
            'ticket_id' => $ticket->ticket_id,
            'message' => 'Ticket received and stored',
        ], 201);
    }
}
