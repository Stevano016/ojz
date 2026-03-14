<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Services\TicketSheetImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function __construct()
    {
    }

    /**
     * Data stats untuk JSON API atau Blade view.
     */
    public function getStatsData(): array
    {
        $csvUrl = config('services.ozj_sheets.tickets_csv_url');
        if ($csvUrl) {
            try {
                app(TicketSheetImporter::class)->syncFromCsv($csvUrl);
            } catch (\Throwable $e) {
                Log::warning('Dashboard: sync dari Google Sheets gagal', [
                    'message' => $e->getMessage(),
                    'url' => $csvUrl,
                ]);
            }
        }

        $totalTickets = Ticket::count();
        $openTickets = Ticket::where('status_ticket', 'Open')->count();
        $inProgressTickets = Ticket::where('status_ticket', 'In Progress')->count();
        $resolvedTickets = Ticket::where('status_ticket', 'Resolved')->count();
        $closedTickets = Ticket::where('status_ticket', 'Closed')->count();

        $byUrgency = Ticket::select('status_eskalasi', DB::raw('count(*) as total'))
            ->groupBy('status_eskalasi')
            ->get()
            ->pluck('total', 'status_eskalasi');

        $byLevel = Ticket::select('level_sinyal', DB::raw('count(*) as total'))
            ->whereNotNull('level_sinyal')
            ->where('level_sinyal', '!=', '')
            ->groupBy('level_sinyal')
            ->get()
            ->pluck('total', 'level_sinyal');

        // Level sinyal dari spreadsheet: Mikro, Meso, Makro (kolom level_sinyal)
        $byLevelLaporan = Ticket::select('level_sinyal', DB::raw('count(*) as total'))
            ->whereNotNull('level_sinyal')
            ->where('level_sinyal', '!=', '')
            ->groupBy('level_sinyal')
            ->get()
            ->pluck('total', 'level_sinyal');

        $byCategory = Ticket::select('kategori_sinyal', DB::raw('count(*) as total'))
            ->whereNotNull('kategori_sinyal')
            ->where('kategori_sinyal', '!=', '')
            ->groupBy('kategori_sinyal')
            ->get()
            ->pluck('total', 'kategori_sinyal');

        $bySource = Ticket::select('jenis_sumber', DB::raw('count(*) as total'))
            ->whereNotNull('jenis_sumber')
            ->groupBy('jenis_sumber')
            ->get()
            ->pluck('total', 'jenis_sumber');

        $scoreDistribution = Ticket::select(
            DB::raw("CASE 
                WHEN skor_urgensi_hukum >= 90 THEN 'KRITIS (90-100)'
                WHEN skor_urgensi_hukum >= 70 THEN 'Tinggi (70-89)'
                WHEN skor_urgensi_hukum >= 50 THEN 'Sedang (50-69)'
                ELSE 'Normal (0-49)'
            END as range_label"),
            DB::raw('count(*) as total')
        )->groupBy('range_label')->get();

        $avgScore = Ticket::avg('skor_urgensi_hukum');

        $recentTickets = Ticket::orderBy('created_at', 'desc')
            ->take(10)
            ->get(['id', 'ticket_id', 'judul_sinyal', 'status_ticket', 'status_eskalasi', 'skor_urgensi_hukum', 'waktu_catat', 'level_sinyal']);

        $ticketsByDate = Ticket::select(
            DB::raw('DATE(waktu_catat) as date'),
            DB::raw('count(*) as total')
        )
            ->whereNotNull('waktu_catat')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->take(30)
            ->get();

        return [
            'summary' => [
                'total' => $totalTickets,
                'open' => $openTickets,
                'in_progress' => $inProgressTickets,
                'resolved' => $resolvedTickets,
                'closed' => $closedTickets,
                'avg_score' => round($avgScore, 1),
            ],
            'by_urgency' => $byUrgency,
            'by_level' => $byLevel,
            'by_level_laporan' => $byLevelLaporan,
            'by_category' => $byCategory,
            'by_source' => $bySource,
            'score_distribution' => $scoreDistribution,
            'recent_tickets' => $recentTickets,
            'tickets_by_date' => $ticketsByDate,
        ];
    }

    public function stats()
    {
        return response()->json($this->getStatsData());
    }

    public function index()
    {
        return view('dashboard', ['stats' => $this->getStatsData()]);
    }
}
