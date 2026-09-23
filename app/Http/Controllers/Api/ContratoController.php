<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contrato;
use Illuminate\Http\JsonResponse;

class ContratoController extends Controller
{
    /**
     * Lista todos os contratos.
     */
    public function index(): JsonResponse
    {
        $contratos = Contrato::query()
            ->with(['cliente:id,nome,telefone', 'movimentacoes', 'pagamentos'])
            ->latest('id')
            ->get()
            ->map(function (Contrato $c) {
                $c->sincronizarStatus();

                return [
                    'id' => $c->id,
                    'cliente_id' => $c->cliente_id,
                    'cliente' => $c->cliente ? [
                        'id' => $c->cliente->id,
                        'nome' => $c->cliente->nome,
                        'telefone' => $c->cliente->telefone,
                    ] : null,
                    'endereco' => $c->endereco,
                    'status' => $c->status,
                    'data_inicio' => $c->data_inicio,
                    'data_fim' => $c->data_fim,
                    'valor_pc_dia' => $c->valor_pc_dia,
                    'qtd_frete' => $c->qtd_frete,
                    'valor_frete' => $c->valor_frete,
                    'total' => 0,
                    'total_pago' => round((float) $c->pagamentos()->sum('valor'), 2),
                    'pecas_atuais' => $c->pecasAtuais(),
                    'proxima_cobranca' => $c->proxima_cobranca,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $contratos,
        ]);
    }

    /**
     * Obtém detalhes de um contrato específico.
     */
    public function show(int $id): JsonResponse
    {
        $contrato = Contrato::query()
            ->with(['cliente', 'movimentacoes', 'pagamentos'])
            ->find($id);

        if (! $contrato) {
            return response()->json([
                'status' => 'error',
                'message' => 'Contrato não encontrado',
            ], 404);
        }

        $contrato->sincronizarStatus();

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $contrato->id,
                'cliente' => $contrato->cliente,
                'endereco' => $contrato->endereco,
                'status' => $contrato->status,
                'data_inicio' => $contrato->data_inicio,
                'data_fim' => $contrato->data_fim,
                'valor_pc_dia' => $contrato->valor_pc_dia,
                'qtd_frete' => $contrato->qtd_frete,
                'valor_frete' => $contrato->valor_frete,
                'total' => round($contrato->totalCalculado(), 2),
                'total_pago' => round((float) $contrato->pagamentos()->sum('valor'), 2),
                'saldo' => round($contrato->totalCalculado() - (float) $contrato->pagamentos()->sum('valor'), 2),
                'pecas_atuais' => $contrato->pecasAtuais(),
                'proxima_cobranca' => $contrato->proxima_cobranca,
                'obs' => $contrato->obs,
                'movimentacoes' => $contrato->movimentacoes,
                'pagamentos' => $contrato->pagamentos,
            ],
        ]);
    }

    /**
     * Lista contratos de um cliente.
     */
    public function byCliente(int $clienteId): JsonResponse
    {
        $contratos = Contrato::query()
            ->with(['cliente', 'movimentacoes', 'pagamentos'])
            ->where('cliente_id', $clienteId)
            ->latest('id')
            ->get()
            ->map(function (Contrato $c) {
                $c->sincronizarStatus();

                return [
                    'id' => $c->id,
                    'status' => $c->status,
                    'endereco' => $c->endereco,
                    'data_inicio' => $c->data_inicio,
                    'data_fim' => $c->data_fim,
                    'total' => round($c->totalCalculado(), 2),
                    'total_pago' => round((float) $c->pagamentos()->sum('valor'), 2),
                    'saldo' => round($c->totalCalculado() - (float) $c->pagamentos()->sum('valor'), 2),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $contratos,
        ]);
    }

    /**
     * Busca contratos por termo.
     */
    public function search(string $termo): JsonResponse
    {
        $contratos = Contrato::query()
            ->with(['cliente', 'movimentacoes', 'pagamentos'])
            ->whereHas('cliente', fn ($q) => $q->where('nome', 'like', "%{$termo}%"))
            ->orWhere('endereco', 'like', "%{$termo}%")
            ->latest('id')
            ->limit(10)
            ->get()
            ->map(function (Contrato $c) {
                $c->sincronizarStatus();

                return [
                    'id' => $c->id,
                    'cliente' => $c->cliente?->nome,
                    'endereco' => $c->endereco,
                    'status' => $c->status,
                    'total' => round($c->totalCalculado(), 2),
                    'total_pago' => round((float) $c->pagamentos()->sum('valor'), 2),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $contratos,
        ]);
    }
}
