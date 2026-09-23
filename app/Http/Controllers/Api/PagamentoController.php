<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contrato;
use App\Models\Pagamento;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagamentoController extends Controller
{
    /**
     * Lista pagamentos de um contrato.
     */
    public function byContrato(int $contratoId): JsonResponse
    {
        $contrato = Contrato::find($contratoId);

        if (! $contrato) {
            return response()->json([
                'status' => 'error',
                'message' => 'Contrato não encontrado',
            ], 404);
        }

        $pagamentos = Pagamento::query()
            ->where('contrato_id', $contratoId)
            ->latest('data')
            ->get(['id', 'data', 'valor']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'contrato_id' => $contratoId,
                'cliente' => $contrato->cliente?->nome,
                'total_contrato' => round($contrato->totalCalculado(), 2),
                'total_pago' => round((float) $contrato->pagamentos()->sum('valor'), 2),
                'saldo' => round($contrato->totalCalculado() - (float) $contrato->pagamentos()->sum('valor'), 2),
                'pagamentos' => $pagamentos,
            ],
        ]);
    }

    /**
     * Registra um novo pagamento.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'contrato_id' => ['required', 'exists:contratos,id'],
            'valor' => ['required', 'numeric', 'min:0.01'],
            'data' => ['required', 'date'],
        ]);

        try {
            DB::transaction(function () use ($validated) {
                Pagamento::create($validated);
                $contrato = Contrato::find($validated['contrato_id']);
                $contrato->sincronizarStatus();
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Pagamento registrado com sucesso',
                'data' => [
                    'contrato_id' => $validated['contrato_id'],
                    'valor' => $validated['valor'],
                    'data' => $validated['data'],
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao registrar pagamento: '.$e->getMessage(),
            ], 500);
        }
    }
}
