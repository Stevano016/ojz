<?php

namespace App\Services;

use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TicketSheetImporter
{
    /**
     * Sync tickets from a published Google Sheets CSV URL.
     *
     * @return int Number of rows processed
     */
    public function syncFromCsv(string $url): int
    {
        // Pastikan URL export CSV pakai sheet pertama jika belum ada gid
        if (str_contains($url, 'output=csv') && ! str_contains($url, 'gid=')) {
            $url .= (str_contains($url, '?') ? '&' : '?').'gid=0';
        }

        $response = Http::timeout(25)
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (compatible; OZJ-SheetSync/1.0; +https://ozj.nl)',
            ])
            ->get($url);

        if (! $response->ok()) {
            throw new \RuntimeException('Gagal mengambil CSV dari Google Sheets: HTTP '.$response->status());
        }

        $body = trim($response->body());

        if ($body === '') {
            return 0;
        }

        // Jika Google mengembalikan HTML (misal halaman login), jangan di-parse
        if (stripos($body, '<!DOCTYPE') !== false || stripos($body, '<html') !== false) {
            throw new \RuntimeException('Google Sheets mengembalikan HTML, bukan CSV. Pastikan sheet dipublikasikan ke web (Publish to web).');
        }

        $lines = preg_split("/\r\n|\n|\r/", $body);

        if (! $lines || count($lines) < 2) {
            return 0;
        }

        $headers = str_getcsv(array_shift($lines));
        $headers = array_map('trim', $headers);

        // Validasi: minimal ada kolom yang kita butuh (ticket_id atau judul_sinyal)
        $hasExpected = ! empty(array_intersect($headers, ['ticket_id', 'judul_sinyal', 'ringkasan_sinyal']));
        if (! $hasExpected) {
            throw new \RuntimeException('Format CSV tidak sesuai: header harus berisi ticket_id / judul_sinyal / ringkasan_sinyal. Ditemukan: '.implode(', ', array_slice($headers, 0, 5)).'...');
        }

        $count = 0;

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }

            $row = str_getcsv($line);

            if (count($row) === 1 && $row[0] === null) {
                continue;
            }

            $row = array_pad($row, count($headers), null);
            $row = array_combine($headers, $row);

            if (! $row) {
                continue;
            }

            $ticketId = isset($row['ticket_id']) ? trim((string) $row['ticket_id']) : null;
            if ($ticketId === '') {
                $ticketId = null;
            }

            // Hanya proses baris yang punya ticket_id. Baris tanpa ticket_id di-skip agar tidak bikin duplikat tiap sync.
            if (! $ticketId) {
                continue;
            }

            $data = [
                'ticket_id' => $ticketId,
                'ringkasan_sinyal' => $row['ringkasan_sinyal'] ?? null,
                'judul_sinyal' => $row['judul_sinyal'] ?? null,
                'deskripsi_sinyal' => $row['deskripsi_sinyal'] ?? null,
                'level_sinyal' => $row['level_sinyal'] ?? null,
                'urgensi_sinyal' => $row['urgensi_sinyal'] ?? null,
                'kategori_sinyal' => $row['kategori_sinyal'] ?? null,
                'nama_program' => $row['nama_program'] ?? null,
                'nama_pelapor' => $row['nama_pelapor'] ?? null,
                'nama_lokasi' => $row['nama_lokasi'] ?? null,
                'risiko_teridentifikasi' => $row['risiko_teridentifikasi'] ?? null,
                'rekomendasi_ai' => $row['rekomendasi_ai'] ?? null,
                'jenis_sumber' => $row['jenis_sumber'] ?? null,
                'waktu_catat' => $this->parseDate($row['waktu_catat'] ?? null),
                'wa_chat_id' => $row['wa_chat_id'] ?? null,
                'status_ticket' => $row['status_ticket'] ?? 'Open',
                'status_eskalasi' => $row['status_eskalasi'] ?? 'Normal',
                'waktu_eskalasi' => $this->parseDate($row['waktu_eskalasi'] ?? null),
                'rekomendasi_keputusan' => $row['rekomendasi_keputusan'] ?? null,
                'skor_urgensi_hukum' => $this->parseInt($row['skor_urgensi_hukum'] ?? null) ?? 0,
                'dasar_hukum' => $row['dasar_hukum'] ?? null,
                'bahasa_output' => $row['bahasa_output'] ?? null,
                'nama_pengirim' => $row['nama_pengirim'] ?? null,
                'nomor_pengirim' => $row['nomor_pengirim'] ?? null,
                'level_laporan' => $row['level_laporan'] ?? null,
                'urgensi_awal' => $row['urgensi_awal'] ?? null,
                'timestamp_laporan_ai' => $row['timestamp_laporan_ai'] ?? null,
                'skor_urgensi_tertinggi' => $this->parseInt($row['skor_urgensi_tertinggi'] ?? null) ?? 0,
                'sinyal_index' => $this->parseInt($row['sinyal_index'] ?? null) ?? 0,
                'sinyal_total' => $this->parseInt($row['sinyal_total'] ?? null) ?? 0,
                'rr_tenggat_waktu' => $row['rr_tenggat_waktu'] ?? null,
                'rr_tindakan_utama' => $row['rr_tindakan_utama'] ?? null,
                'rr_pihak_yang_dihubungi' => $row['rr_pihak_yang_dihubungi'] ?? null,
                'rr_langkah_dokumentasi' => $row['rr_langkah_dokumentasi'] ?? null,
            ];

            Ticket::updateOrCreate(
                ['ticket_id' => $ticketId],
                $data,
            );

            $count++;
        }

        return $count;
    }

    private function generateTicketId(?string $waktuCatat): string
    {
        $date = $this->parseDate($waktuCatat) ?? Carbon::now();

        do {
            $candidate = 'OZJ-'.$date->format('Ymd').'-'.random_int(1000, 9999);
        } while (Ticket::where('ticket_id', $candidate)->exists());

        return $candidate;
    }

    private function parseDate(?string $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function parseInt($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }
}

