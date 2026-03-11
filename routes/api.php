<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the Application's routing configuration
| (see bootstrap/app.php) and are prefixed with /api by default.
|
*/

// Auth
Route::post('/login', [AuthController::class, 'login']);

// Public: tracking tiket untuk pelapor (tanpa login)
Route::get('/tickets/track/{ticket_id}', [TicketController::class, 'track']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Tickets CRUD & actions
    Route::get('/tickets', [TicketController::class, 'index']);
    Route::post('/tickets', [TicketController::class, 'store']);
    Route::get('/tickets/{id}', [TicketController::class, 'show']);
    Route::put('/tickets/{id}', [TicketController::class, 'update']);
    Route::post('/tickets/{id}/actions', [TicketController::class, 'addAction']);

    // Dashboard statistics
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // Export Excel (rekap & per ticket; per-ticket bisa GET ?ids=1,2,3 atau POST { "ids": [1,2,3] })
    Route::get('/export/tickets/rekap', [ExportController::class, 'rekap']);
    Route::get('/export/tickets/per-ticket', [ExportController::class, 'perTicket']);
    Route::post('/export/tickets/per-ticket', [ExportController::class, 'perTicket']);
});

// n8n webhook (unauthenticated)
Route::post('/webhook/n8n', [WebhookController::class, 'n8n']);

