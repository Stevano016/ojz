<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes (Blade — untuk shared hosting tanpa Node/Vite)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/locale/{locale}', function (string $locale) {
    if (in_array($locale, ['id', 'en', 'nl'], true)) {
        session()->put('locale', $locale);
        session()->save();
    }
    return redirect()->back();
})->name('locale.switch');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'loginWeb']);
});

Route::post('/logout', [AuthController::class, 'logoutWeb'])->name('logout')->middleware('auth');

Route::get('/track', [TicketController::class, 'trackPage'])->name('track');
Route::get('/track/{ticket_id}', [TicketController::class, 'trackPage'])->name('track.show');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/new', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{id}', [TicketController::class, 'show'])->name('tickets.show');
    Route::put('/tickets/{id}', [TicketController::class, 'update'])->name('tickets.update');
    Route::post('/tickets/{id}/actions', [TicketController::class, 'addAction'])->name('tickets.actions');

    Route::get('/export/tickets/rekap', [ExportController::class, 'rekap'])->name('export.rekap');
    Route::get('/export/tickets/per-ticket', [ExportController::class, 'perTicket'])->name('export.per-ticket');
    Route::post('/export/tickets/per-ticket', [ExportController::class, 'perTicket'])->name('export.per-ticket.post');
});
