<?php

use App\Http\Controllers\Api\WahaWebhookController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CobrancaController;
use App\Http\Controllers\ContratoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MovimentacaoController;
use App\Http\Controllers\PagamentoController;
use App\Http\Controllers\SearchController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

Route::post('api/whatsapp/waha', WahaWebhookController::class)
    ->withoutMiddleware([ValidateCsrfToken::class])
    ->name('api.whatsapp.waha');

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('api/search', [SearchController::class, 'search'])->name('search');
    Route::get('cobrancas', [CobrancaController::class, 'index'])->name('cobrancas.index');
    Route::post('cobrancas/{contrato}/confirmar', [CobrancaController::class, 'confirmar'])
        ->name('cobrancas.confirmar');

    Route::resource('contratos', ContratoController::class);
    Route::post('contratos/{contrato}/devolver', [ContratoController::class, 'devolver'])
        ->name('contratos.devolver');
    Route::post('contratos/{contrato}/finalizar', [ContratoController::class, 'finalizar'])
        ->name('contratos.finalizar');
    Route::post('contratos/{contrato}/quitar', [ContratoController::class, 'quitar'])
        ->name('contratos.quitar');
    Route::resource('movimentacoes', MovimentacaoController::class)
        ->except(['show'])
        ->parameters(['movimentacoes' => 'movimentacao']);
    Route::resource('pagamentos', PagamentoController::class)->except(['show']);
    Route::post('clientes/rapido', [ClienteController::class, 'storeRapido'])
        ->name('clientes.rapido');
    Route::resource('clientes', ClienteController::class)->only([
        'index',
        'create',
        'store',
        'edit',
        'update',
        'destroy',
    ]);
    Route::post(
        '/cobrancas/enviar-whatsapp',
        [CobrancaController::class, 'enviarRelatorioWhatsApp']
    );
});

require __DIR__.'/settings.php';
