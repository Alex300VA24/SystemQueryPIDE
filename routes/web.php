<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DniPdfController;
use App\Http\Controllers\PartidaRegistralPdfController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('auth')->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::post('consulta/dni/pdf', DniPdfController::class)->name('consulta.dni.pdf');
    Route::post('consulta/partida/pdf', PartidaRegistralPdfController::class)->name('consulta.partida.pdf');
});

require __DIR__.'/auth.php';
