<?php

namespace App\Services;

use App\Models\Ticket;
use Google\Client;
use Google\Service\Sheets;
use Google\Service\Sheets\ValueRange;
use GuzzleHttp\Client as GuzzleClient;

class GoogleSheetsService
{
    private Sheets $service;

    private string $spreadsheetId;

    private string $ticketsRange;

    public function __construct()
    {
        $client = new Client();
        $client->setApplicationName('OZJ Sheets Sync');
        $client->setScopes([Sheets::SPREADSHEETS]);

        $credentialsPath = config('services.google.credentials_path');
        if (! $credentialsPath || ! file_exists(base_path($credentialsPath))) {
            throw new \RuntimeException('Google service account credentials file not found. Check GOOGLE_APPLICATION_CREDENTIALS.');
        }

        $client->setAuthConfig(base_path($credentialsPath));

        // Timeout untuk koneksi & request ke Google (token + Sheets API). Naikkan jika cURL 28 / connection timed out.
        $timeout = (int) config('services.google.http_timeout', 45);
        $connectTimeout = (int) config('services.google.connect_timeout', 25);
        $client->setHttpClient(new GuzzleClient([
            'timeout' => $timeout,
            'connect_timeout' => $connectTimeout,
        ]));

        $this->service = new Sheets($client);
        $this->spreadsheetId = config('services.google.spreadsheet_id');
        $this->ticketsRange = config('services.google.tickets_range', "'A1'!A1:AH");
    }

    /**
     * Sync a single ticket row back to Google Sheets.
     */
    public function syncTicket(Ticket $ticket): void
    {
        if (! $this->spreadsheetId) {
            return;
        }

        // Fetch header + existing rows
        $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $this->ticketsRange);
        $values = $response->getValues() ?? [];

        if (count($values) === 0) {
            return;
        }

        $headers = $values[0];
        $ticketIdIndex = array_search('ticket_id', $headers, true);

        if ($ticketIdIndex === false) {
            return;
        }

        $sheetName = explode('!', $this->ticketsRange)[0];

        // Find existing row by ticket_id
        $rowIndex = null; // 1-based index in sheet
        for ($i = 1; $i < count($values); $i++) {
            $row = $values[$i];
            if (($row[$ticketIdIndex] ?? null) === $ticket->ticket_id) {
                $rowIndex = $i + 1; // +1 because sheet rows start at 1
                break;
            }
        }

        $rowData = $this->mapTicketToRow($ticket, $headers);

        if ($rowIndex !== null) {
            // Update existing row
            $startCol = 'A';
            $endCol = $this->columnLetter(count($headers));
            $range = sprintf('%s!%s%d:%s%d', $sheetName, $startCol, $rowIndex, $endCol, $rowIndex);

            $body = new ValueRange([
                'range' => $range,
                'values' => [$rowData],
            ]);

            $this->service->spreadsheets_values->update(
                $this->spreadsheetId,
                $range,
                $body,
                ['valueInputOption' => 'RAW'],
            );
        } else {
            // Append new row
            $body = new ValueRange([
                'values' => [$rowData],
            ]);

            $this->service->spreadsheets_values->append(
                $this->spreadsheetId,
                $this->ticketsRange,
                $body,
                ['valueInputOption' => 'RAW'],
            );
        }
    }

    /**
     * Map Ticket model to a row based on header order.
     */
    private function mapTicketToRow(Ticket $ticket, array $headers): array
    {
        $data = [];

        foreach ($headers as $header) {
            switch ($header) {
                case 'ringkasan_sinyal':
                    $data[] = $ticket->ringkasan_sinyal;
                    break;
                case 'judul_sinyal':
                    $data[] = $ticket->judul_sinyal;
                    break;
                case 'deskripsi_sinyal':
                    $data[] = $ticket->deskripsi_sinyal;
                    break;
                case 'level_sinyal':
                    $data[] = $ticket->level_sinyal;
                    break;
                case 'urgensi_sinyal':
                    $data[] = $ticket->urgensi_sinyal;
                    break;
                case 'kategori_sinyal':
                    $data[] = $ticket->kategori_sinyal;
                    break;
                case 'nama_program':
                    $data[] = $ticket->nama_program;
                    break;
                case 'nama_pelapor':
                    $data[] = $ticket->nama_pelapor;
                    break;
                case 'nama_lokasi':
                    $data[] = $ticket->nama_lokasi;
                    break;
                case 'risiko_teridentifikasi':
                    $data[] = $ticket->risiko_teridentifikasi;
                    break;
                case 'rekomendasi_ai':
                    $data[] = $ticket->rekomendasi_ai;
                    break;
                case 'jenis_sumber':
                    $data[] = $ticket->jenis_sumber;
                    break;
                case 'waktu_catat':
                    $data[] = optional($ticket->waktu_catat)->toIso8601String();
                    break;
                case 'ticket_id':
                    $data[] = $ticket->ticket_id;
                    break;
                case 'wa_chat_id':
                    $data[] = $ticket->wa_chat_id;
                    break;
                case 'status_ticket':
                    $data[] = $ticket->status_ticket;
                    break;
                case 'status_eskalasi':
                    $data[] = $ticket->status_eskalasi;
                    break;
                case 'waktu_eskalasi':
                    $data[] = optional($ticket->waktu_eskalasi)->toIso8601String();
                    break;
                case 'rekomendasi_keputusan':
                    $data[] = $ticket->rekomendasi_keputusan;
                    break;
                case 'skor_urgensi_hukum':
                    $data[] = $ticket->skor_urgensi_hukum;
                    break;
                case 'dasar_hukum':
                    $data[] = $ticket->dasar_hukum;
                    break;
                case 'bahasa_output':
                    $data[] = $ticket->bahasa_output;
                    break;
                case 'nama_pengirim':
                    $data[] = $ticket->nama_pengirim;
                    break;
                case 'nomor_pengirim':
                    $data[] = $ticket->nomor_pengirim;
                    break;
                case 'level_laporan':
                    $data[] = $ticket->level_laporan;
                    break;
                case 'urgensi_awal':
                    $data[] = $ticket->urgensi_awal;
                    break;
                case 'timestamp_laporan_ai':
                    $data[] = $ticket->timestamp_laporan_ai;
                    break;
                case 'skor_urgensi_tertinggi':
                    $data[] = $ticket->skor_urgensi_tertinggi;
                    break;
                case 'sinyal_index':
                    $data[] = $ticket->sinyal_index;
                    break;
                case 'sinyal_total':
                    $data[] = $ticket->sinyal_total;
                    break;
                case 'rr_tenggat_waktu':
                    $data[] = $ticket->rr_tenggat_waktu;
                    break;
                case 'rr_tindakan_utama':
                    $data[] = $ticket->rr_tindakan_utama;
                    break;
                case 'rr_pihak_yang_dihubungi':
                    $data[] = $ticket->rr_pihak_yang_dihubungi;
                    break;
                case 'rr_langkah_dokumentasi':
                    $data[] = $ticket->rr_langkah_dokumentasi;
                    break;
                case 'Potensi keterlambatan distribusi bantuan':
                    $data[] = null; // kolom opsional di sheet, belum di DB
                    break;
                default:
                    $data[] = null;
            }
        }

        return $data;
    }

    private function columnLetter(int $index): string
    {
        $letter = '';
        while ($index > 0) {
            $mod = ($index - 1) % 26;
            $letter = chr(65 + $mod).$letter;
            $index = intdiv($index - $mod, 26);
        }

        return $letter;
    }
}

