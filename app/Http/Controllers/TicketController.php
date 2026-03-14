<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketAction;
use App\Services\GoogleSheetsService;
use App\Services\N8nSignalForwarder;
use App\Services\TicketSheetImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{
    /**
     * Tidak inject TicketSheetImporter di constructor agar store() tetap jalan
     * saat Google API Client belum terpasang (Class "Google\Client" not found).
     */
    public function __construct()
    {
    }

    private function sheetImporter(): TicketSheetImporter
    {
        return app(TicketSheetImporter::class);
    }

    /**
     * Halaman Blade: form buat tiket manual.
     */
    public function create()
    {
        return view('tickets.create');
    }

    /**
     * Halaman Blade: cek status tiket (public).
     */
    public function trackPage(Request $request, $ticket_id = null)
    {
        $ticket = null;
        $ticketId = $ticket_id ?? $request->get('ticket_id');
        if ($ticketId) {
            $csvUrl = config('services.ozj_sheets.tickets_csv_url');
            if ($csvUrl) {
                try {
                    $this->sheetImporter()->syncFromCsv($csvUrl);
                } catch (\Throwable $e) {
                    // lanjut baca dari DB
                }
            }
            $ticket = Ticket::where('ticket_id', $ticketId)->first();
        }

        return view('track', compact('ticket'));
    }

    /**
     * Tracking tiket untuk pelapor (public, tanpa auth).
     * Data di-sync dari Sheet dulu agar status terbaru.
     */
    public function track(string $ticket_id)
    {
        $csvUrl = config('services.ozj_sheets.tickets_csv_url');
        if ($csvUrl) {
            try {
                $this->sheetImporter()->syncFromCsv($csvUrl);
            } catch (\Throwable $e) {
                // tetap lanjut baca dari DB
            }
        }

        $ticket = Ticket::where('ticket_id', $ticket_id)->first();
        if (! $ticket) {
            return response()->json(['message' => 'Tiket tidak ditemukan.'], 404);
        }

        return response()->json([
            'ticket_id' => $ticket->ticket_id,
            'judul_sinyal' => $ticket->judul_sinyal,
            'status_ticket' => $ticket->status_ticket,
            'status_eskalasi' => $ticket->status_eskalasi,
            'level_sinyal' => $ticket->level_sinyal,
            'urgensi_sinyal' => $ticket->urgensi_sinyal,
            'waktu_catat' => $ticket->waktu_catat?->toIso8601String(),
            'nama_pelapor' => $ticket->nama_pelapor,
        ]);
    }

    public function index(Request $request)
    {
        $csvUrl = config('services.ozj_sheets.tickets_csv_url');
        if ($csvUrl) {
            try {
                $this->sheetImporter()->syncFromCsv($csvUrl);
            } catch (\Throwable $e) {
                Log::warning('Tickets list: sync dari Google Sheets gagal', [
                    'message' => $e->getMessage(),
                    'url' => $csvUrl,
                ]);
            }
        }

        $query = Ticket::query();

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status_ticket', $request->status);
        }

        if ($request->has('urgency') && $request->urgency !== 'all') {
            $query->where('status_eskalasi', $request->urgency);
        }

        if ($request->has('level') && $request->level !== 'all') {
            $query->where('level_sinyal', $request->level);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ticket_id', 'LIKE', "%{$search}%")
                  ->orWhere('judul_sinyal', 'LIKE', "%{$search}%")
                  ->orWhere('deskripsi_sinyal', 'LIKE', "%{$search}%")
                  ->orWhere('nama_pelapor', 'LIKE', "%{$search}%")
                  ->orWhere('nama_lokasi', 'LIKE', "%{$search}%");
            });
        }

        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $tickets = $query->paginate($request->get('per_page', 10))->withQueryString();

        if (! $request->expectsJson()) {
            return view('tickets.index', [
                'tickets' => $tickets,
                'filters' => $request->only('status', 'urgency', 'level', 'search'),
            ]);
        }

        return response()->json($tickets);
    }

    /**
     * Input manual dari web: hanya dikirim ke webhook n8n.
     * Data tidak disimpan ke DB di sini; setelah n8n menyimpan ke spreadsheet, data dibaca di website via sync Sheet → DB.
     */
    public function store(Request $request)
    {
        $request->validate([
            'judul_sinyal' => 'required|string|max:255',
            'deskripsi_sinyal' => 'required|string',
            'level_sinyal' => 'required|string',
            'urgensi_sinyal' => 'required|string',
        ]);

        $url = config('services.n8n.signal_webhook_url');
        if (! $url || $url === '') {
            return response()->json([
                'message' => 'Webhook n8n belum dikonfigurasi. Set N8N_SIGNAL_WEBHOOK_URL di .env.',
            ], 503);
        }

        $result = app(N8nSignalForwarder::class)->sendFromRequest($request->all());

        if (! $result['success']) {
            return response()->json([
                'message' => $result['message'],
            ], $result['status'] >= 400 ? $result['status'] : 502);
        }

        $body = $result['body'] ?? [];
        $payload = [
            'message' => $result['message'],
            'ticket_id' => $body['ticket_id'] ?? null,
            'status' => $body['status'] ?? 'sukses',
            'urgensi' => $body['urgensi'] ?? null,
            'skor' => $body['skor'] ?? null,
        ];

        if (! $request->expectsJson()) {
            return redirect()->route('tickets.create')
                ->with('create_success', $payload);
        }

        return response()->json($payload, 202);
    }

    public function show($id)
    {
        $ticket = Ticket::with(['actions.user'])->findOrFail($id);

        if (! request()->expectsJson()) {
            return view('tickets.show', compact('ticket'));
        }

        return response()->json($ticket);
    }

    public function update(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        $oldStatus = $ticket->status_ticket;

        // Hanya field fillable (kecuali ticket_id) agar update aman dan sinkron ke DB + Sheet
        $allowed = array_diff($ticket->getFillable(), ['ticket_id']);
        $payload = $request->only($allowed);

        $ticket->update($payload);

        if ($request->has('status_ticket') && $oldStatus !== $request->status_ticket) {
            TicketAction::create([
                'ticket_id' => $ticket->id,
                'user_id' => $request->user()->id,
                'action_type' => 'status_change',
                'description' => "Status changed from {$oldStatus} to {$request->status_ticket}",
                'old_status' => $oldStatus,
                'new_status' => $request->status_ticket,
            ]);
        }

        // Sinkron ke Google Sheets secara realtime dengan data terbaru dari DB (perlu waktu cukup jika jaringan lambat)
        set_time_limit(65);
        [$sheetSynced, $sheetError] = $this->syncTicketToSheets($ticket->fresh());
        $ticket = $ticket->fresh()->load('actions.user');
        $ticket->sheet_synced = $sheetSynced;
        if ($sheetError !== null) {
            $ticket->sheet_sync_error = $sheetError;
        }

        if (! $request->expectsJson()) {
            return redirect()->route('tickets.show', $ticket->id)
                ->with('success', 'Status diperbarui.')
                ->with('sheet_synced', $sheetSynced)
                ->with('sheet_sync_error', $sheetError);
        }

        return response()->json($ticket);
    }

    public function addAction(Request $request, $id)
    {
        $request->validate([
            'action_type' => 'required|string',
            'description' => 'required|string',
        ]);

        $ticket = Ticket::findOrFail($id);

        $action = TicketAction::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'action_type' => $request->action_type,
            'description' => $request->description,
            'old_status' => $request->old_status,
            'new_status' => $request->new_status,
        ]);

        if ($request->has('new_status') && $request->new_status) {
            $ticket->update(['status_ticket' => $request->new_status]);
        }

        set_time_limit(65);
        [$sheetSynced, $sheetError] = $this->syncTicketToSheets($ticket->fresh());

        if (! $request->expectsJson()) {
            return redirect()->back()
                ->with('success', 'Aksi ditambahkan.')
                ->with('sheet_synced', $sheetSynced)
                ->with('sheet_sync_error', $sheetError);
        }

        $response = $action->load('user')->toArray();
        $response['sheet_synced'] = $sheetSynced;
        if ($sheetError !== null) {
            $response['sheet_sync_error'] = $sheetError;
        }
        return response()->json($response, 201);
    }

    /**
     * Sinkronkan tiket ke Google Sheets (realtime).
     * Mengembalikan [berhasil, pesan_error]. Pesan error hanya diisi saat APP_DEBUG=true.
     */
    private function syncTicketToSheets(Ticket $ticket): array
    {
        if (! config('services.google.sync_enabled', true)) {
            return [false, 'Sinkron ke Google Sheets dinonaktifkan (GOOGLE_SHEETS_SYNC_ENABLED=false).'];
        }
        try {
            app(GoogleSheetsService::class)->syncTicket($ticket);
            return [true, null];
        } catch (\Throwable $e) {
            $message = $e->getMessage();
            Log::warning('Sync ticket ke Google Sheets gagal', [
                'ticket_id' => $ticket->ticket_id,
                'message' => $message,
            ]);
            $errorForResponse = config('app.debug') ? $message : null;

            return [false, $errorForResponse];
        }
    }

    private function determineEscalation($skor)
    {
        if ($skor >= 90) return 'KRITIS';
        if ($skor >= 70) return 'Tinggi';
        if ($skor >= 50) return 'Sedang';
        return 'Normal';
    }
}
