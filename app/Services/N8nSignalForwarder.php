<?php

namespace App\Services;

use App\Models\Ticket;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sinyal dari web ke workflow n8n "OZJ — AI Signal Intelligence".
 * Aturan: input manual dari web HANYA lewat n8n; data dibaca di website setelah n8n simpan ke spreadsheet.
 * Workflow trigger: Webhook path "ozj-log-signal" (POST).
 */
class N8nSignalForwarder
{
    /** Timeout cukup panjang karena n8n jalankan Gemini + Sheet + routing. */
    private const TIMEOUT_SECONDS = 90;

    /**
     * Kirim payload ke n8n dari data request (tanpa simpan ke DB).
     * Dipakai untuk input manual dari web: web → n8n → spreadsheet → website baca dari sync Sheet.
     *
     * @return array{success: bool, status: int, body: array|null, message: string}
     */
    public function sendFromRequest(array $data): array
    {
        $url = config('services.n8n.signal_webhook_url');
        if (! $url || $url === '') {
            return [
                'success' => false,
                'status' => 0,
                'body' => null,
                'message' => 'N8n webhook URL tidak dikonfigurasi (N8N_SIGNAL_WEBHOOK_URL).',
            ];
        }

        $body = $this->buildPayloadFromArray($data);

        try {
            $response = Http::timeout(self::TIMEOUT_SECONDS)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, $body);

            $decoded = $response->successful() ? $response->json() : null;
            if (! $response->successful()) {
                Log::warning('N8n webhook gagal', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'body' => $decoded,
                'message' => $response->successful()
                    ? 'Sinyal dikirim ke workflow. Data akan muncul di website setelah tersimpan di spreadsheet.'
                    : 'Workflow n8n mengembalikan error: ' . $response->status(),
            ];
        } catch (\Throwable $e) {
            Log::warning('N8n webhook error', [
                'message' => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'status' => 0,
                'body' => null,
                'message' => 'Gagal mengirim ke workflow: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Build payload untuk node "Gemini: Ekstraksi Sinyal dari Observasi".
     */
    private function buildPayloadFromArray(array $data): array
    {
        $teks = implode("\n", array_filter([
            'Judul: ' . ($data['judul_sinyal'] ?? ''),
            'Deskripsi: ' . ($data['deskripsi_sinyal'] ?? ''),
            'Level: ' . ($data['level_sinyal'] ?? ''),
            'Urgensi: ' . ($data['urgensi_sinyal'] ?? ''),
            'Kategori: ' . ($data['kategori_sinyal'] ?? ''),
            'Pelapor: ' . ($data['nama_pelapor'] ?? $data['nama_pengirim'] ?? ''),
            'Lokasi: ' . ($data['nama_lokasi'] ?? ''),
            'Program: ' . ($data['nama_program'] ?? ''),
            'Risiko: ' . ($data['risiko_teridentifikasi'] ?? ''),
            'Rekomendasi: ' . ($data['rekomendasi_ai'] ?? ''),
        ]));

        $notifyName = $data['nama_pelapor'] ?? $data['nama_pengirim'] ?? 'Web';
        $waktu = isset($data['waktu_catat']) ? (is_numeric($data['waktu_catat']) ? (int) $data['waktu_catat'] : strtotime($data['waktu_catat'])) : time();

        return [
            'payload' => [
                'body' => $teks,
                '_data' => ['notifyName' => $notifyName],
                'from' => $data['nomor_pengirim'] ?? $data['wa_chat_id'] ?? '',
                'timestamp' => $waktu,
            ],
            'nama_program' => $data['nama_program'] ?? '',
            'nama_lokasi' => $data['nama_lokasi'] ?? '',
            'level_laporan' => $data['level_laporan'] ?? $data['level_sinyal'] ?? '',
            'urgensi_laporan' => $data['urgensi_awal'] ?? $data['urgensi_sinyal'] ?? '',
        ];
    }

    /** Legacy: forward dari model Ticket (tetap dipakai jika ada path lain yang simpan ke DB dulu). */
    public function forwardToWorkflow(Ticket $ticket): bool
    {
        $data = [
            'judul_sinyal' => $ticket->judul_sinyal,
            'deskripsi_sinyal' => $ticket->deskripsi_sinyal,
            'level_sinyal' => $ticket->level_sinyal,
            'urgensi_sinyal' => $ticket->urgensi_sinyal,
            'kategori_sinyal' => $ticket->kategori_sinyal,
            'nama_pelapor' => $ticket->nama_pelapor,
            'nama_pengirim' => $ticket->nama_pengirim,
            'nama_lokasi' => $ticket->nama_lokasi,
            'nama_program' => $ticket->nama_program,
            'risiko_teridentifikasi' => $ticket->risiko_teridentifikasi,
            'rekomendasi_ai' => $ticket->rekomendasi_ai,
            'nomor_pengirim' => $ticket->nomor_pengirim,
            'wa_chat_id' => $ticket->wa_chat_id,
            'waktu_catat' => $ticket->waktu_catat?->timestamp ?? time(),
            'level_laporan' => $ticket->level_laporan,
            'urgensi_awal' => $ticket->urgensi_awal,
        ];
        $result = $this->sendFromRequest($data);
        return $result['success'];
    }
}
