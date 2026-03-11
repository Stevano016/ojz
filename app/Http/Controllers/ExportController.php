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
     * Download Excel per ticket. Semua tiket atau pilihan (ids di query/body).
     * GET ?ids=1,2,3 atau POST { "ids": [1, 2, 3] }.
     */
    public function perTicket(Request $request): BinaryFileResponse
    {
        $ids = $request->input('ids');
        if (is_string($ids)) {
            $ids = array_filter(array_map('intval', explode(',', $ids)));
        }
        if (! is_array($ids)) {
            $ids = null;
        }

        $filename = 'ozj-per-ticket-' . now()->format('Y-m-d-His') . '.xlsx';

        return $this->excel->download(
            new TicketsPerTicketExport($ids),
            $filename,
            'Xlsx'
        );
    }
}
