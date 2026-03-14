<?php

namespace App\Http\Controllers;

use App\Exports\TicketsPerTicketExport;
use App\Exports\TicketsRekapExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    public function __construct(
        private readonly Excel $excel
    ) {
    }

    /**
     * Download Excel rekap (summary: total, status, level sinyal, eskalasi, kategori).
     */
    public function rekap(): BinaryFileResponse
    {
        $filename = 'ozj-rekap-' . now()->format('Y-m-d-His') . '.xlsx';

        return $this->excel->download(new TicketsRekapExport, $filename, 'Xlsx');
    }

    /**
     * Download Excel per ticket.
     * - Semua tiket: GET/POST tanpa parameter.
     * - Satu tiket by ticket_id: GET ?ticket_id=OZJ-20250314-1234 atau POST { "ticket_id": "OZJ-..." }.
     * - Beberapa tiket by ticket_id: GET ?ticket_ids=OZJ-1,OZJ-2 atau POST { "ticket_ids": ["OZJ-1", "OZJ-2"] }.
     * - By internal id: GET ?ids=1,2,3 atau POST { "ids": [1, 2, 3] }.
     */
    public function perTicket(Request $request): BinaryFileResponse
    {
        $ticketId = $request->input('ticket_id');
        if (is_string($ticketId)) {
            $ticketId = trim($ticketId);
        } else {
            $ticketId = null;
        }

        $ticketIds = $request->input('ticket_ids');
        if (is_string($ticketIds)) {
            $ticketIds = array_filter(array_map('trim', explode(',', $ticketIds)));
        }
        if (is_array($ticketIds)) {
            $ticketIds = array_values(array_filter($ticketIds));
        } else {
            $ticketIds = null;
        }

        $ids = $request->input('ids');
        if (is_string($ids)) {
            $ids = array_filter(array_map('intval', explode(',', $ids)));
        }
        if (! is_array($ids)) {
            $ids = null;
        }

        // Prioritas: ticket_id (satu) → ticket_ids (banyak) → ids (internal) → semua
        if ($ticketId !== null && $ticketId !== '') {
            $ticketIds = [$ticketId];
            $ids = null;
        }
        if ($ticketIds !== null && $ticketIds === []) {
            $ticketIds = null;
        }

        $export = new TicketsPerTicketExport($ids, $ticketIds);

        if ($ticketId !== null && $ticketId !== '') {
            $safe = preg_replace('/[^a-zA-Z0-9\-_]/', '-', $ticketId);
            $filename = 'ozj-ticket-' . $safe . '-' . now()->format('Y-m-d-His') . '.xlsx';
        } else {
            $filename = 'ozj-per-ticket-' . now()->format('Y-m-d-His') . '.xlsx';
        }

        return $this->excel->download($export, $filename, 'Xlsx');
    }
}
