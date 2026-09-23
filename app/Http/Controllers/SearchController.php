<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Contrato;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $termo = $request->query('q', '');

        if (strlen($termo) < 2) {
            return response()->json([
                'clientes' => [],
                'contratos' => [],
            ]);
        }

        $clientes = Cliente::query()
            ->where('nome', 'like', "%{$termo}%")
            ->orWhere('telefone', 'like', "%{$termo}%")
            ->limit(8)
            ->get(['id', 'nome', 'telefone'])
            ->map(fn (Cliente $c) => [
                'id' => $c->id,
                'nome' => $c->nome,
                'telefone' => $c->telefone,
                'type' => 'cliente',
            ]);

        $contratos = Contrato::query()
            ->with('cliente:id,nome')
            ->where('endereco', 'like', "%{$termo}%")
            ->orWhereHas('cliente', fn ($q) => $q->where('nome', 'like', "%{$termo}%"))
            ->limit(8)
            ->get(['id', 'cliente_id', 'endereco', 'status'])
            ->map(fn (Contrato $c) => [
                'id' => $c->id,
                'nome' => "#{$c->id} - {$c->cliente?->nome}",
                'endereco' => $c->endereco,
                'type' => 'contrato',
            ]);

        return response()->json([
            'clientes' => $clientes,
            'contratos' => $contratos,
        ]);
    }
}
