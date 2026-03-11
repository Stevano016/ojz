<?php

use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// POST /api/tickets: didaftarkan di sini agar selalu terbaca (workaround 404 saat dipanggil dari SPA)
Route::post('/api/tickets', [TicketController::class, 'store'])
    ->middleware('auth:sanctum');

// SPA fallback: agar link /track, /track/{id}, /login, dll dari WA/public bisa dibuka langsung
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '^(track|login|dashboard|tickets).*');
