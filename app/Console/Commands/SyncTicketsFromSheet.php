<?php

namespace App\Console\Commands;

use App\Services\TicketSheetImporter;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'tickets:sync-sheet', description: 'Sync tickets from Google Sheets CSV into the database')]
class SyncTicketsFromSheet extends Command
{
    public function handle(TicketSheetImporter $importer): int
    {
        $url = config('services.ozj_sheets.tickets_csv_url');

        if (! $url) {
            $this->error('GOOGLE_SHEETS_TICKETS_CSV_URL belum diset di .env');

            return self::FAILURE;
        }

        try {
            $count = $importer->syncFromCsv($url);
        } catch (\Throwable $e) {
            $this->error('Gagal sync dari Google Sheets: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info("Berhasil sync {$count} baris tiket dari Google Sheets.");

        return self::SUCCESS;
    }
}

