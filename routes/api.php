<?php

use App\Http\Controllers\Api\AgentController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\ContratoController;
use App\Http\Controllers\Api\PagamentoController;
use App\Http\Controllers\Api\WhatsappMessageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes for n8n Integration
|--------------------------------------------------------------------------
|
| Endpoints para integração com n8n e WhatsApp
| Todas as rotas requerem autenticação via Bearer Token (X-API-Key header)
|
| Exemplo de uso:
| curl -H "X-API-Key: seu-token-aqui" https://app.com/api/contratos
|
*/

Route::middleware('api')->group(function () {
    // Rotas públicas (sem autenticação) - descomente se necessário
    // Route::get('/health', fn () => response()->json(['status' => 'ok']));

    // Rotas protegidas por API Key
    Route::middleware('api.auth')->group(function () {
        // === CONTRATOS ===
        Route::prefix('contratos')->group(function () {
            Route::get('/', [ContratoController::class, 'index'])
                ->name('api.contratos.index');
            Route::get('/{id}', [ContratoController::class, 'show'])
                ->name('api.contratos.show');
            Route::get('/cliente/{clienteId}', [ContratoController::class, 'byCliente'])
                ->name('api.contratos.by-cliente');
            Route::get('/search/{termo}', [ContratoController::class, 'search'])
                ->name('api.contratos.search');
        });

        // === CLIENTES ===
        Route::prefix('clientes')->group(function () {
            Route::get('/', [ClienteController::class, 'index'])
                ->name('api.clientes.index');
            Route::get('/{id}', [ClienteController::class, 'show'])
                ->name('api.clientes.show');
            Route::get('/telefone/{telefone}', [ClienteController::class, 'byTelefone'])
                ->name('api.clientes.by-telefone');
            Route::get('/search/{termo}', [ClienteController::class, 'search'])
                ->name('api.clientes.search');
        });

        // === PAGAMENTOS ===
        Route::prefix('pagamentos')->group(function () {
            Route::get('/{contratoId}', [PagamentoController::class, 'byContrato'])
                ->name('api.pagamentos.by-contrato');
            Route::post('/', [PagamentoController::class, 'store'])
                ->name('api.pagamentos.store');
        });

        // === AGENTE DE IA (para n8n) ===
        Route::post('/agent/reply', [AgentController::class, 'reply'])
            ->name('api.agent.reply');

        // === WHATSAPP MESSAGES (para n8n) ===
        Route::prefix('whatsapp')->group(function () {
            Route::get('/messages/unprocessed', [WhatsappMessageController::class, 'unprocessed'])
                ->name('api.whatsapp.messages.unprocessed');
            Route::post('/messages/{id}/mark-processed', [WhatsappMessageController::class, 'markProcessed'])
                ->name('api.whatsapp.messages.mark-processed');
        });
    });
});
