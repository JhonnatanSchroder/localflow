<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\JsonResponse;

class ClienteController extends Controller
{
    /**
     * Lista todos os clientes.
     */
    public function index(): JsonResponse
    {
        $clientes = Cliente::all(['id', 'nome', 'cpf', 'telefone', 'endereco', 'status']);

        return response()->json([
            'status' => 'success',
            'data' => $clientes,
        ]);
    }

    /**
     * Obtém detalhes de um cliente.
     */
    public function show(int $id): JsonResponse
    {
        $cliente = Cliente::find($id);

        if (! $cliente) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cliente não encontrado',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $cliente,
        ]);
    }

    /**
     * Busca cliente por telefone.
     */
    public function byTelefone(string $telefone): JsonResponse
    {
        // Remove caracteres não numéricos
        $telefoneDigitos = preg_replace('/\D/', '', $telefone);

        $cliente = Cliente::where('telefone', 'like', "%{$telefoneDigitos}%")->first();

        if (! $cliente) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cliente não encontrado',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $cliente,
        ]);
    }

    /**
     * Busca clientes por termo.
     */
    public function search(string $termo): JsonResponse
    {
        $clientes = Cliente::query()
            ->where('nome', 'like', "%{$termo}%")
            ->orWhere('telefone', 'like', "%{$termo}%")
            ->orWhere('cpf', 'like', "%{$termo}%")
            ->limit(10)
            ->get(['id', 'nome', 'cpf', 'telefone', 'endereco']);

        return response()->json([
            'status' => 'success',
            'data' => $clientes,
        ]);
    }
}
