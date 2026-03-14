<?php

namespace App\Services;

use App\Models\Ticket;
use Google\Client;
use Google\Service\Sheets;
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
     * Get communication settings from sheet tab "communication" (key in col A, value in col B).
     *
     * @return array{phone_number: string, email: string, whatsapps: string}
     */
    public function getCommunicationSettings(): array
    {
        $range = config('services.google.communication_range', "'comunication'!A1:B10");
        if (! $this->spreadsheetId) {
            return ['phone_number' => '', 'email' => '', 'whatsapps' => ''];
        }
        try {
            $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $range);
        } catch (\Throwable $e) {
            report($e);
            return ['phone_number' => '', 'email' => '', 'whatsapps' => ''];
        }
        $values = $response->getValues() ?? [];
        $keys = ['phone_number', 'email', 'whatsapps'];
        $result = array_fill_keys($keys, '');
        foreach ($values as $row) {
            $key = trim((string) ($row[0] ?? ''));
            if (in_array($key, $keys, true)) {
                $result[$key] = trim((string) ($row[1] ?? ''));
            }
        }
        return $result;
    }

    /**
     * Save communication settings to sheet tab "communication". Expects rows with key in A, value in B.
     */
    public function setCommunicationSettings(array $data): void
    {
        $range = config('services.google.communication_range', "'comunication'!A1:B10");
        if (! $this->spreadsheetId) {
            throw new \RuntimeException('Google Spreadsheet ID not configured.');
        }
        $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $range);
        $values = $response->getValues() ?? [];
        $sheetPart = explode('!', $range)[0];
        $sheetName = trim($sheetPart, "'\"");
        $keys = ['phone_number', 'email', 'whatsapps'];
        $keyToRow = [];
        foreach ($values as $i => $row) {
            $key = trim((string) ($row[0] ?? ''));
            if (in_array($key, $keys, true)) {
                $keyToRow[$key] = $i + 1;
            }
        }
        $updates = [];
        foreach ($keys as $key) {
            $rowIndex = $keyToRow[$key] ?? null;
            $value = trim((string) ($data[$key] ?? ''));
            if ($rowIndex !== null) {
                $cellRange = sprintf('%s!B%d', $sheetName, $rowIndex);
                $updates[] = ['range' => $cellRange, 'values' => [[$value]]];
            }
        }
        if ($updates !== []) {
            $this->batchUpdateValues($updates);
        }
    }

    /**
     * Batch update multiple ranges.
     *
     * @param  array<int, array{range: string, values: array<int, array<int, string>>}>  $updates
     */
    private function batchUpdateValues(array $updates): void
    {
        $url = sprintf(
            'https://sheets.googleapis.com/v4/spreadsheets/%s/values:batchUpdate',
            $this->spreadsheetId
        );
        $body = json_encode([
            'valueInputOption' => 'RAW',
            'data' => $updates,
        ]);
        $this->sheetsRequest('POST', $url, $body);
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
        // Konversi ke list dan cast nilai agar JSON encode jadi array [...], bukan object {"0":...}
        $rowData = array_values(array_map(function ($v) {
            return $v === null ? '' : (string) $v;
        }, $rowData));

        if ($rowIndex !== null) {
            // Update existing row — kirim request langsung agar values tetap array di JSON
            $startCol = 'A';
            $endCol = $this->columnLetter(count($headers));
            $range = sprintf('%s!%s%d:%s%d', $sheetName, $startCol, $rowIndex, $endCol, $rowIndex);
            $this->updateValuesViaHttp($range, [$rowData]);
        } else {
            // Append new row
            $this->appendValuesViaHttp([$rowData]);
        }
    }

    /**
     * Update range via REST API dengan body JSON yang kita kontrol (values = array of arrays).
     */
    private function updateValuesViaHttp(string $range, array $values): void
    {
        $url = sprintf(
            'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s?valueInputOption=RAW',
            $this->spreadsheetId,
            rawurlencode($range)
        );
        $body = json_encode([
            'range' => $range,
            'values' => $values,
        ]);
        $this->sheetsRequest('PUT', $url, $body);
    }

    /**
     * Append rows via REST API dengan body JSON yang kita kontrol.
     */
    private function appendValuesViaHttp(array $values): void
    {
        $url = sprintf(
            'https://sheets.googleapis.com/v4/spreadsheets/%s/values/%s:append?valueInputOption=RAW',
            $this->spreadsheetId,
            rawurlencode($this->ticketsRange)
        );
        $body = json_encode(['values' => $values]);
        $this->sheetsRequest('POST', $url, $body);
    }

    /**
     * Append satu baris dari input manual (form web) langsung ke spreadsheet.
     * Tidak lewat n8n. Kolom mengikuti header yang sudah ada di sheet.
     *
     * @param  array<string, mixed>  $data  Data dari request (judul_sinyal, deskripsi_sinyal, level_sinyal, urgensi_sinyal, nama_pelapor, nama_lokasi, nama_program, kategori_sinyal, dll.)
     * @return string ticket_id yang di-generate (OZJ-YYYYMMDD-XXXX)
     */
    public function appendManualTicket(array $data): string
    {
        $response = $this->service->spreadsheets_values->get($this->spreadsheetId, $this->ticketsRange);
        $values = $response->getValues() ?? [];

        if (count($values) === 0) {
            throw new \RuntimeException('Sheet kosong atau range tidak ditemukan. Tambahkan header baris pertama (ticket_id, judul_sinyal, dll).');
        }

        $headers = $values[0];
        $ticketId = 'OZJ-'.date('Ymd').'-'.random_int(1000, 9999);
        $row = $this->buildRowFromManualData($headers, $data, $ticketId);
        $row = array_values(array_map(function ($v) {
            return $v === null ? '' : (string) $v;
        }, $row));

        $this->appendValuesViaHttp([$row]);

        return $ticketId;
    }

    /**
     * Build satu baris untuk sheet dari data input manual (urutan mengikuti headers).
     *
     * @param  array<int, string>  $headers
     * @param  array<string, mixed>  $data
     * @return array<int, string|null>
     */
    private function buildRowFromManualData(array $headers, array $data, string $ticketId): array
    {
        $now = (new \DateTimeImmutable)->format('Y-m-d\TH:i:s.000\Z');
        $defaults = [
            'ringkasan_sinyal' => $data['judul_sinyal'] ?? '',
            'judul_sinyal' => $data['judul_sinyal'] ?? '',
            'deskripsi_sinyal' => $data['deskripsi_sinyal'] ?? '',
            'level_sinyal' => $data['level_sinyal'] ?? '',
            'urgensi_sinyal' => $data['urgensi_sinyal'] ?? '',
            'kategori_sinyal' => $data['kategori_sinyal'] ?? '',
            'nama_program' => $data['nama_program'] ?? '',
            'nama_pelapor' => $data['nama_pelapor'] ?? $data['nama_pengirim'] ?? '',
            'nama_lokasi' => $data['nama_lokasi'] ?? '',
            'risiko_teridentifikasi' => $data['risiko_teridentifikasi'] ?? '',
            'rekomendasi_ai' => $data['rekomendasi_ai'] ?? '',
            'jenis_sumber' => 'input-manual',
            'waktu_catat' => $now,
            'ticket_id' => $ticketId,
            'wa_chat_id' => $data['nomor_pengirim'] ?? $data['wa_chat_id'] ?? '',
            'status_ticket' => 'Open',
            'status_eskalasi' => $data['status_eskalasi'] ?? 'Normal',
            'waktu_eskalasi' => '',
            'rekomendasi_keputusan' => '',
            'skor_urgensi_hukum' => (string) ($data['skor_urgensi_hukum'] ?? 0),
            'dasar_hukum' => '',
            'bahasa_output' => 'id',
            'nama_pengirim' => $data['nama_pelapor'] ?? $data['nama_pengirim'] ?? '',
            'nomor_pengirim' => $data['nomor_pengirim'] ?? '',
            'level_laporan' => $data['level_laporan'] ?? $data['level_sinyal'] ?? '',
            'urgensi_awal' => $data['urgensi_awal'] ?? $data['urgensi_sinyal'] ?? '',
            'timestamp_laporan_ai' => '',
            'skor_urgensi_tertinggi' => (string) ($data['skor_urgensi_hukum'] ?? $data['skor_urgensi_tertinggi'] ?? 0),
            'sinyal_index' => '1',
            'sinyal_total' => '1',
            'rr_tenggat_waktu' => '',
            'rr_tindakan_utama' => '',
            'rr_pihak_yang_dihubungi' => '',
            'rr_langkah_dokumentasi' => '',
        ];

        $row = [];
        foreach ($headers as $header) {
            $row[] = $defaults[$header] ?? '';
        }

        return $row;
    }

    /**
     * Kirim request ke Sheets API dengan auth dari Google Client.
     */
    private function sheetsRequest(string $method, string $url, string $jsonBody): void
    {
        $client = $this->service->getClient();
        if (! $client->getAccessToken()) {
            $client->fetchAccessTokenWithAssertion();
        }
        $token = $client->getAccessToken()['access_token'] ?? null;
        if (! $token) {
            throw new \RuntimeException('Could not obtain Google API access token.');
        }
        $http = $client->getHttpClient();
        $http->request($method, $url, [
            'headers' => [
                'Authorization' => 'Bearer '.$token,
                'Content-Type' => 'application/json',
            ],
            'body' => $jsonBody,
        ]);
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

